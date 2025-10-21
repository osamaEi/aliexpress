<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use AliExpress\Sdk\IopClient;
use AliExpress\Sdk\IopRequest;

$apiKey = config('services.aliexpress.api_key');
$apiSecret = config('services.aliexpress.api_secret');
$accessToken = config('services.aliexpress.access_token');
$gatewayUrl = config('services.aliexpress.api_url');

echo "=== Testing Product Details API ===\n\n";

$client = new IopClient($gatewayUrl, $apiKey, $apiSecret);

// Test getting a specific product by ID
$request = new IopRequest('aliexpress.ds.product.get');
$request->addApiParam('product_id', '1005006340579394');
$request->addApiParam('ship_to_country', 'US');
$request->addApiParam('target_currency', 'USD');
$request->addApiParam('target_language', 'EN');

try {
    $response = $client->execute($request, $accessToken);

    echo "Response:\n";
    echo $response . "\n\n";

    $data = json_decode($response, true);

    if (isset($data['aliexpress_ds_product_get_response']['result'])) {
        $product = $data['aliexpress_ds_product_get_response']['result'];
        echo "✅ SUCCESS! Product found:\n";
        echo "ID: " . ($product['product_id'] ?? 'N/A') . "\n";
        echo "Title: " . ($product['subject'] ?? 'N/A') . "\n";
        echo "Price: $" . ($product['target_sale_price'] ?? 'N/A') . "\n";
    } else if (isset($data['error_response'])) {
        echo "❌ Error: " . $data['error_response']['msg'] . "\n";
        echo "Code: " . $data['error_response']['code'] . "\n";
    } else {
        echo "Response structure:\n";
        print_r($data);
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
