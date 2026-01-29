@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.admin_dashboard') }}</h4>
        <p class="text-muted">{{ __('messages.welcome_admin_panel') }}</p>
    </div>

    <!-- Statistics Cards - Row 1: Users -->
    <div class="row g-4 mb-4">
        <!-- Total Users -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-1">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-user-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $stats['total_users'] }}</h5>
                            <small class="text-white-50">{{ __('messages.total_users') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Sellers -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-2">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-store-2-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $stats['total_sellers'] }}</h5>
                            <small class="text-white-50">{{ __('messages.total_sellers') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Distributors -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-truck-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $stats['total_distributors'] }}</h5>
                            <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'إجمالي التجار' : 'Total Distributors' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Merchants -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-shopping-cart-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $stats['total_merchants'] }}</h5>
                            <small class="text-white-50">{{ __('messages.total_merchants') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards - Row 2: Products & Orders -->
    <div class="row g-4 mb-4">
        <!-- Total Products -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-2">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-shopping-bag-3-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $stats['total_products'] }}</h5>
                            <small class="text-white-50">{{ __('messages.total_products') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AliExpress Products -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-global-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $aliexpressProducts }}</h5>
                            <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'منتجات الصين' : 'AliExpress Products' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distributor Products -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-store-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $distributorProducts }}</h5>
                            <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'منتجات التجار' : 'Distributor Products' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-1">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-file-list-3-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $stats['total_orders'] }}</h5>
                            <small class="text-white-50">{{ __('messages.total_orders') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards - Row 3: Subscriptions & Revenue -->
    <div class="row g-4 mb-4">
        <!-- Pending Orders -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-time-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $stats['pending_orders'] }}</h5>
                            <small class="text-white-50">{{ __('messages.pending_orders') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-vip-crown-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $stats['active_subscriptions'] }}</h5>
                            <small class="text-white-50">{{ __('messages.active_subscriptions') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Categories -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-1">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-price-tag-3-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{{ $stats['total_categories'] }}</h5>
                            <small class="text-white-50">{{ __('messages.total_categories') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card gradient-2">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="ri-money-dollar-circle-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 text-white">{!! format_currency($stats['total_revenue'], 'AED', 2, true) !!}</h5>
                            <small class="text-white-50">{{ __('messages.total_revenue') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products by Country -->


    <!-- Affiliate Marketing Stats -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #561C04 0%, #7A3206 100%); border: none; border-radius: 12px 12px 0 0;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="mb-0 text-white fw-bold">
                            <i class="ri-coupon-2-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'إحصائيات التسويق بالعمولة' : 'Affiliate Marketing Stats' }}
                        </h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.affiliate.stores') }}" class="btn btn-sm" style="background: white; color: #561C04; border: none; font-weight: 500;">
                                <i class="ri-store-2-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'المتاجر' : 'Stores' }}
                            </a>
                            <a href="{{ route('admin.affiliate.coupons.active') }}" class="btn btn-sm" style="background: white; color: #561C04; border: none; font-weight: 500;">
                                <i class="ri-coupon-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'الكوبونات الفعالة' : 'Active Coupons' }}
                            </a>
                            <a href="{{ route('admin.affiliate.coupons.expired') }}" class="btn btn-sm" style="background: white; color: #561C04; border: none; font-weight: 500;">
                                <i class="ri-coupon-3-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'الكوبونات المنتهية' : 'Expired Coupons' }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Total Coupons -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #561C04; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'إجمالي الكوبونات' : 'Total Coupons' }}</h6>
                                        <h3 class="mb-0" style="color: #561C04; font-weight: 700;">{{ $affiliateStats['total_coupons'] }}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #561C04 0%, #7A3206 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-coupon-2-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Coupons -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #10b981; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'الكوبونات الفعالة' : 'Active Coupons' }}</h6>
                                        <h3 class="mb-0" style="color: #10b981; font-weight: 700;">{{ $affiliateStats['active_coupons'] }}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-coupon-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Expired Coupons -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #ef4444; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'الكوبونات المنتهية' : 'Expired Coupons' }}</h6>
                                        <h3 class="mb-0" style="color: #ef4444; font-weight: 700;">{{ $affiliateStats['expired_coupons'] }}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-coupon-3-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Commissions -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #f59e0b; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'إجمالي العمولات' : 'Total Commissions' }}</h6>
                                        <h3 class="mb-0" style="color: #f59e0b; font-weight: 700;">{{ number_format($affiliateStats['total_commission_earned'], 2) }}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-money-dollar-circle-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Commission (Full Width on Next Row) -->
                    <div class="row g-4 mt-2">
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #8b5cf6; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'العمولات المعلقة' : 'Pending Commissions' }}</h6>
                                        <h3 class="mb-0" style="color: #8b5cf6; font-weight: 700;">{{ number_format($affiliateStats['pending_commission'], 2) }}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-time-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet/PayPal Stats - Unpaid Store Balances -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #561C04 0%, #7A3206 100%); border: none; border-radius: 12px 12px 0 0;">
                    <h5 class="mb-0 text-white fw-bold">
                        <i class="ri-paypal-line me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'أرصدة المتاجر المستحقة (PayPal)' : 'Store Balances Due (PayPal)' }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Total Balances -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #561C04; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'إجمالي الأرصدة' : 'Total Balances' }}</h6>
                                        <h3 class="mb-0" style="color: #561C04; font-weight: 700;">{!! format_currency($walletStats['total_wallet_balance'], 'AED', 2, true) !!}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #561C04 0%, #7A3206 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-wallet-3-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Balances -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #f59e0b; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'الأرصدة المعلقة' : 'Pending Balances' }}</h6>
                                        <h3 class="mb-0" style="color: #f59e0b; font-weight: 700;">{!! format_currency($walletStats['total_pending_balance'], 'AED', 2, true) !!}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-time-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Distributor Balances -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #3b82f6; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'أرصدة التجار' : 'Distributor Balances' }}</h6>
                                        <h3 class="mb-0" style="color: #3b82f6; font-weight: 700;">{!! format_currency($walletStats['distributor_balance'], 'AED', 2, true) !!}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-store-2-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seller Balances -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #10b981; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'أرصدة البائعين' : 'Seller Balances' }}</h6>
                                        <h3 class="mb-0" style="color: #10b981; font-weight: 700;">{!! format_currency($walletStats['seller_balance'], 'AED', 2, true) !!}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-user-3-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Message -->
                    <div class="alert alert-info mt-4 mb-0" style="background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 8px;">
                        <div class="d-flex align-items-start gap-3">
                            <i class="ri-information-line mt-1" style="color: #3b82f6; font-size: 18px;"></i>
                            <div>
                                <p class="mb-0" style="color: #1e40af; font-weight: 500;">
                                    {{ app()->getLocale() == 'ar' ? 'هذه المبالغ يجب أن تكون متوفرة في حساب PayPal للدفع للمتاجر والبائعين' : 'These amounts should be available in PayPal for store and seller payouts' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #561C04 0%, #7A3206 100%); border: none; border-radius: 12px 12px 0 0;">
                    <h5 class="mb-0 text-white fw-bold">
                        <i class="ri-lightbulb-flash-line me-2"></i>
                        {{ __('messages.quick_actions') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <a href="{{ route('admin.tokens') }}" class="text-decoration-none">
                                <div style="background: #f8f9fa; border-left: 4px solid #561C04; padding: 18px; border-radius: 8px; transition: all 0.3s ease; cursor: pointer;" 
                                     onmouseover="this.style.background='#f0f4f8'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(86, 28, 4, 0.1)'" 
                                     onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #561C04 0%, #7A3206 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ri-key-2-line text-white ri-18px"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" style="color: #561C04; font-weight: 600; font-size: 14px;">{{ __('messages.manage_tokens') }}</h6>
                                            <small class="text-muted" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'إدارة التوكنات' : 'Manage tokens' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <a href="{{ route('admin.subscriptions.index') }}" class="text-decoration-none">
                                <div style="background: #f8f9fa; border-left: 4px solid #f59e0b; padding: 18px; border-radius: 8px; transition: all 0.3s ease; cursor: pointer;" 
                                     onmouseover="this.style.background='#fffbf0'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(245, 158, 11, 0.1)'" 
                                     onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ri-vip-crown-line text-white ri-18px"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" style="color: #f59e0b; font-weight: 600; font-size: 14px;">{{ __('messages.manage_subscriptions') }}</h6>
                                            <small class="text-muted" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'إدارة الاشتراكات' : 'Manage subscriptions' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <a href="{{ route('admin.orders.index') }}" class="text-decoration-none">
                                <div style="background: #f8f9fa; border-left: 4px solid #3b82f6; padding: 18px; border-radius: 8px; transition: all 0.3s ease; cursor: pointer;" 
                                     onmouseover="this.style.background='#eff6ff'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.1)'" 
                                     onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ri-file-list-3-line text-white ri-18px"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" style="color: #3b82f6; font-weight: 600; font-size: 14px;">{{ __('messages.manage_orders') }}</h6>
                                            <small class="text-muted" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'إدارة الطلبات' : 'Manage orders' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <a href="{{ route('admin.categories.index') }}" class="text-decoration-none">
                                <div style="background: #f8f9fa; border-left: 4px solid #10b981; padding: 18px; border-radius: 8px; transition: all 0.3s ease; cursor: pointer;" 
                                     onmouseover="this.style.background='#f0fdf4'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.1)'" 
                                     onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ri-price-tag-3-line text-white ri-18px"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" style="color: #10b981; font-weight: 600; font-size: 14px;">{{ __('messages.manage_categories') }}</h6>
                                            <small class="text-muted" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'إدارة الفئات' : 'Manage categories' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <a href="{{ route('admin.affiliate.stores') }}" class="text-decoration-none">
                                <div style="background: #f8f9fa; border-left: 4px solid #8b5cf6; padding: 18px; border-radius: 8px; transition: all 0.3s ease; cursor: pointer;" 
                                     onmouseover="this.style.background='#faf5ff'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(139, 92, 246, 0.1)'" 
                                     onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ri-store-2-line text-white ri-18px"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" style="color: #8b5cf6; font-weight: 600; font-size: 14px;">{{ app()->getLocale() == 'ar' ? 'إدارة المتاجر' : 'Manage Stores' }}</h6>
                                            <small class="text-muted" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'المتاجر التابعة' : 'Affiliate stores' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <a href="{{ route('admin.affiliate.coupons.create') }}" class="text-decoration-none">
                                <div style="background: #f8f9fa; border-left: 4px solid #ef4444; padding: 18px; border-radius: 8px; transition: all 0.3s ease; cursor: pointer;" 
                                     onmouseover="this.style.background='#fef2f2'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(239, 68, 68, 0.1)'" 
                                     onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ri-coupon-2-line text-white ri-18px"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" style="color: #ef4444; font-weight: 600; font-size: 14px;">{{ app()->getLocale() == 'ar' ? 'إضافة كوبون' : 'Add Coupon' }}</h6>
                                            <small class="text-muted" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'كوبون جديد' : 'New coupon' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders & Subscriptions -->
    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header gradient-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">{{ __('messages.recent_orders') }}</h5>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light">
                        {{ __('messages.view_all') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.order_number') }}</th>
                                    <th>{{ __('messages.customer') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td><strong>{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>
                                        <span class="badge status-badge">
                                            {{ __('messages.' . $order->status) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ __('messages.no_orders_yet') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Subscriptions -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header gradient-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">{{ __('messages.recent_subscriptions') }}</h5>
                    <a href="{{ route('admin.subscriptions.users') }}" class="btn btn-sm btn-light">
                        {{ __('messages.view_all') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.user') }}</th>
                                    <th>{{ __('messages.plan_name') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSubscriptions as $subscription)
                                <tr>
                                    <td>{{ $subscription->user->name }}</td>
                                    <td>
                                        <span class="badge status-badge">
                                            {{ $subscription->subscription->localized_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge status-badge">
                                            {{ __('messages.' . $subscription->status) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($subscription->amount_paid, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ __('messages.no_subscriptions_yet') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Gradient Definitions - Professional White Cards with Color Accents */
    .gradient-1,
    .gradient-2,
    .gradient-3,
    .gradient-4 {
        background: white !important;
        border-left: 4px solid #561C04 !important;
        box-shadow: 0 2px 8px rgba(86, 28, 4, 0.1);
    }

    .gradient-header {
        background: linear-gradient(135deg, #561C04 0%, #7A3206 100%) !important;
    }

    /* Stat Cards */
    .stat-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: white !important;
        border-left: 4px solid #561C04 !important;
        box-shadow: 0 2px 8px rgba(86, 28, 4, 0.1);
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(86, 28, 4, 0.2);
    }

    /* Stat Card Text Colors */
    .stat-card .card-info h5,
    .mini-stat h4 {
        color: #561C04 !important;
        font-weight: 600;
    }

    .stat-card .card-info small,
    .mini-stat small {
        color: #6c757d !important;
        font-weight: 500;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: #561C04;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: white;
    }

    [dir="rtl"] .stat-icon {
        margin-right: 0;
        margin-left: 15px;
    }

    /* Mini Stats */
    .mini-stat {
        border-radius: 10px;
        transition: all 0.3s ease;
        background: white !important;
        border-left: 3px solid #561C04 !important;
        box-shadow: 0 2px 8px rgba(86, 28, 4, 0.1);
    }

    .mini-stat:hover {
        transform: scale(1.02);
        box-shadow: 0 5px 15px rgba(86, 28, 4, 0.2);
    }

    /* Country Cards */
    .country-card {
        background: linear-gradient(135deg, #561C04 0%, #7A3206 100%) !important;
        transition: all 0.3s ease;
    }

    .country-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(86, 28, 4, 0.3);
    }

    /* Action Buttons */
    .action-btn {
        background: #561C04;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        background: #3d1503;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(86, 28, 4, 0.3);
    }

    /* Status Badges */
    .status-badge {
        background: #561C04;
        color: white;
        border-radius: 6px;
        padding: 5px 10px;
        font-weight: 500;
    }

    /* Card Hover Effects */
    .card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        background: white;
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(86, 28, 4, 0.15);
    }

    /* Card Headers */
    .card-header.gradient-header {
        border-bottom: none;
    }

    /* Table Row Hover */
    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(86, 28, 4, 0.05);
    }

    /* Text Colors */
    .text-white-50 {
        color: #6c757d !important;
    }

    /* SVG Icons in currency symbols should match text color */
    .stat-card .card-info h5 svg,
    .mini-stat h4 svg {
        fill: #561C04 !important;
    }

    .stat-card .card-info h5 svg path,
    .mini-stat h4 svg path {
        fill: #561C04 !important;
    }
</style>
@endsection
