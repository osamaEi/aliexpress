@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.admin_dashboard') }}</h4>
        <p class="text-muted">{{ __('messages.welcome_admin_panel') }}</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Users -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-primary me-3 p-2">
                            <i class="ri-user-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ $stats['total_users'] }}</h5>
                            <small>{{ __('messages.total_users') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Sellers -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                            <i class="ri-store-2-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ $stats['total_sellers'] }}</h5>
                            <small>{{ __('messages.total_sellers') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-info me-3 p-2">
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

        <!-- Active Subscriptions -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-primary me-3 p-2">
                            <i class="ri-vip-crown-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ $stats['active_subscriptions'] }}</h5>
                            <small>{{ __('messages.active_subscriptions') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Categories -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-info me-3 p-2">
                            <i class="ri-price-tag-3-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ $stats['total_categories'] }}</h5>
                            <small>{{ __('messages.total_categories') }}</small>
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
                        <a href="{{ route('admin.tokens') }}" class="btn btn-primary">
                            <i class="ri-key-2-line me-1"></i>
                            {{ __('messages.manage_tokens') }}
                        </a>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-info">
                            <i class="ri-vip-crown-line me-1"></i>
                            {{ __('messages.manage_subscriptions') }}
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-warning">
                            <i class="ri-file-list-3-line me-1"></i>
                            {{ __('messages.manage_orders') }}
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-success">
                            <i class="ri-price-tag-3-line me-1"></i>
                            {{ __('messages.manage_categories') }}
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
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">{{ __('messages.recent_orders') }}</h5>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">
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
                                        <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'delivered' ? 'success' : 'info') }}">
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
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">{{ __('messages.recent_subscriptions') }}</h5>
                    <a href="{{ route('admin.subscriptions.users') }}" class="btn btn-sm btn-outline-primary">
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
                                        <span class="badge" style="background-color: {{ $subscription->subscription->color }}">
                                            {{ $subscription->subscription->localized_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $subscription->status === 'active' ? 'success' : 'secondary' }}">
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
@endsection
