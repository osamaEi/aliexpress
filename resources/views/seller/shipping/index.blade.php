@extends('dashboard')

@section('title', __('messages.shipping_tracking'))

@section('content')
<div class="container-fluid py-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="ri-truck-line me-2"></i>
                        {{ __('messages.shipping_tracking') }}
                    </h4>
                    <p class="mb-0 mt-2 small">
                        {{ __('messages.track_your_orders_shipping') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('messages.pending_shipment') }}</h6>
                            <h4 class="mb-0 text-warning">{{ $stats['pending'] ?? 0 }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="ri-time-line" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('messages.in_transit') }}</h6>
                            <h4 class="mb-0 text-info">{{ $stats['shipped'] ?? 0 }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="ri-ship-line" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('messages.delivered') }}</h6>
                            <h4 class="mb-0 text-success">{{ $stats['delivered'] ?? 0 }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="ri-checkbox-circle-line" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('messages.total_orders') }}</h6>
                            <h4 class="mb-0 text-primary">{{ $stats['total'] ?? 0 }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="ri-shopping-bag-line" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('seller.shipping.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('messages.search') }}</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="{{ __('messages.order_number_tracking') }}" value="{{ request('search') }}">
                        </div>

                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('messages.status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>{{ __('messages.all') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                                <option value="placed" {{ request('status') == 'placed' ? 'selected' : '' }}>{{ __('messages.placed') }}</option>
                                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>{{ __('messages.shipped') }}</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('messages.delivered') }}</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="date_from" class="form-label">{{ __('messages.from_date') }}</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-search-line"></i> {{ __('messages.filter') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ __('messages.shipping_details') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('messages.order_number') }}</th>
                                    <th>{{ __('messages.customer') }}</th>
                                    <th>{{ __('messages.product') }}</th>
                                    <th>{{ __('messages.order_date') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.shipping_method') }}</th>
                                    <th>{{ __('messages.last_update') }}</th>
                                    <th class="text-center">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                @php
                                    // Get country code for flag
                                    $customerCountry = strtolower($order->shipping_country ?? 'ae');
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            {{ $order->customer_name }}
                                            <img src="https://flagcdn.com/w20/{{ $customerCountry }}.png"
                                                 alt="{{ strtoupper($customerCountry) }}"
                                                 class="ms-1"
                                                 style="width:20px;height:14px;object-fit:cover;vertical-align:middle;border-radius:2px;"
                                                 onerror="this.style.display='none'" />
                                        </div>
                                        <small class="text-muted">{{ $order->customer_phone }}</small>
                                    </td>
                                    <td>
                                        @if($order->product)
                                            <a href="{{ route('products.show', $order->product) }}">
                                                {{ Str::limit($order->product->name, 30) }}
                                            </a>
                                            @if($order->product->isAliexpressProduct())
                                                <br><small class="text-muted">
                                                    🇨🇳 {{ app()->getLocale() == 'ar' ? 'من الصين' : 'From China' }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $order->created_at->format('Y-m-d') }}<br>
                                        <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->getStatusBadgeColor() }}">
                                            {{ $order->getStatusName() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->shipping_method)
                                            {{ $order->shipping_method }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->shipped_at)
                                            {{ $order->shipped_at->format('Y-m-d') }}
                                        @elseif($order->placed_at)
                                            {{ $order->placed_at->format('Y-m-d') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($order->aliexpress_order_id)
                                            <form method="POST" action="{{ route('seller.shipping.sync', $order) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary"
                                                        title="{{ __('messages.sync_tracking') }}">
                                                    <i class="ri-refresh-line"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="ri-inbox-line" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">{{ __('messages.no_orders_found') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($orders->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            {{ __('messages.showing') }} {{ $orders->firstItem() }} {{ __('messages.to') }} {{ $orders->lastItem() }} {{ __('messages.of') }} {{ $orders->total() }} {{ __('messages.orders') }}
                        </div>
                        <div>
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* Primary Color Override */
    .bg-primary,
    .btn-primary,
    .text-primary,
    .border-primary {
        background-color: #561C04 !important;
        color: white !important;
        border-color: #561C04 !important;
    }

    /* Info Color Override - Same as Primary */
    .bg-info,
    .btn-info,
    .text-info,
    .border-info {
        background-color: #561C04 !important;
        color: white !important;
        border-color: #561C04 !important;
    }

    /* Success Color Override - Orange */
    .bg-success,
    .btn-success,
    .text-success,
    .border-success {
        background-color: #e56300 !important;
        color: white !important;
        border-color: #e56300 !important;
    }

    /* Warning Color Override - Orange */
    .bg-warning,
    .btn-warning,
    .text-warning,
    .border-warning {
        background-color: #e56300 !important;
        color: white !important;
        border-color: #e56300 !important;
    }

    .btn-primary:hover,
    .btn-primary:focus,
    .btn-primary:active,
    .btn-primary.active,
    .btn-info:hover,
    .btn-info:focus,
    .btn-info:active,
    .btn-info.active {
        background-color: #7a2805 !important;
        border-color: #7a2805 !important;
        color: white !important;
    }

    .btn-success:hover,
    .btn-success:focus,
    .btn-success:active,
    .btn-success.active,
    .btn-warning:hover,
    .btn-warning:focus,
    .btn-warning:active,
    .btn-warning.active {
        background-color: #c75400 !important;
        border-color: #c75400 !important;
        color: white !important;
    }

    /* All Outline Buttons */
    .btn-outline-primary,
    .btn-outline-secondary,
    .btn-outline-info,
    .btn-outline-warning,
    .btn-outline-success,
    .btn-outline-danger {
        color: #561C04 !important;
        border-color: #561C04 !important;
        background-color: transparent !important;
    }

    .btn-outline-primary:hover,
    .btn-outline-primary:focus,
    .btn-outline-primary:active,
    .btn-outline-secondary:hover,
    .btn-outline-secondary:focus,
    .btn-outline-secondary:active,
    .btn-outline-info:hover,
    .btn-outline-info:focus,
    .btn-outline-info:active,
    .btn-outline-warning:hover,
    .btn-outline-warning:focus,
    .btn-outline-warning:active,
    .btn-outline-success:hover,
    .btn-outline-success:focus,
    .btn-outline-success:active,
    .btn-outline-danger:hover,
    .btn-outline-danger:focus,
    .btn-outline-danger:active {
        background-color: #561C04 !important;
        color: white !important;
        border-color: #561C04 !important;
    }

    /* All Regular Buttons Hover */
    .btn-secondary:hover,
    .btn-danger:hover {
        background-color: #561C04 !important;
        border-color: #561C04 !important;
        color: white !important;
    }

    /* Form Controls */
    .form-control:focus,
    .form-select:focus {
        border-color: #561C04 !important;
        box-shadow: 0 0 0 0.2rem rgba(86, 28, 4, 0.25) !important;
    }

    /* Links */
    a {
        color: #561C04 !important;
    }

    a:hover,
    a:focus {
        color: #7a2805 !important;
    }

    /* Card Hover */
    .card:hover {
        border-color: #561C04 !important;
    }

    /* Table Row Hover */
    .table-hover tbody tr:hover {
        background-color: rgba(86, 28, 4, 0.05) !important;
    }

    /* Badge Hover */
    .badge:hover {
        opacity: 0.85;
        cursor: pointer;
    }

    /* Badge Info */
    .badge.bg-info {
        background-color: #561C04 !important;
    }

    /* Badge Success */
    .badge.bg-success {
        background-color: #e56300 !important;
    }

    /* Badge Warning */
    .badge.bg-warning {
        background-color: #e56300 !important;
    }

    .border-4 {
        border-width: 4px !important;
    }

    .table th {
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .badge {
        font-weight: 500;
        padding: 0.4em 0.6em;
    }
</style>

@endsection
