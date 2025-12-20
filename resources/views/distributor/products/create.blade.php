@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'إضافة منتج جديد' : 'Create New Product' }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('distributor.products.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <!-- Product Name (English) -->
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ app()->getLocale() == 'ar' ? 'اسم المنتج (إنجليزي)' : 'Product Name (English)' }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Product Name (Arabic) -->
                    <div class="col-md-6">
                        <label for="name_ar" class="form-label">{{ app()->getLocale() == 'ar' ? 'اسم المنتج (عربي)' : 'Product Name (Arabic)' }}</label>
                        <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar" value="{{ old('name_ar') }}">
                        @error('name_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر التصنيف' : 'Select Category' }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() == 'ar' && $category->name_ar ? $category->name_ar : $category->name }}
                                </option>
                            @endforeach
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

                    <!-- Price -->
                    <div class="col-md-4">
                        <label for="price" class="form-label">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div class="col-md-4">
                        <label for="currency" class="form-label">{{ app()->getLocale() == 'ar' ? 'العملة' : 'Currency' }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                            <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>AED</option>
                            <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>SAR</option>
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Stock Quantity -->
                    <div class="col-md-4">
                        <label for="stock_quantity" class="form-label">{{ app()->getLocale() == 'ar' ? 'الكمية المتوفرة' : 'Stock Quantity' }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" required>
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

                    <!-- Short Description (English) -->
                    <div class="col-md-6">
                        <label for="short_description" class="form-label">{{ app()->getLocale() == 'ar' ? 'وصف مختصر (إنجليزي)' : 'Short Description (English)' }}</label>
                        <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="3">{{ old('short_description') }}</textarea>
                        @error('short_description')
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

                    <!-- Description (English) -->
                    <div class="col-md-6">
                        <label for="description" class="form-label">{{ app()->getLocale() == 'ar' ? 'الوصف الكامل (إنجليزي)' : 'Full Description (English)' }} <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description (Arabic) -->
                    <div class="col-md-6">
                        <label for="description_ar" class="form-label">{{ app()->getLocale() == 'ar' ? 'الوصف الكامل (عربي)' : 'Full Description (Arabic)' }}</label>
                        <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar" rows="5">{{ old('description_ar') }}</textarea>
                        @error('description_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
@endsection
