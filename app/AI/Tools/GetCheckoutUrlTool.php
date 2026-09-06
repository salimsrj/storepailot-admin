<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\Tools\Concerns\ValidatesToolArguments;
use App\AI\Tools\Contracts\Tool;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;

class GetCheckoutUrlTool implements Tool
{
    use ValidatesToolArguments;

    public function __construct(private WooCommerceClientInterface $woocommerce) {}

    public function name(): string
    {
        return 'get_checkout_url';
    }

    public function description(): string
    {
        return 'Get the WooCommerce checkout URL. Do not create orders directly.';
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
        return $context->settings->enable_checkout;
    }

    public function execute(AgentContext $context, array $arguments): array
    {
        $this->validatedArguments($this->name(), $arguments, []);

        return $this->woocommerce->getCheckoutUrl($context->site, $context->visitor->uuid);
    }
}
