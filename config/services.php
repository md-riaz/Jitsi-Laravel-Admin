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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'jitsi' => [
        'domain' => env('JITSI_DOMAIN', 'meet.example.com'),
        'issuer' => env('JITSI_JWT_ISSUER', 'your-app'),
        'audience' => env('JITSI_JWT_AUDIENCE', 'jitsi'),
        'secret' => env('JITSI_JWT_SECRET', ''),
        'sub' => env('JITSI_JWT_SUB', env('JITSI_DOMAIN', 'meet.example.com')),
        'webhook_secret' => env('JITSI_WEBHOOK_SECRET', ''),
        'recording_ingest_secret' => env('JITSI_RECORDING_INGEST_SECRET', ''),
        'empty_room_grace_seconds' => (int) env('JITSI_EMPTY_ROOM_GRACE_SECONDS', 60),
    ],

];
