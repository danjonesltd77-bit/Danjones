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

    'sendgrid' => [
        'key' => env('SENDGRID_API_KEY'),
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

    'tatum' => [
        'base_url' => env('TATUM_URL', 'https://api.tatum.io'),
        'api_key' => env('TATUM_KEY'),
        'api_key_gaspump' => env('TATUM_KEY_GASPUMP'),
    ],

    'qoreid' => [
        'base_url' => env('QOREID_URL', 'https://api.qoreid.com'),
        'client_id' => env('QOREID_CLIENTID'),
        'secret' => env('QOREID_SECRET'),
    ],

    'flutterwave' => [
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
    ],

    'coingecko' => [
        'base_url' => env('COINGECKO_URL', 'https://api.coingecko.com/api/v3'),
    ],

];
