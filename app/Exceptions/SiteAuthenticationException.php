<?php

namespace App\Exceptions;

class SiteAuthenticationException extends CommercePilotException
{
    public static function invalidToken(): self
    {
        return new self('The site token is invalid.', 'invalid_site_token', 401);
    }

    public static function inactiveSite(): self
    {
        return new self('This site is not active.', 'site_inactive', 403);
    }

    public static function missingToken(): self
    {
        return new self('A site bearer token is required.', 'missing_site_token', 401);
    }
}
