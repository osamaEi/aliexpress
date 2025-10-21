<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = config('services.aliexpress.api_key');
$apiSecret = config('services.aliexpress.api_secret');
$trackingId = config('services.aliexpress.tracking_id') ?: 'default';

echo "=== Testing AliExpress Affiliate API (instead of Dropshipping) ===\n\n";

// Try affiliate API method instead
$timestamp = (string)(time() * 1000);
$params = [
    'app_key' => $apiKey,
    'format' => 'json',
    'method' => 'aliexpress.affiliate.productdetail.get',  // Affiliate API
    'product_ids' => '1005006340579394',
    'fields' => 'commission_rate,sale_price',
    'target_currency' => 'USD',
    'target_language' => 'EN',
    'tracking_id' => $trackingId,
    'sign_method' => 'sha256',
    'timestamp' => $timestamp,
    'v' => '2.0',
];

ksort($params);

// Build signature string
$stringToBeSigned = '';
foreach ($params as $key => $value) {
    if ($value !== '' && $value !== null) {
        $stringToBeSigned .= $key . $value;
    }
}

echo "Signature String: " . substr($stringToBeSigned, 0, 100) . "...\n";
echo "String Length: " . strlen($stringToBeSigned) . "\n\n";

$signature = strtoupper(hash_hmac('sha256', $stringToBeSigned, $apiSecret));
$params['sign'] = $signature;

echo "Making Affiliate API Request...\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api-sg.aliexpress.com/sync');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: {$httpCode}\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT);
echo "\n\n";

// Also test hotproducts query
echo "=== Testing Hot Products Query (Affiliate) ===\n\n";

$timestamp2 = (string)(time() * 1000);
$params2 = [
    'app_key' => $apiKey,
    'format' => 'json',
    'method' => 'aliexpress.affiliate.hotproduct.query',
    'keywords' => 'phone',
    'target_currency' => 'USD',
    'target_language' => 'EN',
    'tracking_id' => $trackingId,
    'sign_method' => 'sha256',
    'timestamp' => $timestamp2,
    'v' => '2.0',
];

ksort($params2);

$stringToBeSigned2 = '';
foreach ($params2 as $key => $value) {
    if ($value !== '' && $value !== null) {
        $stringToBeSigned2 .= $key . $value;
    }
}

$signature2 = strtoupper(hash_hmac('sha256', $stringToBeSigned2, $apiSecret));
$params2['sign'] = $signature2;

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, 'https://api-sg.aliexpress.com/sync');
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query($params2));
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_TIMEOUT, 30);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "HTTP Status: {$httpCode2}\n";
echo "Response:\n";
echo json_encode(json_decode($response2), JSON_PRETTY_PRINT);
echo "\n";
