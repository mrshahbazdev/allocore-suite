<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    ],

    'bank' => [
        'account_holder' => env('BANK_ACCOUNT_HOLDER', ''),
        'iban' => env('BANK_IBAN', ''),
        'bic' => env('BANK_BIC', ''),
        'bank_name' => env('BANK_NAME', ''),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 120),
        'max_retries' => (int) env('GEMINI_MAX_RETRIES', 3),
        'retry_base_delay_ms' => (int) env('GEMINI_RETRY_BASE_DELAY_MS', 1500),
        'max_output_tokens' => (int) env('GEMINI_MAX_OUTPUT_TOKENS', 8192),
        'fallback_models' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'GEMINI_FALLBACK_MODELS',
            'gemini-2.5-flash,gemini-2.0-flash,gemini-flash-latest'
        ))))),
    ],

    'dataforseo' => [
        'base_url' => env('DATAFORSEO_API_URL', 'https://api.dataforseo.com'),
        'login' => env('DATAFORSEO_LOGIN'),
        'password' => env('DATAFORSEO_PASSWORD'),
        'timeout' => (int) env('DATAFORSEO_TIMEOUT', 30),
        'cache_ttl' => (int) env('DATAFORSEO_CACHE_TTL', 86400),
    ],

    'ssl' => [
        'command' => env('SERVICES_SSL_COMMAND', ''),
        'renewal_days' => env('SERVICES_SSL_RENEWAL_DAYS', 14),
    ],

];
