@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.seller_dashboard') }}</h4>
        <p class="text-muted">{{ __('messages.welcome_seller_panel') }}</p>
    </div>

    <!-- Subscription Countdown Timer -->
    @php
        $activeSubscription = auth()->user()->activeSubscription;
    @endphp

    @if($activeSubscription && $activeSubscription->end_date)
        @php
            $endDate = \Carbon\Carbon::parse($activeSubscription->end_date);
            $daysRemaining = now()->diffInDays($endDate, false);
        @endphp

        @if($daysRemaining >= 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="background: linear-gradient(135deg, {{ $daysRemaining <= 5 ? '#dc2626' : ($daysRemaining <= 10 ? '#ea580c' : '#10b981') }} 0%, {{ $daysRemaining <= 5 ? '#991b1b' : ($daysRemaining <= 10 ? '#c2410c' : '#059669') }} 100%);">
                        <div class="card-body text-white">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="text-white mb-2">
                                        <i class="ri-time-line me-2"></i>
                                        {{ app()->getLocale() == 'ar' ? 'انتهاء الاشتراك' : 'Subscription Expiry' }}
                                    </h4>
                                    <p class="mb-0 opacity-75">
                                        {{ app()->getLocale() == 'ar' ? 'ينتهي اشتراكك في:' : 'Your subscription expires on:' }}
                                        <strong>{{ $endDate->format('Y-m-d H:i:s') }}</strong>
                                    </p>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="{{ route('subscriptions.index') }}" class="btn btn-light btn-lg">
                                        <i class="ri-refresh-line me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'تجديد الاشتراك' : 'Renew Subscription' }}
                                    </a>
                                </div>
                            </div>

                            <!-- Countdown Timer -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-center gap-3" id="countdown-timer">
                                        <div class="text-center">
                                            <div class="bg-white bg-opacity-25 rounded p-3" style="min-width: 80px;">
                                                <h2 class="text-dark mb-0 fw-bold" id="days">--</h2>
                                                <small class="text-dark opacity-75">{{ app()->getLocale() == 'ar' ? 'يوم' : 'Days' }}</small>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div class="bg-white bg-opacity-25 rounded p-3" style="min-width: 80px;">
                                                <h2 class="text-dark mb-0 fw-bold" id="hours">--</h2>
                                                <small class="text-dark opacity-75">{{ app()->getLocale() == 'ar' ? 'ساعة' : 'Hours' }}</small>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div class="bg-white bg-opacity-25 rounded p-3" style="min-width: 80px;">
                                                <h2 class="text-dark mb-0 fw-bold" id="minutes">--</h2>
                                                <small class="text-dark opacity-75">{{ app()->getLocale() == 'ar' ? 'دقيقة' : 'Minutes' }}</small>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div class="bg-white bg-opacity-25 rounded p-3" style="min-width: 80px;">
                                                <h2 class="text-dark mb-0 fw-bold" id="seconds">--</h2>
                                                <small class="text-dark opacity-75">{{ app()->getLocale() == 'ar' ? 'ثانية' : 'Seconds' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Countdown Timer
                const endDate = new Date("{{ $endDate->format('Y-m-d H:i:s') }}").getTime();

                function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = endDate - now;

                    if (distance < 0) {
                        document.getElementById('countdown-timer').innerHTML = '<div class="alert alert-danger w-100 text-center mb-0"><strong>{{ app()->getLocale() == 'ar' ? 'انتهى الاشتراك!' : 'Subscription Expired!' }}</strong></div>';
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    document.getElementById('days').innerText = days.toString().padStart(2, '0');
                    document.getElementById('hours').innerText = hours.toString().padStart(2, '0');
                    document.getElementById('minutes').innerText = minutes.toString().padStart(2, '0');
                    document.getElementById('seconds').innerText = seconds.toString().padStart(2, '0');
                }

                // Update countdown every second
                updateCountdown();
                setInterval(updateCountdown, 1000);
            </script>
        @endif
    @endif
    <!-- /Subscription Countdown Timer -->

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
                        <a href="{{ route('products.create') }}" class="btn btn-success">
                            <i class="ri-add-line me-1"></i>
                            {{ __('messages.add_product') }}
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-warning">
                            <i class="ri-shopping-bag-3-line me-1"></i>
                            {{ __('messages.my_products') }}
                        </a>
                        <a href="{{ route('orders.index') }}" class="btn btn-danger">
                            <i class="ri-file-list-3-line me-1"></i>
                            {{ __('messages.my_orders') }}
                        </a>
                        <a href="{{ route('categories.index') }}" class="btn btn-dark">
                            <i class="ri-price-tag-3-line me-1"></i>
                            {{ __('messages.categories') }}
                        </a>
                        <a href="{{ route('seller.profit-settings.index') }}" class="btn btn-secondary">
                            <i class="ri-percent-line me-1"></i>
                            {{ __('messages.profit_settings') }}
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
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-success">
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
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-success">
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
                                            <span class="badge bg-{{ $product->stock > 10 ? 'success' : ($product->stock > 0 ? 'warning' : 'danger') }}">
                                                {{ $product->stock }}
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
@endsection
