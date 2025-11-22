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

    <!-- Sync All Button -->
    <div class="row mb-3">
        <div class="col-12">
            <form method="POST" action="{{ route('seller.shipping.sync-all') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary" id="sync-all-btn">
                    <i class="ri-refresh-line me-1"></i>
                    {{ __('messages.sync_all_from_aliexpress') }}
                </button>
            </form>
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
                                    <th>{{ __('messages.tracking_number') }}</th>
                                    <th>{{ __('messages.shipping_method') }}</th>
                                    <th>{{ __('messages.last_update') }}</th>
                                    <th class="text-center">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                        @if($order->aliexpress_order_id)
                                            <br><small class="text-muted">AE: {{ $order->aliexpress_order_id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $order->customer_name }}<br>
                                        <small class="text-muted">{{ $order->customer_phone }}</small>
                                    </td>
                                    <td>
                                        @if($order->product)
                                            {{ Str::limit($order->product->name, 30) }}
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
                                        @if($order->tracking_number)
                                            <code>{{ $order->tracking_number }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
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
                                    <td colspan="9" class="text-center py-4">
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
