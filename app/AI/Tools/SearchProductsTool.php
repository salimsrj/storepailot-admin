<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\Tools\Concerns\ValidatesToolArguments;
use App\AI\Tools\Contracts\Tool;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;
use Illuminate\Support\Str;

class SearchProductsTool implements Tool
{
    use ValidatesToolArguments;

    public function __construct(private WooCommerceClientInterface $woocommerce) {}

    public function name(): string
    {
        return 'search_products';
    }

    public function description(): string
    {
        return 'Search the WooCommerce catalog for real products matching a customer query.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'query' => ['type' => 'string'],
                'category' => ['type' => 'string'],
                'min_price' => ['type' => 'number'],
                'max_price' => ['type' => 'number'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10],
            ],
            'required' => ['query'],
        ];
    }

    public function enabled(AgentContext $context): bool
    {
        return $context->settings->enable_product_search;
    }

    public function execute(AgentContext $context, array $arguments): array
    {
        $data = $this->validatedArguments($this->name(), $arguments, [
            'query' => ['required', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:120'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $products = $this->woocommerce->searchProducts($context->site, [
            'query' => Str::of((string) $data['query'])->stripTags()->trim()->toString(),
            'category' => $data['category'] ?? null,
            'min_price' => $data['min_price'] ?? null,
            'max_price' => $data['max_price'] ?? null,
            'limit' => $data['limit'] ?? 5,
        ]);

        return [
            'products' => array_map(fn ($product) => $product->toArray(), $products),
        ];
    }
}
