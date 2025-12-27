@extends('dashboard')

@section('title')
    {{ app()->getLocale() == 'ar' && $product->name_ar ? $product->name_ar : $product->name }} - {{ setting('site_name', 'EcommAli') }}
@endsection

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('products.my-assigned') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            {{ app()->getLocale() == 'ar' ? 'العودة للمنتجات' : 'Back to Products' }}
        </a>
    </div>

    <!-- Product Hero Section -->
    <div class="card shadow-lg mb-4" style="border-radius: 20px; overflow: hidden; border: none;">
        <div class="card-body p-0">
            <div class="row g-0">
                <!-- Product Images Gallery -->
                <div class="col-lg-5 bg-light p-4">
                    @php
                        $productImages = [];
                        if ($product->images && is_array($product->images) && count($product->images) > 0) {
                            $productImages = $product->images;
                        } elseif ($product->photo) {
                            $productImages = [asset('storage/' . $product->photo)];
                        }
                    @endphp

                    @if(count($productImages) > 0)
                        <div id="productGallery" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner" style="border-radius: 16px; overflow: hidden;">
                                @foreach($productImages as $index => $image)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ $image }}" class="d-block w-100" alt="{{ $product->name }}" style="height: 450px; object-fit: contain; background: white;">
                                    </div>
                                @endforeach
                            </div>
                            @if(count($productImages) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#productGallery" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#productGallery" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            @endif
                        </div>

                        <!-- Thumbnail Gallery -->
                        @if(count($productImages) > 1)
                            <div class="row mt-3 g-2">
                                @foreach($productImages as $index => $image)
                                    @if($index < 5)
                                        <div class="col">
                                            <img src="{{ $image }}"
                                                 class="img-thumbnail thumbnail-hover"
                                                 style="cursor: pointer; height: 70px; object-fit: cover; width: 100%;"
                                                 onclick="document.querySelector('#productGallery .carousel-control-next').click()">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 450px;">
                            <i class="ri-image-line" style="font-size: 64px; color: #ccc;"></i>
                        </div>
                    @endif
                </div>

                <!-- Product Details -->
                <div class="col-lg-7 p-4">
                    <!-- Local Product Badge -->
                    <div class="mb-3">
                        <span class="badge bg-success px-3 py-2" style="font-size: 14px;">
                            <i class="ri-store-2-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'منتج موزع محلي' : 'Local Distributor Product' }}
                        </span>
                        @if($distributor)
                            <span class="badge bg-info px-3 py-2 ms-2" style="font-size: 14px;">
                                @if($distributor->country == 'AE')
                                    <span class="me-1">🇦🇪</span>
                                @elseif($distributor->country == 'SA')
                                    <span class="me-1">🇸🇦</span>
                                @endif
                                {{ $distributor->store_name ?? $distributor->name }}
                            </span>
                        @endif
                    </div>

                    <h1 class="mb-3 fw-bold" style="font-size: 28px; line-height: 1.4;">
                        {{ app()->getLocale() == 'ar' && $product->name_ar ? $product->name_ar : $product->name }}
                    </h1>

                    @if($product->short_description || $product->short_description_ar)
                        <p class="text-muted mb-4" style="font-size: 15px;">
                            {{ app()->getLocale() == 'ar' && $product->short_description_ar ? $product->short_description_ar : $product->short_description }}
                        </p>
                    @endif

                    <!-- Pricing -->
                    <div class="card text-white mb-4 border-0" style="border-radius: 16px; background: linear-gradient(135deg, #00732f 0%, #00a040 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-baseline mb-2">
                                <h2 class="mb-0 me-3 fw-bold" style="font-size: 36px; color:white;">
                                    {{ $product->currency ?? 'AED' }} {{ number_format($product->price, 2) }}
                                </h2>
                                @if($product->original_price && $product->original_price > $product->price)
                                    <span class="text-white-50 text-decoration-line-through me-2" style="font-size: 20px;">
                                        {{ $product->currency ?? 'AED' }} {{ number_format($product->original_price, 2) }}
                                    </span>
                                    <span class="badge bg-danger" style="font-size: 14px;">
                                        {{ round((($product->original_price - $product->price) / $product->original_price) * 100) }}% OFF
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-white-50">
                                            <i class="ri-truck-line me-1"></i>
                                            {{ app()->getLocale() == 'ar' ? 'شحن محلي سريع' : 'Fast Local Shipping' }}
                                        </small>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-white-50">
                                            <i class="ri-shield-check-line me-1"></i>
                                            {{ app()->getLocale() == 'ar' ? 'ضمان الجودة' : 'Quality Guaranteed' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-3">
                        <a href="{{ route('orders.distributor.create', ['product_id' => $product->id]) }}" class="btn btn-lg btn-success shadow-sm" style="border-radius: 12px; padding: 16px;">
                            <i class="ri-shopping-bag-line me-2"></i> {{ app()->getLocale() == 'ar' ? 'إنشاء طلب محلي' : 'Create Local Order' }}
                        </a>

                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary w-100" style="border-radius: 10px;">
                                    <i class="ri-edit-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'تعديل المنتج' : 'Edit Product' }}
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('products.my-assigned') }}" class="btn btn-outline-secondary w-100" style="border-radius: 10px;">
                                    <i class="ri-arrow-left-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'العودة' : 'Back' }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Distributor Info -->
                    @if($distributor)
                        <div class="mt-4 p-3 bg-light rounded-3">
                            <h6 class="mb-2">
                                <i class="ri-store-2-line me-1 text-success"></i>
                                {{ app()->getLocale() == 'ar' ? 'معلومات الموزع' : 'Distributor Info' }}
                            </h6>
                            <div class="d-flex align-items-center">
                                @if($distributor->logo)
                                    <img src="{{ asset('storage/' . $distributor->logo) }}" alt="{{ $distributor->store_name }}" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="ri-store-2-line" style="font-size: 24px;"></i>
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $distributor->store_name ?? $distributor->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        @if($distributor->country == 'AE')
                                            <span class="me-1">🇦🇪</span> {{ app()->getLocale() == 'ar' ? 'الإمارات' : 'UAE' }}
                                        @elseif($distributor->country == 'SA')
                                            <span class="me-1">🇸🇦</span> {{ app()->getLocale() == 'ar' ? 'السعودية' : 'Saudi Arabia' }}
                                        @else
                                            {{ $distributor->country }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Section -->
    <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">
                <i class="ri-file-text-line me-2"></i>{{ app()->getLocale() == 'ar' ? 'الوصف' : 'Description' }}
            </button>
        </li>
        @if($product->variations && count($product->variations) > 0)
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button">
                    <i class="ri-list-check me-2"></i>{{ app()->getLocale() == 'ar' ? 'التنويعات' : 'Variants' }} ({{ count($product->variations) }})
                </button>
            </li>
        @endif
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Description Tab -->
        <div class="tab-pane fade show active" id="description">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    @if($product->description || $product->description_ar)
                        <div class="product-description">
                            {!! app()->getLocale() == 'ar' && $product->description_ar ? $product->description_ar : $product->description !!}
                        </div>
                    @else
                        <p class="text-muted text-center py-5">{{ app()->getLocale() == 'ar' ? 'لا يوجد وصف متاح' : 'No description available' }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Variants Tab -->
        @if($product->variations && count($product->variations) > 0)
            <div class="tab-pane fade" id="variants">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            @foreach($product->variations as $variation)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border h-100 hover-lift">
                                        <div class="card-body">
                                            @if($variation->image)
                                                <img src="{{ asset('storage/' . $variation->image) }}"
                                                     class="img-fluid rounded mb-3"
                                                     style="height: 180px; object-fit: cover; width: 100%;">
                                            @endif

                                            <div class="mb-3">
                                                <span class="badge bg-secondary mb-1 me-1">
                                                    {{ $variation->name }}
                                                </span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="text-primary fw-bold fs-5">
                                                    {{ $product->currency ?? 'AED' }} {{ number_format($variation->price ?? $product->price, 2) }}
                                                </div>
                                                <span class="badge {{ ($variation->stock ?? 0) > 0 ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                                    {{ ($variation->stock ?? 0) > 0 ? (($variation->stock ?? 0) . ' ' . (app()->getLocale() == 'ar' ? 'متوفر' : 'in stock')) : (app()->getLocale() == 'ar' ? 'غير متوفر' : 'Out of Stock') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.thumbnail-hover {
    transition: all 0.3s ease;
    opacity: 0.7;
}

.thumbnail-hover:hover {
    opacity: 1;
    transform: scale(1.05);
}

.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.product-description img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 15px 0;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 12px 20px;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #e9ecef;
    color: #495057;
}

.nav-tabs .nav-link.active {
    border-bottom-color: #00732f;
    color: #00732f;
    background: none;
}

/* Button Hover Styles */
.btn-success {
    background-color: #00732f;
    border-color: #00732f;
}

.btn-success:hover {
    background-color: #005a25 !important;
    border-color: #005a25 !important;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 115, 47, 0.4);
}

.btn-outline-primary:hover {
    background-color: #561C04 !important;
    border-color: #561C04 !important;
    color: white !important;
}

.btn-outline-secondary:hover {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: white !important;
}

.text-primary {
    color: #00732f !important;
}
</style>
@endsection
