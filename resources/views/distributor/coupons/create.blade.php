@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('distributor.coupons.index') }}">{{ app()->getLocale() == 'ar' ? 'الكوبونات' : 'Coupons' }}</a></li>
                <li class="breadcrumb-item active">{{ app()->getLocale() == 'ar' ? 'إضافة كوبون' : 'Add Coupon' }}</li>
            </ol>
        </nav>
        <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'إضافة كوبون جديد' : 'Add New Coupon' }}</h4>
    </div>

    <form action="{{ route('distributor.coupons.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <!-- Main Form -->
            <div class="col-md-8">
                <!-- Basic Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'معلومات أساسية' : 'Basic Information' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Code -->
                            <div class="col-md-6">
                                <label for="code" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'كود الكوبون' : 'Coupon Code' }}
                                </label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        value="{{ old('code') }}"
                                        maxlength="10"
                                        placeholder="{{ app()->getLocale() == 'ar' ? 'اتركه فارغاً للتوليد التلقائي' : 'Leave empty for auto-generate' }}"
                                    >
                                    <button type="button" class="btn btn-outline-primary" onclick="generateCode()">
                                        <i class="ri-refresh-line"></i>
                                    </button>
                                </div>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Title Arabic -->
                            <div class="col-md-6">
                                <label for="title_ar" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'العنوان (عربي)' : 'Title (Arabic)' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control @error('title_ar') is-invalid @enderror"
                                    id="title_ar"
                                    name="title_ar"
                                    value="{{ old('title_ar') }}"
                                    required
                                >
                                @error('title_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Title English -->
                            <div class="col-md-6">
                                <label for="title_en" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'العنوان (إنجليزي)' : 'Title (English)' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control @error('title_en') is-invalid @enderror"
                                    id="title_en"
                                    name="title_en"
                                    value="{{ old('title_en') }}"
                                    required
                                >
                                @error('title_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description Arabic -->
                            <div class="col-md-6">
                                <label for="description_ar" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'الوصف (عربي)' : 'Description (Arabic)' }}
                                </label>
                                <textarea
                                    class="form-control @error('description_ar') is-invalid @enderror"
                                    id="description_ar"
                                    name="description_ar"
                                    rows="3"
                                >{{ old('description_ar') }}</textarea>
                                @error('description_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description English -->
                            <div class="col-md-6">
                                <label for="description_en" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'الوصف (إنجليزي)' : 'Description (English)' }}
                                </label>
                                <textarea
                                    class="form-control @error('description_en') is-invalid @enderror"
                                    id="description_en"
                                    name="description_en"
                                    rows="3"
                                >{{ old('description_en') }}</textarea>
                                @error('description_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Valid For -->
                            <div class="col-md-6">
                                <label for="valid_for" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'صالح لـ' : 'Valid For' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('valid_for') is-invalid @enderror" id="valid_for" name="valid_for" required>
                                    <option value="both" {{ old('valid_for') === 'both' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'الموقع والمتجر' : 'Website & Store' }}
                                    </option>
                                    <option value="website" {{ old('valid_for') === 'website' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'الموقع فقط' : 'Website Only' }}
                                    </option>
                                    <option value="store" {{ old('valid_for') === 'store' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'المتجر فقط' : 'Store Only' }}
                                    </option>
                                </select>
                                @error('valid_for')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discount & Commission -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الخصم والعمولة' : 'Discount & Commission' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Discount Type -->
                            <div class="col-md-6">
                                <label for="discount_type" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'نوع الخصم' : 'Discount Type' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type" required>
                                    <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'نسبة مئوية (%)' : 'Percentage (%)' }}
                                    </option>
                                    <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'مبلغ ثابت' : 'Fixed Amount' }}
                                    </option>
                                </select>
                                @error('discount_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Discount Value -->
                            <div class="col-md-6">
                                <label for="discount_value" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'قيمة الخصم' : 'Discount Value' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('discount_value') is-invalid @enderror"
                                    id="discount_value"
                                    name="discount_value"
                                    value="{{ old('discount_value') }}"
                                    required
                                >
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Commission Type -->
                            <div class="col-md-6">
                                <label for="commission_type" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'نوع العمولة' : 'Commission Type' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('commission_type') is-invalid @enderror" id="commission_type" name="commission_type" required>
                                    <option value="percentage" {{ old('commission_type') === 'percentage' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'نسبة مئوية (%)' : 'Percentage (%)' }}
                                    </option>
                                    <option value="fixed" {{ old('commission_type') === 'fixed' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'مبلغ ثابت' : 'Fixed Amount' }}
                                    </option>
                                </select>
                                @error('commission_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Commission Value -->
                            <div class="col-md-6">
                                <label for="commission_value" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'قيمة العمولة' : 'Commission Value' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('commission_value') is-invalid @enderror"
                                    id="commission_value"
                                    name="commission_value"
                                    value="{{ old('commission_value') }}"
                                    required
                                >
                                @error('commission_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Limits -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'حدود الاستخدام' : 'Usage Limits' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Max Uses -->
                            <div class="col-md-4">
                                <label for="max_uses" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'الحد الأقصى للاستخدام' : 'Max Uses' }}
                                </label>
                                <input
                                    type="number"
                                    min="1"
                                    class="form-control @error('max_uses') is-invalid @enderror"
                                    id="max_uses"
                                    name="max_uses"
                                    value="{{ old('max_uses') }}"
                                    placeholder="{{ app()->getLocale() == 'ar' ? 'غير محدود' : 'Unlimited' }}"
                                >
                                @error('max_uses')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Max Uses Per User -->
                            <div class="col-md-4">
                                <label for="max_uses_per_user" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'الحد لكل مستخدم' : 'Max Per User' }}
                                </label>
                                <input
                                    type="number"
                                    min="1"
                                    class="form-control @error('max_uses_per_user') is-invalid @enderror"
                                    id="max_uses_per_user"
                                    name="max_uses_per_user"
                                    value="{{ old('max_uses_per_user') }}"
                                    placeholder="{{ app()->getLocale() == 'ar' ? 'غير محدود' : 'Unlimited' }}"
                                >
                                @error('max_uses_per_user')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Min Order Amount -->
                            <div class="col-md-4">
                                <label for="min_order_amount" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'الحد الأدنى للطلب' : 'Min Order Amount' }}
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('min_order_amount') is-invalid @enderror"
                                    id="min_order_amount"
                                    name="min_order_amount"
                                    value="{{ old('min_order_amount') }}"
                                >
                                @error('min_order_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Free Shipping -->
                            <div class="col-md-6">
                                <div class="form-check mt-3">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="free_shipping"
                                        name="free_shipping"
                                        value="1"
                                        {{ old('free_shipping') ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="free_shipping">
                                        {{ app()->getLocale() == 'ar' ? 'شحن مجاني' : 'Free Shipping' }}
                                    </label>
                                </div>
                            </div>

                            <!-- Exclude Discounted -->
                            <div class="col-md-6">
                                <div class="form-check mt-3">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="exclude_discounted"
                                        name="exclude_discounted"
                                        value="1"
                                        {{ old('exclude_discounted') ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="exclude_discounted">
                                        {{ app()->getLocale() == 'ar' ? 'استبعاد المنتجات المخفضة' : 'Exclude Discounted Products' }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'تاريخ الصلاحية' : 'Validity Dates' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Start Date -->
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'تاريخ البدء' : 'Start Date' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="date"
                                    class="form-control @error('start_date') is-invalid @enderror"
                                    id="start_date"
                                    name="start_date"
                                    value="{{ old('start_date') }}"
                                    required
                                >
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'تاريخ الانتهاء' : 'End Date' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="date"
                                    class="form-control @error('end_date') is-invalid @enderror"
                                    id="end_date"
                                    name="end_date"
                                    value="{{ old('end_date') }}"
                                    required
                                >
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> {{ app()->getLocale() == 'ar' ? 'حفظ' : 'Save' }}
                            </button>
                            <a href="{{ route('distributor.coupons.index') }}" class="btn btn-outline-secondary">
                                {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">{{ app()->getLocale() == 'ar' ? 'معلومات مفيدة' : 'Helpful Information' }}</h6>
                    </div>
                    <div class="card-body small text-muted">
                        <p class="mb-3">
                            <strong>{{ app()->getLocale() == 'ar' ? 'كود الكوبون:' : 'Coupon Code:' }}</strong><br>
                            {{ app()->getLocale() == 'ar' ? 'رمز فريد من 6-10 أحرف. اتركه فارغاً للتوليد التلقائي.' : 'A unique code of 6-10 characters. Leave empty for auto-generation.' }}
                        </p>
                        <p class="mb-3">
                            <strong>{{ app()->getLocale() == 'ar' ? 'نوع الخصم:' : 'Discount Type:' }}</strong><br>
                            {{ app()->getLocale() == 'ar' ? 'اختر نسبة مئوية (%) أو مبلغ ثابت' : 'Choose percentage (%) or fixed amount' }}
                        </p>
                        <p class="mb-3">
                            <strong>{{ app()->getLocale() == 'ar' ? 'العمولة:' : 'Commission:' }}</strong><br>
                            {{ app()->getLocale() == 'ar' ? 'المبلغ الذي ستكسبه من كل استخدام' : 'Amount you earn from each use' }}
                        </p>
                        <p>
                            <strong>{{ app()->getLocale() == 'ar' ? 'حدود الاستخدام:' : 'Usage Limits:' }}</strong><br>
                            {{ app()->getLocale() == 'ar' ? 'اترك الحقول فارغة للحدود غير المحدودة' : 'Leave fields empty for unlimited' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function generateCode() {
    fetch('{{ route("distributor.coupons.generate-code") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('code').value = data.code;
        })
        .catch(error => console.error('Error:', error));
}
</script>
@endsection
