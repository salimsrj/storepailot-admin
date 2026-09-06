<?php

namespace App\AI\Contracts;

use App\AI\DTOs\AIRequest;
use App\AI\DTOs\AIResponse;
use Generator;

interface AIProviderInterface
{
    public function complete(AIRequest $request): AIResponse;

    /**
     * @return Generator<int, array{event: string, data: array<string, mixed>}>
     */
    public function stream(AIRequest $request): Generator;

    public function model(): string;
}
