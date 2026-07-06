<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\AliExpressController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\AliExpressTestController;
use App\Http\Controllers\AliExpressWebhookController;
use App\Http\Controllers\SellerRegistrationController;
use App\Http\Controllers\SellerProfileCompletionController;
use App\Http\Controllers\DistributorRegistrationController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\SubscriptionManagementController;

Route::get('/', function () {
    return view('welcome');
});

// Setup: Create first admin (only works when no admin exists)
Route::get('/setup/create-admin', function () {
    $adminExists = \App\Models\User::where('user_type', 'admin')->exists();
    if ($adminExists) {
        abort(403, 'Admin already exists.');
    }
    return view('setup.create-admin');
})->name('setup.create-admin');

Route::post('/setup/create-admin', function (\Illuminate\Http\Request $request) {
    $adminExists = \App\Models\User::where('user_type', 'admin')->exists();
    if ($adminExists) {
        abort(403, 'Admin already exists.');
    }

    $validated = $request->validate([
        'name'                  => 'required|string|max:255',
        'email'                 => 'required|email|unique:users,email',
        'password'              => 'required|min:8|confirmed',
    ]);

    $user = \App\Models\User::create([
        'name'              => $validated['name'],
        'email'             => $validated['email'],
        'password'          => \Illuminate\Support\Facades\Hash::make($validated['password']),
        'user_type'         => 'admin',
        'email_verified_at' => now(),
    ]);

    $adminRole = \App\Models\Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
    }

    return redirect('/admin/dashboard')->with('success', 'Admin account created successfully. Please log in.');
})->name('setup.create-admin.store');

// Language Routes
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// Currency Routes
Route::get('/currency/{code}', [App\Http\Controllers\CurrencyController::class, 'switch'])->name('currency.switch');

// PayPal Callback (Public - No Auth Required for return URL)
Route::get('/payment/callback', [App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');

// Paymob Callback & Webhook Routes (Public - No Auth Required, No CSRF for webhook)
Route::get('/paymob/callback', [App\Http\Controllers\PaymobController::class, 'callback'])->name('paymob.callback');
Route::post('/paymob/webhook', [App\Http\Controllers\PaymobController::class, 'webhook'])->name('paymob.webhook');
Route::get('/paymob-test', function() {
    return view('paymob-test');
})->name('paymob.test');

// AliExpress Webhook Routes (Public - No Auth Required, No CSRF)
Route::prefix('webhooks/aliexpress')->name('webhook.aliexpress.')->group(function () {
    Route::match(['get', 'post'], '/order-status', [AliExpressWebhookController::class, 'handleOrderStatus'])->name('order-status');
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
    Route::get('step-4', [SellerRegistrationController::class, 'showStep4'])->name('step4');
    Route::post('verify-whatsapp-otp', [SellerRegistrationController::class, 'verifyWhatsAppOTP'])->name('verify-whatsapp-otp');
    Route::post('resend-whatsapp-otp', [SellerRegistrationController::class, 'resendWhatsAppOTP'])->name('resend-whatsapp-otp');
});

// Distributor Registration Routes (Public - No Auth Required)
Route::prefix('distributor/register')->name('distributor.register.')->group(function () {
    Route::get('step-1', [DistributorRegistrationController::class, 'showStep1'])->name('step1');
    Route::post('step-1', [DistributorRegistrationController::class, 'processStep1'])->name('step1.process');
    Route::get('step-2', [DistributorRegistrationController::class, 'showStep2'])->name('step2');
    Route::post('step-2', [DistributorRegistrationController::class, 'processStep2'])->name('step2.process');
    Route::get('step-3', [DistributorRegistrationController::class, 'showStep3'])->name('step3');
    Route::post('step-3', [DistributorRegistrationController::class, 'processStep3'])->name('step3.process');
    Route::get('step-4', [DistributorRegistrationController::class, 'showStep4'])->name('step4');
    Route::post('verify-otp', [DistributorRegistrationController::class, 'verifyOTP'])->name('verify-otp');
    Route::post('resend-otp', [DistributorRegistrationController::class, 'resendOTP'])->name('resend-otp');
    Route::get('step-5', [DistributorRegistrationController::class, 'showStep5'])->name('step5');
    Route::post('verify-whatsapp-otp', [DistributorRegistrationController::class, 'verifyWhatsAppOTP'])->name('verify-whatsapp-otp');
    Route::post('resend-whatsapp-otp', [DistributorRegistrationController::class, 'resendWhatsAppOTP'])->name('resend-whatsapp-otp');
    Route::get('check-slug', [DistributorRegistrationController::class, 'checkSlug'])->name('check-slug');
});

// Marketer registration (multi-step, email + WhatsApp OTP — like seller, no categories)
Route::prefix('marketer/register')->name('marketer.register.')->group(function () {
    Route::get('step-1', [App\Http\Controllers\MarketerRegistrationController::class, 'showStep1'])->name('step1');
    Route::post('step-1', [App\Http\Controllers\MarketerRegistrationController::class, 'processStep1'])->name('step1.process');
    Route::get('step-2', [App\Http\Controllers\MarketerRegistrationController::class, 'showStep2'])->name('step2');
    Route::post('verify-otp', [App\Http\Controllers\MarketerRegistrationController::class, 'verifyOTP'])->name('verify-otp');
    Route::post('resend-otp', [App\Http\Controllers\MarketerRegistrationController::class, 'resendOTP'])->name('resend-otp');
    Route::get('step-3', [App\Http\Controllers\MarketerRegistrationController::class, 'showStep3'])->name('step3');
    Route::post('verify-whatsapp-otp', [App\Http\Controllers\MarketerRegistrationController::class, 'verifyWhatsAppOTP'])->name('verify-whatsapp-otp');
    Route::post('resend-whatsapp-otp', [App\Http\Controllers\MarketerRegistrationController::class, 'resendWhatsAppOTP'])->name('resend-whatsapp-otp');
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
    $user = Auth::user();

    // Redirect sellers to seller dashboard
    if ($user && $user->user_type === 'seller') {
        return redirect()->route('seller.dashboard');
    }

    // Redirect distributors to distributor dashboard
    if ($user && $user->user_type === 'distributor') {
        return redirect()->route('distributor.dashboard');
    }

    // Redirect marketers to marketer dashboard
    if ($user && $user->user_type === 'marketer') {
        return redirect()->route('marketer.dashboard');
    }

    // Show customer/buyer dashboard
    return view('customer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// NEW AliExpress Dropshipping Routes (accessible without auth for testing)
Route::get('/aliexpress', [AliExpressController::class, 'index'])->name('aliexpress.index');
Route::post('/aliexpress/search', [AliExpressController::class, 'search'])->name('aliexpress.search');
Route::get('/aliexpress/product/{productId}', [AliExpressController::class, 'details'])->name('aliexpress.details');
Route::get('/aliexpress/check-enrollment', [AliExpressController::class, 'checkEnrollment'])->name('aliexpress.enrollment');

Route::middleware('auth')->group(function () {
    // Training Videos (all authenticated users)
    Route::get('/training-videos', [App\Http\Controllers\TrainingVideoController::class, 'index'])->name('training-videos.index');

    // Seller Profit Setup Routes (must complete before accessing system)
    Route::get('/seller/profit-setup', [SellerProfileCompletionController::class, 'showProfitForm'])->name('seller.profit.setup');
    Route::post('/seller/profit-setup', [SellerProfileCompletionController::class, 'storeProfitSettings'])->name('seller.profit.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/logo', [ProfileController::class, 'updateLogo'])->name('profile.logo.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Withdrawal methods (saved bank / paypal / mobile wallet)
    Route::post('/profile/withdrawal-methods', [App\Http\Controllers\WithdrawalMethodController::class, 'store'])->name('withdrawal-methods.store');
    Route::patch('/profile/withdrawal-methods/{withdrawalMethod}', [App\Http\Controllers\WithdrawalMethodController::class, 'update'])->name('withdrawal-methods.update');
    Route::delete('/profile/withdrawal-methods/{withdrawalMethod}', [App\Http\Controllers\WithdrawalMethodController::class, 'destroy'])->name('withdrawal-methods.destroy');
    Route::post('/profile/withdrawal-methods/{withdrawalMethod}/default', [App\Http\Controllers\WithdrawalMethodController::class, 'setDefault'])->name('withdrawal-methods.default');

    // Subscription routes
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::get('/subscriptions/{subscription}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe.show');
    Route::post('/subscriptions/{subscription}/pay-wallet', [SubscriptionController::class, 'payWithWallet'])->name('subscriptions.pay-with-wallet');
    Route::post('/subscriptions/{subscription}/pay-ziina', [SubscriptionController::class, 'payWithZiina'])->name('subscriptions.pay-with-ziina');
    Route::get('/subscriptions/payment/success', [SubscriptionController::class, 'ziinaPaymentSuccess'])->name('subscriptions.payment.success');
    Route::get('/subscriptions/payment/cancel', [SubscriptionController::class, 'ziinaPaymentCancel'])->name('subscriptions.payment.cancel');
    Route::post('/subscriptions/{subscription}/pay-paymob', [SubscriptionController::class, 'payWithPaymob'])->name('subscriptions.pay-with-paymob');
    Route::post('/subscriptions/{subscription}/initialize-paymob', [SubscriptionController::class, 'initializePaymobPayment'])->name('subscriptions.initialize-paymob');
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::get('/subscriptions-history', [SubscriptionController::class, 'history'])->name('subscriptions.history');
    Route::get('/subscriptions/{userSubscription}/invoice', [SubscriptionController::class, 'invoice'])->name('subscriptions.invoice');

    // Paymob subscription payment routes
    Route::post('/paymob/initiate-subscription/{subscription}', [App\Http\Controllers\PaymobController::class, 'initiateSubscriptionPayment'])->name('paymob.initiate-subscription');

    // Category routes
    // Lightweight DB-only children lookup (used by coupon category cascade for admin & distributor)
    Route::get('/categories/{category}/children', [CategoryController::class, 'children'])->name('categories.children');
    Route::get('/categories/{category}/fetch-subcategories', [CategoryController::class, 'fetchSubcategories'])->name('categories.fetch-subcategories');
    Route::post('/categories/{category}/save-subcategories', [CategoryController::class, 'saveSubcategories'])->name('categories.save-subcategories');
    Route::get('/categories/fetch-tree', [CategoryController::class, 'fetchCategoryTree'])->name('categories.fetch-tree');
    Route::post('/categories/save-tree', [CategoryController::class, 'saveCategoryTree'])->name('categories.save-tree');
    Route::post('/categories/import-all', [CategoryController::class, 'importAllCategories'])->name('categories.import-all');

    // Admin-only category routes
    Route::middleware('admin')->group(function () {
        Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    });

    // Seller category assignment request
    Route::get('/seller/request-category-assignment', [CategoryController::class, 'requestCategoryAssignment'])->name('seller.request-category-assignment');
    Route::post('/seller/submit-category-request', [CategoryController::class, 'submitCategoryRequest'])->name('seller.submit-category-request');

    Route::resource('categories', CategoryController::class);

    // AliExpress Text Search Routes (must be before resource routes)
    Route::get('/products/search-china', [ProductController::class, 'searchPage'])->name('products.search-page');
    Route::get('/products/search-text', [ProductController::class, 'searchByText'])->name('products.search-text');

    // Distributor Products Search (UAE/Saudi) - products from local distributors
    Route::get('/products/search-distributor', [ProductController::class, 'searchDistributorProducts'])->name('products.search-distributor');

    // China Stores Routes - AliExpress feeds/categories as stores
    Route::get('/products/china-stores', [ProductController::class, 'getChinaStores'])->name('products.china-stores');
    Route::get('/products/china-store-products', [ProductController::class, 'searchChinaStoreProducts'])->name('products.china-store-products');

    // AliExpress integration routes (must be before resource routes)
    Route::get('/products/china/import', [ProductController::class, 'import'])->name('products.china.import');
    Route::post('/products/china/search', [ProductController::class, 'searchChina'])->name('products.china.search');
    Route::post('/products/china/import-product', [ProductController::class, 'importFromChina'])->name('products.china.import-product');

    // Product assignment routes (for sellers)
    Route::post('/products/assign', [ProductController::class, 'assignProduct'])->name('products.assign');
    Route::get('/my-assigned-products', [ProductController::class, 'myAssignedProducts'])->name('products.my-assigned');

    // Product routes (must be after specific routes to avoid conflicts)
    Route::get('/products/{product}/detail', [ProductController::class, 'detail'])->name('products.detail');
    Route::get('/products/{product}/distributor-detail', [ProductController::class, 'detailDistributor'])->name('products.detail-distributor');
    Route::get('/products/{product}/debug-skus', [ProductController::class, 'debugSkus'])->name('products.debug-skus');

    // Admin-only product management routes
    Route::middleware('admin')->group(function () {
        Route::resource('products', ProductController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    });

    // Product show route (accessible by all authenticated users)
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    Route::post('/products/{product}/sync', [ProductController::class, 'sync'])->name('products.sync');
    Route::post('/products/sync-all', [ProductController::class, 'syncAll'])->name('products.sync-all');

    // Order routes
    Route::resource('orders', OrderController::class);

    // Distributor order routes (non-AliExpress)
    Route::get('/orders/distributor/create', [OrderController::class, 'createDistributorOrder'])->name('orders.distributor.create');
    Route::post('/orders/distributor', [OrderController::class, 'storeDistributorOrder'])->name('orders.distributor.store');
    Route::post('/orders/distributor/validate-coupon', [OrderController::class, 'validateCoupon'])->name('orders.distributor.validate-coupon');

    // Shipping location + cost lookup for the distributor order page
    Route::get('/orders/shipping/cities/{countryCode}', [OrderController::class, 'shippingCities'])->name('orders.shipping.cities');
    Route::get('/orders/shipping/districts/{city}', [OrderController::class, 'shippingDistricts'])->name('orders.shipping.districts');
    Route::post('/orders/shipping/calculate', [OrderController::class, 'calculateShipping'])->name('orders.shipping.calculate');

    Route::get('/orders/product/{product}/info', [OrderController::class, 'getProductInfo'])->name('orders.product-info');
    Route::post('/orders/calculate-freight', [OrderController::class, 'calculateFreight'])->name('orders.calculate-freight');
    Route::post('/orders/{order}/place-on-aliexpress', [OrderController::class, 'placeOnAliexpress'])->name('orders.place-on-aliexpress');
    Route::post('/orders/{order}/update-tracking', [OrderController::class, 'updateTracking'])->name('orders.update-tracking');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Shipping Test Routes
    Route::get('/shipping/test', [App\Http\Controllers\ShippingTestController::class, 'index'])->name('shipping.test');
    Route::post('/shipping/test/calculate', [App\Http\Controllers\ShippingTestController::class, 'calculate'])->name('shipping.test.calculate');
    Route::post('/shipping/test/product-details', [App\Http\Controllers\ShippingTestController::class, 'getProductDetails'])->name('shipping.test.product-details');

    // Payment routes
    Route::get('/payment/subscription/{subscription}', [App\Http\Controllers\PaymentController::class, 'initiateSubscriptionPayment'])->name('payment.subscription');
    Route::get('/payment/order/{order}', [App\Http\Controllers\PaymentController::class, 'initiateOrderPayment'])->name('payment.order');
    Route::get('/payment/success', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/error', [App\Http\Controllers\PaymentController::class, 'error'])->name('payment.error');

    // Wallet routes
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [App\Http\Controllers\WalletController::class, 'index'])->name('index');
        Route::get('/deposit', [App\Http\Controllers\WalletController::class, 'depositForm'])->name('deposit');
        Route::post('/deposit', [App\Http\Controllers\WalletController::class, 'deposit'])->name('deposit.process');
        Route::post('/deposit/paypal', [App\Http\Controllers\WalletController::class, 'depositPayPal'])->name('deposit.paypal');

        // Ziina deposit routes
        Route::post('/deposit/ziina', [App\Http\Controllers\WalletController::class, 'depositZiina'])->name('deposit.ziina');
        Route::get('/deposit/success', [App\Http\Controllers\WalletController::class, 'depositZiinaSuccess'])->name('deposit.success');
        Route::get('/deposit/cancel', [App\Http\Controllers\WalletController::class, 'depositZiinaCancel'])->name('deposit.cancel');
        Route::post('/deposit/calculate-fees', [App\Http\Controllers\WalletController::class, 'calculateDepositFees'])->name('deposit.calculate-fees');

        Route::get('/transactions', [App\Http\Controllers\WalletController::class, 'transactions'])->name('transactions');
        Route::get('/transfer', [App\Http\Controllers\WalletController::class, 'transferForm'])->name('transfer');
        Route::post('/transfer', [App\Http\Controllers\WalletController::class, 'transfer'])->name('transfer.process');

        // Withdrawal routes
        Route::get('/withdrawal/create', [App\Http\Controllers\WithdrawalController::class, 'create'])->name('withdrawal.create');
        Route::post('/withdrawal', [App\Http\Controllers\WithdrawalController::class, 'store'])->name('withdrawal.store');
        Route::get('/withdrawal/history', [App\Http\Controllers\WithdrawalController::class, 'history'])->name('withdrawal.history');
        Route::delete('/withdrawal/{withdrawal}', [App\Http\Controllers\WithdrawalController::class, 'cancel'])->name('withdrawal.cancel');
    });

    // Subscription routes
    Route::resource('subscriptions', SubscriptionController::class);
    Route::post('/subscriptions/{subscription}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::get('/subscriptions-history', [SubscriptionController::class, 'history'])->name('subscriptions.history');

    // Seller Dashboard Route (with access control)
    Route::middleware('seller.access')->group(function () {
        Route::get('/seller/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');

        // Seller Shipping Tracking Routes
        Route::prefix('seller/shipping')->name('seller.shipping.')->group(function () {
            Route::get('/', [App\Http\Controllers\Seller\ShippingController::class, 'index'])->name('index');
            Route::get('/{shipping}', [App\Http\Controllers\Seller\ShippingController::class, 'show'])->name('show');
            Route::post('/{order}/sync', [App\Http\Controllers\Seller\ShippingController::class, 'sync'])->name('sync');
            Route::post('/sync-all', [App\Http\Controllers\Seller\ShippingController::class, 'syncAll'])->name('sync-all');
        });
    });

    // Seller Profit Management Routes (accessible during setup)
    Route::prefix('seller/profit-settings')->name('seller.profit-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'store'])->name('store');
        Route::post('/bulk-update', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'bulkUpdate'])->name('bulk-update');
        Route::post('/{profit}/toggle', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{profit}', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'destroy'])->name('destroy');
        Route::get('/api/subcategory/{categoryId}', [App\Http\Controllers\SellerSubcategoryProfitController::class, 'getProfitForSubcategory'])->name('api.get');
    });

    // Seller Ticket Routes (always accessible)
    Route::prefix('seller/tickets')->name('seller.tickets.')->group(function () {
        Route::get('/', [App\Http\Controllers\Seller\TicketController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Seller\TicketController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Seller\TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [App\Http\Controllers\Seller\TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [App\Http\Controllers\Seller\TicketController::class, 'reply'])->name('reply');
    });

    // Distributor Routes
    Route::middleware('distributor')->prefix('distributor')->name('distributor.')->group(function () {
        // Distributor Dashboard
        Route::get('/dashboard', [App\Http\Controllers\DistributorController::class, 'dashboard'])->name('dashboard');

        // Products Management
        Route::get('/products', [App\Http\Controllers\DistributorController::class, 'products'])->name('products');
        Route::get('/products/create', [App\Http\Controllers\DistributorController::class, 'createProduct'])->name('products.create');
        Route::post('/products', [App\Http\Controllers\DistributorController::class, 'storeProduct'])->name('products.store');
        Route::get('/products/{id}', [App\Http\Controllers\DistributorController::class, 'showProduct'])->name('products.show');
        Route::get('/products/{id}/edit', [App\Http\Controllers\DistributorController::class, 'editProduct'])->name('products.edit');
        Route::put('/products/{id}', [App\Http\Controllers\DistributorController::class, 'updateProduct'])->name('products.update');

        // Categories View
        Route::get('/categories', [App\Http\Controllers\DistributorController::class, 'categories'])->name('categories');
        Route::get('/categories/{parentId}/subcategories', [App\Http\Controllers\DistributorController::class, 'getSubcategories'])->name('categories.subcategories');

        // Orders View
        Route::get('/orders', [App\Http\Controllers\DistributorController::class, 'orders'])->name('orders');
        Route::post('/orders/{order}/update-status', [App\Http\Controllers\DistributorController::class, 'updateOrderStatus'])->name('orders.update-status');

        // Coupons Management
        Route::prefix('coupons')->name('coupons.')->group(function () {
            Route::get('/', [App\Http\Controllers\Distributor\CouponController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Distributor\CouponController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Distributor\CouponController::class, 'store'])->name('store');
            Route::get('/{coupon}', [App\Http\Controllers\Distributor\CouponController::class, 'show'])->name('show');
            Route::get('/{coupon}/edit', [App\Http\Controllers\Distributor\CouponController::class, 'edit'])->name('edit');
            Route::put('/{coupon}', [App\Http\Controllers\Distributor\CouponController::class, 'update'])->name('update');
            Route::delete('/{coupon}', [App\Http\Controllers\Distributor\CouponController::class, 'destroy'])->name('destroy');
            Route::post('/{coupon}/toggle-status', [App\Http\Controllers\Distributor\CouponController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/api/generate-code', [App\Http\Controllers\Distributor\CouponController::class, 'generateCode'])->name('generate-code');

            // Marketer activation requests (per coupon)
            Route::get('/{coupon}/activation-requests', [App\Http\Controllers\Distributor\CouponController::class, 'activationRequests'])->name('activation-requests');
            Route::post('/{coupon}/activation-requests/{marketer}/approve', [App\Http\Controllers\Distributor\CouponController::class, 'approveActivation'])->name('activation-requests.approve');
            Route::post('/{coupon}/activation-requests/{marketer}/reject', [App\Http\Controllers\Distributor\CouponController::class, 'rejectActivation'])->name('activation-requests.reject');
            Route::put('/{coupon}/activation-requests/{marketer}', [App\Http\Controllers\Distributor\CouponController::class, 'updateActivation'])->name('activation-requests.update');
            Route::delete('/{coupon}/activation-requests/{marketer}', [App\Http\Controllers\Distributor\CouponController::class, 'deleteActivation'])->name('activation-requests.delete');
        });

        // Support Tickets
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [App\Http\Controllers\Distributor\TicketController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Distributor\TicketController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Distributor\TicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [App\Http\Controllers\Distributor\TicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [App\Http\Controllers\Distributor\TicketController::class, 'reply'])->name('reply');
            Route::post('/{ticket}/coupon-decision', [App\Http\Controllers\Distributor\TicketController::class, 'couponDecision'])->name('coupon-decision');
        });

        // Shipping Rates Management
        Route::prefix('shipping')->name('shipping.')->group(function () {
            Route::get('/', [App\Http\Controllers\Distributor\ShippingRateController::class, 'index'])->name('index');
            Route::get('/cities/{countryCode}', [App\Http\Controllers\Distributor\ShippingRateController::class, 'cities'])->name('cities');
            Route::get('/districts/{city}', [App\Http\Controllers\Distributor\ShippingRateController::class, 'districts'])->name('districts');
            Route::post('/', [App\Http\Controllers\Distributor\ShippingRateController::class, 'store'])->name('store');
            Route::put('/{rate}', [App\Http\Controllers\Distributor\ShippingRateController::class, 'update'])->name('update');
            Route::post('/{rate}/toggle', [App\Http\Controllers\Distributor\ShippingRateController::class, 'toggle'])->name('toggle');
            Route::delete('/{rate}', [App\Http\Controllers\Distributor\ShippingRateController::class, 'destroy'])->name('destroy');
        });
    });

    // Marketer Routes (مسوق)
    Route::middleware('marketer')->prefix('marketer')->name('marketer.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\MarketerController::class, 'dashboard'])->name('dashboard');

        // Coupons (browse + request activation)
        Route::get('/coupons', [App\Http\Controllers\MarketerController::class, 'coupons'])->name('coupons.index');
        Route::post('/coupons/{coupon}/request-activation', [App\Http\Controllers\MarketerController::class, 'requestActivation'])->name('coupons.request-activation');

        // Store products (view only) + reports
        Route::get('/products', [App\Http\Controllers\MarketerController::class, 'products'])->name('products');
        Route::get('/reports', [App\Http\Controllers\MarketerController::class, 'reports'])->name('reports');

        // Support Tickets (reuses the marketer ticket controller)
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [App\Http\Controllers\Marketer\TicketController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Marketer\TicketController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Marketer\TicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [App\Http\Controllers\Marketer\TicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [App\Http\Controllers\Marketer\TicketController::class, 'reply'])->name('reply');
        });
    });

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Admin Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Token Management
        Route::get('/tokens', [AdminController::class, 'tokens'])->name('tokens');
        Route::post('/tokens', [AdminController::class, 'updateTokens'])->name('tokens.update');
        Route::get('/tokens/generate', [AdminController::class, 'generateToken'])->name('tokens.generate');
        Route::get('/tokens/callback', [AdminController::class, 'tokenCallback'])->name('tokens.callback');

        // Subscription Management
        Route::get('/subscriptions', [SubscriptionManagementController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/create', [SubscriptionManagementController::class, 'create'])->name('subscriptions.create');
        Route::post('/subscriptions', [SubscriptionManagementController::class, 'store'])->name('subscriptions.store');
        Route::get('/subscriptions/users', [SubscriptionManagementController::class, 'userSubscriptions'])->name('subscriptions.users');
        Route::get('/subscriptions/sellers', [SubscriptionManagementController::class, 'sellerSubscriptions'])->name('subscriptions.sellers');
        Route::get('/subscriptions/distributors', [SubscriptionManagementController::class, 'distributorSubscriptions'])->name('subscriptions.distributors');
        Route::get('/subscriptions/marketers', [SubscriptionManagementController::class, 'marketerSubscriptions'])->name('subscriptions.marketers');
        Route::get('/subscriptions/{subscription}/edit', [SubscriptionManagementController::class, 'edit'])->name('subscriptions.edit');
        Route::put('/subscriptions/{subscription}', [SubscriptionManagementController::class, 'update'])->name('subscriptions.update');
        Route::post('/subscriptions/{userSubscription}/close', [SubscriptionManagementController::class, 'closeSubscription'])->name('subscriptions.close');

        // Order Management
        Route::get('/orders', [OrderManagementController::class, 'index'])->name('orders.index');
        Route::post('/orders/{order}/sync', [OrderManagementController::class, 'sync'])->name('orders.sync');
        Route::post('/orders/bulk-sync', [OrderManagementController::class, 'bulkSync'])->name('orders.bulk-sync');
        Route::post('/orders/{order}/update-status', [OrderManagementController::class, 'updateStatus'])->name('orders.update-status');

        // Order Profit Management
        Route::get('/order-profits', [App\Http\Controllers\Admin\OrderProfitController::class, 'index'])->name('order-profits.index');

        // Training Videos Management
        Route::resource('training-videos', App\Http\Controllers\Admin\TrainingVideoController::class);
        Route::post('/training-videos/{trainingVideo}/toggle', [App\Http\Controllers\Admin\TrainingVideoController::class, 'toggleActive'])->name('training-videos.toggle');

        // Shipping Tracking Management
        Route::prefix('shipping')->name('shipping.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ShippingTrackingController::class, 'index'])->name('index');
            Route::get('/{shipping}', [App\Http\Controllers\Admin\ShippingTrackingController::class, 'show'])->name('show');
            Route::post('/{order}/sync', [App\Http\Controllers\Admin\ShippingTrackingController::class, 'syncTracking'])->name('sync');
            Route::post('/sync-all', [App\Http\Controllers\Admin\ShippingTrackingController::class, 'syncAll'])->name('sync-all');
        });

        // Categories Management (use existing CategoryController)
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

        // User Management - General
        Route::get('/users', [App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/toggle-block', [App\Http\Controllers\Admin\UserManagementController::class, 'toggleBlock'])->name('users.toggle-block');
        Route::post('/users/{user}/send-notification', [App\Http\Controllers\Admin\UserManagementController::class, 'sendNotification'])->name('users.send-notification');
        Route::post('/users/{user}/assign-subscription', [App\Http\Controllers\Admin\UserManagementController::class, 'assignSubscription'])->name('users.assign-subscription');
        Route::post('/users/{user}/extend-subscription', [App\Http\Controllers\Admin\UserManagementController::class, 'extendSubscription'])->name('users.extend-subscription');

        // Admin Users Management
        Route::get('/admins', [App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('admins.index');
        Route::get('/admins/create', [App\Http\Controllers\Admin\AdminUserController::class, 'create'])->name('admins.create');
        Route::post('/admins', [App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('admins.store');
        Route::get('/admins/{admin}/edit', [App\Http\Controllers\Admin\AdminUserController::class, 'edit'])->name('admins.edit');
        Route::put('/admins/{admin}', [App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('admins.update');
        Route::delete('/admins/{admin}', [App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('admins.destroy');

        // Seller Users Management
        Route::get('/sellers', [App\Http\Controllers\Admin\SellerUserController::class, 'index'])->name('sellers.index');
        Route::get('/sellers/{seller}', [App\Http\Controllers\Admin\SellerUserController::class, 'show'])->name('sellers.show');
        Route::patch('/sellers/{seller}/verification', [App\Http\Controllers\Admin\SellerUserController::class, 'updateVerification'])->name('sellers.update-verification');
        Route::patch('/sellers/{seller}/toggle-status', [App\Http\Controllers\Admin\SellerUserController::class, 'toggleStatus'])->name('sellers.toggle-status');
        Route::delete('/sellers/{seller}', [App\Http\Controllers\Admin\SellerUserController::class, 'destroy'])->name('sellers.destroy');

        // Customer Users Management
        Route::get('/customers', [App\Http\Controllers\Admin\CustomerUserController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [App\Http\Controllers\Admin\CustomerUserController::class, 'show'])->name('customers.show');
        Route::delete('/customers/{customer}', [App\Http\Controllers\Admin\CustomerUserController::class, 'destroy'])->name('customers.destroy');

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

        // Currency Management
        Route::resource('currencies', App\Http\Controllers\Admin\CurrencyManagementController::class);
        Route::post('/currencies/{currency}/toggle-active', [App\Http\Controllers\Admin\CurrencyManagementController::class, 'toggleActive'])->name('currencies.toggle-active');
        Route::post('/currencies/{currency}/set-default', [App\Http\Controllers\Admin\CurrencyManagementController::class, 'setDefault'])->name('currencies.set-default');

        // Countries Management
        Route::resource('countries', App\Http\Controllers\Admin\CountryController::class)->except(['show']);
        Route::post('/countries/{country}/toggle-status', [App\Http\Controllers\Admin\CountryController::class, 'toggleStatus'])->name('countries.toggle-status');

        // Cities & Districts Management (locations)
        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\LocationController::class, 'index'])->name('index');
            Route::get('/cities/{city}/districts', [App\Http\Controllers\Admin\LocationController::class, 'districts'])->name('cities.districts');
            // Cities
            Route::post('/cities', [App\Http\Controllers\Admin\LocationController::class, 'storeCity'])->name('cities.store');
            Route::put('/cities/{city}', [App\Http\Controllers\Admin\LocationController::class, 'updateCity'])->name('cities.update');
            Route::post('/cities/{city}/toggle', [App\Http\Controllers\Admin\LocationController::class, 'toggleCity'])->name('cities.toggle');
            Route::delete('/cities/{city}', [App\Http\Controllers\Admin\LocationController::class, 'destroyCity'])->name('cities.destroy');
            // Districts
            Route::post('/districts', [App\Http\Controllers\Admin\LocationController::class, 'storeDistrict'])->name('districts.store');
            Route::put('/districts/{district}', [App\Http\Controllers\Admin\LocationController::class, 'updateDistrict'])->name('districts.update');
            Route::post('/districts/{district}/toggle', [App\Http\Controllers\Admin\LocationController::class, 'toggleDistrict'])->name('districts.toggle');
            Route::delete('/districts/{district}', [App\Http\Controllers\Admin\LocationController::class, 'destroyDistrict'])->name('districts.destroy');
        });

        // Distributor Products Moderation
        Route::prefix('distributor-products')->name('distributor-products.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\DistributorProductController::class, 'index'])->name('index');
            Route::get('/{productId}/{userId}', [App\Http\Controllers\Admin\DistributorProductController::class, 'show'])->name('show');
            Route::post('/{productId}/{userId}/approve', [App\Http\Controllers\Admin\DistributorProductController::class, 'approve'])->name('approve');
            Route::post('/{productId}/{userId}/reject', [App\Http\Controllers\Admin\DistributorProductController::class, 'reject'])->name('reject');
            Route::post('/bulk-approve', [App\Http\Controllers\Admin\DistributorProductController::class, 'bulkApprove'])->name('bulk-approve');
        });

        // Logs Management
        Route::get('/logs', [App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');
        Route::get('/logs/download', [App\Http\Controllers\Admin\LogController::class, 'download'])->name('logs.download');
        Route::delete('/logs/delete', [App\Http\Controllers\Admin\LogController::class, 'delete'])->name('logs.delete');
        Route::delete('/logs/clear', [App\Http\Controllers\Admin\LogController::class, 'clear'])->name('logs.clear');

        // Ticket Management
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\TicketController::class, 'index'])->name('index');
            Route::get('/{ticket}', [App\Http\Controllers\Admin\TicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [App\Http\Controllers\Admin\TicketController::class, 'reply'])->name('reply');
            Route::post('/{ticket}/status', [App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('update-status');
            Route::post('/{ticket}/assign', [App\Http\Controllers\Admin\TicketController::class, 'assign'])->name('assign');
            Route::post('/{ticket}/coupon-decision', [App\Http\Controllers\Admin\TicketController::class, 'couponDecision'])->name('coupon-decision');
        });

        // Affiliate Marketing Management
        Route::prefix('affiliate')->name('affiliate.')->group(function () {
            // Stores Management
            Route::get('/stores', [App\Http\Controllers\Admin\AffiliateController::class, 'stores'])->name('stores');
            Route::get('/stores/create', [App\Http\Controllers\Admin\AffiliateController::class, 'createStore'])->name('stores.create');
            Route::post('/stores', [App\Http\Controllers\Admin\AffiliateController::class, 'storeStore'])->name('stores.store');
            Route::get('/stores/{store}', [App\Http\Controllers\Admin\AffiliateController::class, 'showStore'])->name('stores.show');

            // Coupons Management
            Route::get('/coupons/active', [App\Http\Controllers\Admin\AffiliateController::class, 'activeCoupons'])->name('coupons.active');
            Route::get('/coupons/expired', [App\Http\Controllers\Admin\AffiliateController::class, 'expiredCoupons'])->name('coupons.expired');
            Route::get('/coupons/create', [App\Http\Controllers\Admin\AffiliateController::class, 'createCoupon'])->name('coupons.create');
            Route::post('/coupons', [App\Http\Controllers\Admin\AffiliateController::class, 'storeCoupon'])->name('coupons.store');
            Route::get('/coupons/generate-code', [App\Http\Controllers\Admin\AffiliateController::class, 'generateCode'])->name('coupons.generate-code');
            Route::get('/coupons/{coupon}', [App\Http\Controllers\Admin\AffiliateController::class, 'showCoupon'])->name('coupons.show');
            Route::get('/coupons/{coupon}/edit', [App\Http\Controllers\Admin\AffiliateController::class, 'editCoupon'])->name('coupons.edit');
            Route::put('/coupons/{coupon}', [App\Http\Controllers\Admin\AffiliateController::class, 'updateCoupon'])->name('coupons.update');
            Route::delete('/coupons/{coupon}', [App\Http\Controllers\Admin\AffiliateController::class, 'destroyCoupon'])->name('coupons.destroy');
            Route::post('/coupons/{coupon}/toggle-status', [App\Http\Controllers\Admin\AffiliateController::class, 'toggleCouponStatus'])->name('coupons.toggle-status');
        });
    });
});

require __DIR__.'/auth.php';
