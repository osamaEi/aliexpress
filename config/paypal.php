<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayPal Mode
    |--------------------------------------------------------------------------
    |
    | This option controls the PayPal environment mode: 'sandbox' or 'live'
    |
    */
    'mode' => env('PAYPAL_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | PayPal Sandbox Credentials
    |--------------------------------------------------------------------------
    |
    | These are the sandbox credentials for PayPal testing
    |
    */
    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Live Credentials
    |--------------------------------------------------------------------------
    |
    | These are the live credentials for PayPal production
    |
    */
    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal API URLs
    |--------------------------------------------------------------------------
    |
    | These are the PayPal API endpoints for different environments
    |
    */
    'api_url' => [
        'sandbox' => 'https://api-m.sandbox.paypal.com',
        'live' => 'https://api-m.paypal.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Default currency and other payment settings
    |
    */
    'currency' => env('PAYPAL_CURRENCY', 'USD'),
    'notify_url' => env('PAYPAL_NOTIFY_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Return URLs
    |--------------------------------------------------------------------------
    |
    | URLs where customers are redirected after payment
    |
    */
    'return_url' => env('APP_URL') . '/payment/callback',
    'cancel_url' => env('APP_URL') . '/payment/error',
];
