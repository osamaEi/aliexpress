@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.affiliate.coupons.active') }}">{{ app()->getLocale() == 'ar' ? 'الكوبونات' : 'Coupons' }}</a></li>
                <li class="breadcrumb-item active">{{ app()->getLocale() == 'ar' ? 'تعديل كوبون' : 'Edit Coupon' }}</li>
            </ol>
        </nav>
        <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'تعديل كوبون' : 'Edit Coupon' }}: {{ $coupon->code }}</h4>
    </div>

    <form action="{{ route('admin.affiliate.coupons.update', $coupon) }}" method="POST" enctype="multipart/form-data">
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
                            <!-- Store -->
                            <div class="col-md-6">
                                <label for="store_id" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'المتجر' : 'Store' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('store_id') is-invalid @enderror" id="store_id" name="store_id" required>
                                    <option value="">{{ app()->getLocale() == 'ar' ? 'اختر المتجر' : 'Select Store' }}</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id', $coupon->store_id) == $store->id ? 'selected' : '' }}>
                                            {{ $store->store_name ?? $store->name }} ({{ $store->country }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('store_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

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
                                    disabled
                                >
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

                            <!-- Coupon Method -->
                            <div class="col-md-6">
                                <label for="coupon_method" class="form-label">{{ app()->getLocale() == 'ar' ? 'نوع الكوبون' : 'Coupon Type' }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="coupon_method" name="coupon_method" required onchange="toggleCouponMethod()">
                                    <option value="code" {{ old('coupon_method', $coupon->coupon_method) === 'code' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'كوبون برمز' : 'Code Coupon' }}</option>
                                    <option value="link" {{ old('coupon_method', $coupon->coupon_method) === 'link' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'كوبون برابط مباشر' : 'Direct Link Coupon' }}</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="direct_link_wrap" style="display:none;">
                                <label for="direct_link" class="form-label">{{ app()->getLocale() == 'ar' ? 'الرابط المباشر' : 'Direct Link' }}</label>
                                <input type="url" class="form-control" id="direct_link" name="direct_link" value="{{ old('direct_link', $coupon->direct_link) }}" placeholder="https://...">
                            </div>

                            <!-- Main Category -->
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">{{ app()->getLocale() == 'ar' ? 'التصنيف الرئيسي' : 'Main Category' }}</label>
                                <select class="form-select" id="category_id" name="category_id" onchange="loadSubCategories(this.value)">
                                    <option value="">{{ app()->getLocale() == 'ar' ? 'اختر التصنيف' : 'Select Category' }}</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $coupon->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ app()->getLocale() == 'ar' && $cat->name_ar ? $cat->name_ar : $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Sub Category -->
                            <div class="col-md-6">
                                <label for="sub_category_id" class="form-label">{{ app()->getLocale() == 'ar' ? 'التصنيف الفرعي' : 'Sub Category' }}</label>
                                <select class="form-select" id="sub_category_id" name="sub_category_id">
                                    <option value="">{{ app()->getLocale() == 'ar' ? 'اختر التصنيف الفرعي' : 'Select sub category' }}</option>
                                    @foreach($subCategories as $sub)
                                        <option value="{{ $sub->id }}" {{ old('sub_category_id', $coupon->sub_category_id) == $sub->id ? 'selected' : '' }}>
                                            {{ app()->getLocale() == 'ar' && $sub->name_ar ? $sub->name_ar : $sub->name }}
                                        </option>
                                    @endforeach
                                </select>
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
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'تم استخدامه' : 'Used' }}: {{ $coupon->used_count }}</small>
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
                                        {{ old('free_shipping', $coupon->free_shipping) ? 'checked' : '' }}
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

                <!-- Media -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الوسائط الترويجية' : 'Promotional Media' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Current Images -->
                            @if($coupon->promo_images && count($coupon->promo_images) > 0)
                            <div class="col-12">
                                <label class="form-label">{{ app()->getLocale() == 'ar' ? 'الصور الحالية' : 'Current Images' }}</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($coupon->promo_images as $image)
                                        <img src="{{ asset('storage/' . $image) }}" alt="Promo" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Promo Images -->
                            <div class="col-md-6">
                                <label for="promo_images" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'صور ترويجية جديدة (استبدال)' : 'New Promo Images (Replace)' }}
                                </label>
                                <input
                                    type="file"
                                    class="form-control @error('promo_images') is-invalid @enderror"
                                    id="promo_images"
                                    name="promo_images[]"
                                    accept="image/*"
                                    multiple
                                >
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'حتى 5 صور، 2 ميجابايت لكل صورة' : 'Up to 5 images, 2MB each' }}</small>
                                @error('promo_images')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Promo Video -->
                            <div class="col-md-6">
                                <label for="promo_video" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'فيديو ترويجي جديد' : 'New Promo Video' }}
                                </label>
                                <input
                                    type="file"
                                    class="form-control @error('promo_video') is-invalid @enderror"
                                    id="promo_video"
                                    name="promo_video"
                                    accept="video/mp4,video/mov,video/avi"
                                >
                                @if($coupon->promo_video)
                                    <small class="text-success">{{ app()->getLocale() == 'ar' ? 'يوجد فيديو حالي' : 'Current video exists' }}</small>
                                @endif
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
                <!-- Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="is_active"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="is_active">
                                {{ app()->getLocale() == 'ar' ? 'الكوبون فعال' : 'Coupon Active' }}
                            </label>
                        </div>
                    </div>
                </div>

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
                                value="{{ old('start_date', $coupon->start_date->format('Y-m-d')) }}"
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
                                value="{{ old('end_date', $coupon->end_date->format('Y-m-d')) }}"
                                required
                            >
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الإحصائيات' : 'Statistics' }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>{{ app()->getLocale() == 'ar' ? 'عدد الاستخدام' : 'Usage Count' }}</span>
                                <strong>{{ $coupon->used_count }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>{{ app()->getLocale() == 'ar' ? 'إجمالي الخصومات' : 'Total Discounts' }}</span>
                                <strong>{{ number_format($coupon->total_discount_given, 2) }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>{{ app()->getLocale() == 'ar' ? 'إجمالي العمولات' : 'Total Commissions' }}</span>
                                <strong class="text-success">{{ number_format($coupon->total_commission_earned, 2) }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Submit -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="ri-save-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
                        </button>
                        <a href="{{ route('admin.affiliate.coupons.show', $coupon) }}" class="btn btn-outline-secondary w-100">
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
function toggleCouponMethod() {
    const method = document.getElementById('coupon_method').value;
    document.getElementById('direct_link_wrap').style.display = method === 'link' ? 'block' : 'none';
}
function loadSubCategories(categoryId, selectedSub = null) {
    const subSelect = document.getElementById('sub_category_id');
    const isAr = '{{ app()->getLocale() }}' === 'ar';
    subSelect.innerHTML = `<option value="">${isAr ? 'اختر التصنيف الفرعي' : 'Select sub category'}</option>`;
    if (!categoryId) return;
    fetch(`{{ url('categories') }}/${categoryId}/children`)
        .then(r => r.json())
        .then(d => {
            (d.children || []).forEach(c => {
                const o = document.createElement('option');
                o.value = c.id;
                o.textContent = isAr && c.name_ar ? c.name_ar : c.name;
                if (selectedSub && String(selectedSub) === String(c.id)) o.selected = true;
                subSelect.appendChild(o);
            });
        });
}
document.addEventListener('DOMContentLoaded', toggleCouponMethod);
</script>
@endpush
@endsection
