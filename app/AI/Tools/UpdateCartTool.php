<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\Tools\Concerns\ValidatesToolArguments;
use App\AI\Tools\Contracts\Tool;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;

class UpdateCartTool implements Tool
{
    use ValidatesToolArguments;

    public function __construct(private WooCommerceClientInterface $woocommerce) {}

    public function name(): string
    {
        return 'update_cart';
    }

    public function description(): string
    {
        return 'Update the quantity of an item in the WooCommerce cart.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'item_key' => ['type' => 'string'],
                'quantity' => ['type' => 'integer', 'minimum' => 0],
            ],
            'required' => ['item_key', 'quantity'],
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
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        return $this->woocommerce->updateCart($context->site, $context->visitor->uuid, $data)->toArray();
    }
}
