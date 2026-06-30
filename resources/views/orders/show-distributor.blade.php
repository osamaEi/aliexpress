@extends('dashboard')

@section('content')
@php
    $isAr = app()->getLocale() == 'ar';
    $cur  = $order->currency ?? 'AED';

    // The distributor only ever sees THEIR OWN share: their product base price × qty
    // plus the shipping they fulfil. The seller/admin markup never appears here.
    $unitPrice  = $currentCurrency->convertFrom($order->product->price ?? $order->unit_price, $cur);
    $freight    = $currentCurrency->convertFrom($order->freight_amount ?? 0, $cur);
    $subtotal   = $unitPrice * $order->quantity;
    $grandTotal = $subtotal + $freight;
    $productName = $isAr && $order->product->name_ar ? $order->product->name_ar : $order->product->name;

    $statusOptions = [
        'pending'    => ['warning', $isAr ? 'قيد الانتظار' : 'Pending'],
        'processing' => ['info',    $isAr ? 'قيد المعالجة' : 'Processing'],
        'shipped'    => ['primary', $isAr ? 'تم الشحن' : 'Shipped'],
        'delivered'  => ['success', $isAr ? 'تم التوصيل' : 'Delivered'],
        'cancelled'  => ['danger',  $isAr ? 'إلغاء' : 'Cancel'],
    ];
@endphp
<div class="col-12" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

    {{-- Order Header --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-label-success"><i class="ri-store-2-line me-1"></i>{{ $isAr ? 'طلب موزع' : 'Distributor Order' }}</span>
                        <h4 class="mb-0">{{ __('messages.order') }} {{ $order->order_number }}</h4>
                    </div>
                    <span class="badge bg-{{ $order->getStatusBadgeColor() }} fs-6">{{ $order->getStatusName() }}</span>
                </div>

                {{-- Status control (distributor fulfils the order) --}}
                <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-refresh-line me-1"></i>{{ $isAr ? 'تغيير الحالة' : 'Change Status' }}
                    </button>
                    <ul class="dropdown-menu">
                        @foreach($statusOptions as $value => [$color, $label])
                            @if($order->status !== $value)
                                @if($value === 'cancelled')<li><hr class="dropdown-divider"></li>@endif
                                <li>
                                    <a class="dropdown-item {{ $value === 'cancelled' ? 'text-danger' : '' }}" href="#"
                                       onclick="updateOrderStatus({{ $order->id }}, '{{ $value }}'); return false;">
                                        <span class="badge bg-{{ $color }} me-2">●</span>{{ $label }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">

            {{-- Product & your earning --}}
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
                            <h6>{{ $productName }}</h6>

                            <table class="table table-sm table-borderless mb-0 mt-2" style="width: auto; min-width: 280px;">
                                <tr>
                                    <td class="text-muted py-1">{{ $isAr ? 'سعر منتجك' : 'Your Product Price' }}</td>
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
                                    <td class="py-1 fw-bold">{{ $isAr ? 'إجمالي أرباحك' : 'Your Total Earning' }}</td>
                                    <td class="py-1 fw-bold fs-5" style="direction:ltr; color:#561C04;">
                                        <x-session-currency-icon width="16" height="16" />
                                        {{ number_format($grandTotal, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Customer Information --}}
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

            {{-- Shipping Information --}}
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
                </div>
            </div>
        </div>

        {{-- Order Timeline --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('messages.order_timeline') }}</h6>
                </div>
                @php
                    $steps = [
                        'created'   => true,
                        'processing'=> !empty($order->placed_at),
                        'shipped'   => !empty($order->shipped_at),
                        'delivered' => !empty($order->delivered_at),
                    ];
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

                        {{-- Step 2: Processing --}}
                        <div class="ot-step {{ $steps['processing'] ? 'done' : 'pending' }}">
                            <div class="ot-icon"><i class="ri-settings-3-line"></i></div>
                            <div class="ot-line"></div>
                            <div class="ot-content">
                                <span class="ot-label">{{ $isAr ? 'قيد المعالجة' : 'Processing' }}</span>
                                @if($steps['processing'])
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
                                @endif
                            </div>
                        </div>

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
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-timeline { padding: 4px 0; }
.ot-step { display: flex; align-items: flex-start; gap: 14px; position: relative; }
.ot-line {
    position: absolute;
    {{ $isAr ? 'right' : 'left' }}: 18px;
    top: 36px; bottom: -4px; width: 2px; background: #dee2e6; z-index: 0;
}
.ot-step.last .ot-line { display: none; }
.ot-icon {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0; position: relative; z-index: 1;
    border: 2px solid #dee2e6; background: #fff; color: #adb5bd; transition: all .25s;
}
.ot-step.done .ot-icon {
    background: #561C04; border-color: #561C04; color: #fff;
    box-shadow: 0 0 0 4px rgba(86,28,4,.12);
}
.ot-step.done .ot-line { background: #561C04; }
.ot-content { display: flex; flex-direction: column; padding-bottom: 22px; padding-top: 4px; }
.ot-step.last .ot-content { padding-bottom: 0; }
.ot-label { font-weight: 600; font-size: .9rem; color: #3d3d3d; }
.ot-step.pending .ot-label { color: #adb5bd; }
.ot-date { font-size: .78rem; color: #6c757d; margin-top: 2px; }
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
