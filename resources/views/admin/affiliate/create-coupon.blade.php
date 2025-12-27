@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.affiliate.coupons.active') }}">{{ app()->getLocale() == 'ar' ? 'الكوبونات' : 'Coupons' }}</a></li>
                <li class="breadcrumb-item active">{{ app()->getLocale() == 'ar' ? 'إضافة كوبون' : 'Add Coupon' }}</li>
            </ol>
        </nav>
        <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'إضافة كوبون جديد' : 'Add New Coupon' }}</h4>
    </div>

    <form action="{{ route('admin.affiliate.coupons.store') }}" method="POST" enctype="multipart/form-data">
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
                            <!-- Store -->
                            <div class="col-md-6">
                                <label for="store_id" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'المتجر' : 'Store' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('store_id') is-invalid @enderror" id="store_id" name="store_id" required>
                                    <option value="">{{ app()->getLocale() == 'ar' ? 'اختر المتجر' : 'Select Store' }}</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id', request('store_id')) == $store->id ? 'selected' : '' }}>
                                            {{ $store->store_name ?? $store->name }} ({{ $store->country }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('store_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

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
                                    placeholder="0.00"
                                >
                                @error('min_order_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Checkboxes -->
                            <div class="col-12">
                                <div class="form-check form-check-inline">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="free_shipping"
                                        name="free_shipping"
                                        value="1"
                                        {{ old('free_shipping') ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="free_shipping">
                                        {{ app()->getLocale() == 'ar' ? 'شحن مجاني' : 'Free Shipping' }}
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
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

                <!-- Features & Terms -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'المميزات والشروط' : 'Features & Terms' }}</h5>
                    </div>
                    <div class="card-body">
                        <!-- Features -->
                        <div class="mb-4">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'مميزات الخصم' : 'Discount Features' }}</label>
                            <div id="features-container">
                                <div class="input-group mb-2 feature-row">
                                    <input type="text" class="form-control" name="features[]" placeholder="{{ app()->getLocale() == 'ar' ? 'ميزة...' : 'Feature...' }}">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFeature()">
                                <i class="ri-add-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'إضافة ميزة' : 'Add Feature' }}
                            </button>
                        </div>

                        <!-- Terms -->
                        <div class="mb-4">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'شروط الاستخدام (للعميل)' : 'Usage Terms (For Customer)' }}</label>
                            <div id="terms-container">
                                <div class="input-group mb-2 term-row">
                                    <input type="text" class="form-control" name="terms[]" placeholder="{{ app()->getLocale() == 'ar' ? 'شرط...' : 'Term...' }}">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTerm()">
                                <i class="ri-add-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'إضافة شرط' : 'Add Term' }}
                            </button>
                        </div>

                        <!-- Commission Terms -->
                        <div>
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'شروط العمولة (للموزع)' : 'Commission Terms (For Affiliate)' }}</label>
                            <div id="commission-terms-container">
                                <div class="input-group mb-2 commission-term-row">
                                    <input type="text" class="form-control" name="commission_terms[]" placeholder="{{ app()->getLocale() == 'ar' ? 'شرط عمولة...' : 'Commission term...' }}">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCommissionTerm()">
                                <i class="ri-add-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'إضافة شرط عمولة' : 'Add Commission Term' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الوسائط الترويجية' : 'Promotional Media' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Promo Images -->
                            <div class="col-md-6">
                                <label for="promo_images" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'صور ترويجية (حتى 5)' : 'Promo Images (up to 5)' }}
                                </label>
                                <input
                                    type="file"
                                    class="form-control @error('promo_images') is-invalid @enderror"
                                    id="promo_images"
                                    name="promo_images[]"
                                    accept="image/*"
                                    multiple
                                >
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الحد الأقصى 2 ميجابايت لكل صورة' : 'Max 2MB per image' }}</small>
                                @error('promo_images')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Promo Video -->
                            <div class="col-md-6">
                                <label for="promo_video" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'فيديو ترويجي' : 'Promo Video' }}
                                </label>
                                <input
                                    type="file"
                                    class="form-control @error('promo_video') is-invalid @enderror"
                                    id="promo_video"
                                    name="promo_video"
                                    accept="video/mp4,video/mov,video/avi"
                                >
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'MP4, MOV, AVI - الحد الأقصى 20 ميجابايت' : 'MP4, MOV, AVI - Max 20MB' }}</small>
                                @error('promo_video')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Dates -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الصلاحية' : 'Validity' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">
                                {{ app()->getLocale() == 'ar' ? 'تاريخ البدء' : 'Start Date' }}
                                <span class="text-danger">*</span>
                            </label>
                            <input
                                type="date"
                                class="form-control @error('start_date') is-invalid @enderror"
                                id="start_date"
                                name="start_date"
                                value="{{ old('start_date', date('Y-m-d')) }}"
                                required
                            >
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
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

                <!-- Submit -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="ri-save-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'حفظ الكوبون' : 'Save Coupon' }}
                        </button>
                        <a href="{{ route('admin.affiliate.coupons.active') }}" class="btn btn-outline-secondary w-100">
                            {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function generateCode() {
    fetch('{{ route("admin.affiliate.coupons.generate-code") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('code').value = data.code;
        });
}

function addFeature() {
    const container = document.getElementById('features-container');
    const row = document.createElement('div');
    row.className = 'input-group mb-2 feature-row';
    row.innerHTML = `
        <input type="text" class="form-control" name="features[]" placeholder="{{ app()->getLocale() == 'ar' ? 'ميزة...' : 'Feature...' }}">
        <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)">
            <i class="ri-delete-bin-line"></i>
        </button>
    `;
    container.appendChild(row);
}

function addTerm() {
    const container = document.getElementById('terms-container');
    const row = document.createElement('div');
    row.className = 'input-group mb-2 term-row';
    row.innerHTML = `
        <input type="text" class="form-control" name="terms[]" placeholder="{{ app()->getLocale() == 'ar' ? 'شرط...' : 'Term...' }}">
        <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)">
            <i class="ri-delete-bin-line"></i>
        </button>
    `;
    container.appendChild(row);
}

function addCommissionTerm() {
    const container = document.getElementById('commission-terms-container');
    const row = document.createElement('div');
    row.className = 'input-group mb-2 commission-term-row';
    row.innerHTML = `
        <input type="text" class="form-control" name="commission_terms[]" placeholder="{{ app()->getLocale() == 'ar' ? 'شرط عمولة...' : 'Commission term...' }}">
        <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)">
            <i class="ri-delete-bin-line"></i>
        </button>
    `;
    container.appendChild(row);
}

function removeRow(button) {
    button.closest('.input-group').remove();
}
</script>
@endpush
@endsection
