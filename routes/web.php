<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AliExpressTestController;
use App\Http\Controllers\AliExpressController;
use App\Http\Controllers\SellerRegistrationController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Seller Registration Routes (Public - No Auth Required)
Route::prefix('seller/register')->name('seller.register.')->group(function () {
    Route::get('step-1', [SellerRegistrationController::class, 'showStep1'])->name('step1');
    Route::post('step-1', [SellerRegistrationController::class, 'processStep1'])->name('step1.process');
    Route::get('step-2', [SellerRegistrationController::class, 'showStep2'])->name('step2');
    Route::post('step-2', [SellerRegistrationController::class, 'processStep2'])->name('step2.process');
    Route::get('step-3', [SellerRegistrationController::class, 'showStep3'])->name('step3');
    Route::post('verify-otp', [SellerRegistrationController::class, 'verifyOTP'])->name('verify-otp');
    Route::post('resend-otp', [SellerRegistrationController::class, 'resendOTP'])->name('resend-otp');
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
    Route::get('/categories/{category}/fetch-subcategories', [CategoryController::class, 'fetchSubcategories'])->name('categories.fetch-subcategories');
    Route::post('/categories/{category}/save-subcategories', [CategoryController::class, 'saveSubcategories'])->name('categories.save-subcategories');
    Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::get('/categories/fetch-tree', [CategoryController::class, 'fetchCategoryTree'])->name('categories.fetch-tree');
    Route::post('/categories/save-tree', [CategoryController::class, 'saveCategoryTree'])->name('categories.save-tree');
    Route::post('/categories/import-all', [CategoryController::class, 'importAllCategories'])->name('categories.import-all');
    Route::resource('categories', CategoryController::class);

    // AliExpress Text Search Routes (must be before resource routes)
    Route::get('/products/search-aliexpress', [ProductController::class, 'searchPage'])->name('products.search-page');
    Route::get('/products/search-text', [ProductController::class, 'searchByText'])->name('products.search-text');

    // AliExpress integration routes (must be before resource routes)
    Route::get('/products/aliexpress/import', [ProductController::class, 'import'])->name('products.aliexpress.import');
    Route::post('/products/aliexpress/search', [ProductController::class, 'searchAliexpress'])->name('products.aliexpress.search');
    Route::post('/products/aliexpress/import-product', [ProductController::class, 'importFromAliexpress'])->name('products.aliexpress.import-product');

    // Product assignment routes (for sellers)
    Route::post('/products/assign', [ProductController::class, 'assignProduct'])->name('products.assign');
    Route::get('/my-assigned-products', [ProductController::class, 'myAssignedProducts'])->name('products.my-assigned');

    // Product routes (must be after specific routes to avoid conflicts)
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/sync', [ProductController::class, 'sync'])->name('products.sync');
    Route::post('/products/sync-all', [ProductController::class, 'syncAll'])->name('products.sync-all');

    // Order routes
    Route::resource('orders', OrderController::class);
    Route::post('/orders/{order}/place-on-aliexpress', [OrderController::class, 'placeOnAliexpress'])->name('orders.place-on-aliexpress');
    Route::post('/orders/{order}/update-tracking', [OrderController::class, 'updateTracking'])->name('orders.update-tracking');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});

require __DIR__.'/auth.php';
