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
                <div class="card p-0 overflow-hidden">
                    <img src="{{ setting_image('distributor_dashboard_banner') }}" alt="Distributor Dashboard Banner" class="img-fluid w-100" style="max-height: 300px; object-fit: cover;">
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
                            <h5 class="mb-0">${{ number_format($stats['wallet_balance'], 2) }}</h5>
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
                            <h5 class="mb-0">${{ number_format($stats['total_revenue'], 2) }}</h5>
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
                                    <td>${{ number_format($product->price, 2) }}</td>
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
