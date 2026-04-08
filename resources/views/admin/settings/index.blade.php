@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.site_settings') }}</h4>
        <p class="text-muted">{{ __('messages.manage_site_settings') }}</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Localization & Currency Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.localization_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $siteLanguage = $settings->get('select', collect())->firstWhere('key', 'site_language');
                                $siteCurrency = $settings->get('select', collect())->firstWhere('key', 'site_currency');
                            @endphp

                            <div class="col-md-6 mb-3">
                                <label for="site_language" class="form-label">
                                    {{ __('messages.site_language') }}
                                    @if($siteLanguage?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $siteLanguage->description }}"></i>
                                    @endif
                                </label>
                                <select name="settings[site_language]" id="site_language" class="form-control">
                                    <option value="ar" {{ old('settings.site_language', $siteLanguage?->value ?? 'ar') === 'ar' ? 'selected' : '' }}>
                                        العربية (Arabic)
                                    </option>
                                    <option value="en" {{ old('settings.site_language', $siteLanguage?->value ?? 'ar') === 'en' ? 'selected' : '' }}>
                                        English
                                    </option>
                                </select>
                                <small class="text-muted">{{ __('messages.site_language_hint') }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="site_currency" class="form-label">
                                    {{ __('messages.site_currency') }}
                                    @if($siteCurrency?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $siteCurrency->description }}"></i>
                                    @endif
                                </label>
                                <select name="settings[site_currency]" id="site_currency" class="form-control">
                                    <option value="AED" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'AED' ? 'selected' : '' }}>
                                        AED - درهم إماراتي
                                    </option>
                                    <option value="SAR" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'SAR' ? 'selected' : '' }}>
                                        SAR - ريال سعودي
                                    </option>
                                    <option value="USD" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'USD' ? 'selected' : '' }}>
                                        USD - دولار أمريكي
                                    </option>
                                    <option value="EUR" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'EUR' ? 'selected' : '' }}>
                                        EUR - يورو
                                    </option>
                                    <option value="EGP" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'EGP' ? 'selected' : '' }}>
                                        EGP - جنيه مصري
                                    </option>
                                    <option value="KWD" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'KWD' ? 'selected' : '' }}>
                                        KWD - دينار كويتي
                                    </option>
                                    <option value="QAR" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'QAR' ? 'selected' : '' }}>
                                        QAR - ريال قطري
                                    </option>
                                    <option value="OMR" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'OMR' ? 'selected' : '' }}>
                                        OMR - ريال عماني
                                    </option>
                                    <option value="BHD" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'BHD' ? 'selected' : '' }}>
                                        BHD - دينار بحريني
                                    </option>
                                </select>
                                <small class="text-muted">{{ __('messages.site_currency_hint') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bilingual Site Name & Description -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-translate-2 me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'اسم ووصف الموقع (ثنائي اللغة)' : 'Site Name & Description (Bilingual)' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $siteName = $settings->get('text', collect())->firstWhere('key', 'site_name');
                            $siteNameAr = $settings->get('text', collect())->firstWhere('key', 'site_name_ar');
                            $siteDesc = $settings->get('textarea', collect())->firstWhere('key', 'site_description');
                            $siteDescAr = $settings->get('textarea', collect())->firstWhere('key', 'site_description_ar');
                        @endphp
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="site_name" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'اسم الموقع (English)' : 'Site Name (English)' }}
                                </label>
                                <input type="text" name="settings[site_name]" id="site_name" class="form-control"
                                    value="{{ old('settings.site_name', $siteName?->value) }}" placeholder="My E-Commerce">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="site_name_ar" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'اسم الموقع (عربي)' : 'Site Name (Arabic)' }}
                                </label>
                                <input type="text" name="settings[site_name_ar]" id="site_name_ar" class="form-control"
                                    value="{{ old('settings.site_name_ar', $siteNameAr?->value) }}" placeholder="متجري الإلكتروني" dir="rtl">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="site_description" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'وصف الموقع (English)' : 'Site Description (English)' }}
                                </label>
                                <textarea name="settings[site_description]" id="site_description" class="form-control" rows="3"
                                    placeholder="Your one-stop shop for everything">{{ old('settings.site_description', $siteDesc?->value) }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="site_description_ar" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'وصف الموقع (عربي)' : 'Site Description (Arabic)' }}
                                </label>
                                <textarea name="settings[site_description_ar]" id="site_description_ar" class="form-control" rows="3"
                                    placeholder="متجرك الشامل لكل احتياجاتك" dir="rtl">{{ old('settings.site_description_ar', $siteDescAr?->value) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Banners -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-image-2-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'بنرات لوحة التحكم' : 'Dashboard Banners' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $sellerBanner = $settings->get('image', collect())->firstWhere('key', 'seller_dashboard_banner');
                            $distributorBanner = $settings->get('image', collect())->firstWhere('key', 'distributor_dashboard_banner');
                            $buyerBanner = $settings->get('image', collect())->firstWhere('key', 'buyer_dashboard_banner');
                        @endphp
                        <div class="row">
                            <!-- Seller Dashboard Banner -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'بنر لوحة تحكم البائع' : 'Seller Dashboard Banner' }}
                                </label>
                                @if($sellerBanner?->value)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $sellerBanner->value) }}" alt="Seller Banner" class="img-thumbnail" style="max-width: 100%; max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1 delete-image" data-key="seller_dashboard_banner">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                @endif
                                <input type="file" name="settings[seller_dashboard_banner]" class="form-control" accept="image/*">
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الحجم الموصى به: 1200x300 بكسل' : 'Recommended size: 1200x300px' }}</small>
                            </div>

                            <!-- Distributor Dashboard Banner -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'بنر لوحة تحكم التاجر' : 'Distributor Dashboard Banner' }}
                                </label>
                                @if($distributorBanner?->value)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $distributorBanner->value) }}" alt="Distributor Banner" class="img-thumbnail" style="max-width: 100%; max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1 delete-image" data-key="distributor_dashboard_banner">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                @endif
                                <input type="file" name="settings[distributor_dashboard_banner]" class="form-control" accept="image/*">
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الحجم الموصى به: 1200x300 بكسل' : 'Recommended size: 1200x300px' }}</small>
                            </div>

                            <!-- Buyer Dashboard Banner -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'بنر لوحة تحكم المشتري' : 'Buyer Dashboard Banner' }}
                                </label>
                                @if($buyerBanner?->value)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $buyerBanner->value) }}" alt="Buyer Banner" class="img-thumbnail" style="max-width: 100%; max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1 delete-image" data-key="buyer_dashboard_banner">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                @endif
                                <input type="file" name="settings[buyer_dashboard_banner]" class="form-control" accept="image/*">
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الحجم الموصى به: 1200x300 بكسل' : 'Recommended size: 1200x300px' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promotional Banners (Under Subscription) -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-advertisement-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'بنرات ترويجية (أسفل عداد الاشتراك)' : 'Promotional Banners (Under Subscription Timer)' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $sellerPromoBanner = $settings->get('image', collect())->firstWhere('key', 'seller_promo_banner');
                            $distributorPromoBanner = $settings->get('image', collect())->firstWhere('key', 'distributor_promo_banner');
                            $sellerPromoBannerLink = $settings->get('text', collect())->firstWhere('key', 'seller_promo_banner_link');
                            $distributorPromoBannerLink = $settings->get('text', collect())->firstWhere('key', 'distributor_promo_banner_link');
                        @endphp
                        <div class="row">
                            <!-- Seller Promo Banner -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'بنر ترويجي للبائع' : 'Seller Promo Banner' }}
                                </label>
                                @if($sellerPromoBanner?->value)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $sellerPromoBanner->value) }}" alt="Seller Promo Banner" class="img-thumbnail" style="max-width: 100%; max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1 delete-image" data-key="seller_promo_banner">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                @endif
                                <input type="file" name="settings[seller_promo_banner]" class="form-control" accept="image/*">
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الحجم الموصى به: 1200x200 بكسل' : 'Recommended size: 1200x200px' }}</small>

                                <label class="form-label mt-3">
                                    {{ app()->getLocale() == 'ar' ? 'رابط البنر (اختياري)' : 'Banner Link (Optional)' }}
                                </label>
                                <input type="url" name="settings[seller_promo_banner_link]" class="form-control" value="{{ $sellerPromoBannerLink?->value }}" placeholder="https://example.com">
                            </div>

                            <!-- Distributor Promo Banner -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'بنر ترويجي للتاجر' : 'Distributor Promo Banner' }}
                                </label>
                                @if($distributorPromoBanner?->value)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $distributorPromoBanner->value) }}" alt="Distributor Promo Banner" class="img-thumbnail" style="max-width: 100%; max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1 delete-image" data-key="distributor_promo_banner">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                @endif
                                <input type="file" name="settings[distributor_promo_banner]" class="form-control" accept="image/*">
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الحجم الموصى به: 1200x200 بكسل' : 'Recommended size: 1200x200px' }}</small>

                                <label class="form-label mt-3">
                                    {{ app()->getLocale() == 'ar' ? 'رابط البنر (اختياري)' : 'Banner Link (Optional)' }}
                                </label>
                                <input type="url" name="settings[distributor_promo_banner_link]" class="form-control" value="{{ $distributorPromoBannerLink?->value }}" placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallet Settings - Min Withdrawal/Deposit -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-wallet-3-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'إعدادات المحفظة' : 'Wallet Settings' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $minWithdrawal = $settings->get('number', collect())->firstWhere('key', 'min_withdrawal_amount');
                            $minDeposit = $settings->get('number', collect())->firstWhere('key', 'min_deposit_amount');
                            $maxWithdrawal = $settings->get('number', collect())->firstWhere('key', 'max_withdrawal_amount');
                            $withdrawalFee = $settings->get('number', collect())->firstWhere('key', 'withdrawal_fee_percentage');
                        @endphp
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="min_withdrawal_amount" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'الحد الأدنى للسحب' : 'Min Withdrawal Amount' }}
                                </label>
                                <div class="input-group">
                                    <input type="number" name="settings[min_withdrawal_amount]" id="min_withdrawal_amount" class="form-control"
                                        value="{{ old('settings.min_withdrawal_amount', $minWithdrawal?->value ?? 50) }}" step="0.01" min="0">
                                    <span class="input-group-text">AED</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="max_withdrawal_amount" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'الحد الأقصى للسحب' : 'Max Withdrawal Amount' }}
                                </label>
                                <div class="input-group">
                                    <input type="number" name="settings[max_withdrawal_amount]" id="max_withdrawal_amount" class="form-control"
                                        value="{{ old('settings.max_withdrawal_amount', $maxWithdrawal?->value ?? 10000) }}" step="0.01" min="0">
                                    <span class="input-group-text">AED</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="min_deposit_amount" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'الحد الأدنى للإيداع' : 'Min Deposit Amount' }}
                                </label>
                                <div class="input-group">
                                    <input type="number" name="settings[min_deposit_amount]" id="min_deposit_amount" class="form-control"
                                        value="{{ old('settings.min_deposit_amount', $minDeposit?->value ?? 10) }}" step="0.01" min="0">
                                    <span class="input-group-text">AED</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="withdrawal_fee_percentage" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'رسوم السحب (%)' : 'Withdrawal Fee (%)' }}
                                </label>
                                <div class="input-group">
                                    <input type="number" name="settings[withdrawal_fee_percentage]" id="withdrawal_fee_percentage" class="form-control"
                                        value="{{ old('settings.withdrawal_fee_percentage', $withdrawalFee?->value ?? 0) }}" step="0.01" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Lock Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-lock-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'قفل الاشتراك' : 'Subscription Lock Settings' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $requireSubscriptionSeller = $settings->get('boolean', collect())->firstWhere('key', 'require_subscription_seller');
                            $requireSubscriptionDistributor = $settings->get('boolean', collect())->firstWhere('key', 'require_subscription_distributor');
                            $lockProductsOnExpiry = $settings->get('boolean', collect())->firstWhere('key', 'lock_products_on_subscription_expiry');
                            $gracePeriodDays = $settings->get('number', collect())->firstWhere('key', 'subscription_grace_period_days');
                            $allPlans = \App\Models\Subscription::orderBy('sort_order')->get();
                        @endphp

                        {{-- Enable/disable toggles --}}
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="require_subscription_seller" name="settings[require_subscription_seller]" value="1"
                                        {{ old('settings.require_subscription_seller', $requireSubscriptionSeller?->value) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_subscription_seller">
                                        {{ app()->getLocale() == 'ar' ? 'مطلوب اشتراك للبائعين' : 'Require Subscription for Sellers' }}
                                    </label>
                                </div>
                                <small class="text-muted d-block">{{ app()->getLocale() == 'ar' ? 'يجب على البائعين الاشتراك لإضافة منتجات' : 'Sellers must subscribe to add products' }}</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="require_subscription_distributor" name="settings[require_subscription_distributor]" value="1"
                                        {{ old('settings.require_subscription_distributor', $requireSubscriptionDistributor?->value) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_subscription_distributor">
                                        {{ app()->getLocale() == 'ar' ? 'مطلوب اشتراك للتجار' : 'Require Subscription for Distributors' }}
                                    </label>
                                </div>
                                <small class="text-muted d-block">{{ app()->getLocale() == 'ar' ? 'يجب على التجار الاشتراك للوصول' : 'Distributors must subscribe to access products' }}</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="lock_products_on_subscription_expiry" name="settings[lock_products_on_subscription_expiry]" value="1"
                                        {{ old('settings.lock_products_on_subscription_expiry', $lockProductsOnExpiry?->value) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lock_products_on_subscription_expiry">
                                        {{ app()->getLocale() == 'ar' ? 'قفل المنتجات عند انتهاء الاشتراك' : 'Lock Products on Expiry' }}
                                    </label>
                                </div>
                                <small class="text-muted d-block">{{ app()->getLocale() == 'ar' ? 'إخفاء المنتجات عند انتهاء الاشتراك' : 'Hide products when subscription expires' }}</small>
                            </div>
                        </div>

                        {{-- Grace period --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="subscription_grace_period_days" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'فترة السماح (أيام)' : 'Grace Period (Days)' }}
                                </label>
                                <input type="number" name="settings[subscription_grace_period_days]" id="subscription_grace_period_days" class="form-control"
                                    value="{{ old('settings.subscription_grace_period_days', $gracePeriodDays?->value ?? 0) }}" min="0" max="30">
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'عدد الأيام المسموحة بعد انتهاء الاشتراك' : 'Days allowed after subscription expiry' }}</small>
                            </div>
                        </div>

                        {{-- All subscription plans --}}
                        @if($allPlans->count() > 0)
                        <hr>
                        <p class="fw-600 mb-3">
                            <i class="ri-vip-crown-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'باقات الاشتراك المتاحة' : 'Available Subscription Plans' }}
                        </p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ app()->getLocale() == 'ar' ? 'الباقة' : 'Plan' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'السعر' : 'Price' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'المدة' : 'Duration' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'أقصى منتجات' : 'Max Products' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'نسبة العمولة' : 'Commission %' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'مفعّل' : 'Active' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'إجراء' : 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allPlans as $plan)
                                    <tr>
                                        <td>
                                            <strong>{{ app()->getLocale() == 'ar' ? ($plan->name_ar ?: $plan->name) : $plan->name }}</strong>
                                        </td>
                                        <td>{{ number_format($plan->price, 2) }} AED</td>
                                        <td>{{ $plan->duration_days }} {{ app()->getLocale() == 'ar' ? 'يوم' : 'days' }}</td>
                                        <td>{{ $plan->max_products ?? ($app()->getLocale() == 'ar' ? 'غير محدود' : 'Unlimited') }}</td>
                                        <td>{{ $plan->commission_rate }}%</td>
                                        <td>
                                            <span class="badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}">
                                                {{ $plan->is_active ? (app()->getLocale() == 'ar' ? 'مفعّل' : 'Active') : (app()->getLocale() == 'ar' ? 'معطّل' : 'Inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.subscriptions.edit', $plan) }}" class="btn btn-xs btn-outline-primary">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-sm btn-success">
                                <i class="ri-add-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'إضافة باقة جديدة' : 'Add New Plan' }}
                            </a>
                            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-outline-primary ms-2">
                                {{ app()->getLocale() == 'ar' ? 'إدارة الباقات' : 'Manage Plans' }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Gateway Settings -->
            <div class="col-12 mb-4" id="payment-gateway-settings">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-secure-payment-line me-2"></i>
                            {{ __('messages.payment_gateway_settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $ziinaApiKey = $settings->get('text', collect())->firstWhere('key', 'ziina_api_key');
                            @endphp

                            <!-- Ziina API Key -->
                            <div class="col-md-12 mb-3">
                                <label for="ziina_api_key" class="form-label">
                                    <i class="ri-lock-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'مفتاح API لـ Ziina' : 'Ziina API Key' }}
                                </label>
                                <input
                                    type="password"
                                    name="settings[ziina_api_key]"
                                    id="ziina_api_key"
                                    class="form-control"
                                    value="{{ old('settings.ziina_api_key', $ziinaApiKey?->value ?? '') }}"
                                    placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل مفتاح API الخاص بـ Ziina' : 'Enter your Ziina API Key' }}">
                                <small class="text-muted d-block mt-2">
                                    {{ app()->getLocale() == 'ar' ? 'الحصول على مفتاح API من لوحة تحكم Ziina' : 'Get your API key from Ziina dashboard' }}
                                </small>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="show_ziina_key" onclick="togglePasswordVisibility('ziina_api_key')">
                                    <label class="form-check-label" for="show_ziina_key">
                                        {{ app()->getLocale() == 'ar' ? 'إظهار المفتاح' : 'Show Key' }}
                                    </label>
                                </div>
                            </div>

                            @php
                                $recaptchaSiteKey = $settings->get('text', collect())->firstWhere('key', 'recaptcha_site_key');
                                $recaptchaSecretKey = $settings->get('text', collect())->firstWhere('key', 'recaptcha_secret_key');
                                $recaptchaEnabled = $settings->get('boolean', collect())->firstWhere('key', 'recaptcha_enabled');
                            @endphp

                            <div class="col-12 mt-3 mb-2">
                                <hr>
                                <h6 class="text-muted">
                                    <i class="ri-robot-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'إعدادات reCAPTCHA' : 'reCAPTCHA Settings' }}
                                </h6>
                            </div>

                            <!-- reCAPTCHA Enable Toggle -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                        name="settings[recaptcha_enabled]"
                                        id="recaptcha_enabled"
                                        value="1"
                                        {{ old('settings.recaptcha_enabled', $recaptchaEnabled?->value) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="recaptcha_enabled">
                                        {{ app()->getLocale() == 'ar' ? 'تفعيل التحقق reCAPTCHA' : 'Enable reCAPTCHA Verification' }}
                                    </label>
                                </div>
                            </div>

                            <!-- reCAPTCHA Site Key -->
                            <div class="col-md-6 mb-3">
                                <label for="recaptcha_site_key" class="form-label">
                                    <i class="ri-key-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'مفتاح الموقع (Site Key)' : 'reCAPTCHA Site Key' }}
                                </label>
                                <input
                                    type="password"
                                    name="settings[recaptcha_site_key]"
                                    id="recaptcha_site_key"
                                    class="form-control"
                                    value="{{ old('settings.recaptcha_site_key', $recaptchaSiteKey?->value ?? '') }}"
                                    placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل مفتاح الموقع' : 'Enter reCAPTCHA site key' }}">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="show_recaptcha_site" onclick="togglePasswordVisibility('recaptcha_site_key')">
                                    <label class="form-check-label" for="show_recaptcha_site">
                                        {{ app()->getLocale() == 'ar' ? 'إظهار المفتاح' : 'Show Key' }}
                                    </label>
                                </div>
                            </div>

                            <!-- reCAPTCHA Secret Key -->
                            <div class="col-md-6 mb-3">
                                <label for="recaptcha_secret_key" class="form-label">
                                    <i class="ri-shield-keyhole-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'المفتاح السري (Secret Key)' : 'reCAPTCHA Secret Key' }}
                                </label>
                                <input
                                    type="password"
                                    name="settings[recaptcha_secret_key]"
                                    id="recaptcha_secret_key"
                                    class="form-control"
                                    value="{{ old('settings.recaptcha_secret_key', $recaptchaSecretKey?->value ?? '') }}"
                                    placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل المفتاح السري' : 'Enter reCAPTCHA secret key' }}">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="show_recaptcha_secret" onclick="togglePasswordVisibility('recaptcha_secret_key')">
                                    <label class="form-check-label" for="show_recaptcha_secret">
                                        {{ app()->getLocale() == 'ar' ? 'إظهار المفتاح' : 'Show Key' }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- General Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.general_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $excludeKeys = ['admin_profit_type', 'site_name', 'site_name_ar', 'site_description', 'site_description_ar', 'ziina_api_key', 'recaptcha_site_key', 'recaptcha_secret_key'];
                            @endphp
                            @foreach($settings->get('text', collect())->merge($settings->get('textarea', collect())) as $setting)
                            @if(!in_array($setting->key, $excludeKeys))
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>
                                @if($setting->type === 'textarea')
                                <textarea
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    rows="3">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                @else
                                <input
                                    type="text"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}">
                                @endif
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.email_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($settings->get('email', collect()) as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>
                                <input
                                    type="email"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Settings -->
            @php
                $excludedImageKeys = [
                    'seller_dashboard_banner', 'distributor_dashboard_banner', 'buyer_dashboard_banner',
                    'seller_promo_banner', 'distributor_promo_banner',
                ];
                $otherImageSettings = $settings->get('image', collect())->filter(fn($s) => !in_array($s->key, $excludedImageKeys));
            @endphp
            @if($otherImageSettings->count() > 0)
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.image_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($otherImageSettings as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>

                                @if($setting->value)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $setting->value) }}"
                                         alt="{{ $setting->key }}"
                                         class="img-thumbnail"
                                         style="max-width: 200px; max-height: 200px;">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger ms-2 delete-image"
                                        data-key="{{ $setting->key }}">
                                        <i class="ri-delete-bin-line"></i> {{ __('messages.delete') }}
                                    </button>
                                </div>
                                @endif

                                <input
                                    type="file"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    accept="image/*">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Admin Profit Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.admin_profit_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $profitType = $settings->get('text', collect())->firstWhere('key', 'admin_profit_type');
                                $profitPercentage = $settings->get('number', collect())->firstWhere('key', 'admin_profit_percentage');
                                $profitFixed = $settings->get('number', collect())->firstWhere('key', 'admin_profit_fixed');
                                $profitCommission = $settings->get('number', collect())->firstWhere('key', 'admin_profit_commission');
                            @endphp

                            <div class="col-md-12 mb-3">
                                <label for="admin_profit_type" class="form-label">
                                    {{ __('messages.profit_type') }}
                                </label>
                                <select name="settings[admin_profit_type]" id="admin_profit_type" class="form-control">
                                    <option value="percentage" {{ old('settings.admin_profit_type', $profitType?->value) === 'percentage' ? 'selected' : '' }}>
                                        {{ __('messages.percentage') }}
                                    </option>
                                    <option value="fixed" {{ old('settings.admin_profit_type', $profitType?->value) === 'fixed' ? 'selected' : '' }}>
                                        {{ __('messages.fixed_amount') }}
                                    </option>
                                    <option value="commission" {{ old('settings.admin_profit_type', $profitType?->value) === 'commission' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'عمولة (المتاجرة بالعمولة)' : 'Commission' }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3" id="percentage_field">
                                <label for="admin_profit_percentage" class="form-label">
                                    {{ __('messages.profit_percentage') }}
                                    @if($profitPercentage?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $profitPercentage->description }}"></i>
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="settings[admin_profit_percentage]"
                                        id="admin_profit_percentage"
                                        class="form-control"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        value="{{ old('settings.admin_profit_percentage', $profitPercentage?->value) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3" id="fixed_field">
                                <label for="admin_profit_fixed" class="form-label">
                                    {{ __('messages.fixed_profit_amount') }}
                                    @if($profitFixed?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $profitFixed->description }}"></i>
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="settings[admin_profit_fixed]"
                                        id="admin_profit_fixed"
                                        class="form-control"
                                        step="0.01"
                                        min="0"
                                        value="{{ old('settings.admin_profit_fixed', $profitFixed?->value) }}">
                                    <span class="input-group-text">{{ $settings->get('text', collect())->firstWhere('key', 'currency')?->value ?? 'AED' }}</span>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3" id="commission_field">
                                <label for="admin_profit_commission" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'نسبة العمولة' : 'Commission Rate' }}
                                    <i class="ri-question-line" data-bs-toggle="tooltip"
                                        title="{{ app()->getLocale() == 'ar' ? 'نسبة العمولة التي تحصل عليها المنصة من كل عملية بيع (المتاجرة بالعمولة)' : 'Commission percentage the platform earns from each sale' }}"></i>
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="settings[admin_profit_commission]"
                                        id="admin_profit_commission"
                                        class="form-control"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        value="{{ old('settings.admin_profit_commission', $profitCommission?->value) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    {{ app()->getLocale() == 'ar' ? 'نسبة مئوية تُخصم من قيمة كل طلب كعمولة للمنصة' : 'Percentage deducted from each order value as platform commission' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Numeric Settings (excluding profit, wallet, and subscription settings shown above) -->
            @php
                $numericExcludeKeys = [
                    'admin_profit_percentage', 'admin_profit_fixed', 'admin_profit_commission',
                    'min_withdrawal_amount', 'max_withdrawal_amount', 'min_deposit_amount', 'withdrawal_fee_percentage',
                    'subscription_grace_period_days',
                ];
                $numericSettings = $settings->get('number', collect())->filter(function($setting) use ($numericExcludeKeys) {
                    return !in_array($setting->key, $numericExcludeKeys);
                });
            @endphp

            @if($numericSettings->count() > 0)
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.other_numeric_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($numericSettings as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>
                                <input
                                    type="number"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    step="0.01"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Theme Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.theme_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $primaryColor = $settings->get('color', collect())->firstWhere('key', 'primary_color');
                                $primaryLightColor = $settings->get('color', collect())->firstWhere('key', 'primary_light_color');
                                $themeStyle = $settings->get('select', collect())->firstWhere('key', 'theme_style');
                            @endphp

                            <div class="col-md-6 mb-3">
                                <label for="primary_color" class="form-label">
                                    {{ __('messages.primary_color') }}
                                    @if($primaryColor?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $primaryColor->description }}"></i>
                                    @endif
                                </label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input
                                        type="color"
                                        id="primary_color_picker"
                                        class="form-control form-control-color"
                                        value="{{ old('settings.primary_color', $primaryColor?->value ?? '#666cff') }}"
                                        style="width: 60px; height: 38px;">
                                    <input
                                        type="text"
                                        name="settings[primary_color]"
                                        id="primary_color"
                                        class="form-control"
                                        value="{{ old('settings.primary_color', $primaryColor?->value ?? '#666cff') }}"
                                        placeholder="#666cff"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        maxlength="7">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="reset_primary_color" title="Reset to default">
                                        <i class="ri-restart-line"></i>
                                    </button>
                                </div>
                                <small class="text-muted">{{ __('messages.primary_color_hint') }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="primary_light_color" class="form-label">
                                    {{ __('messages.primary_light_color') }}
                                    @if($primaryLightColor?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $primaryLightColor->description }}"></i>
                                    @endif
                                </label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input
                                        type="color"
                                        id="primary_light_color_picker"
                                        class="form-control form-control-color"
                                        value="{{ old('settings.primary_light_color', $primaryLightColor?->value ?? '#e7e7ff') }}"
                                        style="width: 60px; height: 38px;">
                                    <input
                                        type="text"
                                        name="settings[primary_light_color]"
                                        id="primary_light_color"
                                        class="form-control"
                                        value="{{ old('settings.primary_light_color', $primaryLightColor?->value ?? '#e7e7ff') }}"
                                        placeholder="#e7e7ff"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        maxlength="7">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="auto_generate_light" title="Auto-generate from primary">
                                        <i class="ri-magic-line"></i>
                                    </button>
                                </div>
                                <small class="text-muted">{{ __('messages.primary_light_color_hint') }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="theme_style" class="form-label">
                                    {{ __('messages.theme_style') }}
                                    @if($themeStyle?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $themeStyle->description }}"></i>
                                    @endif
                                </label>
                                <select name="settings[theme_style]" id="theme_style" class="form-control">
                                    <option value="light" {{ old('settings.theme_style', $themeStyle?->value) === 'light' ? 'selected' : '' }}>
                                        {{ __('messages.light') }}
                                    </option>
                                    <option value="dark" {{ old('settings.theme_style', $themeStyle?->value) === 'dark' ? 'selected' : '' }}>
                                        {{ __('messages.dark') }}
                                    </option>
                                </select>
                                <small class="text-muted">{{ __('messages.theme_style_hint') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Button Colors Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.button_colors') }}</h5>
                    </div>
                    <div class="card-body">
                        @php
                            // Primary Button Colors
                            $btnPrimaryColor = $settings->get('color', collect())->firstWhere('key', 'primary_color');
                            $btnPrimaryHoverColor = $settings->get('color', collect())->firstWhere('key', 'btn_primary_hover_color');
                            $btnPrimaryActiveColor = $settings->get('color', collect())->firstWhere('key', 'btn_primary_active_color');

                            // Success Button Colors
                            $btnSuccessColor = $settings->get('color', collect())->firstWhere('key', 'btn_success_color');
                            $btnSuccessHoverColor = $settings->get('color', collect())->firstWhere('key', 'btn_success_hover_color');
                            $btnSuccessActiveColor = $settings->get('color', collect())->firstWhere('key', 'btn_success_active_color');

                            // Warning Button Colors
                            $btnWarningColor = $settings->get('color', collect())->firstWhere('key', 'btn_warning_color');
                            $btnWarningHoverColor = $settings->get('color', collect())->firstWhere('key', 'btn_warning_hover_color');
                            $btnWarningActiveColor = $settings->get('color', collect())->firstWhere('key', 'btn_warning_active_color');

                            // Danger Button Colors
                            $btnDangerColor = $settings->get('color', collect())->firstWhere('key', 'btn_danger_color');
                            $btnDangerHoverColor = $settings->get('color', collect())->firstWhere('key', 'btn_danger_hover_color');
                            $btnDangerActiveColor = $settings->get('color', collect())->firstWhere('key', 'btn_danger_active_color');
                        @endphp

                        <!-- Primary Button -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">{{ __('messages.primary_button') }}</h6>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="primary_color" class="form-label">{{ __('messages.default_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="primary_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.primary_color', $btnPrimaryColor?->value ?? '#561C04') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[primary_color]" id="primary_color" class="form-control"
                                        value="{{ old('settings.primary_color', $btnPrimaryColor?->value ?? '#561C04') }}"
                                        placeholder="#561C04" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_primary_hover_color" class="form-label">{{ __('messages.hover_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_primary_hover_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_primary_hover_color', $btnPrimaryHoverColor?->value ?? '#4a1603') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_primary_hover_color]" id="btn_primary_hover_color" class="form-control"
                                        value="{{ old('settings.btn_primary_hover_color', $btnPrimaryHoverColor?->value ?? '#4a1603') }}"
                                        placeholder="#4a1603" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_primary_active_color" class="form-label">{{ __('messages.active_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_primary_active_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_primary_active_color', $btnPrimaryActiveColor?->value ?? '#3d1202') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_primary_active_color]" id="btn_primary_active_color" class="form-control"
                                        value="{{ old('settings.btn_primary_active_color', $btnPrimaryActiveColor?->value ?? '#3d1202') }}"
                                        placeholder="#3d1202" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Success Button -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-success mb-3">{{ __('messages.success_button') }}</h6>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_success_color" class="form-label">{{ __('messages.default_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_success_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_success_color', $btnSuccessColor?->value ?? '#28a745') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_success_color]" id="btn_success_color" class="form-control"
                                        value="{{ old('settings.btn_success_color', $btnSuccessColor?->value ?? '#28a745') }}"
                                        placeholder="#28a745" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_success_hover_color" class="form-label">{{ __('messages.hover_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_success_hover_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_success_hover_color', $btnSuccessHoverColor?->value ?? '#218838') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_success_hover_color]" id="btn_success_hover_color" class="form-control"
                                        value="{{ old('settings.btn_success_hover_color', $btnSuccessHoverColor?->value ?? '#218838') }}"
                                        placeholder="#218838" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_success_active_color" class="form-label">{{ __('messages.active_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_success_active_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_success_active_color', $btnSuccessActiveColor?->value ?? '#1e7e34') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_success_active_color]" id="btn_success_active_color" class="form-control"
                                        value="{{ old('settings.btn_success_active_color', $btnSuccessActiveColor?->value ?? '#1e7e34') }}"
                                        placeholder="#1e7e34" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Warning Button -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-warning mb-3">{{ __('messages.warning_button') }}</h6>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_warning_color" class="form-label">{{ __('messages.default_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_warning_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_warning_color', $btnWarningColor?->value ?? '#ffc107') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_warning_color]" id="btn_warning_color" class="form-control"
                                        value="{{ old('settings.btn_warning_color', $btnWarningColor?->value ?? '#ffc107') }}"
                                        placeholder="#ffc107" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_warning_hover_color" class="form-label">{{ __('messages.hover_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_warning_hover_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_warning_hover_color', $btnWarningHoverColor?->value ?? '#e0a800') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_warning_hover_color]" id="btn_warning_hover_color" class="form-control"
                                        value="{{ old('settings.btn_warning_hover_color', $btnWarningHoverColor?->value ?? '#e0a800') }}"
                                        placeholder="#e0a800" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_warning_active_color" class="form-label">{{ __('messages.active_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_warning_active_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_warning_active_color', $btnWarningActiveColor?->value ?? '#d39e00') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_warning_active_color]" id="btn_warning_active_color" class="form-control"
                                        value="{{ old('settings.btn_warning_active_color', $btnWarningActiveColor?->value ?? '#d39e00') }}"
                                        placeholder="#d39e00" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Danger Button -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-danger mb-3">{{ __('messages.danger_button') }}</h6>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_danger_color" class="form-label">{{ __('messages.default_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_danger_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_danger_color', $btnDangerColor?->value ?? '#dc3545') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_danger_color]" id="btn_danger_color" class="form-control"
                                        value="{{ old('settings.btn_danger_color', $btnDangerColor?->value ?? '#dc3545') }}"
                                        placeholder="#dc3545" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_danger_hover_color" class="form-label">{{ __('messages.hover_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_danger_hover_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_danger_hover_color', $btnDangerHoverColor?->value ?? '#c82333') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_danger_hover_color]" id="btn_danger_hover_color" class="form-control"
                                        value="{{ old('settings.btn_danger_hover_color', $btnDangerHoverColor?->value ?? '#c82333') }}"
                                        placeholder="#c82333" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="btn_danger_active_color" class="form-label">{{ __('messages.active_color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" id="btn_danger_active_color_picker" class="form-control form-control-color"
                                        value="{{ old('settings.btn_danger_active_color', $btnDangerActiveColor?->value ?? '#bd2130') }}" style="width: 60px; height: 38px;">
                                    <input type="text" name="settings[btn_danger_active_color]" id="btn_danger_active_color" class="form-control"
                                        value="{{ old('settings.btn_danger_active_color', $btnDangerActiveColor?->value ?? '#bd2130') }}"
                                        placeholder="#bd2130" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">{{ __('messages.preview') }}</h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-success" id="preview_btn_success">{{ __('messages.success') }}</button>
                                    <button type="button" class="btn btn-warning" id="preview_btn_warning">{{ __('messages.warning') }}</button>
                                    <button type="button" class="btn btn-danger" id="preview_btn_danger">{{ __('messages.danger') }}</button>
                                </div>
                                <small class="text-muted d-block mt-2">{{ __('messages.button_preview_hint') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="ri-save-line me-1"></i> {{ __('messages.save_settings') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle profit type change
    const profitTypeSelect = document.getElementById('admin_profit_type');
    const percentageField = document.getElementById('percentage_field');
    const fixedField = document.getElementById('fixed_field');
    const commissionField = document.getElementById('commission_field');

    function toggleProfitFields() {
        const val = profitTypeSelect.value;
        percentageField.style.display = val === 'percentage' ? 'block' : 'none';
        fixedField.style.display = val === 'fixed' ? 'block' : 'none';
        commissionField.style.display = val === 'commission' ? 'block' : 'none';
    }

    profitTypeSelect.addEventListener('change', toggleProfitFields);
    toggleProfitFields(); // Initial state

    // Handle color picker and hex input sync
    const colorPicker = document.getElementById('primary_color_picker');
    const colorHex = document.getElementById('primary_color');
    const resetColorBtn = document.getElementById('reset_primary_color');
    const defaultColor = '#666cff';

    const lightColorPicker = document.getElementById('primary_light_color_picker');
    const lightColorHex = document.getElementById('primary_light_color');
    const autoGenerateLightBtn = document.getElementById('auto_generate_light');
    const defaultLightColor = '#e7e7ff';

    // Validate hex color
    function isValidHex(hex) {
        return /^#[0-9A-Fa-f]{6}$/i.test(hex);
    }

    // Convert hex to RGB
    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    // Convert RGB to hex
    function rgbToHex(r, g, b) {
        return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
    }

    // Generate lighter version of a color
    function lightenColor(hex, percent = 60) {
        const rgb = hexToRgb(hex);
        if (!rgb) return hex;

        const r = Math.min(255, Math.round(rgb.r + (255 - rgb.r) * (percent / 100)));
        const g = Math.min(255, Math.round(rgb.g + (255 - rgb.g) * (percent / 100)));
        const b = Math.min(255, Math.round(rgb.b + (255 - rgb.b) * (percent / 100)));

        return rgbToHex(r, g, b);
    }

    // Setup color input sync
    function setupColorSync(picker, hexInput, resetBtn, defaultVal, autoUpdateLight = false) {
        if (!picker || !hexInput) return;

        // Sync picker to hex input
        picker.addEventListener('input', function() {
            hexInput.value = this.value.toUpperCase();
            hexInput.classList.remove('is-invalid');
            hexInput.classList.add('is-valid');

            // Auto-update light color when primary changes
            if (autoUpdateLight && lightColorPicker && lightColorHex) {
                const lightColor = lightenColor(this.value);
                lightColorPicker.value = lightColor;
                lightColorHex.value = lightColor;
                lightColorHex.classList.remove('is-invalid');
                lightColorHex.classList.add('is-valid');
            }
        });

        // Sync hex input to color picker
        hexInput.addEventListener('input', function() {
            let value = this.value.trim();

            // Auto-add # if missing
            if (value && !value.startsWith('#')) {
                value = '#' + value;
                this.value = value;
            }

            // Convert to uppercase
            this.value = value.toUpperCase();

            // Update picker if valid
            if (isValidHex(value)) {
                picker.value = value;
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else if (value) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });

        // Reset to default color
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                picker.value = defaultVal;
                hexInput.value = defaultVal;
                hexInput.classList.remove('is-invalid', 'is-valid');
            });
        }
    }

    // Setup primary color sync (with auto light color update)
    setupColorSync(colorPicker, colorHex, resetColorBtn, defaultColor, true);

    // Setup light color sync
    setupColorSync(lightColorPicker, lightColorHex, null, defaultLightColor, false);

    // Auto-generate light color from primary
    if (autoGenerateLightBtn) {
        autoGenerateLightBtn.addEventListener('click', function() {
            const primaryValue = colorHex.value;
            if (isValidHex(primaryValue)) {
                const lightColor = lightenColor(primaryValue);
                lightColorPicker.value = lightColor;
                lightColorHex.value = lightColor;
                lightColorHex.classList.remove('is-invalid');
                lightColorHex.classList.add('is-valid');
            }
        });
    }

    // Handle image deletion
    document.querySelectorAll('.delete-image').forEach(button => {
        button.addEventListener('click', function() {
            const key = this.getAttribute('data-key');

            if (confirm('{{ __('messages.confirm_delete_image') }}')) {
                fetch('{{ route('admin.settings.delete-image') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ key: key })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __('messages.error_deleting_image') }}');
                });
            }
        });
    });

    // Button color pickers sync and preview
    const buttonColorInputs = [
        { picker: 'btn_success_color_picker', hex: 'btn_success_color', preview: 'preview_btn_success', state: 'default' },
        { picker: 'btn_success_hover_color_picker', hex: 'btn_success_hover_color', preview: 'preview_btn_success', state: 'hover' },
        { picker: 'btn_success_active_color_picker', hex: 'btn_success_active_color', preview: 'preview_btn_success', state: 'active' },
        { picker: 'btn_warning_color_picker', hex: 'btn_warning_color', preview: 'preview_btn_warning', state: 'default' },
        { picker: 'btn_warning_hover_color_picker', hex: 'btn_warning_hover_color', preview: 'preview_btn_warning', state: 'hover' },
        { picker: 'btn_warning_active_color_picker', hex: 'btn_warning_active_color', preview: 'preview_btn_warning', state: 'active' },
        { picker: 'btn_danger_color_picker', hex: 'btn_danger_color', preview: 'preview_btn_danger', state: 'default' },
        { picker: 'btn_danger_hover_color_picker', hex: 'btn_danger_hover_color', preview: 'preview_btn_danger', state: 'hover' },
        { picker: 'btn_danger_active_color_picker', hex: 'btn_danger_active_color', preview: 'preview_btn_danger', state: 'active' },
    ];

    // Function to update button preview styles
    function updateButtonPreview(buttonId, defaultColor, hoverColor, activeColor) {
        const btn = document.getElementById(buttonId);
        if (!btn) return;

        // Set default color
        btn.style.backgroundColor = defaultColor;
        btn.style.borderColor = defaultColor;

        // Remove existing event listeners by cloning
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);

        // Add hover effect
        newBtn.addEventListener('mouseenter', function() {
            this.style.backgroundColor = hoverColor;
            this.style.borderColor = hoverColor;
        });

        newBtn.addEventListener('mouseleave', function() {
            this.style.backgroundColor = defaultColor;
            this.style.borderColor = defaultColor;
        });

        // Add active effect
        newBtn.addEventListener('mousedown', function() {
            this.style.backgroundColor = activeColor;
            this.style.borderColor = activeColor;
        });

        newBtn.addEventListener('mouseup', function() {
            this.style.backgroundColor = hoverColor;
            this.style.borderColor = hoverColor;
        });
    }

    // Setup each button color input
    buttonColorInputs.forEach(item => {
        const picker = document.getElementById(item.picker);
        const hexInput = document.getElementById(item.hex);

        if (!picker || !hexInput) return;

        // Sync picker to hex input
        picker.addEventListener('input', function() {
            hexInput.value = this.value.toUpperCase();
            hexInput.classList.remove('is-invalid');
            hexInput.classList.add('is-valid');
            updateAllButtonPreviews();
        });

        // Sync hex input to picker
        hexInput.addEventListener('input', function() {
            let value = this.value.trim();

            // Auto-add # if missing
            if (value && !value.startsWith('#')) {
                value = '#' + value;
                this.value = value;
            }

            // Convert to uppercase
            this.value = value.toUpperCase();

            // Update picker if valid
            if (isValidHex(value)) {
                picker.value = value;
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                updateAllButtonPreviews();
            } else if (value) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
    });

    // Update all button previews
    function updateAllButtonPreviews() {
        // Success button
        updateButtonPreview(
            'preview_btn_success',
            document.getElementById('btn_success_color').value || '#28a745',
            document.getElementById('btn_success_hover_color').value || '#218838',
            document.getElementById('btn_success_active_color').value || '#1e7e34'
        );

        // Warning button
        updateButtonPreview(
            'preview_btn_warning',
            document.getElementById('btn_warning_color').value || '#ffc107',
            document.getElementById('btn_warning_hover_color').value || '#e0a800',
            document.getElementById('btn_warning_active_color').value || '#d39e00'
        );

        // Danger button
        updateButtonPreview(
            'preview_btn_danger',
            document.getElementById('btn_danger_color').value || '#dc3545',
            document.getElementById('btn_danger_hover_color').value || '#c82333',
            document.getElementById('btn_danger_active_color').value || '#bd2130'
        );
    }

    // Initialize button previews on load
    updateAllButtonPreviews();
});

// Toggle password visibility
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
    } else {
        field.type = 'password';
    }
}
</script>
@endpush
@endsection
