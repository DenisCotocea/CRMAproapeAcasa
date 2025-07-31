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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'olx' => [
        'urn' => env('OLX_URN'),
        'client_id' => env('OLX_CLIENT_ID'),
        'client_secret' => env('OLX_CLIENT_SECRET'),
        'api_key' => env('OLX_API_KEY'),
        'basic_64' => env('OLX_BASIC_64'),
        'api_url' => env('OLX_API_URL'),
    ],

    'imobiliare' => [
        'api_url'  => env('IMOBILIARE_API_URL'),
        'api_user' => env('IMOBILIARE_API_USER'),
        'api_key'  => env('IMOBILIARE_API_KEY'),
    ],

    'romimo' => [
        'api_key' => env('ROMIMO_API_KEY'),
    ],
];
