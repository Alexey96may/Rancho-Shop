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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'), // ex, https://your-domain.com/auth/google/callback
    ],

    'payment' => [
        'driver' => env('PAYMENT_DRIVER', 'fake'),
    ],

    'paymaster' => [
        'merchant_id' => env('PAYMASTER_MERCHANT_ID'),
        'secret_key' => env('PAYMASTER_SECRET_KEY'),
    ],

    'direct' => [
        'phone' => env('PAYMENT_DIRECT_PHONE'),
        'bank' => env('PAYMENT_DIRECT_BANK'),
        'recipient' => env('PAYMENT_DIRECT_RECIPIENT'),
        'note' => env('PAYMENT_DIRECT_NOTE_TEMPLATE', 'Оплата заказа #:order_id'),
    ],

    'tinkoff' => [
        'terminal_key' => env('TINKOFF_TERMINAL_KEY'),
        'secret_key' => env('TINKOFF_SECRET_KEY'),
        'api_url' => env('TINKOFF_API_URL', 'https://rest-api-test.tinkoff.ru/v2/'),
    ],

    'yookassa' => [
        'shop_id' => env('YOOKASSA_SHOP_ID'),
        'secret_key' => env('YOOKASSA_SECRET_KEY'),
        'api_url' => env('YOOKASSA_API_URL', 'https://api.yookassa.ru/v3/'),
    ],

];
