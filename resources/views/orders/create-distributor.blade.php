@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
@php
    // Convert product price to selected currency
    $priceConverted = $currentCurrency->convertFrom($product->price, $product->currency ?? 'AED');
    // Wallet balance is always in AED
    $walletBalanceAED = auth()->user()->wallet ? auth()->user()->wallet->balance : 0;
    $walletBalanceConverted = $currentCurrency->convertFrom($walletBalanceAED, 'AED');
@endphp
<div class="container-fluid order-create-page" dir="{{ $ar ? 'rtl' : 'ltr' }}">
    <div class="row justify-content-center">
        <div class="col-lg-11 col-xl-10">
            {{-- Page Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="header-badge"><i class="ri-shopping-bag-3-line"></i></span>
                    <div>
                        <h4 class="mb-0 fw-bold">{{ $ar ? 'إنشاء طلب جديد' : 'Create New Order' }}</h4>
                        <span class="text-muted small">{{ $ar ? 'طلب من متجر محلي' : 'Order from a local distributor' }}</span>
                    </div>
                </div>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-arrow-{{ $ar ? 'right' : 'left' }}-line me-1"></i>
                    {{ $ar ? 'رجوع' : 'Back' }}
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

            {{-- Stepper progress bar --}}
            <div class="wizard-progress mb-4">
                @php
                    $steps = $ar
                        ? ['المنتج', 'بيانات العميل', 'الشحن', 'المراجعة']
                        : ['Product', 'Customer', 'Shipping', 'Review'];
                    $stepIcons = ['ri-shopping-bag-3-line', 'ri-user-3-line', 'ri-map-pin-2-line', 'ri-checkbox-circle-line'];
                @endphp
                @foreach($steps as $i => $label)
                    <div class="wizard-step {{ $i === 0 ? 'active' : '' }}" data-step-indicator="{{ $i + 1 }}">
                        <div class="wizard-dot"><i class="{{ $stepIcons[$i] }}"></i></div>
                        <span class="wizard-label">{{ $label }}</span>
                    </div>
                    @if(!$loop->last)<div class="wizard-line"></div>@endif
                @endforeach
            </div>

            <form action="{{ route('orders.distributor.store') }}" method="POST" id="orderForm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                {{-- Persistent product banner across steps --}}
                @php
                    $heroImg = $product->photo
                        ? asset('storage/' . $product->photo)
                        : (($product->images && count($product->images) > 0) ? $product->images[0] : null);
                @endphp
                <div class="product-banner mb-4">
                    @if($heroImg)
                        <img src="{{ $heroImg }}" alt="{{ $product->name }}" class="product-banner-img">
                    @else
                        <div class="product-banner-img d-flex align-items-center justify-content-center bg-light">
                            <i class="ri-image-line text-muted fs-3"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <span class="badge bg-label-success mb-1"><i class="ri-store-2-line me-1"></i>{{ $ar ? 'منتج محلي' : 'Local product' }}</span>
                        <div class="fw-semibold">{{ $ar && $product->name_ar ? $product->name_ar : $product->name }}</div>
                    </div>
                    <div class="text-{{ $ar ? 'start' : 'end' }}">
                        <div class="text-muted small">{{ $ar ? 'سعر الوحدة' : 'Unit price' }}</div>
                        <div class="fs-5 fw-bold text-primary">{!! $currentCurrency->format($priceConverted) !!}</div>
                    </div>
                </div>

                <div class="wizard-card card shadow-sm">
                    <div class="card-body p-4">

                        {{-- ───────── STEP 1: Product & Quantity ───────── --}}
                        <div class="wizard-pane" data-step="1">
                            <h5 class="pane-title">{{ $ar ? 'الكمية وكود الخصم' : 'Quantity & Coupon' }}</h5>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label">{{ $ar ? 'الكمية' : 'Quantity' }} *</label>
                                    <div class="qty-stepper">
                                        <button type="button" class="qty-btn" id="qtyMinus"><i class="ri-subtract-line"></i></button>
                                        <input type="number"
                                               class="form-control text-center @error('quantity') is-invalid @enderror"
                                               id="quantity" name="quantity"
                                               value="{{ old('quantity', $queryParams['quantity'] ?? 1) }}"
                                               min="1" required onchange="calculateTotal()">
                                        <button type="button" class="qty-btn" id="qtyPlus"><i class="ri-add-line"></i></button>
                                    </div>
                                    @error('quantity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">{{ $ar ? 'كود الخصم' : 'Coupon Code' }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-coupon-3-line"></i></span>
                                        <input type="text"
                                               class="form-control @error('coupon_code') is-invalid @enderror"
                                               id="coupon_code" name="coupon_code"
                                               value="{{ old('coupon_code') }}"
                                               placeholder="{{ $ar ? 'أدخل كود الخصم' : 'Enter coupon code' }}">
                                        <button type="button" class="btn btn-outline-primary" id="applyCouponBtn" onclick="applyCoupon()">
                                            {{ $ar ? 'تطبيق' : 'Apply' }}
                                        </button>
                                    </div>
                                    <input type="hidden" id="coupon_id" name="coupon_id" value="">
                                    <input type="hidden" id="discount_amount" name="discount_amount" value="0">
                                    @error('coupon_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    <div id="coupon-message" class="mt-2"></div>
                                </div>
                            </div>
                        </div>

                        {{-- ───────── STEP 2: Customer Information ───────── --}}
                        <div class="wizard-pane d-none" data-step="2">
                            <h5 class="pane-title">{{ $ar ? 'معلومات العميل' : 'Customer Information' }}</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="customer_name" class="form-label">{{ $ar ? 'الاسم الكامل' : 'Full Name' }} *</label>
                                    <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                           id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
                                    @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_email" class="form-label">{{ $ar ? 'البريد الإلكتروني' : 'Email' }}</label>
                                    <input type="email" class="form-control @error('customer_email') is-invalid @enderror"
                                           id="customer_email" name="customer_email" value="{{ old('customer_email') }}">
                                    @error('customer_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_phone" class="form-label">{{ $ar ? 'رقم الهاتف' : 'Phone Number' }} *</label>
                                    <div class="phone-group">
                                        <select class="form-select phone-code @error('phone_country') is-invalid @enderror" id="phone_country" name="phone_country" required dir="ltr">
                                            <option value="+971" {{ old('phone_country') == '+971' ? 'selected' : '' }}>🇦🇪 +971</option>
                                            <option value="+966" {{ old('phone_country') == '+966' ? 'selected' : '' }}>🇸🇦 +966</option>
                                            <option value="+20" {{ old('phone_country') == '+20' ? 'selected' : '' }}>🇪🇬 +20</option>
                                            <option value="+1" {{ old('phone_country') == '+1' ? 'selected' : '' }}>🇺🇸 +1</option>
                                        </select>
                                        <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror"
                                               id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required dir="ltr"
                                               style="text-align: {{ $ar ? 'right' : 'left' }};"
                                               placeholder="{{ $ar ? 'رقم الهاتف' : 'Phone number' }}">
                                    </div>
                                    @error('phone_country')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    @error('customer_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- ───────── STEP 3: Shipping ───────── --}}
                        <div class="wizard-pane d-none" data-step="3">
                            <h5 class="pane-title">{{ $ar ? 'عنوان التوصيل' : 'Shipping Address' }}</h5>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="shipping_address" class="form-label">{{ $ar ? 'العنوان' : 'Address' }} *</label>
                                    <input type="text" class="form-control @error('shipping_address') is-invalid @enderror"
                                           id="shipping_address" name="shipping_address" value="{{ old('shipping_address') }}" required>
                                    @error('shipping_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="shipping_address2" class="form-label">{{ $ar ? 'العنوان 2 (اختياري)' : 'Address 2 (Optional)' }}</label>
                                    <input type="text" class="form-control @error('shipping_address2') is-invalid @enderror"
                                           id="shipping_address2" name="shipping_address2" value="{{ old('shipping_address2') }}">
                                    @error('shipping_address2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="shipping_country" class="form-label">{{ $ar ? 'الدولة' : 'Country' }} *</label>
                                    <select class="form-select @error('shipping_country') is-invalid @enderror" id="shipping_country" name="shipping_country" required>
                                        <option value="">{{ $ar ? 'اختر الدولة' : 'Select Country' }}</option>
                                        @foreach($shippingCountries as $sc)
                                            <option value="{{ $sc->code }}" {{ old('shipping_country') == $sc->code ? 'selected' : '' }}>
                                                {{ $sc->flag }} {{ $ar ? ($sc->name_ar ?: $sc->name) : $sc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shipping_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="shipping_city_select" class="form-label">{{ $ar ? 'المدينة' : 'City' }} *</label>
                                    <select class="form-select @error('shipping_city') is-invalid @enderror" id="shipping_city_select" disabled>
                                        <option value="">{{ $ar ? 'اختر الدولة أولاً' : 'Select country first' }}</option>
                                    </select>
                                    <input type="hidden" name="shipping_city" id="shipping_city" value="{{ old('shipping_city') }}">
                                    <input type="hidden" name="shipping_city_id" id="shipping_city_id" value="{{ old('shipping_city_id') }}">
                                    @error('shipping_city')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="shipping_district_select" class="form-label">{{ $ar ? 'الحي' : 'District' }}</label>
                                    <select class="form-select" id="shipping_district_select" disabled>
                                        <option value="">{{ $ar ? 'اختر المدينة أولاً' : 'Select city first' }}</option>
                                    </select>
                                    <input type="hidden" name="shipping_district_id" id="shipping_district_id" value="{{ old('shipping_district_id') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="shipping_province" class="form-label">{{ $ar ? 'المنطقة' : 'Province' }}</label>
                                    <input type="text" class="form-control @error('shipping_province') is-invalid @enderror"
                                           id="shipping_province" name="shipping_province" value="{{ old('shipping_province') }}">
                                    @error('shipping_province')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="shipping_zip" class="form-label">{{ $ar ? 'الرمز البريدي' : 'Postal Code' }} *</label>
                                    <input type="text" class="form-control @error('shipping_zip') is-invalid @enderror"
                                           id="shipping_zip" name="shipping_zip" value="{{ old('shipping_zip') }}" required>
                                    @error('shipping_zip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="customer_notes" class="form-label">{{ $ar ? 'ملاحظات إضافية' : 'Additional Notes' }}</label>
                                    <textarea class="form-control @error('customer_notes') is-invalid @enderror"
                                              id="customer_notes" name="customer_notes" rows="2"
                                              placeholder="{{ $ar ? 'أي ملاحظات خاصة...' : 'Any special notes...' }}">{{ old('customer_notes', $queryParams['customer_notes'] ?? '') }}</textarea>
                                    @error('customer_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- ───────── STEP 4: Review ───────── --}}
                        <div class="wizard-pane d-none" data-step="4">
                            <h5 class="pane-title">{{ $ar ? 'مراجعة الطلب' : 'Review Your Order' }}</h5>

                            <div class="review-grid">
                                {{-- Customer review --}}
                                <div class="review-block">
                                    <div class="review-block-head">
                                        <span><i class="ri-user-3-line me-1"></i>{{ $ar ? 'العميل' : 'Customer' }}</span>
                                        <button type="button" class="btn-edit-step" data-goto="2"><i class="ri-edit-line"></i> {{ $ar ? 'تعديل' : 'Edit' }}</button>
                                    </div>
                                    <dl class="review-list">
                                        <dt>{{ $ar ? 'الاسم' : 'Name' }}</dt><dd id="rv_name">—</dd>
                                        <dt>{{ $ar ? 'الهاتف' : 'Phone' }}</dt><dd id="rv_phone">—</dd>
                                        <dt>{{ $ar ? 'البريد' : 'Email' }}</dt><dd id="rv_email">—</dd>
                                    </dl>
                                </div>
                                {{-- Shipping review --}}
                                <div class="review-block">
                                    <div class="review-block-head">
                                        <span><i class="ri-map-pin-2-line me-1"></i>{{ $ar ? 'الشحن' : 'Shipping' }}</span>
                                        <button type="button" class="btn-edit-step" data-goto="3"><i class="ri-edit-line"></i> {{ $ar ? 'تعديل' : 'Edit' }}</button>
                                    </div>
                                    <dl class="review-list">
                                        <dt>{{ $ar ? 'العنوان' : 'Address' }}</dt><dd id="rv_address">—</dd>
                                        <dt>{{ $ar ? 'المدينة/الحي' : 'City/District' }}</dt><dd id="rv_location">—</dd>
                                        <dt>{{ $ar ? 'الدولة' : 'Country' }}</dt><dd id="rv_country">—</dd>
                                    </dl>
                                </div>
                            </div>

                            {{-- Price breakdown --}}
                            <div class="review-summary mt-3">
                                <div class="summary-row">
                                    <span>{{ $ar ? 'سعر الوحدة' : 'Unit Price' }}</span>
                                    <strong id="unit_price_display">{!! $currentCurrency->format($priceConverted) !!}</strong>
                                </div>
                                <div class="summary-row">
                                    <span>{{ $ar ? 'الكمية' : 'Quantity' }}</span>
                                    <strong id="quantity_display">1</strong>
                                </div>
                                <div class="summary-row">
                                    <span>{{ $ar ? 'المجموع الفرعي' : 'Subtotal' }}</span>
                                    <strong id="subtotal_display">{!! $currentCurrency->format($priceConverted) !!}</strong>
                                </div>
                                <div class="summary-row text-success" id="discount_row" style="display: none !important;">
                                    <span>{{ $ar ? 'الخصم' : 'Discount' }}</span>
                                    <strong id="discount_display">- {{ $currentCurrency->symbol }} 0.00</strong>
                                </div>
                                <div class="summary-row">
                                    <span><i class="ri-truck-line me-1 text-muted"></i>{{ $ar ? 'الشحن' : 'Shipping' }}</span>
                                    <strong id="shipping_display"><span class="text-muted small">{{ $ar ? 'اختر العنوان' : 'Select address' }}</span></strong>
                                </div>
                                <div class="summary-total">
                                    <span>{{ $ar ? 'الإجمالي' : 'Total' }}</span>
                                    <span class="total-amount" id="grand_total">{!! $currentCurrency->format($priceConverted) !!}</span>
                                </div>
                            </div>

                            @if(auth()->user()->wallet)
                                <div class="wallet-inline mt-3">
                                    <span class="wallet-icon"><i class="ri-wallet-3-line"></i></span>
                                    <div>
                                        <small class="text-muted d-block">{{ $ar ? 'رصيدك الحالي' : 'Your Balance' }}</small>
                                        <strong class="text-success">{!! $currentCurrency->format($walletBalanceConverted) !!}</strong>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="ri-error-warning-line me-2"></i>
                                    <small>{{ $ar ? 'ليس لديك محفظة' : 'No wallet available' }}</small>
                                </div>
                            @endif

                            <div id="insufficient-balance-warning-container" class="mt-3"></div>
                        </div>

                        {{-- Navigation buttons --}}
                        <div class="wizard-nav mt-4 pt-3">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display:none;">
                                <i class="ri-arrow-{{ $ar ? 'left' : 'right' }}-line me-1"></i>{{ $ar ? 'السابق' : 'Previous' }}
                            </button>
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary" id="cancelBtn">{{ $ar ? 'إلغاء' : 'Cancel' }}</a>
                            <button type="button" class="btn btn-primary ms-auto" id="nextBtn">
                                {{ $ar ? 'التالي' : 'Next' }}<i class="ri-arrow-{{ $ar ? 'left' : 'right' }}-line ms-1"></i>
                            </button>
                            <button type="submit" class="btn btn-primary ms-auto" id="submitBtn" style="display:none;">
                                <i class="ri-check-double-line me-1"></i>{{ $ar ? 'تأكيد الطلب' : 'Confirm Order' }}
                            </button>
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

// ───────── Multi-step wizard ─────────
const wizard = (function () {
    let current = 1;
    const total = 4;
    const arWiz = {{ $ar ? 'true' : 'false' }};
    const panes = () => document.querySelectorAll('.wizard-pane');
    const indicators = () => document.querySelectorAll('.wizard-step');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    function show(step) {
        current = step;
        panes().forEach(p => p.classList.toggle('d-none', +p.dataset.step !== step));
        // indicator states
        indicators().forEach(ind => {
            const n = +ind.dataset.stepIndicator;
            ind.classList.toggle('active', n === step);
            ind.classList.toggle('done', n < step);
        });
        // nav buttons
        prevBtn.style.display = step > 1 ? 'inline-flex' : 'none';
        document.getElementById('cancelBtn').style.display = step > 1 ? 'none' : 'inline-flex';
        nextBtn.style.display = step < total ? 'inline-flex' : 'none';
        submitBtn.style.display = step === total ? 'inline-flex' : 'none';

        if (step === total) buildReview();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Validate required fields in the current visible pane before moving on
    function validateStep() {
        const pane = document.querySelector(`.wizard-pane[data-step="${current}"]`);
        let ok = true;
        pane.querySelectorAll('[required]').forEach(el => {
            // skip disabled selects (city/district handled via hidden inputs)
            if (el.disabled) return;
            if (!el.value || !el.value.trim()) {
                el.classList.add('is-invalid');
                ok = false;
            } else {
                el.classList.remove('is-invalid');
            }
        });
        // Step 3: city must be chosen (hidden field)
        if (current === 3) {
            const cityHidden = document.getElementById('shipping_city_id');
            const citySelect = document.getElementById('shipping_city_select');
            if (!cityHidden.value) {
                citySelect.classList.add('is-invalid');
                ok = false;
            } else {
                citySelect.classList.remove('is-invalid');
            }
        }
        if (!ok) {
            const firstBad = pane.querySelector('.is-invalid');
            if (firstBad) firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return ok;
    }

    function buildReview() {
        const val = id => (document.getElementById(id)?.value || '').trim();
        const set = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt || '—'; };
        set('rv_name', val('customer_name'));
        const code = document.getElementById('phone_country')?.value || '';
        set('rv_phone', (code + ' ' + val('customer_phone')).trim());
        set('rv_email', val('customer_email'));
        let addr = val('shipping_address');
        if (val('shipping_address2')) addr += ', ' + val('shipping_address2');
        set('rv_address', addr);
        const citySel = document.getElementById('shipping_city_select');
        const distSel = document.getElementById('shipping_district_select');
        const cityTxt = citySel.selectedIndex > 0 ? citySel.options[citySel.selectedIndex].textContent : '';
        const distTxt = (distSel && distSel.selectedIndex > 0) ? distSel.options[distSel.selectedIndex].textContent : '';
        set('rv_location', [cityTxt, distTxt].filter(Boolean).join(' / '));
        const cSel = document.getElementById('shipping_country');
        set('rv_country', cSel.selectedIndex > 0 ? cSel.options[cSel.selectedIndex].textContent : '');
    }

    nextBtn.addEventListener('click', () => { if (validateStep()) show(current + 1); });
    prevBtn.addEventListener('click', () => show(current - 1));
    // clicking an indicator jumps back (only to completed steps)
    indicators().forEach(ind => ind.addEventListener('click', () => {
        const n = +ind.dataset.stepIndicator;
        if (n < current) show(n);
    }));
    // "Edit" buttons in review
    document.querySelectorAll('.btn-edit-step').forEach(b =>
        b.addEventListener('click', () => show(+b.dataset.goto)));

    return { show, validateStep, get current() { return current; } };
})();

// Quantity +/- stepper
document.getElementById('qtyMinus')?.addEventListener('click', () => {
    const q = document.getElementById('quantity');
    q.value = Math.max(1, (parseInt(q.value) || 1) - 1);
    calculateTotal();
});
document.getElementById('qtyPlus')?.addEventListener('click', () => {
    const q = document.getElementById('quantity');
    q.value = (parseInt(q.value) || 1) + 1;
    calculateTotal();
});

// Calculate on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
    wizard.show(1);
});
</script>
@endpush

<style>
.order-create-page .card { border: none; border-radius: 14px; }
.order-create-page .form-control, .order-create-page .form-select {
    border-radius: 8px;
    padding: 0.3rem 0.6rem;
    font-size: 0.825rem;
    min-height: 34px;
    height: 34px;
}
.order-create-page .form-label { font-size: 0.775rem; margin-bottom: 0.2rem; }
.order-create-page .input-group-text { padding: 0.3rem 0.55rem; font-size: 0.825rem; }
.order-create-page textarea.form-control { min-height: auto; height: auto; }
/* Phone group: country code on the left, number on the right (works in RTL) */
.order-create-page .phone-group {
    display: flex;
    flex-direction: row;
    gap: 8px;
}
.order-create-page .phone-group .phone-code {
    flex: 0 0 96px;
    width: 96px;
    text-align: center;
    padding: 0.3rem 0.4rem;
    background-position: left 0.5rem center;
}
.order-create-page .phone-group .form-control {
    flex: 1 1 auto;
    min-width: 0;
}
.order-create-page .btn { border-radius: 8px; }
.order-create-page .btn-primary { background-color: #561C04 !important; border-color: #561C04 !important; }
.order-create-page .btn-primary:hover { background-color: #7A3206 !important; border-color: #7A3206 !important; }
.order-create-page .btn-outline-primary { color: #561C04 !important; border-color: #561C04 !important; }
.order-create-page .btn-outline-primary:hover { background-color: #561C04 !important; border-color: #561C04 !important; color: #fff !important; }
.order-create-page .text-primary { color: #561C04 !important; }
.order-create-page .form-control:focus, .order-create-page .form-select:focus {
    border-color: #561C04; box-shadow: 0 0 0 0.2rem rgba(86, 28, 4, 0.2);
}
.order-create-page .header-badge {
    width: 46px; height: 46px; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 12px; background: linear-gradient(135deg, #561C04 0%, #7A3206 100%);
    color: #fff; font-size: 22px; box-shadow: 0 4px 12px rgba(86, 28, 4, 0.25);
}

/* ── Wizard progress bar ── */
.wizard-progress { display: flex; align-items: center; justify-content: center; gap: 0; }
.wizard-step {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    cursor: default; flex: 0 0 auto; min-width: 90px; text-align: center;
}
.wizard-step .wizard-dot {
    width: 48px; height: 48px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: #f1edea; color: #b9a99e; font-size: 22px;
    border: 2px solid #e8ddd6; transition: all .25s ease;
}
.wizard-step .wizard-label { font-size: 13px; color: #9a8c83; font-weight: 500; transition: color .25s; }
.wizard-step.active .wizard-dot {
    background: linear-gradient(135deg, #561C04 0%, #7A3206 100%);
    color: #fff; border-color: #561C04; box-shadow: 0 4px 12px rgba(86,28,4,.3);
    transform: scale(1.05);
}
.wizard-step.active .wizard-label { color: #561C04; font-weight: 700; }
.wizard-step.done { cursor: pointer; }
.wizard-step.done .wizard-dot { background: #561C04; color: #fff; border-color: #561C04; }
.wizard-step.done .wizard-label { color: #561C04; }
.wizard-line { flex: 1 1 auto; height: 3px; max-width: 90px; background: #e8ddd6; margin: 0 4px; margin-bottom: 26px; border-radius: 2px; }

/* ── Product banner ── */
.product-banner {
    display: flex; align-items: center; gap: 14px;
    background: #fff; border: 1px solid #f0eae6; border-radius: 14px; padding: 14px 18px;
    border-{{ $ar ? 'right' : 'left' }}: 4px solid #561C04;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.product-banner-img { width: 64px; height: 64px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; flex-shrink: 0; }

/* ── Wizard panes ── */
.wizard-card { min-height: 280px; }
.pane-title { font-weight: 700; color: #561C04; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #f0eae6; }
.order-create-page .input-group-text { background: #faf7f5; color: #561C04; }

/* Quantity stepper */
.qty-stepper { display: flex; align-items: stretch; max-width: 180px; }
.qty-stepper .qty-btn {
    width: 42px; border: 1px solid #d9d2cc; background: #faf7f5; color: #561C04;
    display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer;
}
.qty-stepper .qty-btn:first-child { border-radius: {{ $ar ? '0 8px 8px 0' : '8px 0 0 8px' }}; }
.qty-stepper .qty-btn:last-child { border-radius: {{ $ar ? '8px 0 0 8px' : '0 8px 8px 0' }}; }
.qty-stepper .qty-btn:hover { background: #561C04; color: #fff; }
.qty-stepper .form-control { border-radius: 0; border-{{ $ar ? 'right' : 'left' }}: 0; border-{{ $ar ? 'left' : 'right' }}: 0; }

/* ── Review step ── */
.review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 767px) { .review-grid { grid-template-columns: 1fr; } }
.review-block { background: #faf8f6; border: 1px solid #f0eae6; border-radius: 12px; padding: 16px; }
.review-block-head {
    display: flex; justify-content: space-between; align-items: center;
    font-weight: 600; color: #561C04; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px dashed #e8ddd6;
}
.btn-edit-step { background: none; border: none; color: #7A3206; font-size: 12px; font-weight: 600; cursor: pointer; padding: 0; }
.btn-edit-step:hover { text-decoration: underline; }
.review-list { display: grid; grid-template-columns: auto 1fr; gap: 6px 14px; margin: 0; }
.review-list dt { color: #9a8c83; font-weight: 500; font-size: 13px; }
.review-list dd { margin: 0; font-weight: 600; font-size: 13px; color: #2c2c2c; text-align: {{ $ar ? 'left' : 'right' }}; }

.review-summary { background: #fff; border: 1px solid #f0eae6; border-radius: 12px; padding: 16px; }
.summary-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; font-size: 14px; }
.summary-row span { color: #6c757d; }
.summary-total {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 10px; padding-top: 14px; border-top: 2px dashed #e8ded7;
    font-size: 16px; font-weight: 700; color: #2c2c2c;
}
.summary-total .total-amount { color: #561C04; font-size: 24px; }

/* Uniform currency icons everywhere in the review summary + product banner */
.review-summary svg,
.product-banner svg,
.wallet-inline svg {
    width: 17px !important; height: 17px !important;
    vertical-align: -2px !important; flex-shrink: 0;
}
.summary-row strong, .summary-total .total-amount {
    display: inline-flex; align-items: center; gap: 4px;
}
.summary-total .total-amount svg { width: 20px !important; height: 20px !important; }

.wallet-inline {
    display: flex; align-items: center; gap: 12px;
    background: rgba(40,167,69,.06); border: 1px solid rgba(40,167,69,.2);
    border-radius: 12px; padding: 12px 16px;
}
.wallet-inline .wallet-icon {
    width: 42px; height: 42px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 10px; font-size: 20px; background: rgba(40,167,69,.14); color: #28a745;
}

/* ── Wizard nav ── */
.wizard-nav { display: flex; align-items: center; gap: 10px; border-top: 1px solid #f0eae6; }
.wizard-nav .btn { display: inline-flex; align-items: center; }
</style>
@endsection
