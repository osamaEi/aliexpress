@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('distributor.coupons.index') }}">{{ app()->getLocale() == 'ar' ? 'الكوبونات' : 'Coupons' }}</a></li>
                <li class="breadcrumb-item active">{{ app()->getLocale() == 'ar' ? 'تعديل كوبون' : 'Edit Coupon' }}</li>
            </ol>
        </nav>
        <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'تعديل الكوبون: ' . $coupon->code : 'Edit Coupon: ' . $coupon->code }}</h4>
    </div>

    <form action="{{ route('distributor.coupons.update', $coupon) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                            <!-- Code (Read Only) -->
                            <div class="col-md-6">
                                <label for="code" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'كود الكوبون' : 'Coupon Code' }}
                                </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="code"
                                    value="{{ $coupon->code }}"
                                    readonly
                                >
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'لا يمكن تغيير الكود' : 'Code cannot be changed' }}</small>
                            </div>

                            <div class="col-md-6"></div>

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
                                    value="{{ old('title_ar', $coupon->title_ar) }}"
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
                                    value="{{ old('title_en', $coupon->title_en) }}"
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
                                >{{ old('description_ar', $coupon->description_ar) }}</textarea>
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
                                >{{ old('description_en', $coupon->description_en) }}</textarea>
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
                                    <option value="both" {{ old('valid_for', $coupon->valid_for) === 'both' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'الموقع والمتجر' : 'Website & Store' }}
                                    </option>
                                    <option value="website" {{ old('valid_for', $coupon->valid_for) === 'website' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'الموقع فقط' : 'Website Only' }}
                                    </option>
                                    <option value="store" {{ old('valid_for', $coupon->valid_for) === 'store' ? 'selected' : '' }}>
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
                                    <option value="percentage" {{ old('discount_type', $coupon->discount_type) === 'percentage' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'نسبة مئوية (%)' : 'Percentage (%)' }}
                                    </option>
                                    <option value="fixed" {{ old('discount_type', $coupon->discount_type) === 'fixed' ? 'selected' : '' }}>
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
                                    value="{{ old('discount_value', $coupon->discount_value) }}"
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
                                    <option value="percentage" {{ old('commission_type', $coupon->commission_type) === 'percentage' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'نسبة مئوية (%)' : 'Percentage (%)' }}
                                    </option>
                                    <option value="fixed" {{ old('commission_type', $coupon->commission_type) === 'fixed' ? 'selected' : '' }}>
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
                                    value="{{ old('commission_value', $coupon->commission_value) }}"
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
                                    value="{{ old('max_uses', $coupon->max_uses) }}"
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
                                    value="{{ old('max_uses_per_user', $coupon->max_uses_per_user) }}"
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
                                    value="{{ old('min_order_amount', $coupon->min_order_amount) }}"
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
                                        {{ old('free_shipping', $coupon->free_shipping) ? 'checked' : '' }}
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
                                        {{ old('exclude_discounted', $coupon->exclude_discounted) ? 'checked' : '' }}
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
                                    value="{{ old('start_date', $coupon->start_date->format('Y-m-d')) }}"
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
                                    value="{{ old('end_date', $coupon->end_date->format('Y-m-d')) }}"
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
                                <i class="ri-save-line"></i> {{ app()->getLocale() == 'ar' ? 'تحديث' : 'Update' }}
                            </button>
                            <a href="{{ route('distributor.coupons.show', $coupon) }}" class="btn btn-outline-secondary">
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
                        <h6 class="mb-0">{{ app()->getLocale() == 'ar' ? 'معلومات الكوبون' : 'Coupon Information' }}</h6>
                    </div>
                    <div class="card-body small">
                        <div class="mb-3">
                            <strong class="d-block text-muted">{{ app()->getLocale() == 'ar' ? 'تم الإنشاء في:' : 'Created at:' }}</strong>
                            {{ $coupon->created_at->format('Y-m-d H:i') }}
                        </div>
                        <div class="mb-3">
                            <strong class="d-block text-muted">{{ app()->getLocale() == 'ar' ? 'عدد الاستخدامات:' : 'Total Uses:' }}</strong>
                            {{ $coupon->usage_count ?? 0 }}
                        </div>
                        <div class="mb-3">
                            <strong class="d-block text-muted">{{ app()->getLocale() == 'ar' ? 'العمولة المكتسبة:' : 'Commission Earned:' }}</strong>
                            <span class="text-success">{{ $coupon->total_commission_earned ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
