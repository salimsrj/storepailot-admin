<?php

namespace App\Exceptions;

class WooCommerceException extends CommercePilotException
{
    public static function unavailable(): self
    {
        return new self('The store is temporarily unavailable.', 'woocommerce_unavailable', 503);
    }

    public static function invalidResponse(): self
    {
        return new self('The store returned an invalid response.', 'woocommerce_invalid_response', 502);
    }
}
