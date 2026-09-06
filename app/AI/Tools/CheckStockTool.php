<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\Tools\Concerns\ValidatesToolArguments;
use App\AI\Tools\Contracts\Tool;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;

class CheckStockTool implements Tool
{
    use ValidatesToolArguments;

    public function __construct(private WooCommerceClientInterface $woocommerce) {}

    public function name(): string
    {
        return 'check_stock';
    }

    public function description(): string
    {
        return 'Check verified stock for a product or variation.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'product_id' => ['type' => 'integer'],
                'variation_id' => ['type' => 'integer'],
            ],
            'required' => ['product_id'],
        ];
    }

    public function enabled(AgentContext $context): bool
    {
        return true;
    }

    public function execute(AgentContext $context, array $arguments): array
    {
        $data = $this->validatedArguments($this->name(), $arguments, [
            'product_id' => ['required', 'integer', 'min:1'],
            'variation_id' => ['nullable', 'integer', 'min:1'],
        ]);

        return $this->woocommerce->checkStock(
            $context->site,
            (int) $data['product_id'],
            isset($data['variation_id']) ? (int) $data['variation_id'] : null,
        );
    }
}
