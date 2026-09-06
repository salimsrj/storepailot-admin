<?php

namespace App\AI\Tools\Contracts;

use App\AI\Agent\AgentContext;

interface Tool
{
    public function name(): string;

    public function description(): string;

    /**
     * @return array<string, mixed>
     */
    public function schema(): array;

    public function enabled(AgentContext $context): bool;

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function execute(AgentContext $context, array $arguments): array;
}
