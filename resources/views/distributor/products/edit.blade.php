@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'تعديل المنتج' : 'Edit Product' }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('distributor.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Product Name (English) -->
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ app()->getLocale() == 'ar' ? 'اسم المنتج (إنجليزي)' : 'Product Name (English)' }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Product Name (Arabic) -->
                    <div class="col-md-6">
                        <label for="name_ar" class="form-label">{{ app()->getLocale() == 'ar' ? 'اسم المنتج (عربي)' : 'Product Name (Arabic)' }}</label>
                        <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar" value="{{ old('name_ar', $product->name_ar) }}">
                        @error('name_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Product Photo -->
                    <div class="col-12">
                        <label for="photo" class="form-label">{{ app()->getLocale() == 'ar' ? 'صورة المنتج' : 'Product Photo' }}</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*" onchange="previewImage(event)">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <!-- Current Photo -->
                        @if($product->photo)
                            <div class="mt-2">
                                <label class="form-label small text-muted">{{ app()->getLocale() == 'ar' ? 'الصورة الحالية' : 'Current Photo' }}:</label>
                                <div>
                                    <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="rounded" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                </div>
                            </div>
                        @endif

                        <!-- New Photo Preview -->
                        <div id="imagePreview" class="mt-2" style="display: none;">
                            <label class="form-label small text-muted">{{ app()->getLocale() == 'ar' ? 'الصورة الجديدة' : 'New Photo Preview' }}:</label>
                            <div>
                                <img id="preview" src="" alt="Preview" class="rounded" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                            </div>
                        </div>
                    </div>

                    <!-- Main Category -->
                    <div class="col-md-6">
                        <label for="parent_category_id" class="form-label">{{ app()->getLocale() == 'ar' ? 'التصنيف الرئيسي' : 'Main Category' }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('parent_category_id') is-invalid @enderror" id="parent_category_id" name="parent_category_id" required>
                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر التصنيف الرئيسي' : 'Select Main Category' }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('parent_category_id', $parentCategory ? $parentCategory->id : '') == $category->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() == 'ar' && $category->name_ar ? $category->name_ar : $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Subcategory -->
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">{{ app()->getLocale() == 'ar' ? 'التصنيف الفرعي' : 'Subcategory' }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر التصنيف الرئيسي أولاً' : 'Select Main Category First' }}</option>
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- SKU -->
                    <div class="col-md-6">
                        <label for="sku" class="form-label">{{ app()->getLocale() == 'ar' ? 'رمز المنتج (SKU)' : 'SKU' }}</label>
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Price -->
                    <div class="col-md-4">
                        <label for="price" class="form-label">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div class="col-md-4">
                        <label for="currency" class="form-label">{{ app()->getLocale() == 'ar' ? 'العملة' : 'Currency' }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                            <option value="USD" {{ old('currency', $product->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="AED" {{ old('currency', $product->currency) == 'AED' ? 'selected' : '' }}>AED</option>
                            <option value="SAR" {{ old('currency', $product->currency) == 'SAR' ? 'selected' : '' }}>SAR</option>
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Stock Quantity -->
                    <div class="col-md-4">
                        <label for="stock_quantity" class="form-label">{{ app()->getLocale() == 'ar' ? 'الكمية المتوفرة' : 'Stock Quantity' }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Track Inventory -->
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="track_inventory" name="track_inventory" value="1" {{ old('track_inventory', $product->track_inventory) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_inventory">
                                {{ app()->getLocale() == 'ar' ? 'تتبع المخزون' : 'Track Inventory' }}
                            </label>
                        </div>
                    </div>

                    <!-- Short Description (English) -->
                    <div class="col-md-6">
                        <label for="short_description" class="form-label">{{ app()->getLocale() == 'ar' ? 'وصف مختصر (إنجليزي)' : 'Short Description (English)' }}</label>
                        <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="3">{{ old('short_description', $product->short_description) }}</textarea>
                        @error('short_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Short Description (Arabic) -->
                    <div class="col-md-6">
                        <label for="short_description_ar" class="form-label">{{ app()->getLocale() == 'ar' ? 'وصف مختصر (عربي)' : 'Short Description (Arabic)' }}</label>
                        <textarea class="form-control @error('short_description_ar') is-invalid @enderror" id="short_description_ar" name="short_description_ar" rows="3">{{ old('short_description_ar', $product->short_description_ar) }}</textarea>
                        @error('short_description_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description (English) -->
                    <div class="col-md-6">
                        <label for="description" class="form-label">{{ app()->getLocale() == 'ar' ? 'الوصف الكامل (إنجليزي)' : 'Full Description (English)' }} <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description (Arabic) -->
                    <div class="col-md-6">
                        <label for="description_ar" class="form-label">{{ app()->getLocale() == 'ar' ? 'الوصف الكامل (عربي)' : 'Full Description (Arabic)' }}</label>
                        <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar" rows="5">{{ old('description_ar', $product->description_ar) }}</textarea>
                        @error('description_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'تحديث المنتج' : 'Update Product' }}
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

@push('scripts')
<script>
// Image preview functionality
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');

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

// Dynamic subcategory loading
document.getElementById('parent_category_id').addEventListener('change', function() {
    const parentId = this.value;
    const subcategorySelect = document.getElementById('category_id');
    const locale = '{{ app()->getLocale() }}';

    // Reset subcategory dropdown
    subcategorySelect.innerHTML = '<option value="">' + (locale === 'ar' ? 'جاري التحميل...' : 'Loading...') + '</option>';
    subcategorySelect.disabled = true;

    if (parentId) {
        // Fetch subcategories via AJAX
        fetch(`/distributor/categories/${parentId}/subcategories`)
            .then(response => response.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">' + (locale === 'ar' ? 'اختر التصنيف الفرعي' : 'Select Subcategory') + '</option>';

                if (data.length > 0) {
                    data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = (locale === 'ar' && subcategory.name_ar) ? subcategory.name_ar : subcategory.name;

                        // Select the current product's category
                        if (subcategory.id == {{ $product->category_id ?? 'null' }}) {
                            option.selected = true;
                        }

                        subcategorySelect.appendChild(option);
                    });
                    subcategorySelect.disabled = false;
                } else {
                    subcategorySelect.innerHTML = '<option value="">' + (locale === 'ar' ? 'لا توجد تصنيفات فرعية' : 'No subcategories available') + '</option>';
                }
            })
            .catch(error => {
                console.error('Error loading subcategories:', error);
                subcategorySelect.innerHTML = '<option value="">' + (locale === 'ar' ? 'خطأ في التحميل' : 'Error loading') + '</option>';
            });
    } else {
        subcategorySelect.innerHTML = '<option value="">' + (locale === 'ar' ? 'اختر التصنيف الرئيسي أولاً' : 'Select Main Category First') + '</option>';
    }
});

// Load subcategories on page load
document.addEventListener('DOMContentLoaded', function() {
    const parentSelect = document.getElementById('parent_category_id');

    // Trigger change event to load subcategories for the current parent category
    if (parentSelect.value) {
        parentSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush

@endsection
