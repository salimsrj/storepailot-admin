<?php

namespace App\Exceptions;

class UsageLimitException extends CommercePilotException
{
    public static function reached(): self
    {
        return new self(
            'Your monthly message limit has been reached.',
            'usage_limit_reached',
            402,
            [
                'upgrade_required' => true,
                'upgrade_url' => config('commercepilot.billing.upgrade_url'),
            ],
        );
    }
}
