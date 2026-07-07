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
                            <!-- Coupon code generation note -->
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="alert alert-info mb-0 py-2 px-3 w-100" style="font-size:13px;">
                                    <i class="ri-information-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'كود الكوبون يُولَّد تلقائياً عند التفعيل للمسوق.' : 'The coupon code is generated automatically upon activation for a marketer.' }}
                                </div>
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

                            <!-- Coupon Method: code or direct link -->
                            <div class="col-md-6">
                                <label for="coupon_method" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'نوع الكوبون' : 'Coupon Type' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('coupon_method') is-invalid @enderror" id="coupon_method" name="coupon_method" required onchange="toggleCouponMethod()">
                                    <option value="code" {{ old('coupon_method', 'code') === 'code' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'كوبون برمز' : 'Code Coupon' }}
                                    </option>
                                    <option value="link" {{ old('coupon_method') === 'link' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'كوبون برابط مباشر' : 'Direct Link Coupon' }}
                                    </option>
                                </select>
                                @error('coupon_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Direct Link (shown when method = link) -->
                            <div class="col-md-6" id="direct_link_wrap" style="display:none;">
                                <label for="direct_link" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'الرابط المباشر' : 'Direct Link' }}
                                </label>
                                <input type="url" class="form-control @error('direct_link') is-invalid @enderror"
                                       id="direct_link" name="direct_link" value="{{ old('direct_link') }}"
                                       placeholder="https://...">
                                @error('direct_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Main Category -->
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">{{ app()->getLocale() == 'ar' ? 'التصنيف الرئيسي' : 'Main Category' }}</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" onchange="loadSubCategories(this.value)">
                                    <option value="">{{ app()->getLocale() == 'ar' ? 'اختر التصنيف' : 'Select Category' }}</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ app()->getLocale() == 'ar' && $cat->name_ar ? $cat->name_ar : $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Sub Category -->
                            <div class="col-md-6">
                                <label for="sub_category_id" class="form-label">{{ app()->getLocale() == 'ar' ? 'التصنيف الفرعي' : 'Sub Category' }}</label>
                                <select class="form-select @error('sub_category_id') is-invalid @enderror" id="sub_category_id" name="sub_category_id" disabled>
                                    <option value="">{{ app()->getLocale() == 'ar' ? 'اختر التصنيف الرئيسي أولاً' : 'Select main category first' }}</option>
                                </select>
                                @error('sub_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Products this coupon applies to (required) -->
                            <div class="col-12">
                                <label class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'المنتجات التي يطبّق عليها الكوبون' : 'Products this coupon applies to' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="small text-muted mb-2">
                                    {{ app()->getLocale() == 'ar' ? 'سيُطبّق الخصم على المنتجات المحددة فقط من متجرك.' : 'The discount applies only to the selected products from your store.' }}
                                </div>

                                @if($products->isEmpty())
                                    <div class="alert alert-warning mb-0 py-2 px-3">
                                        {{ app()->getLocale() == 'ar' ? 'لا توجد منتجات في متجرك بعد. أضف منتجات أولاً.' : 'You have no products yet. Add products first.' }}
                                    </div>
                                @else
                                    <input type="text" class="form-control mb-2" id="product-search"
                                           placeholder="{{ app()->getLocale() == 'ar' ? 'ابحث عن منتج...' : 'Search products...' }}"
                                           onkeyup="filterProducts(this.value)">

                                    <div class="d-flex gap-3 mb-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAllProducts(true)">
                                            {{ app()->getLocale() == 'ar' ? 'تحديد الكل' : 'Select all' }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllProducts(false)">
                                            {{ app()->getLocale() == 'ar' ? 'إلغاء التحديد' : 'Clear' }}
                                        </button>
                                        <span class="ms-auto small text-muted align-self-center">
                                            <span id="product-count">0</span> {{ app()->getLocale() == 'ar' ? 'محدد' : 'selected' }}
                                        </span>
                                    </div>

                                    @php $oldProducts = (array) old('product_ids', []); @endphp
                                    <div class="border rounded p-2 @error('product_ids') border-danger @enderror"
                                         id="product-list" style="max-height: 260px; overflow-y: auto;">
                                        @foreach($products as $product)
                                            @php $pname = app()->getLocale() == 'ar' && $product->name_ar ? $product->name_ar : $product->name; @endphp
                                            <div class="form-check product-item" data-name="{{ \Illuminate\Support\Str::lower($pname) }}">
                                                <input class="form-check-input product-checkbox" type="checkbox"
                                                       name="product_ids[]" value="{{ $product->id }}"
                                                       id="product-{{ $product->id }}"
                                                       {{ in_array($product->id, $oldProducts) ? 'checked' : '' }}
                                                       onchange="updateProductCount()">
                                                <label class="form-check-label" for="product-{{ $product->id }}">{{ $pname }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @error('product_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
                            <!-- Main Image -->
                            <div class="col-md-12">
                                <label for="image" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'الصورة الرئيسية للكوبون' : 'Coupon Main Image' }}
                                </label>
                                <input
                                    type="file"
                                    class="form-control @error('image') is-invalid @enderror"
                                    id="image"
                                    name="image"
                                    accept="image/*"
                                >
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الحد الأقصى 2 ميجابايت' : 'Max 2MB' }}</small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

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

// Toggle direct-link field based on coupon method
function toggleCouponMethod() {
    const method = document.getElementById('coupon_method').value;
    document.getElementById('direct_link_wrap').style.display = method === 'link' ? 'block' : 'none';
}

// --- Product selector helpers ---
function updateProductCount() {
    const n = document.querySelectorAll('.product-checkbox:checked').length;
    const el = document.getElementById('product-count');
    if (el) el.textContent = n;
}

function toggleAllProducts(state) {
    document.querySelectorAll('#product-list .product-item').forEach(item => {
        if (item.style.display !== 'none') {
            const cb = item.querySelector('.product-checkbox');
            if (cb) cb.checked = state;
        }
    });
    updateProductCount();
}

function filterProducts(term) {
    term = term.toLowerCase();
    document.querySelectorAll('#product-list .product-item').forEach(item => {
        item.style.display = item.dataset.name.includes(term) ? '' : 'none';
    });
}

// Cascade: load sub categories for the chosen main category
function loadSubCategories(categoryId, selectedSub = null) {
    const subSelect = document.getElementById('sub_category_id');
    const isAr = '{{ app()->getLocale() }}' === 'ar';
    subSelect.innerHTML = `<option value="">${isAr ? 'اختر التصنيف الفرعي' : 'Select sub category'}</option>`;
    if (!categoryId) { subSelect.disabled = true; return; }
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
            subSelect.disabled = false;
        });
}

document.addEventListener('DOMContentLoaded', function () {
    toggleCouponMethod();
    updateProductCount();
    const cat = document.getElementById('category_id');
    if (cat && cat.value) loadSubCategories(cat.value, '{{ old('sub_category_id') }}');
});
</script>
@endsection
