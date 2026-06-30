@extends('dashboard')

@section('content')
@php $isAr = app()->getLocale() == 'ar'; @endphp
<div class="col-12" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <!-- Order Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div>
                        <h4 class="mb-2">{{ __('messages.order') }} {{ $order->order_number }}</h4>
                        <span class="badge bg-{{ $order->getStatusBadgeColor() }} fs-6">{{ $order->getStatusName() }}</span>
                        @if($order->aliexpress_order_id)
                            <p class="text-muted mt-2 mb-0">{{ __('messages.supplier_order_id') }}: <strong>{{ $order->aliexpress_order_id }}</strong></p>
                        @endif
                    </div>
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
                                    <td class="text-muted py-1">{{ __('messages.products_subtotal') }}</td>
                                    <td class="py-1" style="direction:ltr;">
                                        <x-session-currency-icon width="13" height="13" />
                                        {{ number_format($subtotal, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-1">{{ __('messages.shipping_cost') }}</td>
                                    <td class="py-1 {{ $freight > 0 ? 'text-info' : 'text-muted' }}" style="direction:ltr;">
                                        @if($freight > 0)
                                            + <x-session-currency-icon width="13" height="13" />
                                            {{ number_format($freight, 2) }}
                                        @else
                                            {{ __('messages.free') }}
                                        @endif
                                    </td>
                                </tr>
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
                                    <span class="text-muted">{{ __('messages.products_subtotal') }}</span>
                                    <span style="direction:ltr;" class="d-inline-flex align-items-center gap-1">
                                        <x-session-currency-icon width="13" height="13" />{{ number_format($subtotal, 2) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">{{ __('messages.shipping_cost') }}</span>
                                    <span class="{{ $freight > 0 ? 'text-info' : 'text-muted' }}" style="direction:ltr;" class="d-inline-flex align-items-center gap-1">
                                        @if($freight > 0)
                                            + <x-session-currency-icon width="13" height="13" />{{ number_format($freight, 2) }}
                                        @else
                                            {{ __('messages.free') }}
                                        @endif
                                    </span>
                                </div>
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
                        @php
                            $shippingNames = [
                                'CAINIAO_FULFILLMENT_STD'   => $isAr ? 'كينياو - شحن قياسي'      : 'Cainiao Standard Shipping',
                                'CAINIAO_FULFILLMENT_FAST'  => $isAr ? 'كينياو - شحن سريع'        : 'Cainiao Fast Shipping',
                                'CAINIAO_STANDARD'          => $isAr ? 'كينياو - قياسي'            : 'Cainiao Standard',
                                'CAINIAO_ECONOMY'           => $isAr ? 'كينياو - اقتصادي'          : 'Cainiao Economy',
                                'AE_STANDARD'               => $isAr ? ' - قياسي'       : 'AliExpress Standard',
                                'AE_PLUS'                   => $isAr ? ' - بلس'         : 'AliExpress Plus',
                                'YANWEN_JYT'                => $isAr ? 'يانوين - اقتصادي'           : 'Yanwen Economy',
                                'EMS'                       => $isAr ? 'بريد سريع دولي (EMS)'       : 'EMS International',
                                'DHL'                       => 'DHL Express',
                                'UPS'                       => 'UPS',
                                'FEDEX'                     => 'FedEx',
                                'SF_EXPRESS'                => $isAr ? 'SF إكسبريس'                 : 'SF Express',
                                'ARAMEX'                    => 'Aramex',
                                'SELLER_SHIPPING'           => $isAr ? 'شحن البائع'                 : 'Seller Shipping',
                            ];
                            $methodLabel = $shippingNames[$order->shipping_method] ?? str_replace('_', ' ', $order->shipping_method);
                        @endphp
                        <div class="mt-3 p-3 bg-light rounded">
                            <p class="mb-1"><strong>{{ __('messages.tracking_number') }}:</strong> {{ $order->tracking_number }}</p>
                            @if($order->shipping_method)
                                <p class="mb-0"><strong>{{ __('messages.shipping_method') }}:</strong> {{ $methodLabel }}</p>
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
                @php
                    $shippingMethodNames = [
                        'CAINIAO_FULFILLMENT_STD'  => $isAr ? 'كينياو - شحن قياسي'   : 'Cainiao Standard Shipping',
                        'CAINIAO_FULFILLMENT_FAST' => $isAr ? 'كينياو - شحن سريع'    : 'Cainiao Fast Shipping',
                        'CAINIAO_STANDARD'         => $isAr ? 'كينياو - قياسي'        : 'Cainiao Standard',
                        'CAINIAO_ECONOMY'          => $isAr ? 'كينياو - اقتصادي'      : 'Cainiao Economy',
                        'AE_STANDARD'              => $isAr ? ' - قياسي'   : 'AliExpress Standard',
                        'AE_PLUS'                  => $isAr ? ' - بلس'     : 'AliExpress Plus',
                        'YANWEN_JYT'               => $isAr ? 'يانوين - اقتصادي'      : 'Yanwen Economy',
                        'EMS'                      => $isAr ? 'بريد سريع دولي (EMS)'  : 'EMS International',
                        'DHL'                      => 'DHL Express',
                        'UPS'                      => 'UPS',
                        'FEDEX'                    => 'FedEx',
                        'SF_EXPRESS'               => 'SF Express',
                        'ARAMEX'                   => 'Aramex',
                        'SELLER_SHIPPING'          => $isAr ? 'شحن البائع' : 'Seller Shipping',
                    ];

                    // Determine which steps are completed
                    $steps = [
                        'created'  => true,
                        'placed'   => !empty($order->placed_at),
                        'shipped'  => !empty($order->shipped_at),
                        'delivered'=> !empty($order->delivered_at),
                    ];
                    $shipping      = $order->shipping ?? null;
                    $trackingEvents = $shipping && $shipping->tracking_events ? $shipping->tracking_events : [];
                @endphp
                <div class="card-body px-3 py-3">
                    <div class="order-timeline">

                        {{-- Step 1: Created --}}
                        <div class="ot-step done">
                            <div class="ot-icon"><i class="ri-file-list-3-line"></i></div>
                            <div class="ot-line"></div>
                            <div class="ot-content">
                                <span class="ot-label">{{ __('messages.order_created') }}</span>
                                <span class="ot-date">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>

                        {{-- Step 2: Placed with supplier --}}
                        <div class="ot-step {{ $steps['placed'] ? 'done' : 'pending' }}">
                            <div class="ot-icon"><i class="ri-shopping-cart-2-line"></i></div>
                            <div class="ot-line"></div>
                            <div class="ot-content">
                                <span class="ot-label">{{ __('messages.placed_with_supplier') }}</span>
                                @if($steps['placed'])
                                    <span class="ot-date">{{ $order->placed_at->format('d M Y, h:i A') }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Step 3: Shipped --}}
                        <div class="ot-step {{ $steps['shipped'] ? 'done' : 'pending' }}">
                            <div class="ot-icon"><i class="ri-truck-line"></i></div>
                            <div class="ot-line"></div>
                            <div class="ot-content">
                                <span class="ot-label">{{ __('messages.shipped') }}</span>
                                @if($steps['shipped'])
                                    <span class="ot-date">{{ $order->shipped_at->format('d M Y, h:i A') }}</span>
                                    @if($order->tracking_number)
                                        <span class="badge bg-dark mt-1" style="font-size:11px; letter-spacing:.5px;">{{ $order->tracking_number }}</span>
                                    @endif
                                    @if($order->shipping_method)
                                        <span class="ot-sub">{{ $shippingMethodNames[$order->shipping_method] ?? str_replace('_', ' ', $order->shipping_method) }}</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Tracking events (sub-steps) --}}
                        @if(!empty($trackingEvents))
                            @foreach($trackingEvents as $event)
                                <div class="ot-step ot-event done">
                                    <div class="ot-icon"><i class="ri-map-pin-2-line"></i></div>
                                    <div class="ot-line"></div>
                                    <div class="ot-content">
                                        <span class="ot-label small">{{ $event['status'] ?? ($isAr ? 'تحديث' : 'Update') }}</span>
                                        @if(!empty($event['description']))
                                            <span class="ot-sub">{{ $event['description'] }}</span>
                                        @endif
                                        @if(isset($event['timestamp']))
                                            <span class="ot-date" style="font-size:.7rem;">
                                                {{ \Carbon\Carbon::createFromTimestampMs($event['timestamp'])->format('d M Y, h:i A') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- Step 4: Delivered --}}
                        <div class="ot-step {{ $steps['delivered'] ? 'done' : 'pending' }} last">
                            <div class="ot-icon"><i class="ri-checkbox-circle-line"></i></div>
                            <div class="ot-content">
                                <span class="ot-label">{{ __('messages.delivered') }}</span>
                                @if($steps['delivered'])
                                    <span class="ot-date">{{ $order->delivered_at->format('d M Y, h:i A') }}</span>
                                @endif
                            </div>
                        </div>

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
/* ── Order Timeline ── */
.order-timeline { padding: 4px 0; }

.ot-step {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    position: relative;
}

/* vertical connector line inside each step */
.ot-line {
    position: absolute;
    {{ $isAr ? 'right' : 'left' }}: 18px;
    top: 36px;
    bottom: -4px;
    width: 2px;
    background: #dee2e6;
    z-index: 0;
}
.ot-step.last .ot-line { display: none; }

/* icon circle */
.ot-icon {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    position: relative; z-index: 1;
    border: 2px solid #dee2e6;
    background: #fff;
    color: #adb5bd;
    transition: all .25s;
}

/* done state */
.ot-step.done .ot-icon {
    background: #561C04;
    border-color: #561C04;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(86,28,4,.12);
}
.ot-step.done .ot-line { background: #561C04; }

/* sub-events (smaller) */
.ot-step.ot-event .ot-icon {
    width: 26px; height: 26px;
    font-size: 12px;
    background: #e56300;
    border-color: #e56300;
    margin-{{ $isAr ? 'right' : 'left' }}: 5px;
}
.ot-step.ot-event { margin-{{ $isAr ? 'right' : 'left' }}: 10px; }
.ot-step.ot-event .ot-line {
    {{ $isAr ? 'right' : 'left' }}: 18px;
    background: #e56300;
}

/* content area */
.ot-content {
    display: flex; flex-direction: column;
    padding-bottom: 22px;
    padding-top: 4px;
}
.ot-step.last .ot-content { padding-bottom: 0; }

.ot-label {
    font-weight: 600;
    font-size: .9rem;
    color: #3d3d3d;
}
.ot-step.pending .ot-label { color: #adb5bd; }

.ot-date {
    font-size: .78rem;
    color: #6c757d;
    margin-top: 2px;
}
.ot-sub {
    font-size: .78rem;
    color: #6c757d;
    margin-top: 2px;
}

/* old timeline kept for compatibility */
.timeline {
    position: relative;
    padding-{{ $isAr ? 'right' : 'left' }}: 30px;
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

@push('scripts')
<script>
async function updateOrderStatus(orderId, newStatus) {
    const isAr = '{{ app()->getLocale() }}' === 'ar';
    const ok = await confirmAction(
        isAr ? 'هل أنت متأكد من تغيير حالة الطلب؟' : 'Are you sure you want to change the order status?',
        { icon: 'question' }
    );
    if (!ok) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/distributor/orders/${orderId}/update-status`;

    const csrf = document.createElement('input');
    csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    const status = document.createElement('input');
    status.type = 'hidden'; status.name = 'status'; status.value = newStatus;
    form.appendChild(status);

    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection
