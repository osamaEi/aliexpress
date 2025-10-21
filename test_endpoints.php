<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = config('services.aliexpress.api_key');
$apiSecret = config('services.aliexpress.api_secret');
$accessToken = config('services.aliexpress.access_token');

$timestamp = (string)(time() * 1000);
$params = [
    'app_key' => $apiKey,
    'format' => 'json',
    'method' => 'aliexpress.ds.product.get',
    'product_id' => '1005006340579394',
    'ship_to_country' => 'EG',
    'sign_method' => 'sha256',
    'target_currency' => 'USD',
    'target_language' => 'EN',
    'timestamp' => $timestamp,
    'v' => '2.0',
];

ksort($params);

// Build signature string
$stringToBeSigned = '';
foreach ($params as $key => $value) {
    $stringToBeSigned .= $key . $value;
}

$signature = strtoupper(hash_hmac('sha256', $stringToBeSigned, $apiSecret));
$params['sign'] = $signature;
$params['access_token'] = $accessToken;

$endpoints = [
    '/rest' => 'https://api-sg.aliexpress.com/rest',
    '/sync' => 'https://api-sg.aliexpress.com/sync',
    '/param2/1/aliexpress.open/api.gw' => 'https://api-sg.aliexpress.com/param2/1/aliexpress.open/api.gw',
];

echo "=== Testing Different Endpoints ===\n\n";

foreach ($endpoints as $path => $url) {
    echo "Testing: {$url}\n";
    echo str_repeat('-', 80) . "\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "HTTP Status: {$httpCode}\n";
    if ($error) {
        echo "cURL Error: {$error}\n";
    }
    $data = json_decode($response, true);
    if (isset($data['code'])) {
        echo "API Error Code: {$data['code']}\n";
        echo "API Error Message: {$data['message']}\n";
    } elseif (isset($data['aliexpress_ds_product_get_response'])) {
        echo "SUCCESS! Got product response\n";
    }
    echo "\n";
}
