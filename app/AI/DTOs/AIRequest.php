<?php

namespace App\AI\DTOs;

readonly class AIRequest
{
    /**
     * @param  list<array<string, mixed>>  $messages
     * @param  list<array<string, mixed>>  $tools
     */
    public function __construct(
        public array $messages,
        public array $tools = [],
        public bool $stream = false,
    ) {}
}
