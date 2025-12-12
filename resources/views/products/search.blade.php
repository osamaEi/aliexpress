@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card mb-6 shadow-sm border-0">
        <div class="card-header bg-gradient d-flex justify-content-between align-items-center py-3"
             style="background: linear-gradient(135deg, #561C04 0%, #e56300 100%);" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
            <div class="d-flex align-items-center">
                <div class="bg-white rounded-circle p-2 me-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                    <i class="ri-search-2-line" style="font-size: 24px; color: #561C04;"></i>
                </div>
                <div>
                    <h5 class="mb-0 text-white fw-bold">{{ __('messages.product_search') }}</h5>
                    <small class="text-white-50">{{ __('messages.search_millions_products') }}</small>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Search Form -->
            <form id="searchForm" method="GET" action="{{ route('products.search-text') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="keyword" class="form-label fw-semibold">
                            <i class="ri-search-line me-1"></i>{{ __('messages.search_keyword') }}
                        </label>
                        <input
                            type="text"
                            id="keyword"
                            name="keyword"
                            class="form-control form-control-lg shadow-sm"
                            placeholder="{{ __('messages.search_placeholder') }}"
                            value="{{ old('keyword', $keyword ?? '') }}"
                        >
                    </div>

                    <div class="col-md-2">
                        <label for="main_category" class="form-label fw-semibold">
                            <i class="ri-folder-line me-1"></i>{{ __('messages.main_category') }}
                        </label>
                        <select id="main_category" class="form-select form-select-lg shadow-sm">
                            <option value="">{{ __('messages.all_categories') }}</option>
                            @if(isset($categories) && count($categories) > 0)
                                @foreach($categories as $category)
                                    @php
                                        $isSelected = false;
                                        $selectedChild = null;
                                        // Check if this main category or any of its children is selected
                                        if (request('category_id') == $category->aliexpress_category_id) {
                                            $isSelected = true;
                                        } elseif (isset($category->children) && count($category->children) > 0) {
                                            foreach ($category->children as $child) {
                                                if (request('category_id') == $child->aliexpress_category_id) {
                                                    $isSelected = true;
                                                    $selectedChild = $child->aliexpress_category_id;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <option value="{{ $category->aliexpress_category_id }}"
                                            data-has-children="{{ isset($category->children) && count($category->children) > 0 ? 'true' : 'false' }}"
                                            data-selected-child="{{ $selectedChild }}"
                                            {{ $isSelected ? 'selected' : '' }}>
                                        {{ $category->name }}
                                        @if($category->name_ar)
                                            ({{ $category->name_ar }})
                                        @endif
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="sub_category" class="form-label fw-semibold">
                            <i class="ri-folder-open-line me-1"></i>{{ __('messages.sub_category') }}
                        </label>
                        <select name="category_id" id="sub_category" class="form-select form-select-lg shadow-sm" disabled>
                            <option value="">{{ __('messages.select_main_category_first') }}</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="country" class="form-label fw-semibold">
                            <i class="ri-ship-line me-1"></i>{{ __('messages.ship_to') }}
                        </label>
                        <select name="country" id="country" class="form-select form-select-lg shadow-sm">
                            <option value="AE" {{ request('country') == 'AE' || !request('country') ? 'selected' : '' }} data-flag="ae">{{ app()->getLocale() == 'ar' ? 'الإمارات' : 'UAE' }}</option>
                            <option value="SA" {{ request('country') == 'SA' ? 'selected' : '' }} data-flag="sa">{{ app()->getLocale() == 'ar' ? 'السعودية' : 'Saudi Arabia' }}</option>
                            <option value="US" {{ request('country') == 'US' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'الولايات المتحدة' : 'USA' }}</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label for="currency" class="form-label fw-semibold">
                            <i class="ri-money-dollar-circle-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'العملة' : 'Currency' }}
                        </label>
                        <select name="currency" id="currency" class="form-select form-select-lg shadow-sm">
                            <option value="AED" {{ request('currency') == 'AED' ? 'selected' : '' }}>AED</option>
                            <option value="USD" {{ request('currency') == 'USD' || !request('currency') ? 'selected' : '' }}>USD</option>
                            <option value="SAR" {{ request('currency') == 'SAR' ? 'selected' : '' }}>SAR</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label fw-semibold d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow">
                            <i class="ri-search-line me-1"></i> {{ __('messages.search') }}
                        </button>
                    </div>
                </div>

                <!-- Filters Row -->
                <div class="row g-3 mt-2">
                    <!-- Choice Filter -->
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="choiceFilter"
                                name="choice_only"
                                value="1"
                                {{ request('choice_only') ? 'checked' : '' }}
                                style="width: 50px; height: 25px; cursor: pointer;">
                            <label class="form-check-label fw-semibold ms-2" for="choiceFilter" style="cursor: pointer; white-space: nowrap;">
                                <span class="badge choice-filter-badge px-3 py-2" style="background: linear-gradient(135deg, #561C04 0%, #e56300 100%) !important; font-size: 0.9rem; color: white;">
                                    <i class="ri-vip-crown-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'منتجات Choice فقط' : 'Choice Products Only' }}
                                </span>
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            <i class="ri-information-line"></i>
                            {{ app()->getLocale() == 'ar' ? 'منتجات مميزة بشحن أسرع وجودة مضمونة' : 'Premium products with faster shipping' }}
                        </small>
                    </div>

                    <!-- Price Range -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">
                            <i class="ri-money-dollar-circle-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'نطاق السعر' : 'Price Range' }}
                        </label>
                        <div class="input-group">
                            <input type="number" name="min_price" class="form-control"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'من' : 'Min' }}"
                                   value="{{ request('min_price') }}" min="0" step="0.01">
                            <span class="input-group-text">-</span>
                            <input type="number" name="max_price" class="form-control"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'إلى' : 'Max' }}"
                                   value="{{ request('max_price') }}" min="0" step="0.01">
                        </div>
                    </div>

                    <!-- Free Shipping Filter -->
                    <div class="col-md-4">
                      
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="minOrders"
                                   name="min_orders" value="100" {{ request('min_orders') ? 'checked' : '' }}>
                            <label class="form-check-label" for="minOrders">
                                <i class="ri-shopping-cart-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'الأكثر مبيعاً (100+ طلب)' : 'Best Sellers (100+ orders)' }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Advanced Filters - Collapsible -->
                <!-- <div class="mt-3">
                    <button class="btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                        <i class="ri-filter-3-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'خيارات متقدمة' : 'Advanced Filters' }}
                        <i class="ri-arrow-down-s-line"></i>
                    </button>
                </div>

                <div class="collapse mt-3" id="advancedFilters">
                    <div class="card card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="locale" class="form-label small">{{ app()->getLocale() == 'ar' ? 'اللغة' : 'Language' }}</label>
                                <select name="locale" id="locale" class="form-select form-select-sm">
                                    <option value="en_US" {{ request('locale', 'en_US') == 'en_US' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'إنجليزي 🇬🇧' : '🇬🇧 English' }}</option>
                                    <option value="ar_MA" {{ request('locale') == 'ar_MA' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'عربي 🇦🇪' : '🇦🇪 العربية' }}</option>
                                    <option value="es_ES" {{ request('locale') == 'es_ES' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'إسباني 🇪🇸' : '🇪🇸 Español' }}</option>
                                    <option value="fr_FR" {{ request('locale') == 'fr_FR' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'فرنسي 🇫🇷' : '🇫🇷 Français' }}</option>
                                    <option value="ru_RU" {{ request('locale') == 'ru_RU' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'روسي 🇷🇺' : '🇷🇺 Русский' }}</option>
                                    <option value="pt_BR" {{ request('locale') == 'pt_BR' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'برتغالي 🇧🇷' : '🇧🇷 Português' }}</option>
                                    <option value="de_DE" {{ request('locale') == 'de_DE' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'ألماني 🇩🇪' : '🇩🇪 Deutsch' }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="currency" class="form-label small">{{ app()->getLocale() == 'ar' ? 'العملة' : 'Currency' }}</label>
                                <select name="currency" id="currency" class="form-select form-select-sm">
                                    <option value="AED" {{ request('currency', 'AED') == 'AED' ? 'selected' : '' }}>AED</option>
                                    <option value="SAR" {{ request('currency') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="min_price" class="form-label small">{{ app()->getLocale() == 'ar' ? 'أقل سعر' : 'Min Price' }}</label>
                                <input type="number" name="min_price" id="min_price" class="form-control form-control-sm"
                                       placeholder="0" value="{{ request('min_price') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="max_price" class="form-label small">{{ app()->getLocale() == 'ar' ? 'أعلى سعر' : 'Max Price' }}</label>
                                <input type="number" name="max_price" id="max_price" class="form-control form-control-sm"
                                       placeholder="1000" value="{{ request('max_price') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="sort_by" class="form-label small">{{ app()->getLocale() == 'ar' ? 'الترتيب' : 'Sort By' }}</label>
                                <select name="sort_by" id="sort_by" class="form-select form-select-sm">
                                    <option value="">{{ app()->getLocale() == 'ar' ? 'الافتراضي' : 'Default' }}</option>
                                    <option value="orders,desc" {{ request('sort_by') == 'orders,desc' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'الأكثر مبيعاً' : 'Most Orders' }}</option>
                                    <option value="min_price,asc" {{ request('sort_by') == 'min_price,asc' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'السعر: من الأقل للأعلى' : 'Price: Low to High' }}</option>
                                    <option value="min_price,desc" {{ request('sort_by') == 'min_price,desc' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'السعر: من الأعلى للأقل' : 'Price: High to Low' }}</option>
                                    <option value="comments,desc" {{ request('sort_by') == 'comments,desc' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'الأكثر تقييماً' : 'Most Reviews' }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">{{ app()->getLocale() == 'ar' ? 'عدد النتائج' : 'Per Page' }}</label>
                                <select name="per_page" class="form-select form-select-sm">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                    <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div> -->
            </form>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">{{ app()->getLocale() == 'ar' ? 'جاري التحميل...' : 'Loading...' }}</span>
                </div>
                <p class="mt-2 text-muted">{{ app()->getLocale() == 'ar' ? 'جاري البحث عن المنتجات...' : 'Searching products...' }}</p>
            </div>

            <!-- Error Message -->
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Results Count -->
            @if(isset($products) && count($products) > 0)
                <!-- Product Type Filter Notice -->
                @if(request('choice_only'))
                    <div class="alert mb-3 d-flex align-items-center" style="background: linear-gradient(135deg, #561C0420 0%, #e5630020 100%); border: 2px solid #e56300;">
                        <i class="ri-vip-crown-line me-2" style="font-size: 1.5rem; color: #e56300;"></i>
                        <div>
                            <strong style="color: #561C04;">{{ app()->getLocale() == 'ar' ? 'فلتر Choice نشط:' : 'Choice Filter Active:' }}</strong>
                            {{ app()->getLocale() == 'ar' ? 'يتم عرض منتجات Choice المميزة فقط' : 'Showing premium Choice products only' }}
                            <br>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'منتجات بشحن أسرع (3-7 أيام)، جودة مضمونة، وإرجاع أسهل' : 'Fast shipping (3-7 days), guaranteed quality, and easier returns' }}</small>
                        </div>
                    </div>
                @elseif(request('non_choice'))
                    <div class="alert mb-3 d-flex align-items-center" style="background: linear-gradient(135deg, #17a2b820 0%, #00897b20 100%); border: 2px solid #00897b;">
                        <i class="ri-store-line me-2" style="font-size: 1.5rem; color: #00897b;"></i>
                        <div>
                            <strong style="color: #00695c;">{{ app()->getLocale() == 'ar' ? 'فلتر المنتجات العادية نشط:' : 'Regular Products Filter Active:' }}</strong>
                            {{ app()->getLocale() == 'ar' ? 'يتم عرض منتجات عادية من البائعين' : 'Showing regular products from sellers' }}
                            <br>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'منتجات بأسعار تنافسية وخيارات متنوعة' : 'Competitive prices and diverse options' }}</small>
                        </div>
                    </div>
                @endif

                <!-- Shipping Filter Notice -->
                <div class="alert alert-success mb-3 d-flex align-items-center">
                    <i class="ri-ship-line me-2" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>{{ app()->getLocale() == 'ar' ? 'فلتر الشحن نشط:' : 'Shipping Filter Active:' }}</strong> {{ app()->getLocale() == 'ar' ? 'جميع المنتجات المعروضة متاحة للشحن إلى' : 'All products shown ship to' }}
                        @if(request('country') == 'SA')
                            <strong>{{ __('messages.saudi_arabia') }}</strong>
                        @else
                            <strong>{{ __('messages.united_arab_emirates') }}</strong>
                        @endif
                        <br>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'يتم عرض المنتجات المتاحة للتوصيل إلى البلد المحدد فقط' : 'Only showing products available for delivery to your selected country' }}</small>
                    </div>
                </div>

                <div class="alert alert-info mb-4">
                    <i class="ri-information-line me-2"></i>
                    {{ app()->getLocale() == 'ar' ? 'تم العثور على' : 'Found' }} <strong>{{ count($products) }}</strong> {{ app()->getLocale() == 'ar' ? 'منتج' : 'products' }}
                    @if(isset($total_count))
                        ({{ app()->getLocale() == 'ar' ? 'الإجمالي:' : 'Total:' }} <strong>{{ number_format($total_count) }}</strong>)
                    @endif
                    @if(!empty($keyword))
                        {{ app()->getLocale() == 'ar' ? 'لـ' : 'for' }} "<strong>{{ $keyword }}</strong>"
                    @endif
                    @if(request('category_id'))
                        @php
                            $selectedCategory = $allCategories->firstWhere('aliexpress_category_id', request('category_id'));
                        @endphp
                        @if($selectedCategory)
                            {{ app()->getLocale() == 'ar' ? 'في الفئة:' : 'in category:' }} <strong>{{ app()->getLocale() == 'ar' && $selectedCategory->name_ar ? $selectedCategory->name_ar : $selectedCategory->name }}</strong>
                        @endif
                    @endif
                </div>
            @endif

            <!-- Products Grid -->
            @if(isset($products) && count($products) > 0)
                <!-- Bulk Actions Bar -->
                @auth
                    @if(auth()->user()->user_type === 'seller')
                    <div class="card mb-3 border-primary" id="bulkActionsBar" style="display: none;">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary me-2"><span id="selectedCount">0</span> {{ app()->getLocale() == 'ar' ? 'محدد' : 'selected' }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                                        <i class="ri-checkbox-multiple-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'تحديد الكل' : 'Select All' }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                                        <i class="ri-close-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'إلغاء الكل' : 'Deselect All' }}
                                    </button>
                                </div>
                                <button type="button" class="btn btn-sm btn-warning" id="bulkAssignBtn">
                                    <i class="ri-pushpin-2-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'إسناد المنتجات المحددة' : 'Assign Selected Products' }}
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                @endauth

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" id="productsGrid">
                    @foreach($products as $product)
                        <div class="col">
                            <div class="card h-100 product-card shadow-sm" data-product-id="{{ $product['item_id'] }}">
                                <!-- Product Image -->
                                <div class="position-relative overflow-hidden" style="background: #f8f9fa;">
                                    @auth
                                        @if(auth()->user()->user_type === 'seller')
                                            @php
                                                $isAssigned = in_array($product['item_id'], $assignedProductIds ?? []);
                                            @endphp
                                            @if(!$isAssigned)
                                            @php
                                                // Get local category ID for bulk assign
                                                $bulkLocalCategoryId = null;
                                                if (request('category_id')) {
                                                    $bulkSelectedCategory = $allCategories->firstWhere('aliexpress_category_id', request('category_id'));
                                                    if ($bulkSelectedCategory) {
                                                        $bulkLocalCategoryId = $bulkSelectedCategory->id;
                                                    }
                                                }
                                            @endphp
                                            <div class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input product-checkbox"
                                                        type="checkbox"
                                                        value="{{ $product['item_id'] }}"
                                                        data-title="{{ addslashes($product['title']) }}"
                                                        data-image="{{ $product['item_main_pic'] }}"
                                                        data-price="{{ $product['original_sale_price'] ?? $product['sale_price'] }}"
                                                        data-currency="{{ request('currency', 'AED') }}"
                                                        data-category-id="{{ $bulkLocalCategoryId ?? '' }}"
                                                        style="width: 20px; height: 20px; cursor: pointer; background-color: white; border: 2px solid #667eea;">
                                                </div>
                                            </div>
                                            @endif
                                        @endif
                                    @endauth
                                    <img
                                        src="{{ $product['item_main_pic'] }}"
                                        class="card-img-top"
                                        alt="{{ $product['title'] }}"
                                        style="height: 280px; object-fit: contain; padding: 15px;"
                                        onerror="this.src='https://via.placeholder.com/280x280?text=No+Image'"
                                    >

                                    <!-- Choice Badge -->
                                    @if(request('choice_only'))
                                        <span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 shadow-sm choice-badge"
                                              style="font-size: 0.85rem; border-radius: 8px; background: linear-gradient(135deg, #561C04 0%, #e56300 100%); border: 2px solid white;">
                                            <i class="ri-vip-crown-fill me-1"></i>{{ app()->getLocale() == 'ar' ? 'Choice' : 'Choice' }}
                                        </span>
                                    @endif

                                    <!-- Discount Badge -->
                                    @if($product['discount'])
                                        <span class="badge bg-danger position-absolute {{ request('choice_only') ? 'top-0' : 'top-0' }} end-0 m-3 px-3 py-2 shadow-sm"
                                              style="font-size: 0.85rem; border-radius: 8px; {{ request('choice_only') ? 'margin-top: 3.5rem !important;' : '' }}">
                                            <i class="ri-percent-line me-1"></i>{{ $product['discount'] }} OFF
                                        </span>
                                    @endif
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <!-- Product Title -->
                                    <h6 class="card-title" style="height: 48px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                        @if(request('choice_only'))
                                            <span class="badge me-1" style="background: linear-gradient(135deg, #561C04 0%, #e56300 100%); font-size: 0.65rem; vertical-align: middle;">
                                                <i class="ri-vip-crown-fill"></i>
                                            </span>
                                        @endif
                                        {{ $product['title'] }}
                                    </h6>

                                    <!-- Price -->
                                    <div class="mb-2">
                                        <h5 class="text-primary mb-0">
                                            @if($product['sale_price_format'])
                                                {{ $product['sale_price_format'] }}
                                            @else
                                                AED {{ number_format((float)$product['sale_price'], 2) }}
                                            @endif
                                        </h5>
                                        @if(request('choice_only'))
                                            <small class="d-block" style="color: #e56300;">
                                                <i class="ri-rocket-line"></i>
                                                {{ app()->getLocale() == 'ar' ? 'شحن سريع 3-7 أيام' : 'Fast shipping 3-7 days' }}
                                            </small>
                                        @endif
                                        @if(isset($product['admin_profit']) && $product['admin_profit'] > 0)
                                            <small class="text-success d-block">
                                                <i class="ri-money-dollar-circle-line"></i>
                                                {{ app()->getLocale() == 'ar' ? 'تشمل' : 'Includes' }} {{ setting('currency', 'AED') }} {{ number_format($product['admin_profit'], 2) }} {{ app()->getLocale() == 'ar' ? 'عمولة' : 'profit' }}
                                            </small>
                                        @endif
                                        @if($product['original_price'] > $product['sale_price'])
                                            <small class="text-muted text-decoration-line-through">
                                                @if($product['original_price_format'])
                                                    {{ $product['original_price_format'] }}
                                                @else
                                                    AED {{ number_format((float)$product['original_price'], 2) }}
                                                @endif
                                            </small>
                                        @endif
                                    </div>

                                    <!-- Stats -->
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        @if($product['evaluate_rate'])
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">
                                                <i class="ri-star-fill"></i> {{ $product['evaluate_rate'] }}
                                            </span>
                                        @endif
                                        @if($product['orders'])
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                                <i class="ri-fire-line"></i> {{ $product['orders'] }}+ {{ app()->getLocale() == 'ar' ? 'مبيع' : 'sold' }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-auto">
                                        <!-- AliExpress Link (Admin Only) -->
                                        @auth
                                            @if(auth()->user()->user_type === 'admin' && !empty($product['item_url']))
                                                <a href="{{ $product['item_url'] }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="btn btn-sm btn-outline-primary w-100 mb-2">
                                                    <i class="ri-external-link-line me-1"></i>
                                                    {{ app()->getLocale() == 'ar' ? 'عرض على الصين 🇨🇳' : 'View on China 🇨🇳' }}
                                                </a>
                                            @endif
                                        @endauth

                                        @auth
                                            @if(auth()->user()->user_type === 'seller')
                                                @php
                                                    $isAssigned = in_array($product['item_id'], $assignedProductIds ?? []);
                                                @endphp

                                                @if($isAssigned)
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-secondary w-100 mb-2"
                                                        disabled
                                                    >
                                                        <i class="ri-check-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'تم الإسناد' : 'Already Assigned' }}
                                                    </button>
                                                @else
                                                    @php
                                                        // Get local category ID based on the selected AliExpress category ID
                                                        $localCategoryId = null;
                                                        if (request('category_id')) {
                                                            $selectedCategory = $allCategories->firstWhere('aliexpress_category_id', request('category_id'));
                                                            if ($selectedCategory) {
                                                                $localCategoryId = $selectedCategory->id;
                                                            }
                                                        }
                                                    @endphp
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-warning w-100 mb-2 assign-product-btn"
                                                        onclick="assignProduct('{{ $product['item_id'] }}', '{{ addslashes($product['title']) }}', '{{ $product['item_main_pic'] }}', {{ $product['original_sale_price'] ?? $product['sale_price'] }}, '{{ request('currency', 'AED') }}', '{{ $localCategoryId ?? '' }}', this)"
                                                        data-product-id="{{ $product['item_id'] }}"
                                                    >
                                                        <i class="ri-pushpin-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'إسناد لي' : 'Assign to Me' }}
                                                    </button>
                                                @endif
                                            @endif
                                        @endauth
                                    </div>
                                </div>

                                <!-- Product ID (hidden) -->
                                <input type="hidden" class="product-id" value="{{ $product['item_id'] }}">
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if(isset($total_count) && $total_count > 10)
                    @php
                        $currentPage = request('page', 1);
                        $perPage = request('per_page', 10);
                        $totalPages = ceil($total_count / $perPage);
                    @endphp

                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Product pagination">
                            <ul class="pagination">
                                {{-- Previous Button --}}
                                <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?{{ http_build_query(array_merge(request()->except('page'), ['page' => $currentPage - 1])) }}">
                                        <i class="ri-arrow-left-s-line"></i> {{ app()->getLocale() == 'ar' ? 'السابق' : 'Previous' }}
                                    </a>
                                </li>

                                {{-- Page Numbers --}}
                                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                    <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="?{{ http_build_query(array_merge(request()->except('page'), ['page' => $i])) }}">
                                            {{ $i }}
                                        </a>
                                    </li>
                                @endfor

                                {{-- Next Button --}}
                                <li class="page-item {{ $currentPage >= $totalPages ? 'disabled' : '' }}">
                                    <a class="page-link" href="?{{ http_build_query(array_merge(request()->except('page'), ['page' => $currentPage + 1])) }}">
                                        {{ app()->getLocale() == 'ar' ? 'التالي' : 'Next' }} <i class="ri-arrow-right-s-line"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>

                    <div class="text-center text-muted small mb-4">
                        {{ app()->getLocale() == 'ar' ? 'عرض صفحة' : 'Showing page' }} {{ $currentPage }} {{ app()->getLocale() == 'ar' ? 'من' : 'of' }} {{ $totalPages }} ({{ number_format($total_count) }} {{ app()->getLocale() == 'ar' ? 'منتج إجمالي' : 'total products' }})
                    </div>
                @endif
            @elseif(isset($keyword))
                <div class="text-center py-5">
                    <i class="ri-search-line" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3 text-muted">{{ app()->getLocale() == 'ar' ? 'لم يتم العثور على منتجات لـ' : 'No products found for' }} "{{ $keyword }}"</h5>
                    <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'جرب هذه الاقتراحات:' : 'Try these suggestions:' }}</p>
                    <ul class="list-unstyled text-muted">
                        <li>{{ app()->getLocale() == 'ar' ? 'استخدم كلمات مختلفة (مثل: phone, laptop, dress, shoes)' : 'Use different keywords (try: phone, laptop, dress, shoes)' }}</li>
                        <li>{{ app()->getLocale() == 'ar' ? 'استخدم مصطلحات عامة بدلاً من أسماء العلامات التجارية' : 'Use more general terms instead of specific brand names' }}</li>
                        <li>{{ app()->getLocale() == 'ar' ? 'جرب البحث بالإنجليزية' : 'Try searching in English' }}</li>
                    </ul>
                    <div class="mt-4">
                        <p class="text-muted small">{{ app()->getLocale() == 'ar' ? 'عمليات بحث شائعة:' : 'Popular searches that usually work:' }}</p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="?keyword=phone" class="btn btn-sm btn-outline-primary">phone</a>
                            <a href="?keyword=laptop" class="btn btn-sm btn-outline-primary">laptop</a>
                            <a href="?keyword=dress" class="btn btn-sm btn-outline-primary">dress</a>
                            <a href="?keyword=shoes" class="btn btn-sm btn-outline-primary">shoes</a>
                            <a href="?keyword=bag" class="btn btn-sm btn-outline-primary">bag</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ri-shopping-bag-line" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3">{{ app()->getLocale() == 'ar' ? 'مرحباً بك في بحث المنتجات' : 'Welcome to Product Search' }}</h5>
                    <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'ابحث عن أي منتج باستخدام شريط البحث أعلاه' : 'Search for any product using the search bar above or click on quick links' }}</p>
                </div>
            @endif
        </div>
    </div>
</div>


<script>
    // Quick search buttons
    document.querySelectorAll('.quick-search').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('keyword').value = this.dataset.keyword;
            document.getElementById('searchForm').submit();
        });
    });

    // Auto-change currency based on country selection
    document.getElementById('country').addEventListener('change', function() {
        const country = this.value;
        const currencySelect = document.getElementById('currency');

        // Map country to currency
        const countryToCurrency = {
            'AE': 'AED',  // UAE -> AED
            'SA': 'SAR',  // Saudi Arabia -> SAR
        };

        if (countryToCurrency[country]) {
            currencySelect.value = countryToCurrency[country];
        }
    });

    // Auto-change country and currency based on language selection
    document.getElementById('locale').addEventListener('change', function() {
        const locale = this.value;
        const countrySelect = document.getElementById('country');
        const currencySelect = document.getElementById('currency');

        // Map locales to country and currency (only for SA/AE)
        const localeMap = {
            'en_US': { country: 'AE', currency: 'AED' },
            'ar_MA': { country: 'AE', currency: 'AED' },
            'es_ES': { country: 'AE', currency: 'AED' },
            'fr_FR': { country: 'AE', currency: 'AED' },
            'ru_RU': { country: 'AE', currency: 'AED' },
            'pt_BR': { country: 'AE', currency: 'AED' },
            'de_DE': { country: 'AE', currency: 'AED' },
        };

        if (localeMap[locale]) {
            countrySelect.value = localeMap[locale].country;
            currencySelect.value = localeMap[locale].currency;
        }
    });

    // Category hierarchy data
    const categoryHierarchy = {
        @if(isset($categories) && count($categories) > 0)
            @foreach($categories as $category)
                '{{ $category->aliexpress_category_id }}': [
                    @if(isset($category->children) && count($category->children) > 0)
                        @foreach($category->children as $child)
                            {
                                id: '{{ $child->aliexpress_category_id }}',
                                name: '{{ $child->name }}',
                                name_ar: '{{ $child->name_ar ?? '' }}'
                            },
                        @endforeach
                    @endif
                ],
            @endforeach
        @endif
    };

    // Main category change handler
    document.getElementById('main_category').addEventListener('change', function() {
        const mainCategoryId = this.value;
        const subCategorySelect = document.getElementById('sub_category');
        const selectedChildId = this.options[this.selectedIndex].getAttribute('data-selected-child');

        // Clear subcategory dropdown
        subCategorySelect.innerHTML = '';

        if (!mainCategoryId) {
            // No main category selected
            subCategorySelect.disabled = true;
            subCategorySelect.innerHTML = '<option value="">Select main category first</option>';
            return;
        }

        const children = categoryHierarchy[mainCategoryId] || [];

        if (children.length === 0) {
            // No subcategories available - use main category ID directly
            subCategorySelect.disabled = true;
            subCategorySelect.innerHTML = '<option value="' + mainCategoryId + '">No subcategories (using main category)</option>';
            const option = document.createElement('option');
            option.value = mainCategoryId;
            option.textContent = 'No subcategories (using main category)';
            option.selected = true;
            subCategorySelect.innerHTML = '';
            subCategorySelect.appendChild(option);
        } else {
            // Has subcategories
            subCategorySelect.disabled = false;

            // Add "All" option
            const allOption = document.createElement('option');
            allOption.value = mainCategoryId;
            allOption.textContent = 'All Subcategories';
            subCategorySelect.appendChild(allOption);

            // Add subcategory options
            children.forEach(child => {
                const option = document.createElement('option');
                option.value = child.id;
                option.textContent = child.name;
                if (child.name_ar) {
                    option.textContent += ' (' + child.name_ar + ')';
                }

                // Check if this child should be selected
                if (selectedChildId && selectedChildId === child.id) {
                    option.selected = true;
                }

                subCategorySelect.appendChild(option);
            });
        }
    });

    // Trigger change on page load to populate subcategories if main category is selected
    document.addEventListener('DOMContentLoaded', function() {
        const mainCategorySelect = document.getElementById('main_category');
        if (mainCategorySelect.value) {
            mainCategorySelect.dispatchEvent(new Event('change'));
        }
    });

    // Assign product to seller function
    function assignProduct(productId, productTitle, productImage, productPrice, currency, categoryId, buttonElement) {
        // Show loading state
        const originalHtml = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="ri-loader-4-line me-1"></i> Assigning...';

        // CSRF token for Laravel
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Prepare request body
        const requestData = {
            aliexpress_product_id: productId,
            product_title: productTitle,
            product_image: productImage,
            product_price: productPrice,
            currency: currency,
            is_choice: document.getElementById('choiceOnlyInput')?.value === '1'
        };

        // Add category_id only if it's provided and not empty
        if (categoryId && categoryId.trim() !== '') {
            requestData.category_id = categoryId;
        }

        // Make AJAX request
        fetch('{{ route("products.assign") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Change button to "Assigned" state
                buttonElement.classList.remove('btn-warning');
                buttonElement.classList.add('btn-secondary');
                buttonElement.innerHTML = '<i class="ri-check-line me-1"></i> Assigned';
                buttonElement.disabled = true;

                // Show success message
                showToast('success', data.message || 'Product assigned successfully!');
            } else {
                // Restore button
                buttonElement.disabled = false;
                buttonElement.innerHTML = originalHtml;

                // Show error message
                showToast('error', data.message || 'Failed to assign product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalHtml;
            showToast('error', 'An error occurred. Please try again.');
        });
    }

    // Toast notification function
    function showToast(type, message) {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        toast.style.cssText = 'min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        toast.innerHTML = `
            <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-line me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        toastContainer.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Toggle debug info
    function toggleDebug() {
        const debugInfo = document.getElementById('debugInfo');
        debugInfo.style.display = debugInfo.style.display === 'none' ? 'block' : 'none';
    }

    // Show loading spinner on form submit
    document.getElementById('searchForm').addEventListener('submit', function() {
        document.getElementById('loadingSpinner').style.display = 'block';
    });

    // Handle product type filter changes
    document.querySelectorAll('input[name="product_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const choiceOnlyInput = document.getElementById('choiceOnlyInput');
            const nonChoiceInput = document.getElementById('nonChoiceInput');
            const filterDescription = document.getElementById('filterDescription');
            const isArabic = '{{ app()->getLocale() }}' === 'ar';

            if (this.value === 'choice') {
                choiceOnlyInput.value = '1';
                nonChoiceInput.value = '';
                filterDescription.textContent = isArabic
                    ? 'منتجات مميزة بشحن أسرع وجودة مضمونة'
                    : 'Premium products with faster shipping and guaranteed quality';
            } else if (this.value === 'non-choice') {
                choiceOnlyInput.value = '';
                nonChoiceInput.value = '1';
                filterDescription.textContent = isArabic
                    ? 'منتجات عادية من البائعين'
                    : 'Regular products from sellers';
            } else {
                choiceOnlyInput.value = '';
                nonChoiceInput.value = '';
                filterDescription.textContent = isArabic
                    ? 'جميع المنتجات (Choice والعادية)'
                    : 'All products (Choice and Regular)';
            }
        });
    });

    // Bulk selection functionality
    document.addEventListener('DOMContentLoaded', function() {
        const bulkActionsBar = document.getElementById('bulkActionsBar');
        const selectedCountSpan = document.getElementById('selectedCount');
        const selectAllBtn = document.getElementById('selectAllBtn');
        const deselectAllBtn = document.getElementById('deselectAllBtn');
        const bulkAssignBtn = document.getElementById('bulkAssignBtn');

        console.log('Bulk selection initialized:', {
            bulkActionsBar: !!bulkActionsBar,
            selectAllBtn: !!selectAllBtn,
            deselectAllBtn: !!deselectAllBtn,
            bulkAssignBtn: !!bulkAssignBtn,
            checkboxCount: document.querySelectorAll('.product-checkbox').length
        });

        // Update selected count and show/hide bulk actions bar
        function updateSelectionUI() {
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const count = selectedCheckboxes.length;

            if (selectedCountSpan) selectedCountSpan.textContent = count;

            if (bulkActionsBar) {
                bulkActionsBar.style.display = count > 0 ? 'block' : 'none';
            }

            // Highlight selected cards
            document.querySelectorAll('.product-card').forEach(card => {
                const checkbox = card.querySelector('.product-checkbox');
                if (checkbox && checkbox.checked) {
                    card.style.border = '2px solid #667eea';
                    card.style.backgroundColor = '#f8f9ff';
                } else {
                    card.style.border = '1px solid #e9ecef';
                    card.style.backgroundColor = 'white';
                }
            });
        }

        // Checkbox change event - use event delegation
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('product-checkbox')) {
                updateSelectionUI();
            }
        });

        // Select all button
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.product-checkbox');
                console.log('Select All clicked, found checkboxes:', checkboxes.length);
                checkboxes.forEach(checkbox => {
                    if (!checkbox.disabled) {
                        checkbox.checked = true;
                    }
                });
                updateSelectionUI();
            });
        }

        // Deselect all button
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.product-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateSelectionUI();
            });
        }

        // Bulk assign button
        if (bulkAssignBtn) {
            bulkAssignBtn.addEventListener('click', function() {
                const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
                console.log('Bulk Assign clicked, selected checkboxes:', selectedCheckboxes.length);

                if (selectedCheckboxes.length === 0) {
                    const isArabic = '{{ app()->getLocale() }}' === 'ar';
                    const errorMessage = isArabic ? 'يرجى اختيار منتج واحد على الأقل' : 'Please select at least one product';
                    showToast('error', errorMessage);
                    return;
                }

                const isArabic = '{{ app()->getLocale() }}' === 'ar';
                const confirmMessage = isArabic
                    ? `هل تريد إسناد ${selectedCheckboxes.length} منتج(منتجات) إلى حسابك؟`
                    : `Assign ${selectedCheckboxes.length} product(s) to your account?`;

                if (!confirm(confirmMessage)) {
                    return;
                }

                // Disable button and show loading
                bulkAssignBtn.disabled = true;
                const loadingText = isArabic ? 'جاري الإسناد...' : 'Assigning...';
                bulkAssignBtn.innerHTML = `<i class="ri-loader-4-line me-1 spinner-border spinner-border-sm"></i> ${loadingText}`;

                // Prepare products data
                // Check if we're on choice filter by checking the URL parameter or checkbox
                const urlParams = new URLSearchParams(window.location.search);
                const isChoiceFilter = urlParams.get('choice_only') === '1' || document.getElementById('choiceFilter')?.checked;

                const products = Array.from(selectedCheckboxes).map(checkbox => {
                    const productData = {
                        aliexpress_product_id: checkbox.value,
                        product_title: checkbox.dataset.title,
                        product_image: checkbox.dataset.image,
                        product_price: checkbox.dataset.price,
                        currency: checkbox.dataset.currency,
                        is_choice: isChoiceFilter
                    };

                    // Add category_id if available
                    if (checkbox.dataset.categoryId && checkbox.dataset.categoryId.trim() !== '') {
                        productData.category_id = checkbox.dataset.categoryId;
                    }

                    return productData;
                });

                // CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

                // Make bulk assign request
                fetch('{{ route("products.assign") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ products: products })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', data.message || `${products.length} product(s) assigned successfully!`);

                        // Remove checkboxes and update UI for assigned products
                        selectedCheckboxes.forEach(checkbox => {
                            const card = checkbox.closest('.product-card');
                            const productId = checkbox.value;

                            // Remove checkbox
                            checkbox.closest('.form-check').remove();

                            // Update assign button if exists
                            const assignBtn = card.querySelector('.assign-product-btn');
                            if (assignBtn) {
                                assignBtn.classList.remove('btn-warning');
                                assignBtn.classList.add('btn-secondary');
                                const assignedText = isArabic ? 'تم الإسناد' : 'Already Assigned';
                                assignBtn.innerHTML = `<i class="ri-check-line me-1"></i> ${assignedText}`;
                                assignBtn.disabled = true;
                            }

                            // Reset card style
                            card.style.border = '1px solid #e9ecef';
                            card.style.backgroundColor = 'white';
                        });

                        updateSelectionUI();
                    } else {
                        showToast('error', data.message || (isArabic ? 'فشل إسناد المنتجات' : 'Failed to assign products'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMessage = isArabic ? 'حدث خطأ. يرجى المحاولة مرة أخرى.' : 'An error occurred. Please try again.';
                    showToast('error', errorMessage);
                })
                .finally(() => {
                    bulkAssignBtn.disabled = false;
                    const buttonText = isArabic ? 'إسناد المنتجات المحددة' : 'Assign Selected Products';
                    bulkAssignBtn.innerHTML = `<i class="ri-pushpin-2-line me-1"></i> ${buttonText}`;
                });
            });
        }
    });
</script>

<style>
    .bg-gradient {
        position: relative;
        overflow: hidden;
    }

    .bg-gradient::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: pulse 15s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(-30%, -30%); }
    }

    .product-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        border-color: #561C04;
    }

    .product-card img {
        transition: transform 0.3s ease;
    }

    .product-card:hover img {
        transform: scale(1.05);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #561C04;
        box-shadow: 0 0 0 0.2rem rgba(86, 28, 4, 0.25);
    }

    .btn-primary {
        background: #561C04;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: #561C04;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(86, 28, 4, 0.4);
    }

    .btn-outline-primary {
        border-color: #561C04;
        color: #561C04;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: #561C04;
        border-color: #561C04;
        color: white;
        transform: translateY(-2px);
    }

    .quick-search {
        transition: all 0.2s ease;
    }

    .quick-search:hover {
        background: #561C04;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(86, 28, 4, 0.3);
    }

    .shadow-sm {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    }

    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    pre {
        max-height: 400px;
        overflow: auto;
        font-size: 0.85rem;
        padding: 1rem;
        border-radius: 0.5rem;
        background: rgba(0,0,0,0.3);
    }

    .alert {
        border-radius: 10px;
        border: none;
    }

    .card {
        border-radius: 15px;
    }

    /* Flag icons using CSS */
    select#country option[data-flag="ae"]::before {
        content: "🇦🇪 ";
    }

    select#country option[data-flag="sa"]::before {
        content: "🇸🇦 ";
    }

    /* Custom flag styling for select dropdown */
    #country {
        background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path fill="%23333" d="M5 8l5 5 5-5z"/></svg>');
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 35px;
    }

    /* Choice Filter Styling */
    .form-check-input:checked {
        background-color: #561C04;
        border-color: #561C04;
    }

    .form-check-input:focus {
        border-color: #561C04;
        box-shadow: 0 0 0 0.25rem rgba(86, 28, 4, 0.25);
    }

    .badge.bg-gradient {
        transition: all 0.3s ease;
    }

    .badge.bg-gradient:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(86, 28, 4, 0.4);
    }

    /* Choice Badge Animation */
    .choice-badge {
        animation: choicePulse 2s ease-in-out infinite;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 4px 15px rgba(229, 99, 0, 0.4) !important;
    }

    @keyframes choicePulse {
        0%, 100% {
            box-shadow: 0 4px 15px rgba(229, 99, 0, 0.4);
        }
        50% {
            box-shadow: 0 6px 20px rgba(229, 99, 0, 0.6);
            transform: scale(1.02);
        }
    }

    .choice-badge i {
        animation: crownSpin 3s ease-in-out infinite;
        display: inline-block;
    }

    @keyframes crownSpin {
        0%, 100% {
            transform: rotate(0deg);
        }
        25% {
            transform: rotate(-10deg);
        }
        75% {
            transform: rotate(10deg);
        }
    }

    /* Pagination Styling */
    .pagination .page-item.active .page-link {
        background-color: #561C04;
        border-color: #561C04;
    }

    .pagination .page-link {
        color: #561C04;
        transition: all 0.3s ease;
    }

    .pagination .page-link:hover {
        background-color: #561C04;
        border-color: #561C04;
        color: white;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
    }

    /* Product Type Filter Buttons */
    .btn-check:checked + .btn-outline-secondary {
        background-color: #561C04;
        border-color: #561C04;
        color: white;
    }

    .btn-outline-secondary:hover {
        background-color: #561C04;
        border-color: #561C04;
        color: white;
    }

    .btn-group .btn {
        transition: all 0.3s ease;
    }

    /* Additional Button Hover Styles */
    .btn-warning:hover {
        background-color: #561C04 !important;
        border-color: #561C04 !important;
        color: white !important;
    }

    .btn-info:hover,
    .btn-outline-info:hover {
        background-color: #561C04 !important;
        border-color: #561C04 !important;
        color: white !important;
    }

    .btn-secondary:hover {
        background-color: #561C04 !important;
        border-color: #561C04 !important;
        color: white !important;
    }

    .btn-success:hover {
        background-color: #561C04 !important;
        border-color: #561C04 !important;
        color: white !important;
    }

    /* Link Hover */
    a:hover {
        color: #561C04 !important;
    }
</style>

@endsection
