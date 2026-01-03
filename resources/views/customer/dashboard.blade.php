@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'لوحة تحكم المشتري' : 'Customer Dashboard' }}</h4>
        <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'مرحباً بك في لوحة التحكم الخاصة بك' : 'Welcome to your customer dashboard' }}</p>
    </div>

    <!-- Dashboard Banner -->
    @if(setting_image('buyer_dashboard_banner'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="position-relative" style="border-radius: 12px; overflow: hidden; height: 280px; background: linear-gradient(135deg, #561C04 0%, #7A3206 100%);">
                    <!-- Banner Image -->
                    <img src="{{ setting_image('buyer_dashboard_banner') }}" alt="Buyer Dashboard Banner" class="img-fluid w-100 h-100" style="object-fit: cover; position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    
                    <!-- Overlay Gradient -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(86, 28, 4, 0.6) 0%, rgba(122, 50, 6, 0.4) 100%);"></div>
                    
                    <!-- Content -->
                    <div class="position-relative h-100 d-flex align-items-center p-5" style="z-index: 2;">
                        <div>
                            <h2 class="text-white fw-bold mb-2" style="font-size: 32px;">
                                <i class="ri-shopping-cart-2-line me-2"></i>
                                {{ app()->getLocale() == 'ar' ? 'لوحة تحكم المشتري' : 'Customer Dashboard' }}
                            </h2>
                            <p class="text-white-50 mb-0" style="font-size: 16px;">
                                {{ app()->getLocale() == 'ar' ? 'تسوق الآن واستمتع بأفضل العروض والخدمات' : 'Shop now and enjoy the best offers and services' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Orders -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-primary me-3 p-2">
                            <i class="ri-file-list-3-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ auth()->user()->orders()->count() }}</h5>
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
                        <div class="badge rounded-pill bg-label-warning me-3 p-2">
                            <i class="ri-time-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ auth()->user()->orders()->where('status', 'pending')->count() }}</h5>
                            <small>{{ __('messages.pending_orders') }}</small>
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
                            <i class="ri-checkbox-circle-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ auth()->user()->orders()->where('status', 'completed')->count() }}</h5>
                            <small>{{ __('messages.completed_orders') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Spent -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-danger me-3 p-2">
                            <i class="ri-money-dollar-circle-line ri-24px"></i>
                        </div>
                        <div class="card-info">
                            <h5 class="mb-0">{{ format_currency(auth()->user()->orders()->sum('total_amount'), 'AED', 2, true) }}</h5>
                            <small>{{ __('messages.total_spent') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('messages.recent_orders') }}</h5>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-primary">{{ app()->getLocale() == 'ar' ? 'عرض الكل' : 'View All' }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('messages.order_id') }}</th>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.total') }}</th>
                                <th>{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(auth()->user()->orders()->latest()->limit(5)->get() as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('orders.show', $order) }}" class="text-primary">
                                        #{{ $order->id }}
                                    </a>
                                </td>
                                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>{{ format_currency($order->total, 'AED', 2, true) }}</td>
                                <td>
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-icon btn-text-primary">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    {{ app()->getLocale() == 'ar' ? 'لا توجد طلبات' : 'No orders yet' }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
