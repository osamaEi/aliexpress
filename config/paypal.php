<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PayPal Mode
    |--------------------------------------------------------------------------
    |
    | This option controls which PayPal API environment to use.
    | Options: 'sandbox' or 'live'
    |
    */

    'mode' => env('PAYPAL_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | PayPal Sandbox Credentials
    |--------------------------------------------------------------------------
    |
    | Your PayPal sandbox API credentials for testing.
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
    | Your PayPal live API credentials for production.
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
    | The base URLs for PayPal API endpoints.
    |
    */

    'api_url' => [
        'sandbox' => 'https://api-m.sandbox.paypal.com',
        'live' => 'https://api-m.paypal.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Currency
    |--------------------------------------------------------------------------
    |
    | The default currency code for PayPal transactions.
    |
    */

    'currency' => env('PAYPAL_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | PayPal Locale
    |--------------------------------------------------------------------------
    |
    | The locale for PayPal UI.
    |
    */

    'locale' => env('PAYPAL_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | PayPal Return and Cancel URLs
    |--------------------------------------------------------------------------
    |
    | URLs where users will be redirected after completing or canceling payment.
    |
    */

    'return_url' => env('APP_URL') . '/payment/callback',
    'cancel_url' => env('APP_URL') . '/payment/cancel',

];
