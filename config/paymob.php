<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paymob API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Paymob Payment Gateway (UAE)
    | Get your credentials from: https://uae.paymob.com/portal2/en/profile
    |
    */

    'api_key' => env('PAYMOB_API_KEY', ''),

    'integration_id' => env('PAYMOB_INTEGRATION_ID', ''),

    'iframe_id' => env('PAYMOB_IFRAME_ID', ''),

    'hmac_secret' => env('PAYMOB_HMAC_SECRET', ''),

    'base_url' => env('PAYMOB_BASE_URL', 'https://uae.paymob.com/api'),

    'currency' => env('PAYMOB_CURRENCY', 'AED'),

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    */

    'callback_url' => env('PAYMOB_CALLBACK_URL', '/payment/callback'),

    'success_url' => env('PAYMOB_SUCCESS_URL', '/payment/success'),

    'error_url' => env('PAYMOB_ERROR_URL', '/payment/error'),

];
