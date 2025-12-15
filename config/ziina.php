<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ziina API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Ziina payment gateway integration
    |
    */

    'api_key' => env('ZIINA_API_KEY', ''),

    'api_url' => env('ZIINA_API_URL', 'https://api-v2.ziina.com/api'),

    'test_mode' => env('ZIINA_TEST_MODE', true),

    'currency' => env('ZIINA_CURRENCY', 'AED'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The secret key used to verify webhook signatures from Ziina
    |
    */

    'webhook_secret' => env('ZIINA_WEBHOOK_SECRET', ''),
];
