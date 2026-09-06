<?php

namespace App\Enums;

enum SiteStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Inactive = 'inactive';
    case Revoked = 'revoked';

    public function canAuthenticate(): bool
    {
        return $this === self::Active;
    }
}
