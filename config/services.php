<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'wa_provider' => env('WA_PROVIDER', 'evolution'),

    'evolution' => [
        'url' => env('EVOLUTION_URL', 'http://localhost:8101'),
        'key' => env('EVOLUTION_API_KEY'),
        'instance' => env('EVOLUTION_INSTANCE', 'radio'),
        'webhook_url' => env('EVOLUTION_WEBHOOK_URL'),
    ],

    'stt' => [
        'driver' => env('STT_DRIVER', 'fake'),
        'assemblyai_key' => env('ASSEMBLYAI_API_KEY'),
        'deepgram_key' => env('DEEPGRAM_API_KEY'),
        'openai_key' => env('OPENAI_API_KEY'),
    ],

    'classify' => [
        'driver' => env('CLASSIFY_DRIVER', 'fake'),
        'anthropic_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('CLASSIFY_MODEL', 'claude-haiku-4-5-20251001'),
    ],

];
