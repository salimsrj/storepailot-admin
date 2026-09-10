<?php

namespace App\AI\DTOs;

readonly class ToolCall
{
    /**
     * @param  array<string, mixed>  $arguments
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $arguments,
        public ?string $thoughtSignature = null,
    ) {}
}
