<?php

namespace App\Exceptions;

class AIProviderException extends CommercePilotException
{
    public static function unavailable(): self
    {
        return new self('The AI service is temporarily unavailable.', 'ai_unavailable', 503);
    }

    public static function rateLimited(): self
    {
        return new self('The assistant is busy right now. Please try again in a moment.', 'ai_rate_limited', 429);
    }

    public static function quotaExceeded(): self
    {
        return new self(
            'The OpenAI quota for this CommercePilot account is exhausted. Add billing or a new API key in Laravel AI settings.',
            'ai_quota_exceeded',
            429,
        );
    }

    public static function invalidResponse(): self
    {
        return new self('The AI service returned an unusable response.', 'ai_invalid_response', 502);
    }
}
