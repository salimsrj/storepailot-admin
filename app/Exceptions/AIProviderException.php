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
            'The AI quota for this CommercePilot account is exhausted. Add billing or a new API key in Laravel AI settings.',
            'ai_quota_exceeded',
            429,
        );
    }

    public static function invalidApiKey(?string $provider = null): self
    {
        $provider ??= 'AI';

        $hint = match (strtolower($provider)) {
            'gemini' => 'Paste a Google AI Studio key in Laravel AI settings.',
            'openai' => 'Paste an OpenAI API key in Laravel AI settings.',
            default => 'Paste a valid API key in Laravel AI settings.',
        };

        return new self(
            "The {$provider} API key is missing or not valid. {$hint}",
            'ai_invalid_api_key',
            401,
        );
    }

    public static function invalidModel(string $model): self
    {
        return new self(
            "The AI model [{$model}] is not available for the selected provider. Set a matching model in Laravel AI settings.",
            'ai_invalid_model',
            422,
        );
    }

    public static function invalidResponse(): self
    {
        return new self('The AI service returned an unusable response.', 'ai_invalid_response', 502);
    }
}
