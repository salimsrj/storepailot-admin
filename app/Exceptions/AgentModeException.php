<?php

namespace App\Exceptions;

class AgentModeException extends CommercePilotException
{
    public static function subscriptionRequired(): self
    {
        return new self(
            'An active subscription is required to enable Agent Mode.',
            'agent_subscription_required',
            422,
        );
    }

    public static function disabled(): self
    {
        return new self(
            'Agent Mode is disabled for this site. Direct messaging only.',
            'agent_mode_disabled',
            422,
        );
    }
}
