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
        $finalContent = 'I could not complete that request right now.';

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
                'tool_calls' => array_map(fn (ToolCall $call): array => [
                    'id' => $call->id,
                    'type' => 'function',
                    'function' => [
                        'name' => $call->name,
                        'arguments' => json_encode($call->arguments, JSON_THROW_ON_ERROR),
                    ],
                ], $response->toolCalls),
            ];

            foreach ($response->toolCalls as $call) {
                $this->storeMessage(
                    $context->conversation,
                    MessageRole::Assistant,
                    $response->content ?: '',
                    $call->name,
                    $call->id,
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

        foreach ($history as $message) {
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

    private function storeMessage(
        Conversation $conversation,
        MessageRole $role,
        string $content,
        ?string $toolName = null,
        ?string $toolCallId = null,
        ?int $inputTokens = null,
        ?int $outputTokens = null,
        ?string $providerResponseId = null,
    ): Message {
        return $conversation->messages()->create([
            'role' => $role,
            'content' => $content,
            'tool_name' => $toolName,
            'tool_call_id' => $toolCallId,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'provider_response_id' => $providerResponseId,
        ]);
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
