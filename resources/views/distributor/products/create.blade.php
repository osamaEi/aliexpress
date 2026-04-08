@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- Subscription plan usage badge --}}
    @if($subscriptionPlan)
    @php
        $isAr = app()->getLocale() == 'ar';
        $remaining = $maxProducts ? ($maxProducts - $productCount) : null;
    @endphp
    <div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="border-radius:12px;">
        <i class="ri-vip-crown-line fs-5"></i>
        <span>
            <strong>{{ $isAr ? ($subscriptionPlan->name_ar ?: $subscriptionPlan->name) : $subscriptionPlan->name }}</strong>
            &mdash;
            {{ $isAr ? 'المنتجات المستخدمة:' : 'Products used:' }}
            <strong>{{ $productCount }}</strong> /
            {{ $maxProducts ?? ($isAr ? 'غير محدود' : 'Unlimited') }}
            @if($remaining !== null)
                &mdash; <span class="text-{{ $remaining <= 2 ? 'danger' : 'success' }}">
                    {{ $remaining }} {{ $isAr ? 'متبقية' : 'remaining' }}
                </span>
            @endif
        </span>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'إضافة منتج جديد' : 'Create New Product' }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('distributor.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf

                <div class="row g-3">
                    <!-- Product Name (Arabic) - First -->
                    <div class="col-md-6">
                        <label for="name_ar" class="form-label">{{ app()->getLocale() == 'ar' ? 'اسم المنتج (عربي)' : 'Product Name (Arabic)' }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required>
                        @error('name_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Product Name (English) - Second -->
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ app()->getLocale() == 'ar' ? 'اسم المنتج (إنجليزي)' : 'Product Name (English)' }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Main Product Photo -->
                    <div class="col-12">
                        <label for="photo" class="form-label">{{ app()->getLocale() == 'ar' ? 'الصورة الرئيسية للمنتج' : 'Main Product Photo' }} <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*" onchange="previewMainImage(event)" required>
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="mainImagePreview" class="mt-2" style="display: none;">
                            <img id="mainPreview" src="" alt="Preview" class="rounded" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                        </div>
                    </div>

                    <!-- Additional Product Images (Max 5) -->
                    <div class="col-12">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'صور إضافية للمنتج' : 'Additional Product Images' }} <small class="text-muted">({{ app()->getLocale() == 'ar' ? 'حد أقصى 5 صور' : 'Max 5 images' }})</small></label>
                        <div class="additional-images-container">
                            <div class="row g-2" id="additionalImagesRow">
                                @for($i = 0; $i < 5; $i++)
                                <div class="col-auto additional-image-wrapper" id="imageWrapper{{ $i }}" style="{{ $i > 0 ? 'display: none;' : '' }}">
                                    <div class="image-upload-box" onclick="document.getElementById('additional_image_{{ $i }}').click()">
                                        <input type="file" class="d-none additional-image-input" id="additional_image_{{ $i }}" name="additional_images[]" accept="image/*" onchange="previewAdditionalImage(event, {{ $i }})">
                                        <div class="image-preview-box" id="additionalPreviewBox{{ $i }}">
                                            <i class="ri-add-line"></i>
                                            <span>{{ app()->getLocale() == 'ar' ? 'إضافة صورة' : 'Add Image' }}</span>
                                        </div>
                                        <img id="additionalPreview{{ $i }}" src="" alt="Preview" class="additional-preview-img" style="display: none;">
                                        <button type="button" class="btn-remove-image" id="removeBtn{{ $i }}" style="display: none;" onclick="removeAdditionalImage(event, {{ $i }})">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                        @error('additional_images')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('additional_images.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Product Video (Optional) -->
                    <div class="col-12">
                        <label for="video" class="form-label">{{ app()->getLocale() == 'ar' ? 'فيديو المنتج' : 'Product Video' }} <small class="text-muted">({{ app()->getLocale() == 'ar' ? 'اختياري' : 'Optional' }})</small></label>
                        <input type="file" class="form-control @error('video') is-invalid @enderror" id="video" name="video" accept="video/mp4,video/webm,video/ogg">
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الصيغ المدعومة: MP4, WebM, OGG - الحد الأقصى: 50MB' : 'Supported formats: MP4, WebM, OGG - Max: 50MB' }}</small>
                        @error('video')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="videoPreview" class="mt-2" style="display: none;">
                            <video id="videoPlayer" controls style="max-width: 300px; max-height: 200px;">
                                <source id="videoSource" src="" type="video/mp4">
                            </video>
                            <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeVideo()">
                                <i class="ri-delete-bin-line"></i> {{ app()->getLocale() == 'ar' ? 'إزالة' : 'Remove' }}
                            </button>
                        </div>
                    </div>

                    <!-- Main Category (الفئة الرئيسية) -->
                    <div class="col-md-6">
                        <label for="parent_category_id" class="form-label">{{ app()->getLocale() == 'ar' ? 'الفئة الرئيسية' : 'Main Category' }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('parent_category_id') is-invalid @enderror" id="parent_category_id" name="parent_category_id" required>
                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر الفئة الرئيسية' : 'Select Main Category' }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('parent_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() == 'ar' && $category->name_ar ? $category->name_ar : $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Subcategory (الفئة الفرعية) -->
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">{{ app()->getLocale() == 'ar' ? 'الفئة الفرعية' : 'Subcategory' }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required disabled>
                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر الفئة الرئيسية أولاً' : 'Select Main Category First' }}</option>
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- SKU -->
                    <div class="col-md-6">
                        <label for="sku" class="form-label">{{ app()->getLocale() == 'ar' ? 'رمز المنتج (SKU)' : 'SKU' }}</label>
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku') }}">
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Short Description (Arabic) -->
                    <div class="col-md-6">
                        <label for="short_description_ar" class="form-label">{{ app()->getLocale() == 'ar' ? 'وصف مختصر (عربي)' : 'Short Description (Arabic)' }}</label>
                        <textarea class="form-control @error('short_description_ar') is-invalid @enderror" id="short_description_ar" name="short_description_ar" rows="3">{{ old('short_description_ar') }}</textarea>
                        @error('short_description_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Short Description (English) -->
                    <div class="col-md-6">
                        <label for="short_description" class="form-label">{{ app()->getLocale() == 'ar' ? 'وصف مختصر (إنجليزي)' : 'Short Description (English)' }}</label>
                        <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="3">{{ old('short_description') }}</textarea>
                        @error('short_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description (Arabic) -->
                    <div class="col-md-6">
                        <label for="description_ar" class="form-label">{{ app()->getLocale() == 'ar' ? 'الوصف الكامل (عربي)' : 'Full Description (Arabic)' }} <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar" rows="5" required>{{ old('description_ar') }}</textarea>
                        @error('description_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description (English) -->
                    <div class="col-md-6">
                        <label for="description" class="form-label">{{ app()->getLocale() == 'ar' ? 'الوصف الكامل (إنجليزي)' : 'Full Description (English)' }} <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Has Variations Toggle -->
                    <div class="col-12">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="has_variations" name="has_variations" value="1" {{ old('has_variations') ? 'checked' : '' }} onchange="toggleVariations()">
                                    <label class="form-check-label fw-bold" for="has_variations">
                                        {{ app()->getLocale() == 'ar' ? 'هل المنتج به أحجام أو ألوان مختلفة؟' : 'Does this product have different sizes or colors?' }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Simple Price & Quantity (shown when no variations) -->
                    <div class="col-12" id="simplePriceSection">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">
                                    {{ app()->getLocale() == 'ar' ? 'السعر' : 'Price' }} <span class="text-danger">*</span>
                                    <span class="badge bg-primary ms-2">
                                        @if(auth()->user()->default_currency === 'AED')
                                            <x-dirham-icon width="14" height="14" /> AED
                                        @elseif(auth()->user()->default_currency === 'SAR')
                                            <x-riyal-icon width="14" height="14" /> SAR
                                        @elseif(auth()->user()->default_currency === 'USD')
                                            <x-dollar-icon width="14" height="14" /> USD
                                        @else
                                            {{ auth()->user()->default_currency ?? 'AED' }}
                                        @endif
                                    </span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        @if(auth()->user()->default_currency === 'AED')
                                            <x-dirham-icon width="18" height="18" />
                                        @elseif(auth()->user()->default_currency === 'SAR')
                                            <x-riyal-icon width="18" height="18" />
                                        @elseif(auth()->user()->default_currency === 'USD')
                                            <x-dollar-icon width="18" height="18" />
                                        @else
                                            {{ auth()->user()->default_currency ?? 'د.إ' }}
                                        @endif
                                    </span>
                                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}">
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <input type="hidden" name="currency" value="{{ auth()->user()->default_currency ?? 'AED' }}">
                            </div>

                            <div class="col-md-6">
                                <label for="stock_quantity" class="form-label">{{ app()->getLocale() == 'ar' ? 'الكمية المتوفرة' : 'Stock Quantity' }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}">
                                @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Variations Table (shown when has variations) -->
                    <div class="col-12" id="variationsSection" style="display: none;">
                        <div class="card border">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الاختلافات (الأحجام / الألوان)' : 'Variations (Sizes / Colors)' }}</h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addVariationRow()">
                                    <i class="ri-add-line"></i> {{ app()->getLocale() == 'ar' ? 'إضافة اختلاف' : 'Add Variation' }}
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="variationsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 40%;">{{ app()->getLocale() == 'ar' ? 'الاختلاف (مثال: أحمر - كبير)' : 'Variation (e.g. Red - Large)' }}</th>
                                                <th style="width: 25%;">
                                                    {{ app()->getLocale() == 'ar' ? 'السعر' : 'Price' }}
                                                    <span class="badge bg-secondary">
                                                        @if(auth()->user()->default_currency === 'AED')
                                                            <x-dirham-icon width="12" height="12" />
                                                        @elseif(auth()->user()->default_currency === 'SAR')
                                                            <x-riyal-icon width="12" height="12" />
                                                        @elseif(auth()->user()->default_currency === 'USD')
                                                            <x-dollar-icon width="12" height="12" />
                                                        @endif
                                                        {{ auth()->user()->default_currency ?? 'AED' }}
                                                    </span>
                                                </th>
                                                <th style="width: 20%;">{{ app()->getLocale() == 'ar' ? 'الكمية' : 'Quantity' }}</th>
                                                <th style="width: 15%;">{{ app()->getLocale() == 'ar' ? 'إجراء' : 'Action' }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="variationsBody">
                                            <tr class="variation-row">
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="variations[0][name]" placeholder="{{ app()->getLocale() == 'ar' ? 'مثال: أحمر - كبير' : 'e.g. Red - Large' }}">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control form-control-sm" name="variations[0][price]" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="variations[0][quantity]" placeholder="0" value="0">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariationRow(this)" disabled>
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Track Inventory -->
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="track_inventory" name="track_inventory" value="1" {{ old('track_inventory', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_inventory">
                                {{ app()->getLocale() == 'ar' ? 'تتبع المخزون' : 'Track Inventory' }}
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'حفظ المنتج' : 'Save Product' }}
                        </button>
                        <a href="{{ route('distributor.products') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.image-upload-box {
    position: relative;
    width: 120px;
    height: 120px;
    cursor: pointer;
}

.image-preview-box {
    width: 120px;
    height: 120px;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    transition: all 0.3s;
}

.image-preview-box:hover {
    border-color: #0d6efd;
    background: #e7f1ff;
}

.image-preview-box i {
    font-size: 24px;
    color: #6c757d;
    margin-bottom: 5px;
}

.image-preview-box span {
    font-size: 12px;
    color: #6c757d;
}

.additional-preview-img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #dee2e6;
}

.btn-remove-image {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #dc3545;
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    z-index: 10;
}

.btn-remove-image:hover {
    background: #bb2d3b;
}

.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
}

.form-switch .form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

#variationsTable input {
    min-width: 100px;
}
</style>

@push('scripts')
<script>
// Main image preview
function previewMainImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('mainPreview');
    const previewContainer = document.getElementById('mainImagePreview');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        previewContainer.style.display = 'none';
    }
}

// Additional images handling
let additionalImagesCount = 1;

function previewAdditionalImage(event, index) {
    event.stopPropagation();
    const file = event.target.files[0];
    const preview = document.getElementById('additionalPreview' + index);
    const previewBox = document.getElementById('additionalPreviewBox' + index);
    const removeBtn = document.getElementById('removeBtn' + index);

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            previewBox.style.display = 'none';
            removeBtn.style.display = 'flex';

            // Show next upload box if available
            showNextUploadBox();
        }
        reader.readAsDataURL(file);
    }
}

function removeAdditionalImage(event, index) {
    event.stopPropagation();
    const input = document.getElementById('additional_image_' + index);
    const preview = document.getElementById('additionalPreview' + index);
    const previewBox = document.getElementById('additionalPreviewBox' + index);
    const removeBtn = document.getElementById('removeBtn' + index);

    input.value = '';
    preview.src = '';
    preview.style.display = 'none';
    previewBox.style.display = 'flex';
    removeBtn.style.display = 'none';

    reorderUploadBoxes();
}

function showNextUploadBox() {
    for (let i = 0; i < 5; i++) {
        const wrapper = document.getElementById('imageWrapper' + i);
        const input = document.getElementById('additional_image_' + i);
        if (wrapper.style.display === 'none') {
            wrapper.style.display = 'block';
            break;
        }
    }
}

function reorderUploadBoxes() {
    // Count visible boxes with images
    let visibleWithImages = 0;
    for (let i = 0; i < 5; i++) {
        const input = document.getElementById('additional_image_' + i);
        if (input.files.length > 0) {
            visibleWithImages++;
        }
    }

    // Show one empty box after the last image (if not at max)
    let shownEmpty = false;
    for (let i = 0; i < 5; i++) {
        const wrapper = document.getElementById('imageWrapper' + i);
        const input = document.getElementById('additional_image_' + i);

        if (input.files.length > 0) {
            wrapper.style.display = 'block';
        } else if (!shownEmpty && visibleWithImages < 5) {
            wrapper.style.display = 'block';
            shownEmpty = true;
        } else {
            wrapper.style.display = 'none';
        }
    }
}

// Video preview
document.getElementById('video').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('videoPreview');
    const videoSource = document.getElementById('videoSource');
    const videoPlayer = document.getElementById('videoPlayer');

    if (file) {
        if (file.size > 50 * 1024 * 1024) { // 50MB
            alert('{{ app()->getLocale() == "ar" ? "حجم الفيديو يجب أن يكون أقل من 50MB" : "Video size must be less than 50MB" }}');
            this.value = '';
            return;
        }

        const url = URL.createObjectURL(file);
        videoSource.src = url;
        videoPlayer.load();
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});

function removeVideo() {
    const input = document.getElementById('video');
    const preview = document.getElementById('videoPreview');
    input.value = '';
    preview.style.display = 'none';
}

// Toggle variations
function toggleVariations() {
    const hasVariations = document.getElementById('has_variations').checked;
    const simpleSection = document.getElementById('simplePriceSection');
    const variationsSection = document.getElementById('variationsSection');

    if (hasVariations) {
        simpleSection.style.display = 'none';
        variationsSection.style.display = 'block';
        // Remove required from simple fields
        document.getElementById('price').removeAttribute('required');
        document.getElementById('stock_quantity').removeAttribute('required');
    } else {
        simpleSection.style.display = 'block';
        variationsSection.style.display = 'none';
        // Add required back to simple fields
        document.getElementById('price').setAttribute('required', 'required');
        document.getElementById('stock_quantity').setAttribute('required', 'required');
    }
}

// Variations table
let variationIndex = 1;

function addVariationRow() {
    const tbody = document.getElementById('variationsBody');
    const row = document.createElement('tr');
    row.className = 'variation-row';
    row.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm" name="variations[${variationIndex}][name]" placeholder="{{ app()->getLocale() == 'ar' ? 'مثال: أحمر - كبير' : 'e.g. Red - Large' }}">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control form-control-sm" name="variations[${variationIndex}][price]" placeholder="0.00">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" name="variations[${variationIndex}][quantity]" placeholder="0" value="0">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariationRow(this)">
                <i class="ri-delete-bin-line"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    variationIndex++;
    updateRemoveButtons();
}

function removeVariationRow(btn) {
    btn.closest('tr').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.variation-row');
    rows.forEach((row, index) => {
        const btn = row.querySelector('.btn-outline-danger');
        btn.disabled = rows.length === 1;
    });
}

// Dynamic subcategory loading
document.getElementById('parent_category_id').addEventListener('change', function() {
    const parentId = this.value;
    const subcategorySelect = document.getElementById('category_id');
    const locale = '{{ app()->getLocale() }}';

    subcategorySelect.innerHTML = '<option value="">' + (locale === 'ar' ? 'جاري التحميل...' : 'Loading...') + '</option>';
    subcategorySelect.disabled = true;

    if (parentId) {
        fetch(`/distributor/categories/${parentId}/subcategories`)
            .then(response => response.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">' + (locale === 'ar' ? 'اختر الفئة الفرعية' : 'Select Subcategory') + '</option>';

                if (data.length > 0) {
                    data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = (locale === 'ar' && subcategory.name_ar) ? subcategory.name_ar : subcategory.name;
                        subcategorySelect.appendChild(option);
                    });
                    subcategorySelect.disabled = false;
                } else {
                    subcategorySelect.innerHTML = '<option value="">' + (locale === 'ar' ? 'لا توجد فئات فرعية' : 'No subcategories available') + '</option>';
                }
            })
            .catch(error => {
                console.error('Error loading subcategories:', error);
                subcategorySelect.innerHTML = '<option value="">' + (locale === 'ar' ? 'خطأ في التحميل' : 'Error loading') + '</option>';
            });
    } else {
        subcategorySelect.innerHTML = '<option value="">' + (locale === 'ar' ? 'اختر الفئة الرئيسية أولاً' : 'Select Main Category First') + '</option>';
    }
});

// Load on page ready
document.addEventListener('DOMContentLoaded', function() {
    const parentSelect = document.getElementById('parent_category_id');
    const oldCategoryId = '{{ old("category_id") }}';

    if (parentSelect.value && oldCategoryId) {
        parentSelect.dispatchEvent(new Event('change'));
        setTimeout(function() {
            document.getElementById('category_id').value = oldCategoryId;
        }, 500);
    }

    // Initialize variations toggle
    toggleVariations();
});
</script>
@endpush

@endsection
