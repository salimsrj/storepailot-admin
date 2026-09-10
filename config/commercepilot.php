<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | The default AI provider and model used by CommerceAgent. Application
    | code must read these values through config() rather than env().
    |
    */

    'ai' => [
        'provider' => env('COMMERCEPILOT_AI_PROVIDER', 'openai'),
        'model' => env('COMMERCEPILOT_AI_MODEL', 'gpt-4o-mini'),
        'gemini_model' => env('COMMERCEPILOT_GEMINI_MODEL', 'gemini-3.8-flash'),
        'timeout' => (int) env('COMMERCEPILOT_AI_TIMEOUT', 30),
        'max_tool_iterations' => (int) env('COMMERCEPILOT_MAX_TOOL_ITERATIONS', 5),
        'max_context_messages' => (int) env('COMMERCEPILOT_MAX_CONTEXT_MESSAGES', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Greeting shortcuts
    |--------------------------------------------------------------------------
    |
    | Whole-message greetings skip the AI provider and return a ready reply.
    |
    */

    'greetings' => [
        'phrases' => [
            'hi',
            'hello',
            'hey',
            'how are you',
            'good morning',
            'good afternoon',
            'good evening',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | WordPress / WooCommerce
    |--------------------------------------------------------------------------
    */

    'wordpress' => [
        'timeout' => (int) env('COMMERCEPILOT_WORDPRESS_TIMEOUT', 10),
        'retry_times' => (int) env('COMMERCEPILOT_WORDPRESS_RETRY_TIMES', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'visitor_per_minute' => (int) env('COMMERCEPILOT_VISITOR_RATE_LIMIT', 20),
        'site_per_minute' => (int) env('COMMERCEPILOT_SITE_RATE_LIMIT', 100),
        'site_per_day' => (int) env('COMMERCEPILOT_SITE_DAILY_RATE_LIMIT', 500),

        // Conversation reads and handover polling are plain database queries with
        // no AI cost, so they get a separate, far more generous bucket. Sharing the
        // chat limits above would let polling exhaust the daily allowance.
        'poll_site_per_minute' => (int) env('COMMERCEPILOT_POLL_RATE_LIMIT', 600),
    ],

    /*
    |--------------------------------------------------------------------------
    | HMAC
    |--------------------------------------------------------------------------
    */

    'hmac' => [
        'timestamp_tolerance_seconds' => (int) env('COMMERCEPILOT_HMAC_TIMESTAMP_TOLERANCE', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Billing records are never deleted by retention jobs.
    |
    */

    'retention' => [
        'conversation_days' => (int) env('COMMERCEPILOT_CONVERSATION_RETENTION_DAYS', 90),
        'api_log_days' => (int) env('COMMERCEPILOT_API_LOG_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing
    |--------------------------------------------------------------------------
    */

    'billing' => [
        'upgrade_url' => env('COMMERCEPILOT_UPGRADE_URL', 'https://app.commercepilot.com/upgrade'),
        'default_currency' => env('COMMERCEPILOT_DEFAULT_CURRENCY', 'USD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Site Tokens
    |--------------------------------------------------------------------------
    */

    'site_tokens' => [
        'prefix' => env('COMMERCEPILOT_SITE_TOKEN_PREFIX', 'cp_live_'),
        'length' => (int) env('COMMERCEPILOT_SITE_TOKEN_LENGTH', 48),
    ],

];
