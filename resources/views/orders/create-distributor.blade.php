@extends('dashboard')

@section('content')
@php
    // Convert product price to selected currency
    $priceConverted = $currentCurrency->convertFrom($product->price, $product->currency ?? 'AED');
    // Wallet balance is always in AED
    $walletBalanceAED = auth()->user()->wallet ? auth()->user()->wallet->balance : 0;
    $walletBalanceConverted = $currentCurrency->convertFrom($walletBalanceAED, 'AED');
@endphp
<div class="container-fluid" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            {{-- Page Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1">
                        <i class="ri-shopping-bag-line me-2 text-primary"></i>
                        {{ app()->getLocale() == 'ar' ? 'إنشاء طلب جديد' : 'Create New Order' }}
                    </h3>
                    <p class="text-muted mb-0">
                        {{ app()->getLocale() == 'ar' ? 'طلب من متجر محلي' : 'Order from Local Distributor' }}
                    </p>
                </div>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i>
                    {{ app()->getLocale() == 'ar' ? 'رجوع' : 'Back' }}
                </a>
            </div>

            {{-- Alert Messages --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>
                    <strong>{{ app()->getLocale() == 'ar' ? 'خطأ!' : 'Error!' }}</strong>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    <strong>{{ app()->getLocale() == 'ar' ? 'نجح!' : 'Success!' }}</strong>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-alert-line me-2"></i>
                    <strong>{{ app()->getLocale() == 'ar' ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('orders.distributor.store') }}" method="POST" id="orderForm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="row g-4">
                    {{-- Left Column: Product & Order Info --}}
                    <div class="col-lg-8">
                        {{-- Product Card --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="ri-product-hunt-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'تفاصيل المنتج' : 'Product Details' }}
                                </h6>
                                <div class="row align-items-center">
                                    @if($product->photo)
                                        <div class="col-auto">
                                            <img src="{{ asset('storage/' . $product->photo) }}"
                                                 alt="{{ $product->name }}"
                                                 class="rounded border"
                                                 style="width: 80px; height: 80px; object-fit: cover;">
                                        </div>
                                    @elseif($product->images && count($product->images) > 0)
                                        <div class="col-auto">
                                            <img src="{{ $product->images[0] }}"
                                                 alt="{{ $product->name }}"
                                                 class="rounded border"
                                                 style="width: 80px; height: 80px; object-fit: cover;">
                                        </div>
                                    @endif
                                    <div class="col">
                                        <h5 class="mb-1">{{ $product->name }}</h5>
                                        @if($product->name_ar && app()->getLocale() == 'ar')
                                            <p class="text-muted small mb-2">{{ $product->name_ar }}</p>
                                        @endif
                                        <div class="d-flex align-items-center gap-3">
                                            <h4 class="text-primary mb-0">{!! $currentCurrency->format($priceConverted) !!}</h4>
                                            <span class="badge bg-success">
                                                <i class="ri-store-line me-1"></i>
                                                {{ app()->getLocale() == 'ar' ? 'محلي' : 'Local' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Quantity --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="ri-shopping-cart-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'الكمية' : 'Quantity' }}
                                </h6>
                                <input type="number"
                                       class="form-control form-control-lg @error('quantity') is-invalid @enderror"
                                       id="quantity"
                                       name="quantity"
                                       value="{{ old('quantity', $queryParams['quantity'] ?? 1) }}"
                                       min="1"
                                       required
                                       onchange="calculateTotal()"
                                       placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل الكمية' : 'Enter quantity' }}">
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Coupon Code --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="ri-coupon-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'كود الخصم' : 'Coupon Code' }}
                                </h6>
                                <div class="input-group">
                                    <input type="text"
                                           class="form-control @error('coupon_code') is-invalid @enderror"
                                           id="coupon_code"
                                           name="coupon_code"
                                           value="{{ old('coupon_code') }}"
                                           placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل كود الخصم' : 'Enter coupon code' }}">
                                    <button type="button" class="btn btn-outline-primary" id="applyCouponBtn" onclick="applyCoupon()">
                                        <i class="ri-check-line me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'تطبيق' : 'Apply' }}
                                    </button>
                                </div>
                                <input type="hidden" id="coupon_id" name="coupon_id" value="">
                                <input type="hidden" id="discount_amount" name="discount_amount" value="0">
                                @error('coupon_code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div id="coupon-message" class="mt-2"></div>
                            </div>
                        </div>

                        {{-- Customer Information --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="ri-user-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'معلومات العميل' : 'Customer Information' }}
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="customer_name" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'الاسم الكامل' : 'Full Name' }} *
                                        </label>
                                        <input type="text"
                                               class="form-control @error('customer_name') is-invalid @enderror"
                                               id="customer_name"
                                               name="customer_name"
                                               value="{{ old('customer_name') }}"
                                               required>
                                        @error('customer_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="customer_email" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'البريد الإلكتروني' : 'Email' }}
                                        </label>
                                        <input type="email"
                                               class="form-control @error('customer_email') is-invalid @enderror"
                                               id="customer_email"
                                               name="customer_email"
                                               value="{{ old('customer_email') }}">
                                        @error('customer_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="phone_country" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'رمز الدولة' : 'Code' }} *
                                        </label>
                                        <select class="form-select @error('phone_country') is-invalid @enderror"
                                                id="phone_country"
                                                name="phone_country"
                                                required>
                                            <option value="+971" {{ old('phone_country') == '+971' ? 'selected' : '' }}>🇦🇪 +971</option>
                                            <option value="+966" {{ old('phone_country') == '+966' ? 'selected' : '' }}>🇸🇦 +966</option>
                                            <option value="+20" {{ old('phone_country') == '+20' ? 'selected' : '' }}>🇪🇬 +20</option>
                                            <option value="+1" {{ old('phone_country') == '+1' ? 'selected' : '' }}>🇺🇸 +1</option>
                                        </select>
                                        @error('phone_country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-8">
                                        <label for="customer_phone" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'رقم الهاتف' : 'Phone Number' }} *
                                        </label>
                                        <input type="tel"
                                               class="form-control @error('customer_phone') is-invalid @enderror"
                                               id="customer_phone"
                                               name="customer_phone"
                                               value="{{ old('customer_phone') }}"
                                               required>
                                        @error('customer_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Shipping Information --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="ri-map-pin-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'عنوان التوصيل' : 'Shipping Address' }}
                                </h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="shipping_address" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'العنوان' : 'Address' }} *
                                        </label>
                                        <input type="text"
                                               class="form-control @error('shipping_address') is-invalid @enderror"
                                               id="shipping_address"
                                               name="shipping_address"
                                               value="{{ old('shipping_address') }}"
                                               required>
                                        @error('shipping_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="shipping_address2" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'العنوان 2 (اختياري)' : 'Address 2 (Optional)' }}
                                        </label>
                                        <input type="text"
                                               class="form-control @error('shipping_address2') is-invalid @enderror"
                                               id="shipping_address2"
                                               name="shipping_address2"
                                               value="{{ old('shipping_address2') }}">
                                        @error('shipping_address2')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shipping_country" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }} *
                                        </label>
                                        <select class="form-select @error('shipping_country') is-invalid @enderror"
                                                id="shipping_country"
                                                name="shipping_country"
                                                required>
                                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر الدولة' : 'Select Country' }}</option>
                                            @foreach($shippingCountries as $sc)
                                                <option value="{{ $sc->code }}" {{ old('shipping_country') == $sc->code ? 'selected' : '' }}>
                                                    {{ $sc->flag }} {{ app()->getLocale() == 'ar' ? ($sc->name_ar ?: $sc->name) : $sc->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('shipping_country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shipping_city_select" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'المدينة' : 'City' }} *
                                        </label>
                                        <select class="form-select @error('shipping_city') is-invalid @enderror"
                                                id="shipping_city_select" disabled>
                                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر الدولة أولاً' : 'Select country first' }}</option>
                                        </select>
                                        {{-- hidden fields submitted with the form --}}
                                        <input type="hidden" name="shipping_city" id="shipping_city" value="{{ old('shipping_city') }}">
                                        <input type="hidden" name="shipping_city_id" id="shipping_city_id" value="{{ old('shipping_city_id') }}">
                                        @error('shipping_city')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shipping_district_select" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'الحي' : 'District' }}
                                        </label>
                                        <select class="form-select" id="shipping_district_select" disabled>
                                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر المدينة أولاً' : 'Select city first' }}</option>
                                        </select>
                                        <input type="hidden" name="shipping_district_id" id="shipping_district_id" value="{{ old('shipping_district_id') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shipping_province" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'المنطقة' : 'Province' }}
                                        </label>
                                        <input type="text"
                                               class="form-control @error('shipping_province') is-invalid @enderror"
                                               id="shipping_province"
                                               name="shipping_province"
                                               value="{{ old('shipping_province') }}">
                                        @error('shipping_province')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shipping_zip" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'الرمز البريدي' : 'Postal Code' }} *
                                        </label>
                                        <input type="text"
                                               class="form-control @error('shipping_zip') is-invalid @enderror"
                                               id="shipping_zip"
                                               name="shipping_zip"
                                               value="{{ old('shipping_zip') }}"
                                               required>
                                        @error('shipping_zip')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="customer_notes" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'ملاحظات إضافية' : 'Additional Notes' }}
                                        </label>
                                        <textarea class="form-control @error('customer_notes') is-invalid @enderror"
                                                  id="customer_notes"
                                                  name="customer_notes"
                                                  rows="3"
                                                  placeholder="{{ app()->getLocale() == 'ar' ? 'أي ملاحظات خاصة...' : 'Any special notes...' }}">{{ old('customer_notes', $queryParams['customer_notes'] ?? '') }}</textarea>
                                        @error('customer_notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Order Summary --}}
                    <div class="col-lg-4">
                        <div class="sticky-top" style="top: 20px;">
                            {{-- Order Summary --}}
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0" style="color :#fff;">
                                        <i class="ri-file-list-line me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'ملخص الطلب' : 'Order Summary' }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'سعر الوحدة:' : 'Unit Price:' }}</span>
                                        <strong id="unit_price_display">{!! $currentCurrency->format($priceConverted) !!}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'الكمية:' : 'Quantity:' }}</span>
                                        <strong id="quantity_display">1</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'المجموع الفرعي:' : 'Subtotal:' }}</span>
                                        <strong id="subtotal_display">{!! $currentCurrency->format($priceConverted) !!}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2" id="discount_row" style="display: none !important;">
                                        <span class="text-success">{{ app()->getLocale() == 'ar' ? 'الخصم:' : 'Discount:' }}</span>
                                        <strong class="text-success" id="discount_display">- {{ $currentCurrency->symbol }} 0.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'الشحن:' : 'Shipping:' }}</span>
                                        <strong id="shipping_display">
                                            <span class="text-muted small">{{ app()->getLocale() == 'ar' ? 'اختر العنوان' : 'Select address' }}</span>
                                        </strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الإجمالي:' : 'Total:' }}</h5>
                                        <h4 class="mb-0 text-primary" id="grand_total">{!! $currentCurrency->format($priceConverted) !!}</h4>
                                    </div>
                                </div>
                            </div>

                            {{-- Wallet Balance --}}
                            @if(auth()->user()->wallet)
                                <div class="card shadow-sm mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block">{{ app()->getLocale() == 'ar' ? 'رصيدك الحالي' : 'Your Balance' }}</small>
                                                <h5 class="mb-0 text-success">
                                                    <i class="ri-wallet-3-line me-1"></i>
                                                    {!! $currentCurrency->format($walletBalanceConverted) !!}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="ri-error-warning-line me-2"></i>
                                    <small>{{ app()->getLocale() == 'ar' ? 'ليس لديك محفظة' : 'No wallet available' }}</small>
                                </div>
                            @endif

                            {{-- Warning for insufficient balance (will be added dynamically) --}}
                            <div id="insufficient-balance-warning-container"></div>

                            {{-- Submit Button --}}
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-2" id="submitBtn">
                                <i class="ri-check-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'إنشاء الطلب' : 'Create Order' }}
                            </button>

                            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary w-100">
                                {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let appliedCoupon = null;
let discountAmount = 0;
let shippingCost = 0;        // resolved shipping cost (in product currency / AED)
let shippingResolved = false; // whether a valid rate was found for the address

// Currency data
const currentCurrency = {
    code: '{{ $currentCurrency->code }}',
    symbol: '{{ $currentCurrency->symbol }}',
    exchangeRate: {{ $currentCurrency->exchange_rate }}
};

// Currency icon SVGs
const currencyIcons = {
    'AED': '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" class="inline-block" style="vertical-align: middle;"><path d="M8 7V17H12C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7H8Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 11H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 13H12.5H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
    'SAR': '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" class="inline-block" style="vertical-align: middle;"><text x="4" y="17" font-size="14" fill="currentColor">﷼</text></svg>',
    'USD': '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" class="inline-block" style="vertical-align: middle;"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
};

// Format amount with currency icon
function formatCurrency(amount) {
    const formattedAmount = parseFloat(amount).toFixed(2);
    const icon = currencyIcons[currentCurrency.code] || currentCurrency.symbol;
    const isArabic = '{{ app()->getLocale() }}' === 'ar';

    if (isArabic) {
        return formattedAmount + ' ' + icon;
    }
    return icon + ' ' + formattedAmount;
}

function calculateTotal() {
    // Price already converted to current currency
    const unitPrice = {{ $priceConverted }};
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    // Wallet balance converted to current currency
    const walletBalance = {{ $walletBalanceConverted }};
    const locale = '{{ app()->getLocale() }}';

    const subtotal = unitPrice * quantity;
    let afterDiscount = subtotal - discountAmount;
    if (afterDiscount < 0) afterDiscount = 0;
    let grandTotal = afterDiscount + shippingCost;

    // Update display
    document.getElementById('quantity_display').textContent = quantity;
    document.getElementById('subtotal_display').innerHTML = formatCurrency(subtotal);
    document.getElementById('grand_total').innerHTML = formatCurrency(grandTotal);

    // Show/hide discount row
    const discountRow = document.getElementById('discount_row');
    if (discountAmount > 0) {
        discountRow.style.display = 'flex';
        discountRow.style.setProperty('display', 'flex', 'important');
        document.getElementById('discount_display').innerHTML = '- ' + formatCurrency(discountAmount);
    } else {
        discountRow.style.display = 'none';
        discountRow.style.setProperty('display', 'none', 'important');
    }

    // Check if wallet balance is sufficient
    const submitBtn = document.getElementById('submitBtn');
    const warningContainer = document.getElementById('insufficient-balance-warning-container');

    if (walletBalance < grandTotal) {
        // Show warning
        const warningTitle = locale === 'ar' ? 'رصيد غير كافٍ!' : 'Insufficient Balance!';
        const warningMessage = locale === 'ar'
            ? 'رصيدك الحالي غير كافٍ لإنشاء هذا الطلب.'
            : 'Your current balance is insufficient for this order.';

        warningContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="ri-error-warning-line me-2"></i>
                <strong>${warningTitle}</strong><br>
                <small>${warningMessage}</small>
            </div>
        `;

        submitBtn.disabled = true;
        submitBtn.classList.remove('btn-primary');
        submitBtn.classList.add('btn-secondary');
    } else {
        // Remove warning
        warningContainer.innerHTML = '';
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-secondary');
        submitBtn.classList.add('btn-primary');
    }
}

function applyCoupon() {
    const couponCode = document.getElementById('coupon_code').value.trim();
    const couponMessage = document.getElementById('coupon-message');
    const applyCouponBtn = document.getElementById('applyCouponBtn');
    const locale = '{{ app()->getLocale() }}';
    const productId = {{ $product->id }};

    if (!couponCode) {
        couponMessage.innerHTML = `<div class="alert alert-warning py-2 mb-0">
            <small>${locale === 'ar' ? 'يرجى إدخال كود الخصم' : 'Please enter a coupon code'}</small>
        </div>`;
        return;
    }

    // Disable button while processing
    applyCouponBtn.disabled = true;
    applyCouponBtn.innerHTML = '<i class="ri-loader-4-line me-1"></i>' + (locale === 'ar' ? 'جاري التحقق...' : 'Checking...');

    // Calculate subtotal (using converted price)
    const unitPrice = {{ $priceConverted }};
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const subtotal = unitPrice * quantity;

    // Send AJAX request to validate coupon
    fetch('{{ route("orders.distributor.validate-coupon") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            coupon_code: couponCode,
            product_id: productId,
            subtotal: subtotal
        })
    })
    .then(response => response.json())
    .then(data => {
        applyCouponBtn.disabled = false;
        applyCouponBtn.innerHTML = '<i class="ri-check-line me-1"></i>' + (locale === 'ar' ? 'تطبيق' : 'Apply');

        if (data.success) {
            appliedCoupon = data.coupon;
            discountAmount = parseFloat(data.discount_amount);

            // Update hidden fields
            document.getElementById('coupon_id').value = data.coupon.id;
            document.getElementById('discount_amount').value = discountAmount;

            // Show success message
            const discountText = data.coupon.discount_type === 'percentage'
                ? data.coupon.discount_value + '%'
                : formatCurrency(parseFloat(data.coupon.discount_value));

            couponMessage.innerHTML = `<div class="alert alert-success py-2 mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="ri-check-circle-line me-1"></i>
                        <small>${locale === 'ar' ? 'تم تطبيق الخصم: ' : 'Discount applied: '}${discountText}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeCoupon()">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>`;

            // Disable coupon input
            document.getElementById('coupon_code').disabled = true;
            applyCouponBtn.style.display = 'none';

            // Recalculate total
            calculateTotal();
        } else {
            couponMessage.innerHTML = `<div class="alert alert-danger py-2 mb-0">
                <small><i class="ri-error-warning-line me-1"></i>${data.message}</small>
            </div>`;
        }
    })
    .catch(error => {
        applyCouponBtn.disabled = false;
        applyCouponBtn.innerHTML = '<i class="ri-check-line me-1"></i>' + (locale === 'ar' ? 'تطبيق' : 'Apply');
        couponMessage.innerHTML = `<div class="alert alert-danger py-2 mb-0">
            <small>${locale === 'ar' ? 'حدث خطأ أثناء التحقق من الكود' : 'An error occurred while validating the coupon'}</small>
        </div>`;
    });
}

function removeCoupon() {
    appliedCoupon = null;
    discountAmount = 0;

    // Clear hidden fields
    document.getElementById('coupon_id').value = '';
    document.getElementById('discount_amount').value = '0';

    // Clear coupon input and message
    document.getElementById('coupon_code').value = '';
    document.getElementById('coupon_code').disabled = false;
    document.getElementById('coupon-message').innerHTML = '';
    document.getElementById('applyCouponBtn').style.display = 'block';

    // Recalculate total
    calculateTotal();
}

// ───────── Shipping location selectors + cost calculation ─────────
const productId = {{ $product->id }};
const isAr = '{{ app()->getLocale() }}' === 'ar';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

const countrySel = document.getElementById('shipping_country');
const citySel = document.getElementById('shipping_city_select');
const districtSel = document.getElementById('shipping_district_select');
const cityHidden = document.getElementById('shipping_city');
const cityIdHidden = document.getElementById('shipping_city_id');
const districtIdHidden = document.getElementById('shipping_district_id');
const shippingDisplay = document.getElementById('shipping_display');

function setShippingMsg(text, muted = true) {
    shippingDisplay.innerHTML = `<span class="${muted ? 'text-muted' : 'text-danger'} small">${text}</span>`;
}

function resetCities() {
    citySel.innerHTML = `<option value="">${isAr ? 'اختر المدينة' : 'Select city'}</option>`;
    citySel.disabled = true;
    cityHidden.value = '';
    cityIdHidden.value = '';
    resetDistricts();
}
function resetDistricts() {
    districtSel.innerHTML = `<option value="">${isAr ? 'كل المدينة' : 'All districts'}</option>`;
    districtSel.disabled = true;
    districtIdHidden.value = '';
}

if (countrySel) {
    countrySel.addEventListener('change', function () {
        resetCities();
        shippingCost = 0; shippingResolved = false;
        setShippingMsg(isAr ? 'اختر المدينة' : 'Select city');
        calculateTotal();
        if (!this.value) return;
        fetch(`{{ url('orders/shipping/cities') }}/${this.value}`)
            .then(r => r.json())
            .then(d => {
                d.cities.forEach(c => {
                    const o = document.createElement('option');
                    o.value = c.id;
                    o.dataset.name = isAr ? (c.name_ar || c.name) : c.name;
                    o.textContent = o.dataset.name;
                    citySel.appendChild(o);
                });
                citySel.disabled = false;
            });
    });

    citySel.addEventListener('change', function () {
        resetDistricts();
        const opt = this.options[this.selectedIndex];
        cityIdHidden.value = this.value;
        cityHidden.value = opt ? (opt.dataset.name || opt.textContent) : '';
        if (!this.value) { shippingCost = 0; shippingResolved = false; setShippingMsg(isAr ? 'اختر المدينة' : 'Select city'); calculateTotal(); return; }
        // load districts
        fetch(`{{ url('orders/shipping/districts') }}/${this.value}`)
            .then(r => r.json())
            .then(d => {
                d.districts.forEach(dist => {
                    const o = document.createElement('option');
                    o.value = dist.id;
                    o.textContent = isAr ? (dist.name_ar || dist.name) : dist.name;
                    districtSel.appendChild(o);
                });
                districtSel.disabled = false;
            });
        fetchShipping();
    });

    districtSel.addEventListener('change', function () {
        districtIdHidden.value = this.value;
        fetchShipping();
    });
}

function fetchShipping() {
    if (!countrySel.value) return;
    setShippingMsg(isAr ? 'جاري الحساب...' : 'Calculating...');
    fetch(`{{ route('orders.shipping.calculate') }}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            product_id: productId,
            country_code: countrySel.value,
            city_id: cityIdHidden.value || null,
            district_id: districtIdHidden.value || null
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            shippingCost = parseFloat(d.shipping_cost) || 0;
            shippingResolved = true;
            let html = formatCurrency(shippingCost);
            if (d.delivery_days_min || d.delivery_days_max) {
                html += ` <small class="text-muted">(${d.delivery_days_min}–${d.delivery_days_max} ${isAr ? 'يوم' : 'days'})</small>`;
            }
            shippingDisplay.innerHTML = html;
        } else {
            shippingCost = 0;
            shippingResolved = false;
            setShippingMsg(d.message || (isAr ? 'لا يوجد سعر شحن' : 'No shipping rate'), false);
        }
        calculateTotal();
    })
    .catch(() => { setShippingMsg(isAr ? 'تعذّر حساب الشحن' : 'Could not calculate shipping', false); });
}

// Calculate on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>
@endpush

<style>
.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.card-header.bg-primary {
    background: linear-gradient(135deg, #561C04 0%, #7A3206 100%) !important;
    border: none;
}

.card-header.bg-primary h6,
.card-header.bg-primary .mb-0,
.card-header.text-white h6 {
    color: #fff !important;
}

.form-control, .form-select {
    border-radius: 8px;
}

.btn {
    border-radius: 8px;
}

.btn-primary {
    background-color: #561C04 !important;
    border-color: #561C04 !important;
}

.btn-primary:hover {
    background-color: #7A3206 !important;
    border-color: #7A3206 !important;
}

.btn-outline-primary {
    color: #561C04 !important;
    border-color: #561C04 !important;
}

.btn-outline-primary:hover {
    background-color: #561C04 !important;
    border-color: #561C04 !important;
    color: #fff !important;
}

.btn-info {
    background-color: #561C04 !important;
    border-color: #561C04 !important;
}

.btn-info:hover {
    background-color: #7A3206 !important;
    border-color: #7A3206 !important;
}

.text-primary {
    color: #561C04 !important;
}

.text-info {
    color: #561C04 !important;
}

.bg-primary {
    background-color: #561C04 !important;
}

.sticky-top {
    z-index: 1020;
}

.form-control:focus, .form-select:focus {
    border-color: #561C04;
    box-shadow: 0 0 0 0.2rem rgba(86, 28, 4, 0.25);
}
</style>
@endsection
