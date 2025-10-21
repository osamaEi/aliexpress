<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\AliExpressService;

Route::get('/test-aliexpress', function (AliExpressService $service) {
    try {
        $products = $service->searchProducts('phone', ['page' => 1, 'limit' => 5]);

        return response()->json([
            'success' => true,
            'message' => 'API working!',
            'products_count' => count($products['products'] ?? []),
            'data' => $products
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'check_logs' => 'See storage/logs/laravel.log for details'
        ], 500);
    }
});
