<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Incomplete = 'incomplete';

    public function isBillable(): bool
    {
        return in_array($this, [self::Trialing, self::Active], true);
    }
}
