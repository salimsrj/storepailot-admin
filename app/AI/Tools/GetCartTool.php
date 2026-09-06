<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\Tools\Concerns\ValidatesToolArguments;
use App\AI\Tools\Contracts\Tool;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;

class GetCartTool implements Tool
{
    use ValidatesToolArguments;

    public function __construct(private WooCommerceClientInterface $woocommerce) {}

    public function name(): string
    {
        return 'get_cart';
    }

    public function description(): string
    {
        return 'Get the visitor cart from WooCommerce.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => new \stdClass,
        ];
    }

    public function enabled(AgentContext $context): bool
    {
        return $context->settings->enable_cart;
    }

    public function execute(AgentContext $context, array $arguments): array
    {
        $this->validatedArguments($this->name(), $arguments, []);

        return $this->woocommerce->getCart($context->site, $context->visitor->uuid)->toArray();
    }
}
