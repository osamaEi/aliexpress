@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ __('messages.shipping_tracking') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.manage_shipping_tracking') }}</p>
        </div>
        <form method="POST" action="{{ route('admin.shipping.sync-all') }}">
            @csrf
            <button type="submit" class="btn btn-primary" onclick="return confirm('{{ __('messages.sync_all_shipments_confirm') }}')">
                <i class="ri-refresh-line me-1"></i>
                {{ __('messages.sync_all') }}
            </button>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('messages.total_shipments') }}</h6>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-primary rounded">
                                <i class="ri-ship-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('messages.pending') }}</h6>
                            <h3 class="mb-0">{{ number_format($stats['pending']) }}</h3>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-warning rounded">
                                <i class="ri-time-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('messages.in_transit') }}</h6>
                            <h3 class="mb-0">{{ number_format($stats['in_transit']) }}</h3>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-info rounded">
                                <i class="ri-truck-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('messages.delivered') }}</h6>
                            <h3 class="mb-0">{{ number_format($stats['delivered']) }}</h3>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-label-success rounded">
                                <i class="ri-checkbox-circle-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.shipping.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('messages.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>{{ __('messages.all_status') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                        <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>{{ __('messages.in_transit') }}</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('messages.delivered') }}</option>
                        <option value="exception" {{ request('status') == 'exception' ? 'selected' : '' }}>{{ __('messages.exception') }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('messages.search') }}</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="{{ __('messages.search_tracking_order') }}"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri-search-line me-1"></i>
                        {{ __('messages.filter') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Shipping Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.shipments') }}: {{ $shippings->total() }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.order_number') }}</th>
                            <th>{{ __('messages.tracking_number') }}</th>
                            <th>{{ __('messages.carrier') }}</th>
                            <th>{{ __('messages.customer') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.shipped_at') }}</th>
                            <th>{{ __('messages.estimated_delivery') }}</th>
                            <th>{{ __('messages.last_synced') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shippings as $shipping)
                        <tr>
                            <td>
                                <strong>{{ $shipping->order->order_number }}</strong>
                                <div class="text-muted small">{{ $shipping->order->created_at->format('Y-m-d H:i') }}</div>
                            </td>
                            <td>
                                @if($shipping->tracking_number)
                                    <code>{{ $shipping->tracking_number }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($shipping->carrier_name)
                                    <div>{{ $shipping->carrier_name }}</div>
                                    @if($shipping->shipping_method)
                                        <small class="text-muted">{{ $shipping->shipping_method }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $shipping->order->user->name }}</strong>
                                    <div class="text-muted small">{{ $shipping->order->user->email }}</div>
                                </div>
                            </td>
                            <td>
                                @if($shipping->status === 'pending')
                                    <span class="badge bg-warning">{{ __('messages.pending') }}</span>
                                @elseif($shipping->status === 'in_transit')
                                    <span class="badge bg-info">{{ __('messages.in_transit') }}</span>
                                @elseif($shipping->status === 'delivered')
                                    <span class="badge bg-success">{{ __('messages.delivered') }}</span>
                                @elseif($shipping->status === 'exception')
                                    <span class="badge bg-danger">{{ __('messages.exception') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($shipping->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($shipping->shipped_at)
                                    {{ $shipping->shipped_at->format('Y-m-d H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($shipping->estimated_delivery_at)
                                    {{ $shipping->estimated_delivery_at->format('Y-m-d') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($shipping->last_synced_at)
                                    <span title="{{ $shipping->last_synced_at->format('Y-m-d H:i:s') }}">
                                        {{ $shipping->last_synced_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted">{{ __('messages.never') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.shipping.show', $shipping) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="{{ __('messages.view_details') }}">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.shipping.sync', $shipping->order) }}" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-info"
                                                title="{{ __('messages.sync_tracking') }}">
                                            <i class="ri-refresh-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                {{ __('messages.no_shipments_yet') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($shippings->hasPages())
        <div class="card-footer">
            {{ $shippings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
