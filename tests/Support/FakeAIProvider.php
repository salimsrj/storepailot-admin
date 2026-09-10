<?php

namespace Tests\Support;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIRequest;
use App\AI\DTOs\AIResponse;
use Generator;

class FakeAIProvider implements AIProviderInterface
{
    /**
     * @var list<AIResponse>
     */
    public array $queue = [];

    public function push(AIResponse $response): self
    {
        $this->queue[] = $response;

        return $this;
    }

    public function name(): string
    {
        return 'fake';
    }

    public function complete(AIRequest $request): AIResponse
    {
        return array_shift($this->queue) ?? new AIResponse(
            content: 'I found these running shoes for you.',
            toolCalls: [],
            inputTokens: 12,
            outputTokens: 9,
            model: 'fake-model',
            providerResponseId: 'resp_fake',
        );
    }

    public function stream(AIRequest $request): Generator
    {
        $response = $this->complete($request);

        yield ['event' => 'message.start', 'data' => []];
        yield ['event' => 'message.delta', 'data' => ['content' => $response->content]];
        yield ['event' => 'message.complete', 'data' => ['content' => $response->content]];
    }

    public function model(): string
    {
        return 'fake-model';
    }
}
