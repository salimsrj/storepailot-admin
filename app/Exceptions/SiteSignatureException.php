<?php

namespace App\Exceptions;

class SiteSignatureException extends CommercePilotException
{
    public static function missing(): self
    {
        return new self('The request signature is missing.', 'missing_signature', 401);
    }

    public static function invalid(): self
    {
        return new self('The request signature is invalid.', 'invalid_signature', 401);
    }

    public static function expired(): self
    {
        return new self('The request timestamp is outside the allowed tolerance.', 'expired_signature', 401);
    }

    public static function replayed(): self
    {
        return new self('This request has already been processed.', 'replayed_request', 401);
    }
}
