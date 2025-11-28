<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paymob API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Paymob API settings.
    |
    */

    'api_key' => env('PAYMOB_API_KEY'),
    'hmac' => env('PAYMOB_HMAC'),
    'iframe_id' => env('PAYMOB_IFRAME_ID'),
    'card_integration_id' => env('PAYMOB_CARD_INTEGRATION_ID'),
    'wallet_integration_id' => env('PAYMOB_WALLET_INTEGRATION_ID'),
    'base_url' => env('PAYMOB_BASE', 'https://accept.paymob.com'),

    /*
    |--------------------------------------------------------------------------
    | Exchange Rate (USD to AED)
    |--------------------------------------------------------------------------
    |
    | Configure the exchange rate from USD to AED for Paymob payments.
    | Default: 3.67 AED per 1 USD (UAE Dirham is pegged to USD)
    | Update this if the rate changes.
    |
    */

    'exchange_rate' => env('PAYMOB_EXCHANGE_RATE', 3.67),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The currency code for Paymob transactions (AED for UAE Dirham)
    |
    */

    'currency' => env('PAYMOB_CURRENCY', 'AED'),

];
