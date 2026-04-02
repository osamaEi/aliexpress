@extends('dashboard')

@section('content')
@php $isAr = app()->getLocale() == 'ar'; @endphp
<div class="col-12" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
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

                     @if($order->aliexpress_order_id && in_array($order->status, ['placed', 'paid', 'shipped']))
                        <form action="{{ route('orders.update-tracking', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info">
                                <i class="ri-refresh-line me-1"></i> {{ __('messages.update_tracking') }}
                            </button>
                        </form>
                    @endif

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
                            @php
                                $cur       = $order->currency ?? 'AED';
                                $unitPrice = $currentCurrency->convertFrom($order->unit_price, $cur);
                                $freight   = $currentCurrency->convertFrom($order->freight_amount ?? 0, $cur);
                                $subtotal  = $unitPrice * $order->quantity;
                                $grandTotal = $subtotal + $freight;
                                $productName = $isAr && $order->product->name_ar ? $order->product->name_ar : $order->product->name;
                            @endphp
                            <h6>{{ $productName }}</h6>

                            {{-- Price breakdown table --}}
                            <table class="table table-sm table-borderless mb-0 mt-2" style="width: auto; min-width: 280px;">
                                <tr>
                                    <td class="text-muted py-1">{{ __('messages.unit_price') }}</td>
                                    <td class="py-1" style="direction:ltr;">
                                        <x-session-currency-icon width="13" height="13" />
                                        {{ number_format($unitPrice, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-1">{{ __('messages.quantity') }}</td>
                                    <td class="py-1">× {{ $order->quantity }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-1">{{ $isAr ? 'سعر المنتج' : 'Products Subtotal' }}</td>
                                    <td class="py-1" style="direction:ltr;">
                                        <x-session-currency-icon width="13" height="13" />
                                        {{ number_format($subtotal, 2) }}
                                    </td>
                                </tr>
                                @if($freight > 0)
                                <tr>
                                    <td class="text-muted py-1">{{ __('messages.shipping_cost') }}</td>
                                    <td class="py-1 text-info" style="direction:ltr;">
                                        + <x-session-currency-icon width="13" height="13" />
                                        {{ number_format($freight, 2) }}
                                    </td>
                                </tr>
                                @endif
                                <tr class="border-top">
                                    <td class="py-1 fw-bold">{{ __('messages.total_amount') }}</td>
                                    <td class="py-1 fw-bold fs-5" style="direction:ltr; color:#561C04;">
                                        <x-session-currency-icon width="16" height="16" />
                                        {{ number_format($grandTotal, 2) }}
                                    </td>
                                </tr>
                                @if($order->seller_profit > 0)
                                <tr>
                                    <td class="text-muted py-1">{{ __('messages.your_profit') }}</td>
                                    <td class="py-1 text-success fw-semibold" style="direction:ltr;">
                                        + <x-session-currency-icon width="13" height="13" />
                                        {{ number_format($currentCurrency->convertFrom($order->seller_profit, $cur), 2) }}
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="align-self-start">
                            <a href="{{ route('products.detail', $order->product) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ri-external-link-line me-1"></i>{{ __('messages.view_product') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ri-bank-card-line me-2"></i>{{ $isAr ? 'تفاصيل الدفع' : 'Payment Details' }}</h6>
                    @php
                        $payBadge = match($order->payment_status) {
                            'paid'     => 'success',
                            'failed'   => 'danger',
                            'refunded' => 'warning',
                            default    => 'secondary',
                        };
                        $payLabel = match($order->payment_status) {
                            'paid'     => $isAr ? 'مدفوع'  : 'Paid',
                            'failed'   => $isAr ? 'فشل'    : 'Failed',
                            'refunded' => $isAr ? 'مسترجع' : 'Refunded',
                            default    => $isAr ? 'معلق'   : 'Pending',
                        };
                    @endphp
                    <span class="badge bg-{{ $payBadge }}">{{ $payLabel }}</span>
                </div>
                <div class="card-body">
                    @php
                        $cur        = $order->currency ?? 'AED';
                        $unitPrice  = $currentCurrency->convertFrom($order->unit_price, $cur);
                        $freight    = $currentCurrency->convertFrom($order->freight_amount ?? 0, $cur);
                        $subtotal   = $unitPrice * $order->quantity;
                        $grandTotal = $subtotal + $freight;
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">{{ __('messages.unit_price') }}</span>
                                    <span style="direction:ltr;" class="d-inline-flex align-items-center gap-1">
                                        <x-session-currency-icon width="13" height="13" />{{ number_format($unitPrice, 2) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">{{ __('messages.quantity') }}</span>
                                    <span>× {{ $order->quantity }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">{{ $isAr ? 'سعر المنتج' : 'Products Subtotal' }}</span>
                                    <span style="direction:ltr;" class="d-inline-flex align-items-center gap-1">
                                        <x-session-currency-icon width="13" height="13" />{{ number_format($subtotal, 2) }}
                                    </span>
                                </div>
                                @if($freight > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">{{ __('messages.shipping_cost') }}</span>
                                    <span class="text-info" style="direction:ltr;" class="d-inline-flex align-items-center gap-1">
                                        + <x-session-currency-icon width="13" height="13" />{{ number_format($freight, 2) }}
                                    </span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <span class="fw-bold">{{ __('messages.total_amount') }}</span>
                                    <span class="fw-bold fs-5 d-inline-flex align-items-center gap-1" style="direction:ltr; color:#561C04;">
                                        <x-session-currency-icon width="16" height="16" />{{ number_format($grandTotal, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded h-100">
                                <p class="mb-2">
                                    <span class="text-muted">{{ $isAr ? 'طريقة الدفع:' : 'Payment Method:' }}</span>
                                    <strong class="ms-2">{{ $isAr ? 'المحفظة الإلكترونية' : 'Wallet' }}</strong>
                                </p>
                                <p class="mb-2">
                                    <span class="text-muted">{{ $isAr ? 'حالة الدفع:' : 'Payment Status:' }}</span>
                                    <span class="badge bg-{{ $payBadge }} ms-2">{{ $payLabel }}</span>
                                </p>
                                @if($order->seller_profit > 0)
                                <p class="mb-2">
                                    <span class="text-muted">{{ __('messages.your_profit') }}:</span>
                                    <strong class="text-success ms-2 d-inline-flex align-items-center gap-1" style="direction:ltr;">
                                        + <x-session-currency-icon width="13" height="13" />{{ number_format($currentCurrency->convertFrom($order->seller_profit, $cur), 2) }}
                                    </strong>
                                </p>
                                @endif
                                <p class="mb-0">
                                    <span class="text-muted">{{ $isAr ? 'تاريخ الإنشاء:' : 'Created At:' }}</span>
                                    <strong class="ms-2">{{ $order->created_at->format('Y-m-d H:i') }}</strong>
                                </p>
                            </div>
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
                            <strong>{{ __('messages.customer_notes') }}:</strong>
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
                                <strong><i class="ri-user-line me-1"></i>{{ $isAr ? 'الاسم:' : 'Name:' }}</strong>
                                {{ $order->customer_name }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="ri-phone-line me-1"></i>{{ $isAr ? 'رقم الهاتف:' : 'Phone:' }}</strong>
                                +{{ $order->phone_country }} {{ $order->customer_phone }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="ri-global-line me-1"></i>{{ $isAr ? 'الدولة:' : 'Country:' }}</strong>
                                {{ $order->shipping_country }}
                            </p>
                            @if($order->shipping_province)
                                <p class="mb-2">
                                    <strong><i class="ri-map-2-line me-1"></i>{{ $isAr ? 'المنطقة/المحافظة:' : 'Region/Province:' }}</strong>
                                    {{ $order->shipping_province }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($order->shipping_city)
                                <p class="mb-2">
                                    <strong><i class="ri-building-line me-1"></i>{{ $isAr ? 'المدينة:' : 'City:' }}</strong>
                                    {{ $order->shipping_city }}
                                </p>
                            @endif
                            <p class="mb-2">
                                <strong><i class="ri-home-line me-1"></i>{{ $isAr ? 'العنوان:' : 'Address:' }}</strong>
                                {{ $order->shipping_address }}
                            </p>
                            @if($order->shipping_address2)
                                <p class="mb-2">
                                    <strong><i class="ri-home-2-line me-1"></i>{{ $isAr ? 'العنوان 2:' : 'Address 2:' }}</strong>
                                    {{ $order->shipping_address2 }}
                                </p>
                            @endif
                            @if($order->shipping_zip)
                                <p class="mb-2">
                                    <strong><i class="ri-mail-send-line me-1"></i>{{ $isAr ? 'صندوق البريد/الرمز البريدي:' : 'P.O. Box/Zip Code:' }}</strong>
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
                            <strong>{{ __('messages.admin_notes') }}:</strong>
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
