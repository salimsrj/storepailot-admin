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

class OpenAIProvider implements AIProviderInterface
{
    public function __construct(private AiSettingsService $settings) {}

    public function complete(AIRequest $request): AIResponse
    {
        $payload = $this->post($request, stream: false);
        $choice = $payload['choices'][0]['message'] ?? [];

        return new AIResponse(
            content: isset($choice['content']) ? (string) $choice['content'] : null,
            toolCalls: $this->mapToolCalls($choice['tool_calls'] ?? []),
            inputTokens: (int) ($payload['usage']['prompt_tokens'] ?? 0),
            outputTokens: (int) ($payload['usage']['completion_tokens'] ?? 0),
            model: (string) ($payload['model'] ?? $this->model()),
            providerResponseId: isset($payload['id']) ? (string) $payload['id'] : null,
        );
    }

    public function stream(AIRequest $request): Generator
    {
        yield ['event' => 'message.start', 'data' => []];

        $payload = $this->post($request, stream: false);
        $choice = $payload['choices'][0]['message'] ?? [];
        $content = (string) ($choice['content'] ?? '');

        if ($content !== '') {
            yield ['event' => 'message.delta', 'data' => ['content' => $content]];
        }

        yield [
            'event' => 'message.complete',
            'data' => [
                'content' => $content,
                'input_tokens' => (int) ($payload['usage']['prompt_tokens'] ?? 0),
                'output_tokens' => (int) ($payload['usage']['completion_tokens'] ?? 0),
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
    private function post(AIRequest $request, bool $stream): array
    {
        $apiKey = $this->settings->apiKey();

        if (! is_string($apiKey) || $apiKey === '') {
            throw AIProviderException::unavailable();
        }

        $attempts = 0;
        $maxAttempts = 3;

        while (true) {
            $attempts++;

            try {
                $http = Http::baseUrl('https://api.openai.com/v1')
                    ->withToken($apiKey)
                    ->acceptJson()
                    ->connectTimeout(5)
                    ->timeout($this->settings->timeout());

                $organization = $this->settings->organization();

                if (is_string($organization) && $organization !== '') {
                    $http = $http->withHeaders(['OpenAI-Organization' => $organization]);
                }

                $response = $http
                    ->post('/chat/completions', [
                        'model' => $this->model(),
                        'messages' => $request->messages,
                        'tools' => $request->tools === [] ? null : $request->tools,
                        'stream' => $stream,
                    ])
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
                $errorType = (string) ($error['type'] ?? $error['code'] ?? '');

                Log::channel('ai')->warning('openai.request_failed', [
                    'request_id' => RequestId::current(),
                    'model' => $this->model(),
                    'status' => 'error',
                    'http_status' => $httpStatus,
                    'error_type' => $errorType,
                    'connection' => $exception instanceof ConnectionException,
                    'attempt' => $attempts,
                ]);

                if ($httpStatus === 429 && str_contains($errorType, 'insufficient_quota')) {
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

        Log::channel('ai')->info('openai.request', [
            'request_id' => RequestId::current(),
            'model' => $this->model(),
            'input_tokens' => $json['usage']['prompt_tokens'] ?? 0,
            'output_tokens' => $json['usage']['completion_tokens'] ?? 0,
            'status' => 'ok',
        ]);

        return $json;
    }

    /**
     * @param  list<array<string, mixed>>  $toolCalls
     * @return list<ToolCall>
     */
    private function mapToolCalls(array $toolCalls): array
    {
        $mapped = [];

        foreach ($toolCalls as $call) {
            $arguments = json_decode((string) ($call['function']['arguments'] ?? '{}'), true);

            $mapped[] = new ToolCall(
                id: (string) ($call['id'] ?? ''),
                name: (string) ($call['function']['name'] ?? ''),
                arguments: is_array($arguments) ? $arguments : [],
            );
        }

        return $mapped;
    }
}
