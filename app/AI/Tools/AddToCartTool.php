<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\Tools\Concerns\ValidatesToolArguments;
use App\AI\Tools\Contracts\Tool;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;

class AddToCartTool implements Tool
{
    use ValidatesToolArguments;

    public function __construct(private WooCommerceClientInterface $woocommerce) {}

    public function name(): string
    {
        return 'add_to_cart';
    }

    public function description(): string
    {
        return 'Add a verified product to the WooCommerce cart.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'product_id' => ['type' => 'integer'],
                'quantity' => ['type' => 'integer', 'minimum' => 1],
                'variation_id' => ['type' => 'integer'],
            ],
            'required' => ['product_id', 'quantity'],
        ];
    }

    public function enabled(AgentContext $context): bool
    {
        return $context->settings->enable_cart;
    }

    public function execute(AgentContext $context, array $arguments): array
    {
        $data = $this->validatedArguments($this->name(), $arguments, [
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'variation_id' => ['nullable', 'integer', 'min:1'],
        ]);

        return $this->woocommerce->addToCart($context->site, $context->visitor->uuid, $data)->toArray();
    }
}
