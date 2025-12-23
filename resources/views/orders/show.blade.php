@extends('dashboard')

@section('content')
<div class="col-12">
    <!-- Order Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-2">{{ __('messages.order') }} {{ $order->order_number }}</h4>
                    <span class="badge bg-{{ $order->getStatusBadgeColor() }} fs-6">{{ $order->getStatusName() }}</span>
                    @if($order->aliexpress_order_id)
                        <p class="text-muted mt-2 mb-0">{{ __('messages.supplier_order_id') }}: <strong>{{ $order->aliexpress_order_id }}</strong></p>
                    @endif
                </div>
                <div class="btn-group">
                    <!-- @if($order->canBePlaced() && empty($order->aliexpress_order_id))
                        <div class="alert alert-info mb-2" style="font-size: 0.875rem;">
                            <i class="ri-information-line me-1"></i>
                            {{ __('messages.order_auto_place_info') }}
                        </div>
                        <form action="{{ route('orders.place-on-aliexpress', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('{{ __('messages.confirm_place_with_supplier') }}')">
                                <i class="ri-shopping-cart-line me-1"></i> {{ __('messages.place_with_supplier') }}
                            </button>
                        </form>
                    @endif -->

                    <!-- @if($order->aliexpress_order_id && in_array($order->status, ['placed', 'paid', 'shipped']))
                        <form action="{{ route('orders.update-tracking', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info">
                                <i class="ri-refresh-line me-1"></i> {{ __('messages.update_tracking') }}
                            </button>
                        </form>
                    @endif -->

                    <!-- @if($order->canBeCancelled())
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('{{ __('messages.confirm_cancel_order') }}')">
                                <i class="ri-close-circle-line me-1"></i> {{ __('messages.cancel') }}
                            </button>
                        </form>
                    @endif -->

                    <!-- <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> {{ __('messages.back') }}
                    </a> -->
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Product & Pricing -->
        <div class="col-md-8">
            <div class="card mb-4">
                    <div class="card-header">
                    <h6 class="mb-0">{{ __('messages.order_items') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        @if($order->product->images && count($order->product->images) > 0)
                            <img src="{{ $order->product->images[0] }}" alt="{{ $order->product->name }}" class="me-3" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                        @endif
                        <div class="flex-grow-1">
                            <h6>{{ $order->product->name }}</h6>
                            <p class="text-muted mb-2">{{ __('messages.quantity') }}: {{ $order->quantity }}</p>
                            <p class="mb-0" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <strong>{{ app()->getLocale() == 'ar' ? 'سعر الوحدة:' : 'Unit Price:' }}</strong>
                                {{ $order->currency }} {{ number_format($order->unit_price, 2) }}
                                <br>
                                <strong>{{ app()->getLocale() == 'ar' ? 'الإجمالي:' : 'Total:' }}</strong>
                                <span class="text-primary fs-5">
                                    {{ $order->currency }} {{ number_format($order->total_price, 2) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('products.detail', $order->product) }}" class="btn btn-sm btn-outline-primary">{{ __('messages.view_product') }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('messages.customer_information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>{{ __('messages.full_name') }}:</strong> {{ $order->customer_name }}</p>
                            @if($order->customer_email)
                                <p class="mb-2"><strong>{{ __('messages.email') }}:</strong> {{ $order->customer_email }}</p>
                            @endif
                            <p class="mb-0"><strong>{{ __('messages.phone') }}:</strong> +{{ $order->phone_country }} {{ $order->customer_phone }}</p>
                        </div>
                    </div>
                    @if($order->customer_notes)
                        <div class="mt-3">
                            <strong>Customer Notes:</strong>
                            <p class="mb-0 mt-1">{{ $order->customer_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('messages.shipping_information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="ri-user-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'الاسم:' : 'Name:' }}</strong>
                                {{ $order->customer_name }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="ri-phone-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'رقم الهاتف:' : 'Phone:' }}</strong>
                                +{{ $order->phone_country }} {{ $order->customer_phone }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="ri-global-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'الدولة:' : 'Country:' }}</strong>
                                {{ $order->shipping_country }}
                            </p>
                            @if($order->shipping_province)
                                <p class="mb-2">
                                    <strong><i class="ri-map-2-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'المنطقة/المحافظة:' : 'Region/Province:' }}</strong>
                                    {{ $order->shipping_province }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($order->shipping_city)
                                <p class="mb-2">
                                    <strong><i class="ri-building-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'المدينة:' : 'City:' }}</strong>
                                    {{ $order->shipping_city }}
                                </p>
                            @endif
                            <p class="mb-2">
                                <strong><i class="ri-home-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'العنوان:' : 'Address:' }}</strong>
                                {{ $order->shipping_address }}
                            </p>
                            @if($order->shipping_address2)
                                <p class="mb-2">
                                    <strong><i class="ri-home-2-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'العنوان 2:' : 'Address 2:' }}</strong>
                                    {{ $order->shipping_address2 }}
                                </p>
                            @endif
                            @if($order->shipping_zip)
                                <p class="mb-2">
                                    <strong><i class="ri-mail-send-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'صندوق البريد/الرمز البريدي:' : 'P.O. Box/Zip Code:' }}</strong>
                                    {{ $order->shipping_zip }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if($order->tracking_number)
                        <div class="mt-3 p-3 bg-light rounded">
                            <p class="mb-1"><strong>{{ __('messages.tracking_number') }}:</strong> {{ $order->tracking_number }}</p>
                            @if($order->shipping_method)
                                <p class="mb-0"><strong>{{ __('messages.shipping_method') }}:</strong> {{ $order->shipping_method }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('messages.order_timeline') }}</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <i class="ri-checkbox-circle-line text-success"></i>
                            <div>
                                <strong>{{ __('messages.order_created') }}</strong>
                                <p class="text-muted small mb-0">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>

                        @if($order->placed_at)
                            <div class="timeline-item">
                                <i class="ri-shopping-cart-line text-primary"></i>
                                <div>
                                    <strong>{{ __('messages.placed_with_supplier') }}</strong>
                                    <p class="text-muted small mb-0">{{ $order->placed_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($order->shipped_at)
                            <div class="timeline-item">
                                <i class="ri-truck-line text-info"></i>
                                <div>
                                    <strong>{{ __('messages.shipped') }}</strong>
                                    <p class="text-muted small mb-0">{{ $order->shipped_at->format('d M Y, h:i A') }}</p>
                                    @if($order->tracking_number)
                                        <p class="small mb-0 mt-1">
                                            <span class="badge bg-info">{{ $order->tracking_number }}</span>
                                        </p>
                                    @endif
                                    @if($order->shipping_method)
                                        <p class="text-muted small mb-0">{{ $order->shipping_method }}</p>
                                    @endif
                                </div>
                            </div>

                            @php
                                $shipping = $order->shipping;
                                $trackingEvents = $shipping && $shipping->tracking_events ? $shipping->tracking_events : [];
                            @endphp

                            @if(!empty($trackingEvents))
                                @foreach($trackingEvents as $event)
                                    <div class="timeline-item">
                                        <i class="ri-map-pin-line text-secondary" style="font-size: 14px;"></i>
                                        <div>
                                            <strong class="small">{{ $event['status'] ?? 'Update' }}</strong>
                                            <p class="text-muted small mb-0">{{ $event['description'] ?? '' }}</p>
                                            @if(isset($event['timestamp']))
                                                <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                                                    {{ \Carbon\Carbon::createFromTimestampMs($event['timestamp'])->format('d M Y, h:i A') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endif

                        @if($order->delivered_at)
                            <div class="timeline-item">
                                <i class="ri-check-double-line text-success"></i>
                                <div>
                                    <strong>{{ __('messages.delivered') }}</strong>
                                    <p class="text-muted small mb-0">{{ $order->delivered_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($order->admin_notes)
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Admin Notes:</strong>
                            <p class="mb-0 mt-1 small">{{ $order->admin_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
    display: flex;
    gap: 15px;
}

.timeline-item i {
    font-size: 20px;
    flex-shrink: 0;
}
</style>
@endsection
