<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\Tools\Concerns\ValidatesToolArguments;
use App\AI\Tools\Contracts\Tool;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;

class GetProductTool implements Tool
{
    use ValidatesToolArguments;

    public function __construct(private WooCommerceClientInterface $woocommerce) {}

    public function name(): string
    {
        return 'get_product';
    }

    public function description(): string
    {
        return 'Get verified details for a single WooCommerce product by ID.';
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

        return $this->woocommerce->getProduct($context->site, (int) $data['product_id'])->toArray();
    }
}
