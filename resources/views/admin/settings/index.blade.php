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
                                        name="env_settings[recaptcha_enabled]"
                                        id="recaptcha_enabled"
                                        value="true"
                                        {{ config('services.recaptcha.enabled') ? 'checked' : '' }}>
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
                                    name="env_settings[recaptcha_site_key]"
                                    id="recaptcha_site_key"
                                    class="form-control"
                                    value="{{ old('env_settings.recaptcha_site_key', config('services.recaptcha.site_key') ?? '') }}"
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
                                    name="env_settings[recaptcha_secret_key]"
                                    id="recaptcha_secret_key"
                                    class="form-control"
                                    value="{{ old('env_settings.recaptcha_secret_key', config('services.recaptcha.secret_key') ?? '') }}"
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
                                    <span class="input-group-text">AED</span>
                                </div>
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'المبلغ بالدرهم الإماراتي (العملة الأساسية للنظام)' : 'Amount in AED (system base currency)' }}</small>
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
