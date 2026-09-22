<?php

namespace App\AI\Agent;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIRequest;
use App\AI\DTOs\AIResponse;
use App\AI\DTOs\ToolCall;
use App\AI\Tools\ToolExecutor;
use App\AI\Tools\ToolRegistry;
use App\Enums\MessageRole;
use App\Jobs\RecordUsageAnalytics;
use App\Jobs\SummarizeConversation;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Ai\AiSettingsService;
use Illuminate\Support\Collection;

class CommerceAgent
{
    public function __construct(
        private AIProviderInterface $provider,
        private PromptBuilder $prompts,
        private ToolRegistry $tools,
        private ToolExecutor $executor,
        private AiSettingsService $settings,
    ) {}

    /**
     * @return array{message: Message, products: list<array<string, mixed>>, response: AIResponse}
     */
    public function handle(AgentContext $context): array
    {
        $this->storeMessage($context->conversation, MessageRole::User, $context->message);

        $messages = $this->buildMessages($context);
        $products = [];
        $inputTokens = 0;
        $outputTokens = 0;
        $providerResponseId = null;
        $finalContent = CustomerLanguage::fallbackReply($context->message);

        $maxIterations = $this->settings->maxToolIterations();

        for ($iteration = 0; $iteration <= $maxIterations; $iteration++) {
            $response = $this->provider->complete(new AIRequest(
                messages: $messages,
                tools: $this->tools->definitions($context),
            ));

            $inputTokens += $response->inputTokens;
            $outputTokens += $response->outputTokens;
            $providerResponseId = $response->providerResponseId ?? $providerResponseId;

            if (! $response->hasToolCalls()) {
                $finalContent = $response->content ?: $finalContent;
                break;
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $response->content,
                'tool_calls' => array_map(function (ToolCall $call): array {
                    $payload = [
                        'id' => $call->id,
                        'type' => 'function',
                        'function' => [
                            'name' => $call->name,
                            'arguments' => json_encode($call->arguments, JSON_THROW_ON_ERROR),
                        ],
                    ];

                    if ($call->thoughtSignature !== null && $call->thoughtSignature !== '') {
                        $payload['thought_signature'] = $call->thoughtSignature;
                    }

                    return $payload;
                }, $response->toolCalls),
            ];

            foreach ($response->toolCalls as $call) {
                $metadata = ['arguments' => $call->arguments];

                if ($call->thoughtSignature !== null && $call->thoughtSignature !== '') {
                    $metadata['thought_signature'] = $call->thoughtSignature;
                }

                $this->storeMessage(
                    $context->conversation,
                    MessageRole::Assistant,
                    $response->content ?: '',
                    $call->name,
                    $call->id,
                    metadata: $metadata,
                );

                $result = $this->executor->execute($context, $call);
                $products = $this->extractProducts($products, $result);

                $this->storeMessage(
                    $context->conversation,
                    MessageRole::Tool,
                    (string) json_encode($result, JSON_THROW_ON_ERROR),
                    $call->name,
                    $call->id,
                );

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call->id,
                    'content' => json_encode($result, JSON_THROW_ON_ERROR),
                ];
            }

            if ($iteration === $maxIterations) {
                break;
            }
        }

        $assistant = $this->storeMessage(
            $context->conversation,
            MessageRole::Assistant,
            $finalContent,
            null,
            null,
            $inputTokens,
            $outputTokens,
            $providerResponseId,
        );

        $context->conversation->forceFill([
            'message_count' => $context->conversation->messages()->count(),
            'last_message_at' => now(),
        ])->save();

        RecordUsageAnalytics::dispatch(
            $context->site->id,
            $context->conversation->id,
            $inputTokens,
            $outputTokens,
            $this->provider->model(),
        );

        if ($context->conversation->message_count >= $this->settings->maxContextMessages()) {
            SummarizeConversation::dispatch($context->conversation->id);
        }

        return [
            'message' => $assistant,
            'products' => $products,
            'response' => new AIResponse(
                $finalContent,
                [],
                $inputTokens,
                $outputTokens,
                $this->provider->model(),
                $providerResponseId,
            ),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildMessages(AgentContext $context): array
    {
        $limit = $this->settings->maxContextMessages();

        $history = $context->conversation
            ->messages()
            ->whereIn('role', [MessageRole::User, MessageRole::Assistant, MessageRole::Tool])
            ->orderBy('id')
            ->get()
            ->slice($limit * -1);

        $messages = [
            [
                'role' => 'system',
                'content' => $this->prompts->systemPrompt(
                    $context->site,
                    $context->settings,
                    $context->conversation->summary,
                ),
            ],
        ];

        $skippedToolCallIds = [];

        foreach ($history as $message) {
            if ($message->role === MessageRole::Assistant && $message->tool_call_id) {
                $toolCalls = $this->toolCallsFromMessage($message);

                if ($toolCalls === [] || ! $this->canReplayToolCall($message)) {
                    $skippedToolCallIds[] = $message->tool_call_id;

                    continue;
                }

                $payload = [
                    'role' => $message->role->value,
                    'content' => $message->content,
                    'tool_calls' => $toolCalls,
                ];
                $messages[] = $payload;

                continue;
            }

            if ($message->role === MessageRole::Tool && $message->tool_call_id && in_array($message->tool_call_id, $skippedToolCallIds, true)) {
                continue;
            }

            $payload = [
                'role' => $message->role->value,
                'content' => $message->content,
            ];

            if ($message->role === MessageRole::Tool && $message->tool_call_id) {
                $payload['tool_call_id'] = $message->tool_call_id;
            }

            $messages[] = $payload;
        }

        return $messages;
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    private function storeMessage(
        Conversation $conversation,
        MessageRole $role,
        string $content,
        ?string $toolName = null,
        ?string $toolCallId = null,
        ?int $inputTokens = null,
        ?int $outputTokens = null,
        ?string $providerResponseId = null,
        ?array $metadata = null,
    ): Message {
        return $conversation->messages()->create([
            'role' => $role,
            'content' => $content,
            'tool_name' => $toolName,
            'tool_call_id' => $toolCallId,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'provider_response_id' => $providerResponseId,
            'metadata' => $metadata,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function toolCallsFromMessage(Message $message): array
    {
        if ($message->tool_call_id === null || $message->tool_name === null || $message->tool_name === '') {
            return [];
        }

        $metadata = is_array($message->metadata) ? $message->metadata : [];
        $arguments = $metadata['arguments'] ?? [];

        if (! is_array($arguments)) {
            $arguments = [];
        }

        $payload = [
            'id' => $message->tool_call_id,
            'type' => 'function',
            'function' => [
                'name' => $message->tool_name,
                'arguments' => json_encode($arguments, JSON_THROW_ON_ERROR),
            ],
        ];

        $signature = $metadata['thought_signature'] ?? null;

        if (is_string($signature) && $signature !== '') {
            $payload['thought_signature'] = $signature;
        }

        return [$payload];
    }

    private function canReplayToolCall(Message $message): bool
    {
        $metadata = is_array($message->metadata) ? $message->metadata : [];
        $signature = $metadata['thought_signature'] ?? null;
        $arguments = $metadata['arguments'] ?? null;

        if (is_string($signature) && $signature !== '') {
            return true;
        }

        return is_array($arguments) && $arguments !== [];
    }

    /**
     * @param  list<array<string, mixed>>  $products
     * @param  array<string, mixed>  $result
     * @return list<array<string, mixed>>
     */
    private function extractProducts(array $products, array $result): array
    {
        if (isset($result['products']) && is_array($result['products'])) {
            return Collection::make($products)->concat($result['products'])->unique('id')->values()->all();
        }

        if (isset($result['id'], $result['name'])) {
            return Collection::make($products)->push($result)->unique('id')->values()->all();
        }

        return $products;
    }
}
