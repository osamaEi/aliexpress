<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AliExpressTestController;
use App\Http\Controllers\AliExpressController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// AliExpress API Test Routes (Public - No Auth Required)
Route::get('/test-aliexpress', [AliExpressTestController::class, 'testConnection'])->name('aliexpress.test');
Route::get('/test-aliexpress-token', [AliExpressTestController::class, 'testTokenCreation'])->name('aliexpress.test.token');
Route::get('/test-aliexpress-refresh', [AliExpressTestController::class, 'testTokenRefresh'])->name('aliexpress.test.refresh');
Route::get('/test-aliexpress-all', [AliExpressTestController::class, 'testAllEndpoints'])->name('aliexpress.test.all');

// AliExpress OAuth Routes
Route::get('/aliexpress-oauth-start', [AliExpressTestController::class, 'getAuthUrl'])->name('aliexpress.oauth.start');
Route::get('/aliexpress-oauth-callback', [AliExpressTestController::class, 'oauthCallback'])->name('aliexpress.oauth.callback');

// AliExpress Documentation
Route::get('/aliexpress-guide', function () {
    return view('aliexpress-guide-ar');
})->name('aliexpress.guide');

// AliExpress Manual Token Test
Route::get('/aliexpress-manual-token', function () {
    return view('aliexpress-manual-token');
})->name('aliexpress.manual.token');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// NEW AliExpress Dropshipping Routes (accessible without auth for testing)
Route::get('/aliexpress', [AliExpressController::class, 'index'])->name('aliexpress.index');
Route::post('/aliexpress/search', [AliExpressController::class, 'search'])->name('aliexpress.search');
Route::get('/aliexpress/product/{productId}', [AliExpressController::class, 'details'])->name('aliexpress.details');
Route::get('/aliexpress/check-enrollment', [AliExpressController::class, 'checkEnrollment'])->name('aliexpress.enrollment');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Category routes
    Route::resource('categories', CategoryController::class);

    // AliExpress Text Search Routes (must be before resource routes)
    Route::get('/products/search-aliexpress', [ProductController::class, 'searchPage'])->name('products.search-page');
    Route::get('/products/search-text', [ProductController::class, 'searchByText'])->name('products.search-text');

    // AliExpress integration routes (must be before resource routes)
    Route::get('/products/aliexpress/import', [ProductController::class, 'import'])->name('products.aliexpress.import');
    Route::post('/products/aliexpress/search', [ProductController::class, 'searchAliexpress'])->name('products.aliexpress.search');
    Route::post('/products/aliexpress/import-product', [ProductController::class, 'importFromAliexpress'])->name('products.aliexpress.import-product');

    // Product routes (must be after specific routes to avoid conflicts)
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/sync', [ProductController::class, 'sync'])->name('products.sync');
    Route::post('/products/sync-all', [ProductController::class, 'syncAll'])->name('products.sync-all');
});

require __DIR__.'/auth.php';
