<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\Tools\Concerns\ValidatesToolArguments;
use App\AI\Tools\Contracts\Tool;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;

class GetProductVariationsTool implements Tool
{
    use ValidatesToolArguments;

    public function __construct(private WooCommerceClientInterface $woocommerce) {}

    public function name(): string
    {
        return 'get_product_variations';
    }

    public function description(): string
    {
        return 'Get verified WooCommerce variations for a variable product. Never guess variation IDs.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'product_id' => ['type' => 'integer'],
            ],
            'required' => ['product_id'],
        ];
    }

    public function enabled(AgentContext $context): bool
    {
        return $context->settings->enable_product_search;
    }

    public function execute(AgentContext $context, array $arguments): array
    {
        $data = $this->validatedArguments($this->name(), $arguments, [
            'product_id' => ['required', 'integer', 'min:1'],
        ]);

        return [
            'variations' => $this->woocommerce->getProductVariations($context->site, (int) $data['product_id']),
        ];
    }
}
