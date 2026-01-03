@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'لوحة تحكم المتجر' : 'Distributor Dashboard' }}</h4>
        <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'مرحباً بك في لوحة التحكم الخاصة بك' : 'Welcome to your distributor panel' }}</p>
    </div>

    <!-- Dashboard Banner -->
    @if(setting_image('distributor_dashboard_banner'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="position-relative" style="border-radius: 12px; overflow: hidden; height: 280px; background: linear-gradient(135deg, #561C04 0%, #7A3206 100%);">
                    <!-- Banner Image -->
                    <img src="{{ setting_image('distributor_dashboard_banner') }}" alt="Distributor Dashboard Banner" class="img-fluid w-100 h-100" style="object-fit: cover; position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    
                    <!-- Overlay Gradient -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(86, 28, 4, 0.6) 0%, rgba(122, 50, 6, 0.4) 100%);"></div>
                    
                    <!-- Content -->
                    <div class="position-relative h-100 d-flex align-items-center p-5" style="z-index: 2;">
                        <div>
                            <h2 class="text-white fw-bold mb-2" style="font-size: 32px;">
                                <i class="ri-store-3-line me-2"></i>
                                {{ app()->getLocale() == 'ar' ? 'لوحة تحكم المتجر' : 'Distributor Dashboard' }}
                            </h2>
                            <p class="text-white-50 mb-0" style="font-size: 16px;">
                                {{ app()->getLocale() == 'ar' ? 'أدر منتجاتك والتزم بطلبات العملاء' : 'Manage your products and fulfill customer orders' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Promotional Banner (Under Header) -->
    @if(setting_image('distributor_promo_banner'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="position-relative" style="border-radius: 12px; overflow: hidden;">
                    <a href="{{ setting('distributor_promo_banner_link') ?: '#' }}" target="{{ setting('distributor_promo_banner_link') ? '_blank' : '_self' }}">
                        <img src="{{ setting_image('distributor_promo_banner') }}" alt="{{ app()->getLocale() == 'ar' ? 'بنر ترويجي' : 'Promotional Banner' }}" class="img-fluid w-100" style="max-height: 200px; object-fit: cover;">
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Products -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-warning me-3 p-2">
                            <i class="ri-shopping-bag-3-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ $stats['total_products'] }}</h5>
                            <small>{{ __('messages.total_products') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-warning me-3 p-2">
                            <i class="ri-file-list-3-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ $stats['total_orders'] }}</h5>
                            <small>{{ __('messages.total_orders') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-danger me-3 p-2">
                            <i class="ri-time-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ $stats['pending_orders'] }}</h5>
                            <small>{{ __('messages.pending_orders') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wallet Balance -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                            <i class="ri-wallet-3-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 d-inline-flex align-items-center gap-1" style="direction: ltr;"><x-session-currency-icon width="18" height="18" /> {{ number_format($currentCurrency->convertFrom($stats['wallet_balance'], 'AED'), 2) }}</h5>
                            <small>{{ __('messages.wallet_balance') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Products -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                            <i class="ri-checkbox-circle-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ $stats['active_products'] }}</h5>
                            <small>{{ __('messages.active_products') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-warning me-3 p-2">
                            <i class="ri-price-tag-3-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ $stats['total_categories'] }}</h5>
                            <small>{{ __('messages.categories') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                            <i class="ri-check-double-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ $stats['completed_orders'] }}</h5>
                            <small>{{ __('messages.completed_orders') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                            <i class="ri-money-dollar-circle-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0 d-inline-flex align-items-center gap-1" style="direction: ltr;"><x-session-currency-icon width="18" height="18" /> {{ number_format($currentCurrency->convertFrom($stats['total_revenue'], 'AED'), 2) }}</h5>
                            <small>{{ __('messages.total_revenue') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.quick_actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('distributor.products.create') }}" class="btn btn-warning">
                            <i class="ri-add-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'إضافة منتج جديد' : 'Create New Product' }}
                        </a>
                        <a href="{{ route('distributor.products') }}" class="btn btn-primary">
                            <i class="ri-shopping-bag-3-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'منتجاتي' : 'My Products' }}
                        </a>
                        <a href="{{ route('distributor.orders') }}" class="btn btn-danger">
                            <i class="ri-file-list-3-line me-1"></i>
                            {{ __('messages.my_orders') }}
                        </a>
                        <a href="{{ route('distributor.categories') }}" class="btn btn-dark">
                            <i class="ri-price-tag-3-line me-1"></i>
                            {{ __('messages.categories') }}
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-secondary">
                            <i class="ri-user-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'الملف الشخصي' : 'Profile' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coupon Stats Section -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #561C04 0%, #7A3206 100%); border: none; border-radius: 12px 12px 0 0;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="mb-0 text-white fw-bold">
                            <i class="ri-coupon-2-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'إحصائيات الكوبونات الخاصة بي' : 'My Coupons Statistics' }}
                        </h5>
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
                                        <h3 class="mb-0" style="color: #561C04; font-weight: 700;">{{ $stats['total_coupons'] }}</h3>
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
                                        <h3 class="mb-0" style="color: #10b981; font-weight: 700;">{{ $stats['active_coupons'] }}</h3>
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
                                        <h3 class="mb-0" style="color: #ef4444; font-weight: 700;">{{ $stats['expired_coupons'] }}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-coupon-3-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Commission Earned -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #f59e0b; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'إجمالي العمولات' : 'Total Commissions' }}</h6>
                                        <h3 class="mb-0" style="color: #f59e0b; font-weight: 700;">{{ number_format($stats['total_commission_earned'], 2) }}</h3>
                                    </div>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-money-dollar-circle-line text-white ri-24px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Commission -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-box" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 4px solid #8b5cf6; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted fw-500 mb-1" style="font-size: 12px;">{{ app()->getLocale() == 'ar' ? 'العمولات المعلقة' : 'Pending Commissions' }}</h6>
                                        <h3 class="mb-0" style="color: #8b5cf6; font-weight: 700;">{{ number_format($stats['pending_commission'], 2) }}</h3>
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

    <!-- Recent Coupons Table -->
    @if($recentCoupons && $recentCoupons->count() > 0)
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #561C04 0%, #7A3206 100%); border: none; border-radius: 12px 12px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white fw-bold">
                            <i class="ri-coupon-2-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'أحدث الكوبونات' : 'Recent Coupons' }}
                        </h5>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="border: none;">{{ app()->getLocale() == 'ar' ? 'الكود' : 'Code' }}</th>
                                <th style="border: none;">{{ app()->getLocale() == 'ar' ? 'العنوان' : 'Title' }}</th>
                                <th style="border: none;">{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                                <th style="border: none;">{{ app()->getLocale() == 'ar' ? 'المستخدمة' : 'Used' }}</th>
                                <th style="border: none;">{{ app()->getLocale() == 'ar' ? 'العمولة' : 'Commission' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCoupons as $coupon)
                            <tr>
                                <td style="border: none; vertical-align: middle;">
                                    <span class="badge bg-light text-dark" style="font-family: monospace;">{{ $coupon->code }}</span>
                                </td>
                                <td style="border: none; vertical-align: middle;">
                                    {{ app()->getLocale() == 'ar' ? $coupon->title_ar : $coupon->title_en }}
                                </td>
                                <td style="border: none; vertical-align: middle;">
                                    @if($coupon->isValid())
                                        <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'فعال' : 'Active' }}</span>
                                    @elseif($coupon->isExpired())
                                        <span class="badge bg-danger">{{ app()->getLocale() == 'ar' ? 'منتهي' : 'Expired' }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ app()->getLocale() == 'ar' ? 'معطل' : 'Inactive' }}</span>
                                    @endif
                                </td>
                                <td style="border: none; vertical-align: middle;">
                                    <span class="badge bg-light text-dark">{{ $coupon->used_count }}/{{ $coupon->max_uses }}</span>
                                </td>
                                <td style="border: none; vertical-align: middle;">
                                    <strong style="color: #10b981;">{{ number_format($coupon->total_commission_earned, 2) }}</strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Orders & Products -->
    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">{{ __('messages.recent_orders') }}</h5>
                    <a href="{{ route('distributor.orders') }}" class="btn btn-sm btn-outline-success">
                        {{ __('messages.view_all') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.order_number') }}</th>
                                    <th>{{ __('messages.product') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td><strong>{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->product->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'delivered' ? 'success' : 'secondary') }}">
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

        <!-- Recent Products -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">{{ __('messages.recent_products') }}</h5>
                    <a href="{{ route('distributor.products') }}" class="btn btn-sm btn-outline-success">
                        {{ __('messages.view_all') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.product') }}</th>
                                    <th>{{ __('messages.price') }}</th>
                                    <th>{{ __('messages.stock') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td style="direction: ltr;"><span class="d-inline-flex align-items-center gap-1"><x-session-currency-icon width="14" height="14" /> {{ number_format($currentCurrency->convertFrom($product->price, $product->currency ?? 'AED'), 2) }}</span></td>
                                    <td>
                                        @if($product->track_inventory)
                                            <span class="badge bg-{{ $product->stock_quantity > 10 ? 'success' : ($product->stock_quantity > 0 ? 'warning' : 'danger') }}">
                                                {{ $product->stock_quantity }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('messages.unlimited') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                            {{ $product->is_active ? __('messages.active') : __('messages.inactive') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ __('messages.no_products_yet') }}</td>
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
    .bg-label-warning,
    .bg-label-success,
    .bg-label-danger {
        transition: all 0.3s ease;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(86, 28, 4, 0.3);
    }

    .btn-warning,
    .btn-danger,
    .btn-dark,
    .btn-secondary,
    .btn-primary,
    .btn-info {
        transition: all 0.3s ease;
    }

    .btn-success {
        transition: all 0.3s ease;
    }

    .btn-warning:hover,
    .btn-danger:hover,
    .btn-dark:hover,
    .btn-secondary:hover,
    .btn-info:hover {
        background-color: #561C04 !important;
        border-color: #561C04 !important;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(86, 28, 4, 0.4);
    }

    .btn-success:hover {
        background-color: #e56300 !important;
        border-color: #e56300 !important;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(229, 99, 0, 0.4);
    }

    .btn-primary:hover {
        background-color: #561C04 !important;
        border-color: #561C04 !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(86, 28, 4, 0.4);
    }

    .btn-outline-success:hover {
        background-color: #e56300 !important;
        border-color: #e56300 !important;
        color: white !important;
    }

    .badge {
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .badge.bg-success:hover {
        background-color: #e56300 !important;
        box-shadow: 0 4px 10px rgba(229, 99, 0, 0.3);
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(86, 28, 4, 0.05);
    }
</style>
@endsection
