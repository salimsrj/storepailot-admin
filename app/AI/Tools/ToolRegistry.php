<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\Tools\Contracts\Tool;
use Illuminate\Support\Collection;

class ToolRegistry
{
    /**
     * @param  iterable<int, Tool>  $tools
     */
    public function __construct(private iterable $tools) {}

    /**
     * @return Collection<int, Tool>
     */
    public function enabled(AgentContext $context): Collection
    {
        return Collection::make($this->tools)
            ->filter(fn (Tool $tool): bool => $tool->enabled($context))
            ->values();
    }

    public function find(string $name, AgentContext $context): ?Tool
    {
        return $this->enabled($context)->first(
            fn (Tool $tool): bool => $tool->name() === $name,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function definitions(AgentContext $context): array
    {
        return $this->enabled($context)->map(fn (Tool $tool): array => [
            'type' => 'function',
            'function' => [
                'name' => $tool->name(),
                'description' => $tool->description(),
                'parameters' => $tool->schema(),
            ],
        ])->all();
    }
}
