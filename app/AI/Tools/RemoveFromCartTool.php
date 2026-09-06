<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\Tools\Concerns\ValidatesToolArguments;
use App\AI\Tools\Contracts\Tool;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;

class RemoveFromCartTool implements Tool
{
    use ValidatesToolArguments;

    public function __construct(private WooCommerceClientInterface $woocommerce) {}

    public function name(): string
    {
        return 'remove_from_cart';
    }

    public function description(): string
    {
        return 'Remove an item from the WooCommerce cart.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'item_key' => ['type' => 'string'],
            ],
            'required' => ['item_key'],
        ];
    }

    public function enabled(AgentContext $context): bool
    {
        return $context->settings->enable_cart;
    }

    public function execute(AgentContext $context, array $arguments): array
    {
        $data = $this->validatedArguments($this->name(), $arguments, [
            'item_key' => ['required', 'string', 'max:120'],
        ]);

        return $this->woocommerce->removeFromCart($context->site, $context->visitor->uuid, $data['item_key'])->toArray();
    }
}
