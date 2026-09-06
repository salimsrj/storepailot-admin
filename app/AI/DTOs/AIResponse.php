<?php

namespace App\AI\DTOs;

readonly class AIResponse
{
    /**
     * @param  list<ToolCall>  $toolCalls
     */
    public function __construct(
        public ?string $content,
        public array $toolCalls,
        public int $inputTokens,
        public int $outputTokens,
        public string $model,
        public ?string $providerResponseId = null,
    ) {}

    public function hasToolCalls(): bool
    {
        return $this->toolCalls !== [];
    }
}
