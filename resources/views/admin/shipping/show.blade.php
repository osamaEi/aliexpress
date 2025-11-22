@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ __('messages.shipping_details') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.tracking_number') }}: {{ $shipping->tracking_number ?? __('messages.not_available') }}</p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.shipping.sync', $shipping->order) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="ri-refresh-line me-1"></i>
                    {{ __('messages.sync_tracking') }}
                </button>
            </form>
            <a href="{{ route('admin.shipping.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i>
                {{ __('messages.back') }}
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Shipping Information -->
        <div class="col-lg-8 mb-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.shipping_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">{{ __('messages.tracking_number') }}</label>
                            <div>
                                @if($shipping->tracking_number)
                                    <code class="fs-5">{{ $shipping->tracking_number }}</code>
                                @else
                                    <span class="text-muted">{{ __('messages.not_available') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">{{ __('messages.status') }}</label>
                            <div>
                                @if($shipping->status === 'pending')
                                    <span class="badge bg-warning fs-6">{{ __('messages.pending') }}</span>
                                @elseif($shipping->status === 'in_transit')
                                    <span class="badge bg-info fs-6">{{ __('messages.in_transit') }}</span>
                                @elseif($shipping->status === 'delivered')
                                    <span class="badge bg-success fs-6">{{ __('messages.delivered') }}</span>
                                @elseif($shipping->status === 'exception')
                                    <span class="badge bg-danger fs-6">{{ __('messages.exception') }}</span>
                                @else
                                    <span class="badge bg-secondary fs-6">{{ ucfirst($shipping->status) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">{{ __('messages.carrier') }}</label>
                            <div>
                                @if($shipping->carrier_name)
                                    <strong>{{ $shipping->carrier_name }}</strong>
                                    @if($shipping->carrier_code)
                                        <span class="text-muted">({{ $shipping->carrier_code }})</span>
                                    @endif
                                @else
                                    <span class="text-muted">{{ __('messages.not_available') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">{{ __('messages.shipping_method') }}</label>
                            <div>
                                {{ $shipping->shipping_method ?? __('messages.not_available') }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">{{ __('messages.origin_country') }}</label>
                            <div>
                                {{ $shipping->origin_country ?? __('messages.not_available') }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">{{ __('messages.destination_country') }}</label>
                            <div>
                                {{ $shipping->destination_country ?? __('messages.not_available') }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">{{ __('messages.shipped_at') }}</label>
                            <div>
                                @if($shipping->shipped_at)
                                    {{ $shipping->shipped_at->format('Y-m-d H:i:s') }}
                                @else
                                    <span class="text-muted">{{ __('messages.not_available') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">{{ __('messages.estimated_delivery') }}</label>
                            <div>
                                @if($shipping->estimated_delivery_at)
                                    {{ $shipping->estimated_delivery_at->format('Y-m-d') }}
                                @else
                                    <span class="text-muted">{{ __('messages.not_available') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">{{ __('messages.delivered_at') }}</label>
                            <div>
                                @if($shipping->delivered_at)
                                    {{ $shipping->delivered_at->format('Y-m-d H:i:s') }}
                                @else
                                    <span class="text-muted">{{ __('messages.not_delivered_yet') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">{{ __('messages.last_synced') }}</label>
                            <div>
                                @if($shipping->last_synced_at)
                                    {{ $shipping->last_synced_at->format('Y-m-d H:i:s') }}
                                    <span class="text-muted">({{ $shipping->last_synced_at->diffForHumans() }})</span>
                                @else
                                    <span class="text-muted">{{ __('messages.never') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking Events -->
            @if($shipping->tracking_events && count($shipping->tracking_events) > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.tracking_history') }}</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($shipping->tracking_events as $index => $event)
                        <div class="timeline-item {{ $index === 0 ? 'timeline-item-primary' : '' }}">
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">{{ $event['status'] ?? __('messages.status_update') }}</h6>
                                    <small class="text-muted">
                                        @if(isset($event['timestamp']))
                                            {{ \Carbon\Carbon::parse($event['timestamp'])->format('Y-m-d H:i') }}
                                        @endif
                                    </small>
                                </div>
                                @if(isset($event['description']))
                                <p class="mb-0 mt-1">{{ $event['description'] }}</p>
                                @endif
                                @if(isset($event['location']))
                                <p class="text-muted mb-0 mt-1">
                                    <i class="ri-map-pin-line"></i>
                                    {{ $event['location'] }}
                                </p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Order Information -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.order_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">{{ __('messages.order_number') }}</label>
                        <div><strong>{{ $shipping->order->order_number }}</strong></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">{{ __('messages.order_status') }}</label>
                        <div>
                            @if($shipping->order->status === 'pending')
                                <span class="badge bg-warning">{{ __('messages.pending') }}</span>
                            @elseif($shipping->order->status === 'processing')
                                <span class="badge bg-info">{{ __('messages.processing') }}</span>
                            @elseif($shipping->order->status === 'shipped')
                                <span class="badge bg-primary">{{ __('messages.shipped') }}</span>
                            @elseif($shipping->order->status === 'delivered')
                                <span class="badge bg-success">{{ __('messages.delivered') }}</span>
                            @elseif($shipping->order->status === 'cancelled')
                                <span class="badge bg-danger">{{ __('messages.cancelled') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">{{ __('messages.total_amount') }}</label>
                        <div><strong>${{ number_format($shipping->order->total_amount, 2) }}</strong></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">{{ __('messages.order_date') }}</label>
                        <div>{{ $shipping->order->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                    @if($shipping->order->aliexpress_order_id)
                    <div class="mb-3">
                        <label class="form-label text-muted">{{ __('messages.aliexpress_order_id') }}</label>
                        <div><code>{{ $shipping->order->aliexpress_order_id }}</code></div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.customer_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">{{ __('messages.name') }}</label>
                        <div><strong>{{ $shipping->order->user->name }}</strong></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">{{ __('messages.email') }}</label>
                        <div>{{ $shipping->order->user->email }}</div>
                    </div>
                    @if($shipping->order->user->phone)
                    <div class="mb-3">
                        <label class="form-label text-muted">{{ __('messages.phone') }}</label>
                        <div>{{ $shipping->order->user->phone }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    padding-bottom: 25px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: -25px;
    width: 2px;
    background-color: #e5e7eb;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-item::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #6c757d;
}

.timeline-item-primary::after {
    background-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
}

.timeline-event {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #e5e7eb;
}

.timeline-item-primary .timeline-event {
    border-left-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>
@endpush
@endsection
