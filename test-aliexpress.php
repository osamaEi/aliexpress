<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing AliExpress Feed Item IDs API...\n";
echo "==========================================\n\n";

try {
    $service = $app->make(App\Services\AliExpressDropshippingService::class);

    echo "1. Checking dropshipping enrollment...\n";
    $enrollment = $service->checkDropshippingAccess();
    echo json_encode($enrollment, JSON_PRETTY_PRINT) . "\n\n";

    echo "2. Searching for products...\n";
    $result = $service->searchProducts('', ['limit' => 5]);

    if (isset($result['success']) && $result['success']) {
        echo "SUCCESS! Found " . count($result['products']) . " products\n";
        echo "Total available: " . ($result['total_count'] ?? 'unknown') . "\n";
        echo "\nProducts:\n";
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "ERROR: " . ($result['error'] ?? $result['message'] ?? 'Unknown error') . "\n";
        echo "\nFull response:\n";
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n==========================================\n";
echo "Test completed!\n";
