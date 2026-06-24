@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
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

            @php
                $localTabs = $localTabs ?? collect();
                $localTotal = $localTabs->sum('count');
                $totalCount = ($chinaCount ?? 0) + $localTotal;
                $currentTab = $tab ?? 'china';
                $isAr = app()->getLocale() == 'ar';

                // China flag stays as an inline SVG; local countries use their emoji flag.
                $chinaFlagSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" style="width: 20px; height: 15px;"><path fill="#de2910" d="M0 0h640v480H0z"/><path fill="#ffde00" d="M140 60l20 60h63l-51 37 20 60-52-38-52 38 20-60-51-37h63z"/></svg>';

                // Number of stat cards/tabs that have products (total card + china + each local country)
                $activeTabs = ($chinaCount ?? 0 > 0 ? 1 : 0) + $localTabs->count();
                $colCount = 1 + ($chinaCount ?? 0 > 0 ? 1 : 0) + $localTabs->count();
                $colWidth = $colCount <= 2 ? 6 : ($colCount == 3 ? 4 : 3);
            @endphp

            @if($totalCount > 0)
                <!-- Stats Cards - Only show cards for tabs with products -->
                <div class="row mb-4">
                    <div class="col-md-{{ $colWidth }}">
                        <div class="card text-white" style="background-color: #561C04;">
                            <div class="card-body">
                                <h3 class="mb-0" style="color: white;">{{ $totalCount }}</h3>
                                <small style="color: white;">{{ $isAr ? 'إجمالي المخصص' : 'Total Assigned' }}</small>
                            </div>
                        </div>
                    </div>
                    @if(($chinaCount ?? 0) > 0)
                    <div class="col-md-{{ $colWidth }}">
                        <a href="{{ route('products.my-assigned', ['tab' => 'china']) }}" class="text-decoration-none">
                            <div class="card text-white" style="background-color: #de2910;">
                                <div class="card-body">
                                    <h3 class="mb-0" style="color: white;">{{ $chinaCount }}</h3>
                                    <small style="color: white;" class="d-flex align-items-center gap-1">{!! $chinaFlagSvg !!} {{ $isAr ? 'الصين' : 'China' }}</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif
                    @foreach($localTabs as $lt)
                    <div class="col-md-{{ $colWidth }}">
                        <a href="{{ route('products.my-assigned', ['tab' => $lt['tab']]) }}" class="text-decoration-none">
                            <div class="card text-white" style="background-color: #00732f;">
                                <div class="card-body">
                                    <h3 class="mb-0" style="color: white;">{{ $lt['count'] }}</h3>
                                    <small style="color: white;" class="d-flex align-items-center gap-1">
                                        <span style="font-size: 1.1em;">{{ $lt['flag'] }}</span> {{ $isAr ? $lt['name_ar'] : $lt['name'] }}
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>

                <!-- Tabs Navigation - Only show tabs that have products -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    @if(($chinaCount ?? 0) > 0)
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $currentTab === 'china' ? 'active' : '' }}" href="{{ route('products.my-assigned', ['tab' => 'china']) }}">
                            <span class="d-flex align-items-center gap-2">
                                {!! $chinaFlagSvg !!}
                                {{ $isAr ? 'الصين' : 'China' }}
                                <span class="badge bg-danger">{{ $chinaCount }}</span>
                            </span>
                        </a>
                    </li>
                    @endif
                    @foreach($localTabs as $lt)
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $currentTab === $lt['tab'] ? 'active' : '' }}" href="{{ route('products.my-assigned', ['tab' => $lt['tab']]) }}">
                            <span class="d-flex align-items-center gap-2">
                                <span style="font-size: 1.1em;">{{ $lt['flag'] }}</span>
                                {{ $isAr ? $lt['name_ar'] : $lt['name'] }}
                                <span class="badge bg-success">{{ $lt['count'] }}</span>
                            </span>
                        </a>
                    </li>
                    @endforeach
                </ul>

                <!-- Products Table -->
                @if($assignedProducts->count() > 0)
                    <div class="table-responsive">
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
                                @foreach($assignedProducts as $product)
                                    @php
                                        $aliexpressProductId = $product->pivot->aliexpress_product_id ?? null;
                                        $createdAt = $product->pivot->created_at;
                                        $isAliexpress = !empty($product->aliexpress_id) && strlen((string)$product->aliexpress_id) >= 10;
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
                                            @if($currentTab === 'china')
                                                <small class="text-success d-inline-flex align-items-center gap-1">
                                                    {!! $chinaFlagSvg !!}
                                                    {{ $isAr ? 'منتج الصين' : 'China Product' }}
                                                </small>
                                            @else
                                                @php $rowLocal = $localTabs->firstWhere('tab', $currentTab); @endphp
                                                @if($rowLocal)
                                                <small class="text-success d-inline-flex align-items-center gap-1">
                                                    <span>{{ $rowLocal['flag'] }}</span>
                                                    {{ $isAr ? 'منتج ' . $rowLocal['name_ar'] : $rowLocal['name'] . ' Product' }}
                                                </small>
                                                @endif
                                            @endif
                                        </td>
                                        <td style="direction: ltr; text-align: left;">
                                            @php
                                                // Use pivot price if available, otherwise calculate from product + amounts
                                                $pivotPrice = $product->pivot->price ?? null;
                                                $sellerAmount = $product->pivot->seller_amount ?? 0;
                                                $adminAmount = $product->pivot->admin_amount ?? 0;

                                                // Display price: pivot price if set, or product price + seller + admin amounts
                                                $displayPrice = $pivotPrice ?: ($product->price + $sellerAmount + $adminAmount);
                                            @endphp
                                            @if($displayPrice > 0)
                                                <span class="fw-bold d-inline-flex align-items-center gap-1" style="color: #561C04;">
                                                    <x-session-currency-icon width="16" height="16" />
                                                    {{ number_format(convert_price($displayPrice), 2) }}
                                                </span>
                                                @if($sellerAmount > 0)
                                                    <br>
                                                    <small class="text-success">
                                                        {{ app()->getLocale() == 'ar' ? 'ربحك:' : 'Profit:' }}
                                                        {{ number_format(convert_price($sellerAmount), 2) }}
                                                    </small>
                                                @endif
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
                                            @if($currentTab === 'china')
                                                <a href="{{ route('products.detail', $product->id) }}"
                                                   class="btn btn-sm btn-danger mb-1">
                                                    <i class="ri-ship-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'عرض وشحن دولي' : 'View & International Ship' }}
                                                </a>
                                            @else
                                                <a href="{{ route('products.detail-distributor', $product->id) }}"
                                                   class="btn btn-sm btn-success mb-1">
                                                    <i class="ri-truck-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'عرض وطلب' : 'View & Order' }}
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $assignedProducts->links() }}
                    </div>
                @else
                    <!-- Empty State for current tab -->
                    @php
                        $chinaLargeFlag = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" style="width: 80px; height: 60px; opacity: 0.5;"><path fill="#de2910" d="M0 0h640v480H0z"/><path fill="#ffde00" d="M140 60l20 60h63l-51 37 20 60-52-38-52 38 20-60-51-37h63z"/></svg>';
                        $emptyLocal = str_starts_with($currentTab, 'local_') ? $localTabs->firstWhere('tab', $currentTab) : null;
                        $emptyCode = $emptyLocal['code'] ?? substr($currentTab, strlen('local_'));
                    @endphp
                    <div class="text-center py-5">
                        @if($currentTab === 'china')
                            <div class="mb-3">{!! $chinaLargeFlag !!}</div>
                            <h5 class="text-muted">{{ $isAr ? 'لا توجد منتجات من الصين' : 'No China Products Yet' }}</h5>
                            <p class="text-muted">{{ $isAr ? 'ابحث عن منتجات من الصين وقم بتخصيصها' : 'Search for China products and assign them' }}</p>
                            <a href="{{ route('products.search-page') }}?ship_from=CN" class="btn btn-danger">
                                <i class="ri-search-line me-1"></i> {{ $isAr ? 'البحث في منتجات الصين' : 'Search China Products' }}
                            </a>
                        @else
                            @php $emptyName = $emptyLocal ? ($isAr ? $emptyLocal['name_ar'] : $emptyLocal['name']) : $emptyCode; @endphp
                            <div class="mb-3" style="font-size: 60px; opacity: 0.5;">{{ $emptyLocal['flag'] ?? '🏳️' }}</div>
                            <h5 class="text-muted">{{ $isAr ? 'لا توجد منتجات من ' . $emptyName : 'No ' . $emptyName . ' Products Yet' }}</h5>
                            <p class="text-muted">{{ $isAr ? 'ابحث عن منتجات وقم بتخصيصها' : 'Search for products and assign them' }}</p>
                            <a href="{{ route('products.search-distributor') }}?country_code={{ $emptyCode }}" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <span style="font-size: 1.1em;">{{ $emptyLocal['flag'] ?? '🏳️' }}</span>
                                {{ $isAr ? 'البحث في منتجات ' . $emptyName : 'Search ' . $emptyName . ' Products' }}
                            </a>
                        @endif
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
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
</style>
@endsection
