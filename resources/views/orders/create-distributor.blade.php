@extends('dashboard')

@section('content')
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
                        {{ app()->getLocale() == 'ar' ? 'طلب من موزع محلي' : 'Order from Local Distributor' }}
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
                                            <h4 class="text-primary mb-0">{{ $product->currency }} {{ number_format($product->price, 2) }}</h4>
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
                                        <label for="shipping_city" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'المدينة' : 'City' }} *
                                        </label>
                                        <input type="text"
                                               class="form-control @error('shipping_city') is-invalid @enderror"
                                               id="shipping_city"
                                               name="shipping_city"
                                               value="{{ old('shipping_city') }}"
                                               required>
                                        @error('shipping_city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                        <label for="shipping_country" class="form-label">
                                            {{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }} *
                                        </label>
                                        <select class="form-select @error('shipping_country') is-invalid @enderror"
                                                id="shipping_country"
                                                name="shipping_country"
                                                required>
                                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر الدولة' : 'Select Country' }}</option>
                                            <option value="AE" {{ old('shipping_country') == 'AE' ? 'selected' : '' }}>🇦🇪 UAE</option>
                                            <option value="SA" {{ old('shipping_country') == 'SA' ? 'selected' : '' }}>🇸🇦 Saudi Arabia</option>
                                            <option value="EG" {{ old('shipping_country') == 'EG' ? 'selected' : '' }}>🇪🇬 Egypt</option>
                                            <option value="US" {{ old('shipping_country') == 'US' ? 'selected' : '' }}>🇺🇸 USA</option>
                                        </select>
                                        @error('shipping_country')
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
                                    <h6 class="mb-0">
                                        <i class="ri-file-list-line me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'ملخص الطلب' : 'Order Summary' }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'سعر الوحدة:' : 'Unit Price:' }}</span>
                                        <strong>{{ $product->currency }} {{ number_format($product->price, 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'الكمية:' : 'Quantity:' }}</span>
                                        <strong id="quantity_display">1</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الإجمالي:' : 'Total:' }}</h5>
                                        <h4 class="mb-0 text-primary" id="grand_total">{{ $product->currency }} {{ number_format($product->price, 2) }}</h4>
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
                                                    AED {{ number_format(auth()->user()->wallet->balance, 2) }}
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
function calculateTotal() {
    const unitPrice = {{ $product->price }};
    const currency = '{{ $product->currency }}';
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const walletBalance = {{ auth()->user()->wallet ? auth()->user()->wallet->balance : 0 }};
    const locale = '{{ app()->getLocale() }}';

    const grandTotal = unitPrice * quantity;

    // Update display
    document.getElementById('quantity_display').textContent = quantity;
    document.getElementById('grand_total').textContent = currency + ' ' + grandTotal.toFixed(2);

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

.form-control, .form-select {
    border-radius: 8px;
}

.btn {
    border-radius: 8px;
}

.sticky-top {
    z-index: 1020;
}
</style>
@endsection
