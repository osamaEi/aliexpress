<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AliExpressTestController;
use App\Http\Controllers\AliExpressController;
use App\Http\Controllers\AliExpressWebhookController;
use App\Http\Controllers\SellerRegistrationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SubscriptionManagementController;
use App\Http\Controllers\Admin\OrderManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Language Routes
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// PayPal Callback (Public - No Auth Required for return URL)
Route::get('/payment/callback', [App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');
// AliExpress Webhook Routes (Public - No Auth Required, No CSRF)
Route::prefix('webhooks/aliexpress')->name('webhook.aliexpress.')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    Route::post('/order-status', [AliExpressWebhookController::class, 'handleOrderStatus'])->name('order-status');
    Route::post('/tracking-update', [AliExpressWebhookController::class, 'handleTrackingUpdate'])->name('tracking-update');
    Route::get('/test', [AliExpressWebhookController::class, 'test'])->name('test');
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

    // Subscription routes
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/subscriptions/{subscription}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::get('/subscriptions-history', [SubscriptionController::class, 'history'])->name('subscriptions.history');

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

    // Payment routes
    Route::post('/payment/subscription/{subscription}', [App\Http\Controllers\PaymentController::class, 'initiateSubscriptionPayment'])->name('payment.subscription');
    Route::post('/payment/order/{order}', [App\Http\Controllers\PaymentController::class, 'initiateOrderPayment'])->name('payment.order');
    Route::get('/payment/success', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/error', [App\Http\Controllers\PaymentController::class, 'error'])->name('payment.error');

    // Wallet routes
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [App\Http\Controllers\WalletController::class, 'index'])->name('index');
        Route::get('/deposit', [App\Http\Controllers\WalletController::class, 'depositForm'])->name('deposit');
        Route::post('/deposit', [App\Http\Controllers\WalletController::class, 'deposit'])->name('deposit.process');
        Route::get('/withdrawal', [App\Http\Controllers\WalletController::class, 'withdrawalForm'])->name('withdrawal');
        Route::post('/withdrawal', [App\Http\Controllers\WalletController::class, 'withdrawal'])->name('withdrawal.process');
        Route::get('/transactions', [App\Http\Controllers\WalletController::class, 'transactions'])->name('transactions');
        Route::get('/transfer', [App\Http\Controllers\WalletController::class, 'transferForm'])->name('transfer');
        Route::post('/transfer', [App\Http\Controllers\WalletController::class, 'transfer'])->name('transfer.process');
    });

    // Subscription routes
    Route::resource('subscriptions', SubscriptionController::class);
    Route::post('/subscriptions/{subscription}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::get('/subscriptions-history', [SubscriptionController::class, 'history'])->name('subscriptions.history');

    // Seller Profit Management Routes
    Route::prefix('seller/profits')->name('seller.profits.')->group(function () {
        Route::get('/', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'store'])->name('store');
        Route::post('/bulk-update', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'bulkUpdate'])->name('bulk-update');
        Route::post('/{profit}/toggle', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{profit}', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'destroy'])->name('destroy');
        Route::get('/api/subcategory/{categoryId}', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'getProfitForSubcategory'])->name('api.get');
    });

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Admin Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Token Management
        Route::get('/tokens', [AdminController::class, 'tokens'])->name('tokens');
        Route::post('/tokens', [AdminController::class, 'updateTokens'])->name('tokens.update');

        // Subscription Management
        Route::get('/subscriptions', [SubscriptionManagementController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/users', [SubscriptionManagementController::class, 'userSubscriptions'])->name('subscriptions.users');
        Route::get('/subscriptions/{subscription}/edit', [SubscriptionManagementController::class, 'edit'])->name('subscriptions.edit');
        Route::put('/subscriptions/{subscription}', [SubscriptionManagementController::class, 'update'])->name('subscriptions.update');

        // Order Management
        Route::get('/orders', [OrderManagementController::class, 'index'])->name('orders.index');
        Route::post('/orders/{order}/sync', [OrderManagementController::class, 'sync'])->name('orders.sync');
        Route::post('/orders/bulk-sync', [OrderManagementController::class, 'bulkSync'])->name('orders.bulk-sync');
        Route::post('/orders/{order}/update-status', [OrderManagementController::class, 'updateStatus'])->name('orders.update-status');

        // Categories Management (use existing CategoryController)
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

        // User Management
        Route::get('/users', [App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('users.index');

        // Wallet Management
        Route::prefix('wallets')->name('wallets.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminWalletController::class, 'index'])->name('index');
            Route::get('/{wallet}', [App\Http\Controllers\Admin\AdminWalletController::class, 'show'])->name('show');
            Route::post('/{wallet}/credit', [App\Http\Controllers\Admin\AdminWalletController::class, 'creditWallet'])->name('credit');
            Route::post('/{wallet}/debit', [App\Http\Controllers\Admin\AdminWalletController::class, 'debitWallet'])->name('debit');
            Route::get('/transactions/all', [App\Http\Controllers\Admin\AdminWalletController::class, 'transactions'])->name('transactions');

            // Withdrawal requests
            Route::get('/withdrawals/requests', [App\Http\Controllers\Admin\AdminWalletController::class, 'withdrawalRequests'])->name('withdrawals');
            Route::post('/withdrawals/{withdrawalRequest}/approve', [App\Http\Controllers\Admin\AdminWalletController::class, 'approveWithdrawal'])->name('withdrawals.approve');
            Route::post('/withdrawals/{withdrawalRequest}/reject', [App\Http\Controllers\Admin\AdminWalletController::class, 'rejectWithdrawal'])->name('withdrawals.reject');
            Route::post('/withdrawals/{withdrawalRequest}/complete', [App\Http\Controllers\Admin\AdminWalletController::class, 'completeWithdrawal'])->name('withdrawals.complete');
        });

        // Settings Management
        Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/delete-image', [App\Http\Controllers\Admin\SettingController::class, 'deleteImage'])->name('settings.delete-image');

        // Logs Management
        Route::get('/logs', [App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');
        Route::get('/logs/download', [App\Http\Controllers\Admin\LogController::class, 'download'])->name('logs.download');
        Route::delete('/logs/delete', [App\Http\Controllers\Admin\LogController::class, 'delete'])->name('logs.delete');
        Route::delete('/logs/clear', [App\Http\Controllers\Admin\LogController::class, 'clear'])->name('logs.clear');
    });
});

require __DIR__.'/auth.php';
