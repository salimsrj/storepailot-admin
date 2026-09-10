<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIRequest;
use App\AI\DTOs\AIResponse;
use App\AI\DTOs\ToolCall;
use App\Exceptions\AIProviderException;
use App\Services\Ai\AiSettingsService;
use App\Support\RequestId;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;

class GeminiProvider implements AIProviderInterface
{
    public function __construct(private AiSettingsService $settings) {}

    public function name(): string
    {
        return 'gemini';
    }

    public function complete(AIRequest $request): AIResponse
    {
        $payload = $this->post($request);
        $parts = $payload['candidates'][0]['content']['parts'] ?? [];
        $usage = is_array($payload['usageMetadata'] ?? null) ? $payload['usageMetadata'] : [];

        return new AIResponse(
            content: $this->textFromParts(is_array($parts) ? $parts : []),
            toolCalls: $this->mapToolCalls(is_array($parts) ? $parts : []),
            inputTokens: (int) ($usage['promptTokenCount'] ?? 0),
            outputTokens: (int) ($usage['candidatesTokenCount'] ?? 0),
            model: (string) ($payload['modelVersion'] ?? $this->model()),
            providerResponseId: isset($payload['responseId']) ? (string) $payload['responseId'] : null,
        );
    }

    public function stream(AIRequest $request): Generator
    {
        yield ['event' => 'message.start', 'data' => []];

        $response = $this->complete($request);
        $content = (string) ($response->content ?? '');

        if ($content !== '') {
            yield ['event' => 'message.delta', 'data' => ['content' => $content]];
        }

        yield [
            'event' => 'message.complete',
            'data' => [
                'content' => $content,
                'input_tokens' => $response->inputTokens,
                'output_tokens' => $response->outputTokens,
            ],
        ];
    }

    public function model(): string
    {
        return $this->settings->model();
    }

    /**
     * @return array<string, mixed>
     */
    private function post(AIRequest $request): array
    {
        $apiKey = $this->settings->geminiApiKey();

        if (! is_string($apiKey) || $apiKey === '') {
            throw AIProviderException::invalidApiKey('Gemini');
        }

        $attempts = 0;
        $maxAttempts = 3;
        $model = rawurlencode($this->model());

        while (true) {
            $attempts++;

            try {
                $response = Http::baseUrl('https://generativelanguage.googleapis.com/v1beta')
                    ->withHeaders(['x-goog-api-key' => $apiKey])
                    ->withQueryParameters(['key' => $apiKey])
                    ->acceptJson()
                    ->connectTimeout(5)
                    ->timeout($this->settings->timeout())
                    ->post("/models/{$model}:generateContent", $this->payload($request))
                    ->throw();

                break;
            } catch (ConnectionException|RequestException $exception) {
                $httpStatus = $exception instanceof RequestException
                    ? $exception->response?->status()
                    : null;
                $payload = $exception instanceof RequestException
                    ? $exception->response?->json()
                    : null;
                $error = is_array($payload) && is_array($payload['error'] ?? null)
                    ? $payload['error']
                    : [];
                $errorStatus = (string) ($error['status'] ?? $error['code'] ?? '');
                $errorMessage = (string) ($error['message'] ?? '');

                Log::channel('ai')->warning('gemini.request_failed', [
                    'request_id' => RequestId::current(),
                    'model' => $this->model(),
                    'status' => 'error',
                    'http_status' => $httpStatus,
                    'error_type' => $errorStatus,
                    'error_message' => $errorMessage !== '' ? substr($errorMessage, 0, 300) : null,
                    'connection' => $exception instanceof ConnectionException,
                    'attempt' => $attempts,
                ]);

                if ($this->isInvalidApiKey($httpStatus, $errorMessage)) {
                    throw AIProviderException::invalidApiKey('Gemini');
                }

                if ($httpStatus === 404 || strtoupper($errorStatus) === 'NOT_FOUND') {
                    throw AIProviderException::invalidModel($this->model());
                }

                if ($httpStatus === 429 && str_contains(strtoupper($errorStatus), 'RESOURCE_EXHAUSTED')) {
                    throw AIProviderException::quotaExceeded();
                }

                $retryable = $httpStatus === 429 && $attempts < $maxAttempts;
                if ($retryable) {
                    $retryAfter = $exception instanceof RequestException
                        ? (int) ($exception->response?->header('Retry-After') ?? 0)
                        : 0;
                    usleep(max(1, $retryAfter > 0 ? $retryAfter : $attempts) * 1_000_000);

                    continue;
                }

                throw $httpStatus === 429
                    ? AIProviderException::rateLimited()
                    : AIProviderException::unavailable();
            }
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw AIProviderException::invalidResponse();
        }

        Log::channel('ai')->info('gemini.request', [
            'request_id' => RequestId::current(),
            'model' => $this->model(),
            'input_tokens' => $json['usageMetadata']['promptTokenCount'] ?? 0,
            'output_tokens' => $json['usageMetadata']['candidatesTokenCount'] ?? 0,
            'status' => 'ok',
        ]);

        return $json;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AIRequest $request): array
    {
        $converted = $this->convertMessages($request->messages);
        $body = [
            'contents' => $converted['contents'],
        ];

        if ($converted['system'] !== null) {
            $body['systemInstruction'] = [
                'parts' => [['text' => $converted['system']]],
            ];
        }

        $declarations = $this->functionDeclarations($request->tools);
        if ($declarations !== []) {
            $body['tools'] = [['functionDeclarations' => $declarations]];
        }

        return $body;
    }

    /**
     * @param  list<array<string, mixed>>  $messages
     * @return array{system: ?string, contents: list<array<string, mixed>>}
     */
    private function convertMessages(array $messages): array
    {
        $system = null;
        $contents = [];
        $toolNames = [];
        $pendingToolParts = [];

        $flushToolParts = function () use (&$contents, &$pendingToolParts): void {
            if ($pendingToolParts === []) {
                return;
            }

            $contents[] = [
                'role' => 'user',
                'parts' => $pendingToolParts,
            ];
            $pendingToolParts = [];
        };

        foreach ($messages as $message) {
            $role = (string) ($message['role'] ?? '');

            if ($role === 'system') {
                $text = trim((string) ($message['content'] ?? ''));
                $system = $system === null ? $text : $system."\n\n".$text;

                continue;
            }

            if ($role === 'tool') {
                $callId = (string) ($message['tool_call_id'] ?? '');
                $name = $toolNames[$callId] ?? $callId;
                $pendingToolParts[] = [
                    'functionResponse' => [
                        'name' => $name !== '' ? $name : 'tool',
                        'response' => $this->decodeToolResponse($message['content'] ?? ''),
                    ],
                ];

                continue;
            }

            $flushToolParts();

            if ($role === 'assistant') {
                $parts = [];
                $text = (string) ($message['content'] ?? '');
                if ($text !== '') {
                    $parts[] = ['text' => $text];
                }

                foreach ((array) ($message['tool_calls'] ?? []) as $call) {
                    if (! is_array($call)) {
                        continue;
                    }

                    $id = (string) ($call['id'] ?? '');
                    $name = (string) ($call['function']['name'] ?? '');
                    if ($id !== '' && $name !== '') {
                        $toolNames[$id] = $name;
                    }

                    $arguments = $this->decodeArguments($call['function']['arguments'] ?? '{}');
                    $part = [
                        'functionCall' => [
                            'name' => $name,
                            'args' => $arguments,
                        ],
                    ];

                    $thoughtSignature = (string) ($call['thought_signature'] ?? '');
                    if ($thoughtSignature !== '') {
                        $part['thoughtSignature'] = $thoughtSignature;
                    }

                    $parts[] = $part;
                }

                if ($parts !== []) {
                    $contents[] = [
                        'role' => 'model',
                        'parts' => $parts,
                    ];
                }

                continue;
            }

            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => (string) ($message['content'] ?? '')]],
            ];
        }

        $flushToolParts();

        if ($contents === []) {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => '']],
            ];
        }

        return [
            'system' => $system !== null && $system !== '' ? $system : null,
            'contents' => $contents,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $tools
     * @return list<array<string, mixed>>
     */
    private function functionDeclarations(array $tools): array
    {
        $declarations = [];

        foreach ($tools as $tool) {
            $function = is_array($tool['function'] ?? null) ? $tool['function'] : $tool;
            $name = (string) ($function['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $declaration = [
                'name' => $name,
                'description' => (string) ($function['description'] ?? ''),
            ];

            if (isset($function['parameters']) && is_array($function['parameters'])) {
                $declaration['parameters'] = $this->sanitizeSchema($function['parameters']);
            }

            $declarations[] = $declaration;
        }

        return $declarations;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function sanitizeSchema(array $schema): array
    {
        unset($schema['additionalProperties']);

        if (isset($schema['properties']) && is_array($schema['properties'])) {
            $schema['properties'] = array_map(
                function (mixed $property): mixed {
                    return is_array($property) ? $this->sanitizeSchema($property) : $property;
                },
                $schema['properties'],
            );
        }

        if (isset($schema['items']) && is_array($schema['items'])) {
            $schema['items'] = $this->sanitizeSchema($schema['items']);
        }

        return $schema;
    }

    /**
     * @param  list<array<string, mixed>>  $parts
     */
    private function textFromParts(array $parts): ?string
    {
        $chunks = [];

        foreach ($parts as $part) {
            if (! is_array($part) || ! isset($part['text'])) {
                continue;
            }

            $chunks[] = (string) $part['text'];
        }

        if ($chunks === []) {
            return null;
        }

        return implode('', $chunks);
    }

    /**
     * @param  list<array<string, mixed>>  $parts
     * @return list<ToolCall>
     */
    private function mapToolCalls(array $parts): array
    {
        $mapped = [];
        $index = 0;

        foreach ($parts as $part) {
            if (! is_array($part) || ! isset($part['functionCall']) || ! is_array($part['functionCall'])) {
                continue;
            }

            $index++;
            $call = $part['functionCall'];
            $arguments = $call['args'] ?? [];
            $thoughtSignature = (string) ($part['thoughtSignature'] ?? $part['thought_signature'] ?? '');

            $mapped[] = new ToolCall(
                id: (string) ($call['id'] ?? 'call_gemini_'.$index),
                name: (string) ($call['name'] ?? ''),
                arguments: is_array($arguments) ? $arguments : [],
                thoughtSignature: $thoughtSignature !== '' ? $thoughtSignature : null,
            );
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeArguments(mixed $arguments): array
    {
        if (is_array($arguments)) {
            return $arguments;
        }

        try {
            $decoded = json_decode((string) $arguments, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeToolResponse(mixed $content): array
    {
        if (is_array($content)) {
            return $this->objectWrap($content);
        }

        try {
            $decoded = json_decode((string) $content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ['result' => (string) $content];
        }

        if (is_array($decoded)) {
            return $this->objectWrap($decoded);
        }

        return ['result' => $decoded];
    }

    private function isInvalidApiKey(?int $httpStatus, string $errorMessage): bool
    {
        if ($httpStatus === 401 || $httpStatus === 403) {
            return true;
        }

        return $httpStatus === 400 && str_contains(strtolower($errorMessage), 'api key not valid');
    }

    /**
     * @param  array<mixed>  $value
     * @return array<string, mixed>
     */
    private function objectWrap(array $value): array
    {
        if ($value === [] || array_is_list($value)) {
            return ['result' => $value];
        }

        /** @var array<string, mixed> $value */
        return $value;
    }
}
