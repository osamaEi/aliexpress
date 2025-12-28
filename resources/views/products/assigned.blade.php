@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
            <div>
                <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'المنتجات المخصصة لي' : 'My Assigned Products' }}</h5>
                <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'المنتجات التي قمت بتخصيصها' : 'Products you have assigned' }}</small>
            </div>
            <div>
                <a href="{{ route('products.search-page') }}" class="btn btn-primary btn-sm">
                    <i class="ri-search-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'البحث عن المزيد من المنتجات' : 'Search More Products' }}
                </a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($assignedProducts->count() > 0)
                @php
                    // Separate AliExpress products from Distributor products
                    // Real AliExpress IDs are long numeric strings (typically 13+ digits)
                    // Local product IDs are short numbers
                    $aliexpressProducts = $assignedProducts->filter(function($product) {
                        // Check if aliexpress_id exists and is a real AliExpress ID (long numeric string)
                        return !empty($product->aliexpress_id) && strlen((string)$product->aliexpress_id) >= 10;
                    });
                    $distributorProducts = $assignedProducts->filter(function($product) {
                        // Products without aliexpress_id OR with short IDs (local products)
                        return empty($product->aliexpress_id) || strlen((string)$product->aliexpress_id) < 10;
                    });
                @endphp

                <!-- Stats Cards -->
                <div class="row mb-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="col-md-3">
                        <div class="card text-white" style="background-color: #561C04;">
                            <div class="card-body">
                                <h3 class="mb-0" style="color: white;">{{ $assignedProducts->total() }}</h3>
                                <small style="color: white;">{{ app()->getLocale() == 'ar' ? 'إجمالي المخصص' : 'Total Assigned' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white" style="background-color: #de2910;">
                            <div class="card-body">
                                <h3 class="mb-0" style="color: white;">{{ $aliexpressProducts->count() }}</h3>
                                <small style="color: white;"><img src="https://flagcdn.com/w16/cn.png" alt="CN" class="me-1" style="width: 16px;"> {{ app()->getLocale() == 'ar' ? 'الصين' : 'China' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white" style="background-color: #00732f;">
                            <div class="card-body">
                                <h3 class="mb-0" style="color: white;">{{ $uaeProducts->count() }}</h3>
                                <small style="color: white;"><img src="https://flagcdn.com/w16/ae.png" alt="AE" class="me-1" style="width: 16px;"> {{ app()->getLocale() == 'ar' ? 'الإمارات' : 'UAE' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white" style="background-color: #006c35;">
                            <div class="card-body">
                                <h3 class="mb-0" style="color: white;">{{ $saudiProducts->count() }}</h3>
                                <small style="color: white;"><img src="https://flagcdn.com/w16/sa.png" alt="SA" class="me-1" style="width: 16px;"> {{ app()->getLocale() == 'ar' ? 'السعودية' : 'Saudi' }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="aliexpress-tab" data-bs-toggle="tab" data-bs-target="#aliexpress" type="button" role="tab">
                            <span class="d-flex align-items-center">
                                <img src="https://flagcdn.com/w20/cn.png" alt="CN" class="me-2" style="width: 20px; height: 15px; object-fit: cover; border-radius: 2px;">
                                {{ app()->getLocale() == 'ar' ? 'الصين' : 'China' }}
                                <span class="badge bg-danger ms-2">{{ $aliexpressProducts->count() }}</span>
                            </span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="uae-tab" data-bs-toggle="tab" data-bs-target="#uae" type="button" role="tab">
                            <span class="d-flex align-items-center">
                                <img src="https://flagcdn.com/w20/ae.png" alt="AE" class="me-2" style="width: 20px; height: 15px; object-fit: cover; border-radius: 2px;">
                                {{ app()->getLocale() == 'ar' ? 'الإمارات' : 'UAE' }}
                                @php
                                    $uaeProducts = $distributorProducts->filter(function($p) {
                                        return ($p->country_code ?? 'AE') === 'AE';
                                    });
                                @endphp
                                <span class="badge bg-success ms-2">{{ $uaeProducts->count() }}</span>
                            </span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="saudi-tab" data-bs-toggle="tab" data-bs-target="#saudi" type="button" role="tab">
                            <span class="d-flex align-items-center">
                                <img src="https://flagcdn.com/w20/sa.png" alt="SA" class="me-2" style="width: 20px; height: 15px; object-fit: cover; border-radius: 2px;">
                                {{ app()->getLocale() == 'ar' ? 'السعودية' : 'Saudi' }}
                                @php
                                    $saudiProducts = $distributorProducts->filter(function($p) {
                                        return ($p->country_code ?? '') === 'SA';
                                    });
                                @endphp
                                <span class="badge bg-success ms-2">{{ $saudiProducts->count() }}</span>
                            </span>
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="productTabsContent">
                    <!-- AliExpress Products Tab -->
                    <div class="tab-pane fade show active" id="aliexpress" role="tabpanel">
                        @if($aliexpressProducts->count() > 0)
                            <div class="table-responsive" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">{{ app()->getLocale() == 'ar' ? 'الصورة' : 'Image' }}</th>
                                            <th>{{ app()->getLocale() == 'ar' ? 'معلومات المنتج' : 'Product Info' }}</th>
                                            <th style="width: 120px;">{{ app()->getLocale() == 'ar' ? 'السعر' : 'Price' }}</th>
                                            <th style="width: 150px;">{{ app()->getLocale() == 'ar' ? 'تاريخ التخصيص' : 'Assigned Date' }}</th>
                                            <th style="width: 200px;">{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($aliexpressProducts as $product)
                                            @php
                                                $aliexpressProductId = $product->pivot->aliexpress_product_id;
                                                $createdAt = $product->pivot->created_at;
                                            @endphp
                                            <tr>
                                                <td>
                                                    @php
                                                        $productImage = null;
                                                        if($product->images && is_array($product->images) && count($product->images) > 0) {
                                                            $productImage = $product->images[0];
                                                        }
                                                    @endphp
                                                    @if($productImage)
                                                        <img src="{{ $productImage }}"
                                                             alt="Product"
                                                             class="img-thumbnail"
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                                             style="width: 60px; height: 60px; border-radius: 8px;">
                                                            <i class="ri-image-line text-muted" style="font-size: 24px;"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ app()->getLocale() == 'ar' && $product->name_ar ? $product->name_ar : $product->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="ri-barcode-line me-1"></i>
                                                        AliExpress ID: {{ $aliexpressProductId ?? $product->aliexpress_id }}
                                                    </small>
                                                </td>
                                                <td>
                                                    @if($product->price)
                                                        <span class="fw-bold d-flex align-items-center" style="color: #561C04;">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" class="me-1">
                                                                <path d="M8 7V17H12C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7H8Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                <path d="M6.5 11H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                <path d="M6.5 13H12.5H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            </svg>
                                                            {{ number_format($product->price, 2) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $createdAt->format('M d, Y') }}
                                                        <br>
                                                        {{ $createdAt->format('h:i A') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <!-- AliExpress products always go to AliExpress detail view -->
                                                    <a href="{{ route('products.detail', $product->id) }}"
                                                       class="btn btn-sm btn-danger mb-1">
                                                        <i class="ri-ship-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'عرض وشحن دولي' : 'View & International Ship' }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 32 32" class="mb-3" style="opacity: 0.5;">
                                    <rect x="1" y="4" width="30" height="24" rx="4" ry="4" fill="#de2910"></rect>
                                    <g fill="#ffde00">
                                        <path d="M7.5,9.5 l0.9,2.8 l2.9,0 l-2.4,1.7 l0.9,2.8 l-2.4,-1.7 l-2.4,1.7 l0.9,-2.8 l-2.4,-1.7 l2.9,0z"/>
                                    </g>
                                </svg>
                                <h5 class="text-muted">{{ app()->getLocale() == 'ar' ? 'لا توجد منتجات من الصين' : 'No China Products Yet' }}</h5>
                                <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'ابحث عن منتجات من الصين وقم بتخصيصها' : 'Search for China products and assign them' }}</p>
                                <a href="{{ route('products.search-page') }}?ship_from=CN" class="btn btn-danger">
                                    <i class="ri-search-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'البحث في منتجات الصين' : 'Search China Products' }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- UAE Products Tab -->
                    <div class="tab-pane fade" id="uae" role="tabpanel">
                        @if($uaeProducts->count() > 0)
                            <div class="table-responsive" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">{{ app()->getLocale() == 'ar' ? 'الصورة' : 'Image' }}</th>
                                            <th>{{ app()->getLocale() == 'ar' ? 'معلومات المنتج' : 'Product Info' }}</th>
                                            <th style="width: 120px;">{{ app()->getLocale() == 'ar' ? 'السعر' : 'Price' }}</th>
                                            <th style="width: 150px;">{{ app()->getLocale() == 'ar' ? 'تاريخ التخصيص' : 'Assigned Date' }}</th>
                                            <th style="width: 200px;">{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($uaeProducts as $product)
                                            @php
                                                $createdAt = $product->pivot->created_at;
                                            @endphp
                                            <tr>
                                                <td>
                                                    @php
                                                        $productImage = null;
                                                        if($product->images && is_array($product->images) && count($product->images) > 0) {
                                                            $productImage = $product->images[0];
                                                        } elseif($product->photo) {
                                                            $productImage = asset('storage/' . $product->photo);
                                                        }
                                                    @endphp
                                                    @if($productImage)
                                                        <img src="{{ $productImage }}"
                                                             alt="Product"
                                                             class="img-thumbnail"
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                                             style="width: 60px; height: 60px; border-radius: 8px;">
                                                            <i class="ri-image-line text-muted" style="font-size: 24px;"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ app()->getLocale() == 'ar' && $product->name_ar ? $product->name_ar : $product->name }}</strong>
                                                    <br>
                                                    <small class="text-success">
                                                        <img src="https://flagcdn.com/w16/ae.png" alt="AE" class="me-1" style="width: 16px;">
                                                        {{ app()->getLocale() == 'ar' ? 'منتج الإمارات' : 'UAE Product' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    @if($product->price)
                                                        <span class="fw-bold d-flex align-items-center" style="color: #561C04;">
                                                            <x-dirham-icon width="16" height="16" />
                                                            {{ number_format($product->price, 2) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $createdAt->format('M d, Y') }}
                                                        <br>
                                                        {{ $createdAt->format('h:i A') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <a href="{{ route('products.detail-distributor', $product->id) }}"
                                                       class="btn btn-sm btn-success mb-1">
                                                        <i class="ri-truck-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'عرض وطلب' : 'View & Order' }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <img src="https://flagcdn.com/w80/ae.png" alt="UAE" class="mb-3" style="width: 80px; opacity: 0.5;">
                                <h5 class="text-muted">{{ app()->getLocale() == 'ar' ? 'لا توجد منتجات من الإمارات' : 'No UAE Products Yet' }}</h5>
                                <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'ابحث عن منتجات من الإمارات وقم بتخصيصها' : 'Search for UAE products and assign them' }}</p>
                                <a href="{{ route('products.search-distributor') }}?country_code=AE" class="btn btn-success">
                                    <img src="https://flagcdn.com/w20/ae.png" alt="AE" class="me-1" style="width: 20px;">
                                    {{ app()->getLocale() == 'ar' ? 'البحث في منتجات الإمارات' : 'Search UAE Products' }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Saudi Products Tab -->
                    <div class="tab-pane fade" id="saudi" role="tabpanel">
                        @if($saudiProducts->count() > 0)
                            <div class="table-responsive" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">{{ app()->getLocale() == 'ar' ? 'الصورة' : 'Image' }}</th>
                                            <th>{{ app()->getLocale() == 'ar' ? 'معلومات المنتج' : 'Product Info' }}</th>
                                            <th style="width: 120px;">{{ app()->getLocale() == 'ar' ? 'السعر' : 'Price' }}</th>
                                            <th style="width: 150px;">{{ app()->getLocale() == 'ar' ? 'تاريخ التخصيص' : 'Assigned Date' }}</th>
                                            <th style="width: 200px;">{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($saudiProducts as $product)
                                            @php
                                                $createdAt = $product->pivot->created_at;
                                            @endphp
                                            <tr>
                                                <td>
                                                    @php
                                                        $productImage = null;
                                                        if($product->images && is_array($product->images) && count($product->images) > 0) {
                                                            $productImage = $product->images[0];
                                                        } elseif($product->photo) {
                                                            $productImage = asset('storage/' . $product->photo);
                                                        }
                                                    @endphp
                                                    @if($productImage)
                                                        <img src="{{ $productImage }}"
                                                             alt="Product"
                                                             class="img-thumbnail"
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                                             style="width: 60px; height: 60px; border-radius: 8px;">
                                                            <i class="ri-image-line text-muted" style="font-size: 24px;"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ app()->getLocale() == 'ar' && $product->name_ar ? $product->name_ar : $product->name }}</strong>
                                                    <br>
                                                    <small class="text-success">
                                                        <img src="https://flagcdn.com/w16/sa.png" alt="SA" class="me-1" style="width: 16px;">
                                                        {{ app()->getLocale() == 'ar' ? 'منتج السعودية' : 'Saudi Product' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    @if($product->price)
                                                        <span class="fw-bold d-flex align-items-center" style="color: #561C04;">
                                                            <x-riyal-icon width="16" height="16" />
                                                            {{ number_format($product->price, 2) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $createdAt->format('M d, Y') }}
                                                        <br>
                                                        {{ $createdAt->format('h:i A') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <a href="{{ route('products.detail-distributor', $product->id) }}"
                                                       class="btn btn-sm btn-success mb-1">
                                                        <i class="ri-truck-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'عرض وطلب' : 'View & Order' }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <img src="https://flagcdn.com/w80/sa.png" alt="Saudi" class="mb-3" style="width: 80px; opacity: 0.5;">
                                <h5 class="text-muted">{{ app()->getLocale() == 'ar' ? 'لا توجد منتجات من السعودية' : 'No Saudi Products Yet' }}</h5>
                                <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'ابحث عن منتجات من السعودية وقم بتخصيصها' : 'Search for Saudi products and assign them' }}</p>
                                <a href="{{ route('products.search-distributor') }}?country_code=SA" class="btn btn-success">
                                    <img src="https://flagcdn.com/w20/sa.png" alt="SA" class="me-1" style="width: 20px;">
                                    {{ app()->getLocale() == 'ar' ? 'البحث في منتجات السعودية' : 'Search Saudi Products' }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $assignedProducts->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <i class="ri-inbox-line" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3">{{ app()->getLocale() == 'ar' ? 'لا توجد منتجات مخصصة بعد' : 'No Assigned Products Yet' }}</h5>
                    <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'ابدأ بالبحث عن المنتجات وتخصيصها لحسابك.' : 'Start by searching products and assigning them to your account.' }}</p>
                    <a href="{{ route('products.search-page') }}" class="btn btn-primary mt-3">
                        <i class="ri-search-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'البحث عن المنتجات' : 'Search Products' }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>


<style>
    .card-body h3 {
        font-size: 2rem;
        font-weight: 700;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        transition: background-color 0.2s;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Stats Cards Hover */
    .row .card {
        transition: all 0.3s ease;
    }

    .row .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(86, 28, 4, 0.3);
    }

    /* Nav Tabs Styling */
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
    }

    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 500;
        padding: 12px 20px;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #561C04;
    }

    .nav-tabs .nav-link.active {
        border-color: transparent;
        border-bottom: 3px solid #561C04;
        color: #561C04;
        background: transparent;
    }

    /* Button Hover Styles */
    .btn-primary {
        background-color: #561C04;
        border-color: #561C04;
    }

    .btn-primary:hover {
        background-color: #3d1503 !important;
        border-color: #3d1503 !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(86, 28, 4, 0.4);
    }

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

    .btn-danger {
        background-color: #de2910;
        border-color: #de2910;
    }

    .btn-danger:hover {
        background-color: #b8220d !important;
        border-color: #b8220d !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(222, 41, 16, 0.4);
    }

    .btn-outline-primary:hover {
        background-color: #561C04 !important;
        border-color: #561C04 !important;
        color: white !important;
    }

    .btn-outline-danger:hover {
        background-color: #de2910 !important;
        border-color: #de2910 !important;
        color: white !important;
    }

    /* Badge Hover */
    .badge {
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    /* Tab Content Animation */
    .tab-pane {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
