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
    @if($productsByCountry->count() > 0)
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header gradient-header">
                    <h5 class="mb-0 text-white">
                        <i class="ri-global-line me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'المنتجات حسب الدولة' : 'Products by Country' }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @php
                            $countryFlags = [
                                'AE' => ['flag' => '🇦🇪', 'ar' => 'الإمارات', 'en' => 'UAE'],
                                'SA' => ['flag' => '🇸🇦', 'ar' => 'السعودية', 'en' => 'Saudi Arabia'],
                                'KW' => ['flag' => '🇰🇼', 'ar' => 'الكويت', 'en' => 'Kuwait'],
                                'QA' => ['flag' => '🇶🇦', 'ar' => 'قطر', 'en' => 'Qatar'],
                                'BH' => ['flag' => '🇧🇭', 'ar' => 'البحرين', 'en' => 'Bahrain'],
                                'OM' => ['flag' => '🇴🇲', 'ar' => 'عمان', 'en' => 'Oman'],
                                'EG' => ['flag' => '🇪🇬', 'ar' => 'مصر', 'en' => 'Egypt'],
                                'JO' => ['flag' => '🇯🇴', 'ar' => 'الأردن', 'en' => 'Jordan'],
                                'LB' => ['flag' => '🇱🇧', 'ar' => 'لبنان', 'en' => 'Lebanon'],
                                'CN' => ['flag' => '🇨🇳', 'ar' => 'الصين', 'en' => 'China'],
                            ];
                        @endphp
                        @foreach($productsByCountry as $code => $data)
                            @php
                                $country = $countryFlags[$code] ?? ['flag' => '🏳️', 'ar' => $code, 'en' => $code];
                            @endphp
                            <div class="col-sm-6 col-md-4 col-lg-3">
                                <div class="p-3 country-card rounded d-flex align-items-center">
                                    <span style="font-size: 2rem;" class="me-3">{{ $country['flag'] }}</span>
                                    <div>
                                        <h6 class="mb-0 text-white">{{ app()->getLocale() == 'ar' ? $country['ar'] : $country['en'] }}</h6>
                                        <small class="text-white-50">{{ $data->count }} {{ app()->getLocale() == 'ar' ? 'منتج' : 'products' }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Affiliate Marketing Stats -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header gradient-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">
                        <i class="ri-coupon-2-line me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'إحصائيات التسويق بالعمولة' : 'Affiliate Marketing Stats' }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.affiliate.stores') }}" class="btn btn-sm btn-light">
                            <i class="ri-store-2-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'المتاجر' : 'Stores' }}
                        </a>
                        <a href="{{ route('admin.affiliate.coupons.active') }}" class="btn btn-sm btn-light">
                            <i class="ri-coupon-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'الكوبونات الفعالة' : 'Active Coupons' }}
                        </a>
                        <a href="{{ route('admin.affiliate.coupons.expired') }}" class="btn btn-sm btn-light">
                            <i class="ri-coupon-3-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'الكوبونات المنتهية' : 'Expired Coupons' }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <div class="p-3 mini-stat gradient-1 rounded text-center">
                                <h4 class="mb-1 text-white">{{ $affiliateStats['total_coupons'] }}</h4>
                                <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'إجمالي الكوبونات' : 'Total Coupons' }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <div class="p-3 mini-stat gradient-2 rounded text-center">
                                <h4 class="mb-1 text-white">{{ $affiliateStats['active_coupons'] }}</h4>
                                <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'الكوبونات الفعالة' : 'Active Coupons' }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <div class="p-3 mini-stat gradient-3 rounded text-center">
                                <h4 class="mb-1 text-white">{{ $affiliateStats['expired_coupons'] }}</h4>
                                <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'الكوبونات المنتهية' : 'Expired Coupons' }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="p-3 mini-stat gradient-4 rounded text-center">
                                <h4 class="mb-1 text-white">{{ number_format($affiliateStats['total_commission_earned'], 2) }}</h4>
                                <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'إجمالي العمولات' : 'Total Commissions' }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="p-3 mini-stat gradient-1 rounded text-center">
                                <h4 class="mb-1 text-white">{{ number_format($affiliateStats['pending_commission'], 2) }}</h4>
                                <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'العمولات المعلقة' : 'Pending Commissions' }}</small>
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
            <div class="card">
                <div class="card-header gradient-header">
                    <h5 class="mb-0 text-white">
                        <i class="ri-paypal-line me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'أرصدة المتاجر المستحقة (PayPal)' : 'Store Balances Due (PayPal)' }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 mini-stat gradient-1 rounded text-center">
                                <h4 class="mb-1 text-white">{!! format_currency($walletStats['total_wallet_balance'], 'AED', 2, true) !!}</h4>
                                <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'إجمالي الأرصدة' : 'Total Balances' }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 mini-stat gradient-2 rounded text-center">
                                <h4 class="mb-1 text-white">{!! format_currency($walletStats['total_pending_balance'], 'AED', 2, true) !!}</h4>
                                <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'الأرصدة المعلقة' : 'Pending Balances' }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 mini-stat gradient-3 rounded text-center">
                                <h4 class="mb-1 text-white">{!! format_currency($walletStats['distributor_balance'], 'AED', 2, true) !!}</h4>
                                <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'أرصدة التجار' : 'Distributor Balances' }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 mini-stat gradient-4 rounded text-center">
                                <h4 class="mb-1 text-white">{!! format_currency($walletStats['seller_balance'], 'AED', 2, true) !!}</h4>
                                <small class="text-white-50">{{ app()->getLocale() == 'ar' ? 'أرصدة البائعين' : 'Seller Balances' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <p class="text-muted mb-0">
                            <i class="ri-information-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'هذه المبالغ يجب أن تكون متوفرة في حساب PayPal للدفع للمتاجر' : 'These amounts should be available in PayPal for store payouts' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header gradient-header">
                    <h5 class="mb-0 text-white">{{ __('messages.quick_actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.tokens') }}" class="btn action-btn">
                            <i class="ri-key-2-line me-1"></i>
                            {{ __('messages.manage_tokens') }}
                        </a>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn action-btn">
                            <i class="ri-vip-crown-line me-1"></i>
                            {{ __('messages.manage_subscriptions') }}
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="btn action-btn">
                            <i class="ri-file-list-3-line me-1"></i>
                            {{ __('messages.manage_orders') }}
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="btn action-btn">
                            <i class="ri-price-tag-3-line me-1"></i>
                            {{ __('messages.manage_categories') }}
                        </a>
                        <a href="{{ route('admin.affiliate.stores') }}" class="btn action-btn">
                            <i class="ri-store-2-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'إدارة المتاجر' : 'Manage Stores' }}
                        </a>
                        <a href="{{ route('admin.affiliate.coupons.create') }}" class="btn action-btn">
                            <i class="ri-coupon-2-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'إضافة كوبون' : 'Add Coupon' }}
                        </a>
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
    /* Gradient Definitions */
    .gradient-1 {
        background: linear-gradient(135deg, #561C04 0%, #8B3508 100%);
    }

    .gradient-2 {
        background: linear-gradient(135deg, #e56300 0%, #ff8c33 100%);
    }

    .gradient-3 {
        background: linear-gradient(135deg, #8B3508 0%, #e56300 100%);
    }

    .gradient-4 {
        background: linear-gradient(135deg, #3d1503 0%, #561C04 100%);
    }

    .gradient-header {
        background: linear-gradient(135deg, #561C04 0%, #e56300 100%);
    }

    /* Stat Cards */
    .stat-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(86, 28, 4, 0.4);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.2);
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
    }

    .mini-stat:hover {
        transform: scale(1.02);
        box-shadow: 0 5px 15px rgba(86, 28, 4, 0.3);
    }

    /* Country Cards */
    .country-card {
        background: linear-gradient(135deg, #561C04 0%, #8B3508 100%);
        transition: all 0.3s ease;
    }

    .country-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(86, 28, 4, 0.3);
    }

    /* Action Buttons */
    .action-btn {
        background: linear-gradient(135deg, #561C04 0%, #e56300 100%);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        background: linear-gradient(135deg, #3d1503 0%, #561C04 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(86, 28, 4, 0.4);
    }

    /* Status Badges */
    .status-badge {
        background: linear-gradient(135deg, #561C04 0%, #e56300 100%);
        color: white;
        border-radius: 6px;
        padding: 5px 10px;
    }

    /* Card Hover Effects */
    .card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(86, 28, 4, 0.2);
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
        color: rgba(255, 255, 255, 0.7) !important;
    }

    /* SVG Icons in currency symbols should be white */
    .stat-card .card-info h5 svg,
    .mini-stat h4 svg {
        fill: white !important;
    }

    .stat-card .card-info h5 svg path,
    .mini-stat h4 svg path {
        fill: white !important;
    }
</style>
@endsection
