<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = config('services.aliexpress.api_key');
$apiSecret = config('services.aliexpress.api_secret');
$accessToken = config('services.aliexpress.access_token');

echo "=== AliExpress API Detailed Test ===\n\n";
echo "API Key: {$apiKey}\n";
echo "API Secret: " . substr($apiSecret, 0, 4) . "***\n";
echo "Access Token: " . substr($accessToken, 0, 10) . "***\n\n";

// Test parameters
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

// Sort parameters
ksort($params);

echo "Sorted Parameters:\n";
foreach ($params as $key => $value) {
    echo "  {$key} => {$value}\n";
}
echo "\n";

// Build signature string
$stringToBeSigned = '';
foreach ($params as $key => $value) {
    $stringToBeSigned .= $key . $value;
}

echo "String to Sign:\n{$stringToBeSigned}\n\n";
echo "String Length: " . strlen($stringToBeSigned) . "\n\n";

// Generate signature
$signature = strtoupper(hash_hmac('sha256', $stringToBeSigned, $apiSecret));
echo "Generated Signature:\n{$signature}\n\n";

// Add signature and access token
$params['sign'] = $signature;
$params['access_token'] = $accessToken;

// Make request
echo "Making API Request...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api-sg.aliexpress.com/rest');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "\nHTTP Status: {$httpCode}\n";
if ($error) {
    echo "cURL Error: {$error}\n";
}
echo "\nAPI Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT);
echo "\n";
