@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="ri-add-circle-line me-2"></i>
                {{ app()->getLocale() == 'ar' ? 'إنشاء طلب جديد' : 'Create New Order' }}
            </h5>
            <small>{{ app()->getLocale() == 'ar' ? 'املأ التفاصيل أدناه لإنشاء طلب جديد' : 'Fill in the details below to create a new order' }}</small>
        </div>

        <div class="card-body">
            {{-- Display session error messages --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Display session success messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Display validation errors --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-alert-line me-2"></i>
                    <strong>{{ app()->getLocale() == 'ar' ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('orders.distributor.store') }}" method="POST" id="orderForm">
                @csrf

                <!-- Product Information -->
                @if(isset($product))
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="ri-shopping-bag-line text-primary me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'المنتج' : 'Product' }}
                            </label>
                            <div class="card border-primary">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        @if($product->photo)
                                            <div class="col-auto">
                                                <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="rounded" style="width: 100px; height: 100px; object-fit: cover;">
                                            </div>
                                        @elseif($product->images && count($product->images) > 0)
                                            <div class="col-auto">
                                                <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="rounded" style="width: 100px; height: 100px; object-fit: cover;">
                                            </div>
                                        @endif
                                        <div class="col">
                                            <h6 class="mb-1">{{ $product->name }}</h6>
                                            @if($product->name_ar)
                                                <p class="text-muted small mb-1">{{ $product->name_ar }}</p>
                                            @endif
                                            <p class="mb-1 text-primary"><strong>{{ $product->currency }} {{ number_format($product->price, 2) }}</strong></p>
                                            <span class="badge bg-success">
                                                <i class="ri-store-line me-1"></i>
                                                {{ app()->getLocale() == 'ar' ? 'منتج محلي' : 'Local Product' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Quantity -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="quantity" class="form-label fw-semibold">
                            <i class="ri-shopping-cart-line text-primary me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'الكمية' : 'Quantity' }} *
                        </label>
                        <input type="number"
                               class="form-control @error('quantity') is-invalid @enderror"
                               id="quantity"
                               name="quantity"
                               value="{{ old('quantity', $queryParams['quantity'] ?? 1) }}"
                               min="1"
                               required
                               onchange="calculateTotal()">
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="mb-3">{{ app()->getLocale() == 'ar' ? 'ملخص الطلب' : 'Order Summary' }}</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ app()->getLocale() == 'ar' ? 'سعر الوحدة' : 'Unit Price' }}:</span>
                                    <strong id="unit_price">{{ $product->currency }} {{ number_format($product->price, 2) }}</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="fs-5">{{ app()->getLocale() == 'ar' ? 'المجموع الكلي' : 'Grand Total' }}:</span>
                                    <strong class="text-primary fs-5" id="grand_total">{{ $product->currency }} {{ number_format($product->price, 2) }}</strong>
                                </div>

                                {{-- Display Wallet Balance --}}
                                @if(auth()->user()->wallet)
                                    <div class="alert alert-info mb-0 d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="ri-wallet-3-line me-2"></i>
                                            <strong>{{ app()->getLocale() == 'ar' ? 'رصيدك الحالي:' : 'Your Current Balance:' }}</strong>
                                        </div>
                                        <span class="badge bg-success fs-6">
                                            AED {{ number_format(auth()->user()->wallet->balance, 2) }}
                                        </span>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        <i class="ri-error-warning-line me-2"></i>
                                        {{ app()->getLocale() == 'ar' ? 'ليس لديك محفظة. يرجى الاتصال بالإدارة.' : 'You do not have a wallet. Please contact administration.' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="ri-user-line text-primary me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'معلومات العميل' : 'Customer Information' }}
                        </h6>
                    </div>

                    <div class="col-md-6 mb-3">
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

                    <div class="col-md-6 mb-3">
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

                    <div class="col-md-3 mb-3">
                        <label for="phone_country" class="form-label">
                            {{ app()->getLocale() == 'ar' ? 'رمز الدولة' : 'Country Code' }} *
                        </label>
                        <select class="form-select @error('phone_country') is-invalid @enderror" id="phone_country" name="phone_country" required>
                            <option value="+971" {{ old('phone_country') == '+971' ? 'selected' : '' }}>🇦🇪 +971 (UAE)</option>
                            <option value="+966" {{ old('phone_country') == '+966' ? 'selected' : '' }}>🇸🇦 +966 (KSA)</option>
                            <option value="+20" {{ old('phone_country') == '+20' ? 'selected' : '' }}>🇪🇬 +20 (Egypt)</option>
                            <option value="+1" {{ old('phone_country') == '+1' ? 'selected' : '' }}>🇺🇸 +1 (USA)</option>
                        </select>
                        @error('phone_country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-9 mb-3">
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

                <!-- Shipping Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="ri-map-pin-line text-primary me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'معلومات الشحن' : 'Shipping Information' }}
                        </h6>
                    </div>

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-6 mb-3">
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

                    <div class="col-md-6 mb-3">
                        <label for="shipping_province" class="form-label">
                            {{ app()->getLocale() == 'ar' ? 'المحافظة/المنطقة' : 'Province/State' }}
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

                    <div class="col-md-6 mb-3">
                        <label for="shipping_country" class="form-label">
                            {{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }} *
                        </label>
                        <select class="form-select @error('shipping_country') is-invalid @enderror" id="shipping_country" name="shipping_country" required>
                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر الدولة' : 'Select Country' }}</option>
                            <option value="AE" {{ old('shipping_country') == 'AE' ? 'selected' : '' }}>United Arab Emirates</option>
                            <option value="SA" {{ old('shipping_country') == 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                            <option value="EG" {{ old('shipping_country') == 'EG' ? 'selected' : '' }}>Egypt</option>
                            <option value="US" {{ old('shipping_country') == 'US' ? 'selected' : '' }}>United States</option>
                        </select>
                        @error('shipping_country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
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
                                  rows="3">{{ old('customer_notes', $queryParams['customer_notes'] ?? '') }}</textarea>
                        @error('customer_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-check-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'إنشاء الطلب' : 'Create Order' }}
                        </button>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Calculate total amount
function calculateTotal() {
    const unitPrice = {{ $product->price }};
    const currency = '{{ $product->currency }}';
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const walletBalance = {{ auth()->user()->wallet ? auth()->user()->wallet->balance : 0 }};
    const locale = '{{ app()->getLocale() }}';

    const grandTotal = unitPrice * quantity;

    document.getElementById('grand_total').textContent = currency + ' ' + grandTotal.toFixed(2);

    // Check if wallet balance is sufficient
    const submitBtn = document.querySelector('button[type="submit"]');
    if (walletBalance < grandTotal) {
        // Show warning and disable submit button
        if (!document.getElementById('insufficient-balance-warning')) {
            const warningDiv = document.createElement('div');
            warningDiv.id = 'insufficient-balance-warning';
            warningDiv.className = 'alert alert-danger mt-3';

            const warningTitle = locale === 'ar' ? 'رصيد غير كافٍ!' : 'Insufficient Balance!';
            const warningMessage = locale === 'ar'
                ? 'رصيدك الحالي غير كافٍ لإنشاء هذا الطلب. يرجى زيادة رصيدك.'
                : 'Your current balance is not sufficient to create this order. Please increase your balance.';

            warningDiv.innerHTML = '<i class="ri-error-warning-line me-2"></i><strong>' + warningTitle + '</strong> ' + warningMessage;

            const summaryCard = document.querySelector('.card.bg-light');
            summaryCard.parentNode.appendChild(warningDiv);
        }
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-secondary');
        }
    } else {
        // Remove warning and enable submit button
        const warningDiv = document.getElementById('insufficient-balance-warning');
        if (warningDiv) {
            warningDiv.remove();
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-primary');
        }
    }
}

// Calculate on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>
@endpush

@endsection
