@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('distributor.dashboard') }}">{{ app()->getLocale() == 'ar' ? 'لوحة التحكم' : 'Dashboard' }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('distributor.products') }}">{{ app()->getLocale() == 'ar' ? 'المنتجات' : 'Products' }}</a></li>
                            <li class="breadcrumb-item active">{{ $product->localizedName }}</li>
                        </ol>
                    </nav>
                    <h4 class="mb-0">{{ app()->getLocale() == 'ar' ? 'تفاصيل المنتج' : 'Product Details' }}</h4>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('distributor.products.edit', $product->id) }}" class="btn btn-primary">
                        <i class="ri-edit-line me-1"></i>
                        {{ app()->getLocale() == 'ar' ? 'تعديل المنتج' : 'Edit Product' }}
                    </a>
                    <a href="{{ route('distributor.products') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}-line me-1"></i>
                        {{ app()->getLocale() == 'ar' ? 'العودة للمنتجات' : 'Back to Products' }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Product Images Section -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-body">
                    <!-- Main Image -->
                    <div class="main-product-image mb-3 text-center">
                        @if($product->photo)
                            <img id="mainImage" src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 400px; object-fit: contain; width: 100%;">
                        @elseif($product->images && count($product->images) > 0)
                            <img id="mainImage" src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 400px; object-fit: contain; width: 100%;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                <i class="ri-image-line text-muted" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Thumbnail Gallery -->
                    @php
                        $allImages = [];
                        if ($product->photo) {
                            $allImages[] = ['type' => 'local', 'path' => $product->photo];
                        }
                        if ($product->additionalImages && $product->additionalImages->count() > 0) {
                            foreach ($product->additionalImages as $img) {
                                $allImages[] = ['type' => 'local', 'path' => $img->image_path];
                            }
                        }
                        if ($product->images && count($product->images) > 0) {
                            foreach ($product->images as $img) {
                                $allImages[] = ['type' => 'external', 'path' => $img];
                            }
                        }
                    @endphp

                    @if(count($allImages) > 1)
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            @foreach($allImages as $index => $img)
                                <div class="thumbnail-item {{ $index === 0 ? 'active' : '' }}"
                                     onclick="changeMainImage(this, '{{ $img['type'] === 'local' ? asset('storage/' . $img['path']) : $img['path'] }}')"
                                     style="cursor: pointer; border: 2px solid {{ $index === 0 ? '#1e40af' : '#e5e7eb' }}; border-radius: 8px; overflow: hidden;">
                                    <img src="{{ $img['type'] === 'local' ? asset('storage/' . $img['path']) : $img['path'] }}"
                                         alt="Image {{ $index + 1 }}"
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Product Video -->
                    @if($product->video)
                        <div class="mt-4">
                            <h6 class="mb-3">
                                <i class="ri-video-line me-2"></i>
                                {{ app()->getLocale() == 'ar' ? 'فيديو المنتج' : 'Product Video' }}
                            </h6>
                            <video controls class="w-100 rounded" style="max-height: 300px;">
                                <source src="{{ asset('storage/' . $product->video) }}" type="video/mp4">
                                {{ app()->getLocale() == 'ar' ? 'متصفحك لا يدعم تشغيل الفيديو' : 'Your browser does not support the video tag.' }}
                            </video>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Product Details Section -->
        <div class="col-lg-7">
            <!-- Basic Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'معلومات المنتج' : 'Product Information' }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Arabic Name -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'اسم المنتج (عربي)' : 'Product Name (Arabic)' }}</label>
                            <p class="mb-0 fw-semibold">{{ $product->name_ar ?: '-' }}</p>
                        </div>

                        <!-- English Name -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'اسم المنتج (إنجليزي)' : 'Product Name (English)' }}</label>
                            <p class="mb-0 fw-semibold">{{ $product->name ?: '-' }}</p>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'الفئة' : 'Category' }}</label>
                            <p class="mb-0">
                                @if($product->category)
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ app()->getLocale() == 'ar' ? ($product->category->name_ar ?: $product->category->name) : $product->category->name }}
                                    </span>
                                @else
                                    -
                                @endif
                            </p>
                        </div>

                        <!-- SKU -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'رمز المنتج (SKU)' : 'SKU' }}</label>
                            <p class="mb-0 font-monospace">{{ $product->sku ?: '-' }}</p>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                    {{ $product->is_active ? (app()->getLocale() == 'ar' ? 'نشط' : 'Active') : (app()->getLocale() == 'ar' ? 'غير نشط' : 'Inactive') }}
                                </span>
                            </p>
                        </div>

                        <!-- Track Inventory -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'تتبع المخزون' : 'Track Inventory' }}</label>
                            <p class="mb-0">
                                @if($product->track_inventory)
                                    <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? 'مفعل' : 'Enabled' }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ app()->getLocale() == 'ar' ? 'معطل' : 'Disabled' }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing & Stock Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'التسعير والمخزون' : 'Pricing & Stock' }}</h5>
                </div>
                <div class="card-body">
                    @if($product->has_variations && $product->variations->count() > 0)
                        <!-- Product has variations -->
                        <div class="alert alert-info mb-3">
                            <i class="ri-information-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'هذا المنتج له متغيرات متعددة' : 'This product has multiple variations' }}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ app()->getLocale() == 'ar' ? 'المتغير' : 'Variation' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'السعر' : 'Price' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'الكمية' : 'Quantity' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->variations as $variation)
                                        <tr>
                                            <td>
                                                <strong>{{ $variation->name }}</strong>
                                                @if($variation->sku)
                                                    <br><small class="text-muted font-monospace">{{ $variation->sku }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="d-inline-flex align-items-center gap-1">
                                                    @if($product->currency == 'AED')
                                                        <x-dirham-icon width="16" height="16" />
                                                    @elseif($product->currency == 'SAR')
                                                        <x-riyal-icon width="16" height="16" />
                                                    @elseif($product->currency == 'USD')
                                                        <x-dollar-icon width="16" height="16" />
                                                    @else
                                                        {{ $product->currency }}
                                                    @endif
                                                    {{ number_format($variation->price, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $variation->quantity > 10 ? 'success' : ($variation->quantity > 0 ? 'warning' : 'danger') }}">
                                                    {{ $variation->quantity }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $variation->is_active ? 'success' : 'secondary' }}">
                                                    {{ $variation->is_active ? (app()->getLocale() == 'ar' ? 'نشط' : 'Active') : (app()->getLocale() == 'ar' ? 'غير نشط' : 'Inactive') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td><strong>{{ app()->getLocale() == 'ar' ? 'الإجمالي' : 'Total' }}</strong></td>
                                        <td>
                                            <span class="d-inline-flex align-items-center gap-1">
                                                @if($product->currency == 'AED')
                                                    <x-dirham-icon width="16" height="16" />
                                                @elseif($product->currency == 'SAR')
                                                    <x-riyal-icon width="16" height="16" />
                                                @elseif($product->currency == 'USD')
                                                    <x-dollar-icon width="16" height="16" />
                                                @else
                                                    {{ $product->currency }}
                                                @endif
                                                {{ number_format($product->price, 2) }}
                                            </span>
                                            <small class="text-muted">({{ app()->getLocale() == 'ar' ? 'أقل سعر' : 'min price' }})</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $product->variations->sum('quantity') }}</span>
                                        </td>
                                        <td>-</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <!-- Simple product without variations -->
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <label class="form-label text-muted small d-block">{{ app()->getLocale() == 'ar' ? 'السعر' : 'Price' }}</label>
                                    <h4 class="mb-0 text-primary d-inline-flex align-items-center gap-1">
                                        @if($product->currency == 'AED')
                                            <x-dirham-icon width="24" height="24" />
                                        @elseif($product->currency == 'SAR')
                                            <x-riyal-icon width="24" height="24" />
                                        @elseif($product->currency == 'USD')
                                            <x-dollar-icon width="24" height="24" />
                                        @else
                                            {{ $product->currency }}
                                        @endif
                                        {{ number_format($product->price, 2) }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <label class="form-label text-muted small d-block">{{ app()->getLocale() == 'ar' ? 'المخزون' : 'Stock' }}</label>
                                    @if($product->track_inventory)
                                        <h4 class="mb-0">
                                            <span class="badge bg-{{ $product->stock_quantity > 10 ? 'success' : ($product->stock_quantity > 0 ? 'warning' : 'danger') }}" style="font-size: 1.25rem;">
                                                {{ $product->stock_quantity }}
                                            </span>
                                        </h4>
                                    @else
                                        <h4 class="mb-0">
                                            <span class="badge bg-info" style="font-size: 1rem;">{{ app()->getLocale() == 'ar' ? 'غير محدود' : 'Unlimited' }}</span>
                                        </h4>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <label class="form-label text-muted small d-block">{{ app()->getLocale() == 'ar' ? 'العملة' : 'Currency' }}</label>
                                    <h4 class="mb-0">
                                        @if($product->currency == 'AED')
                                            <x-dirham-icon width="28" height="28" /> AED
                                        @elseif($product->currency == 'SAR')
                                            <x-riyal-icon width="28" height="28" /> SAR
                                        @elseif($product->currency == 'USD')
                                            <x-dollar-icon width="28" height="28" /> USD
                                        @else
                                            {{ $product->currency }}
                                        @endif
                                    </h4>
                                </div>
                            </div>
                        </div>

                        @if($product->original_price && $product->original_price != $product->price)
                            <div class="mt-3">
                                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'السعر الأصلي:' : 'Original Price:' }}</small>
                                <span class="text-decoration-line-through text-muted">
                                    {{ $product->currency }} {{ number_format($product->original_price, 2) }}
                                </span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Description Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الوصف' : 'Description' }}</h5>
                </div>
                <div class="card-body">
                    <!-- Short Description -->
                    @if($product->short_description_ar || $product->short_description)
                        <div class="mb-4">
                            <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'الوصف المختصر' : 'Short Description' }}</label>
                            <p class="mb-0">{{ app()->getLocale() == 'ar' ? ($product->short_description_ar ?: $product->short_description) : ($product->short_description ?: $product->short_description_ar) }}</p>
                        </div>
                    @endif

                    <!-- Full Description Arabic -->
                    <div class="mb-4">
                        <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'الوصف الكامل (عربي)' : 'Full Description (Arabic)' }}</label>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($product->description_ar ?: '-')) !!}
                        </div>
                    </div>

                    <!-- Full Description English -->
                    <div>
                        <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'الوصف الكامل (إنجليزي)' : 'Full Description (English)' }}</label>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($product->description ?: '-')) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Statistics Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'معلومات إضافية' : 'Additional Information' }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</label>
                            <p class="mb-0">{{ $product->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'آخر تحديث' : 'Last Updated' }}</label>
                            <p class="mb-0">{{ $product->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                        @if($product->seller_amount)
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'نصيب المتجر' : 'Distributor Share' }}</label>
                                <p class="mb-0 text-success">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        @if($product->currency == 'AED')
                                            <x-dirham-icon width="16" height="16" />
                                        @elseif($product->currency == 'SAR')
                                            <x-riyal-icon width="16" height="16" />
                                        @elseif($product->currency == 'USD')
                                            <x-dollar-icon width="16" height="16" />
                                        @else
                                            {{ $product->currency }}
                                        @endif
                                        {{ number_format($product->seller_amount, 2) }}
                                    </span>
                                    <small class="text-muted">(70%)</small>
                                </p>
                            </div>
                        @endif
                        @if($product->admin_amount)
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ app()->getLocale() == 'ar' ? 'عمولة المنصة' : 'Platform Commission' }}</label>
                                <p class="mb-0 text-info">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        @if($product->currency == 'AED')
                                            <x-dirham-icon width="16" height="16" />
                                        @elseif($product->currency == 'SAR')
                                            <x-riyal-icon width="16" height="16" />
                                        @elseif($product->currency == 'USD')
                                            <x-dollar-icon width="16" height="16" />
                                        @else
                                            {{ $product->currency }}
                                        @endif
                                        {{ number_format($product->admin_amount, 2) }}
                                    </span>
                                    <small class="text-muted">(30%)</small>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function changeMainImage(element, imageSrc) {
    // Update main image
    document.getElementById('mainImage').src = imageSrc;

    // Update active thumbnail
    document.querySelectorAll('.thumbnail-item').forEach(function(thumb) {
        thumb.style.borderColor = '#e5e7eb';
    });
    element.style.borderColor = '#1e40af';
}
</script>
@endpush

<style>
.thumbnail-item:hover {
    border-color: #1e40af !important;
    opacity: 0.8;
}
</style>
@endsection
