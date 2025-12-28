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
            @if(auth()->user() && auth()->user()->user_type === 'seller' && (!isset($categories) || count($categories) == 0))
                <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
                    <i class="ri-information-line me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <h6 class="alert-heading mb-1">
                            {{ app()->getLocale() == 'ar' ? 'لا توجد فئات مخصصة لك' : 'No Categories Assigned' }}
                        </h6>
                        <p class="mb-2">
                            {{ app()->getLocale() == 'ar'
                                ? 'لم يتم تعيين أي فئات لحسابك بعد. لاستخدام مرشح الفئات، يرجى طلب تعيين فئة من صفحة الفئات.'
                                : 'No categories have been assigned to your account yet. To use the category filter, please request a category assignment from the Categories page.' }}
                        </p>
                        <a href="{{ route('seller.request-category-assignment') }}" class="btn btn-sm btn-warning">
                            <i class="ri-add-circle-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'طلب تعيين فئة' : 'Request Category Assignment' }}
                        </a>
                    </div>
                </div>
            @endif

            <!-- Main Categories Horizontal Scroll -->
            @if(isset($categories) && count($categories) > 0)
            <div class="categories-scroll-container mb-4">
                <div class="d-flex align-items-center mb-2">
                    <button class="btn btn-sm btn-light rounded-circle me-2 scroll-btn scroll-left" onclick="scrollCategories('left')">
                        <i class="ri-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}-s-line"></i>
                    </button>
                    <div class="categories-scroll flex-grow-1" id="categoriesScroll">
                        @foreach($categories as $category)
                            <div class="category-item {{ request('category_id') == $category->aliexpress_category_id ? 'active' : '' }}"
                                 onclick="selectMainCategory('{{ $category->aliexpress_category_id }}')"
                                 data-category-id="{{ $category->aliexpress_category_id }}">
                                <div class="category-icon">
                                    @if($category->photo)
                                        <img src="{{ asset('storage/' . $category->photo) }}" alt="{{ $category->name }}" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <i class="ri-folder-line" style="display: none;"></i>
                                    @else
                                        <i class="ri-folder-line"></i>
                                    @endif
                                </div>
                                <span class="category-name">{{ app()->getLocale() == 'ar' && $category->name_ar ? $category->name_ar : $category->name }}</span>
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-sm btn-light rounded-circle ms-2 scroll-btn scroll-right" onclick="scrollCategories('right')">
                        <i class="ri-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}-s-line"></i>
                    </button>
                </div>
            </div>
            @endif

            <!-- Product Source Cards (Dynamic from Distributors + China) -->
            @php
                $isChinaActive = request('ship_from') == 'CN';
                $activeCountryCode = request('country_code') ?? (isset($source_country) ? $source_country : null);

                // Country names mapping
                $countryNames = [
                    'AE' => ['ar' => 'الإمارات', 'en' => 'UAE', 'flag' => '🇦🇪'],
                    'SA' => ['ar' => 'السعودية', 'en' => 'Saudi', 'flag' => '🇸🇦'],
                    'KW' => ['ar' => 'الكويت', 'en' => 'Kuwait', 'flag' => '🇰🇼'],
                    'QA' => ['ar' => 'قطر', 'en' => 'Qatar', 'flag' => '🇶🇦'],
                    'BH' => ['ar' => 'البحرين', 'en' => 'Bahrain', 'flag' => '🇧🇭'],
                    'OM' => ['ar' => 'عمان', 'en' => 'Oman', 'flag' => '🇴🇲'],
                    'EG' => ['ar' => 'مصر', 'en' => 'Egypt', 'flag' => '🇪🇬'],
                    'JO' => ['ar' => 'الأردن', 'en' => 'Jordan', 'flag' => '🇯🇴'],
                    'LB' => ['ar' => 'لبنان', 'en' => 'Lebanon', 'flag' => '🇱🇧'],
                ];
            @endphp
            <div class="source-cards-container mb-4">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Dynamic Country Cards from Distributors -->
                    @if(isset($distributorCountries) && count($distributorCountries) > 0)
                        @foreach($distributorCountries as $country)
                            @php
                                $countryCode = $country['code'];
                                $isActive = $activeCountryCode == $countryCode;
                                $countryInfo = $countryNames[$countryCode] ?? ['ar' => $countryCode, 'en' => $countryCode, 'flag' => '🏳️'];
                            @endphp
                            <div class="source-card-mini {{ $isActive ? 'active' : '' }}"
                                 onclick="toggleDistributors('{{ $countryCode }}')"
                                 data-source="{{ $countryCode }}">
                                <span class="country-flag">{{ $countryInfo['flag'] }}</span>
                                <span class="country-name">{{ app()->getLocale() == 'ar' ? $countryInfo['ar'] : $countryInfo['en'] }}</span>
                            </div>
                        @endforeach
                    @endif

                    <!-- China Card - Always Show -->
                    <div class="source-card-mini china {{ $isChinaActive ? 'active' : '' }}"
                         onclick="toggleChinaStores()"
                         data-source="china">
                        <span class="country-flag">🇨🇳</span>
                        <span class="country-name">{{ app()->getLocale() == 'ar' ? 'الصين' : 'China' }}</span>
                    </div>
                </div>

                <!-- Distributors Dropdown (shown below country cards) -->
                @php
                    $showDistributorDropdown = isset($source_country) && !empty($source_country);
                @endphp
                <div id="distributorsDropdown" class="distributors-dropdown" style="display: {{ $showDistributorDropdown ? 'block' : 'none' }};">
                    <div class="distributors-dropdown-header">
                        <span id="dropdownCountryName">
                            @if($showDistributorDropdown)
                                @php
                                    $dropdownCountryInfo = $countryNames[$source_country] ?? ['ar' => $source_country, 'en' => $source_country, 'flag' => '🏳️'];
                                @endphp
                                {{ $dropdownCountryInfo['flag'] }} {{ app()->getLocale() == 'ar' ? 'متاجر ' . $dropdownCountryInfo['ar'] : $dropdownCountryInfo['en'] . ' Stores' }}
                            @endif
                        </span>
                        <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="hideDistributors()">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div id="distributorsList" class="distributors-inline-list">
                        @if($showDistributorDropdown && isset($distributorsByCountry[$source_country]))
                            @php
                                $currentDistributorId = request('distributor_id');
                            @endphp
                            <!-- All Products from this country -->
                            <div class="distributor-card {{ empty($currentDistributorId) ? 'active' : '' }}" onclick="viewAllCountryProducts('{{ $source_country }}')">
                                <div class="distributor-card-avatar all-avatar">
                                    <i class="ri-apps-line"></i>
                                </div>
                                <span class="distributor-card-name">{{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}</span>
                            </div>
                            @foreach($distributorsByCountry[$source_country] as $dist)
                                <div class="distributor-card {{ $currentDistributorId == $dist['id'] ? 'active' : '' }}" onclick="viewDistributorProducts({{ $dist['id'] }}, '{{ $source_country }}')">
                                    <div class="distributor-card-avatar">
                                        @if(!empty($dist['avatar']))
                                            <img src="{{ asset('storage/' . $dist['avatar']) }}" alt="{{ $dist['store_name'] ?? $dist['name'] }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        @endif
                                        <div class="avatar-placeholder" @if(!empty($dist['avatar'])) style="display:none;" @endif>
                                            <i class="ri-store-2-line"></i>
                                        </div>
                                    </div>
                                    <span class="distributor-card-name">{{ $dist['store_name'] ?? $dist['name'] }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- China Stores Dropdown (shown below country cards when China is clicked) -->
                <div id="chinaStoresDropdown" class="distributors-dropdown china-stores-dropdown" style="display: none;">
                    <div class="distributors-dropdown-header">
                        <span>🇨🇳 {{ app()->getLocale() == 'ar' ? 'متاجر الصين' : 'China Stores' }}</span>
                        <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="hideChinaStores()">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div id="chinaStoresList" class="distributors-inline-list">
                        <!-- Loading state -->
                        <div class="text-center py-3" id="chinaStoresLoading">
                            <div class="spinner-border spinner-border-sm text-danger" role="status">
                                <span class="visually-hidden">{{ app()->getLocale() == 'ar' ? 'جاري التحميل...' : 'Loading...' }}</span>
                            </div>
                            <span class="ms-2 text-muted">{{ app()->getLocale() == 'ar' ? 'جاري تحميل المتاجر...' : 'Loading stores...' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Store distributors data for JavaScript -->
            <script>
                const distributorsByCountry = @json($distributorsByCountry ?? []);
                const countryNames = @json($countryNames);
                let chinaStoresLoaded = false;
                let chinaStoresData = [];
            </script>

            <!-- Subcategories Container (shown when main category selected) -->
            @php
                $currentCategoryId = request('category_id');
                $parentCategory = null;
                $showSubcategories = false;

                if ($currentCategoryId && isset($categories)) {
                    // Find the parent category that contains this subcategory
                    foreach ($categories as $cat) {
                        if ($cat->aliexpress_category_id == $currentCategoryId) {
                            // Current category is a main category, show its children
                            $parentCategory = $cat;
                            $showSubcategories = $cat->children && count($cat->children) > 0;
                            break;
                        }
                        // Check if current category is a child of this category
                        if ($cat->children) {
                            foreach ($cat->children as $child) {
                                if ($child->aliexpress_category_id == $currentCategoryId || $child->id == $currentCategoryId) {
                                    $parentCategory = $cat;
                                    $showSubcategories = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            @endphp
            <div id="subcategoriesContainer" class="mb-4" style="display: {{ $showSubcategories ? 'block' : 'none' }};">
                <div class="subcategories-header">
                    <h6 class="mb-0">
                        <i class="ri-folder-open-line me-2"></i>
                        <span id="selectedCategoryName">
                            @if($parentCategory)
                                {{ app()->getLocale() == 'ar' && $parentCategory->name_ar ? $parentCategory->name_ar : $parentCategory->name }}
                            @else
                                {{ app()->getLocale() == 'ar' ? 'الفئات الفرعية' : 'Subcategories' }}
                            @endif
                        </span>
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearCategorySelection()">
                        <i class="ri-close-line"></i> {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Clear' }}
                    </button>
                </div>
                <div class="subcategories-grid" id="subcategoriesGrid">
                    @if($showSubcategories && $parentCategory && $parentCategory->children)
                        @foreach($parentCategory->children as $child)
                            @php
                                $isActiveSubcat = ($child->aliexpress_category_id == $currentCategoryId || $child->id == $currentCategoryId);
                                $childName = app()->getLocale() == 'ar' && $child->name_ar ? $child->name_ar : $child->name;
                                $childImage = $child->photo ? asset('storage/' . $child->photo) : ($child->image ?? '');
                            @endphp
                            <div class="subcategory-item {{ $isActiveSubcat ? 'active' : '' }}" onclick="searchByCategory('{{ $child->aliexpress_category_id ?? $child->id }}')">
                                <div class="subcategory-icon {{ $childImage ? 'has-image' : '' }}">
                                    @if($childImage)
                                        <img src="{{ $childImage }}" alt="{{ $childName }}" onerror="this.style.display='none'; this.parentElement.classList.remove('has-image'); this.nextElementSibling.style.display='flex';">
                                    @endif
                                    <i class="ri-price-tag-3-line" @if($childImage) style="display:none;" @endif></i>
                                </div>
                                <span class="subcategory-name">{{ $childName }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Search Form -->
            @php
                $isDistributorSearch = isset($source_country) || request('country_code');
                $formAction = $isDistributorSearch ? route('products.search-distributor') : route('products.search-text');
            @endphp
            <form id="searchForm" method="GET" action="{{ $formAction }}" class="mb-4">
                <input type="hidden" name="ship_from" id="shipFromInput" value="{{ request('ship_from') }}">
                <input type="hidden" name="choice_only" id="choiceOnlyInput" value="{{ request('choice_only') }}">
                <input type="hidden" name="category_id" id="categoryIdInput" value="{{ request('category_id') }}">
                <input type="hidden" name="country" id="countryInput" value="{{ request('country', 'AE') }}">
                <input type="hidden" name="currency" id="currencyInput" value="{{ request('currency', 'USD') }}">
                <input type="hidden" name="country_code" id="countryCodeInput" value="{{ request('country_code') ?? ($source_country ?? '') }}">
                <input type="hidden" name="distributor_id" id="distributorIdInput" value="{{ request('distributor_id', '') }}">

                <div class="search-box-container">
                    <div class="row g-2">
                        <div class="col">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white">
                                    <i class="ri-search-line text-muted"></i>
                                </span>
                                <input type="text" name="keyword" id="keyword" class="form-control"
                                       placeholder="{{ app()->getLocale() == 'ar' ? 'ابحث عن منتج...' : 'Search for products...' }}"
                                       value="{{ old('keyword', $keyword ?? '') }}">
                                <button type="submit" class="btn btn-warning btn-lg px-5">
                                    {{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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

            <!-- Active Filters Display -->
            @if(request('ship_from') || request('category_id') || request('country_code') || isset($source_country))
                <div class="active-filters mb-3">
                    <span class="filter-label">{{ app()->getLocale() == 'ar' ? 'الفلاتر النشطة:' : 'Active Filters:' }}</span>
                    @if(request('country_code') == 'AE' || (isset($source_country) && $source_country == 'AE'))
                        <span class="filter-tag" style="background: #e8f5e9;">
                            <i class="ri-map-pin-line"></i> {{ app()->getLocale() == 'ar' ? 'موزعين الإمارات' : 'UAE Distributors' }}
                            <button type="button" onclick="window.location.href='{{ route('products.search-page') }}'"><i class="ri-close-line"></i></button>
                        </span>
                    @endif
                    @if(request('country_code') == 'SA' || (isset($source_country) && $source_country == 'SA'))
                        <span class="filter-tag" style="background: #e8f5e9;">
                            <i class="ri-map-pin-line"></i> {{ app()->getLocale() == 'ar' ? 'موزعين السعودية' : 'Saudi Distributors' }}
                            <button type="button" onclick="window.location.href='{{ route('products.search-page') }}'"><i class="ri-close-line"></i></button>
                        </span>
                    @endif
                    @if(request('ship_from') == 'CN')
                        <span class="filter-tag" style="background: #ffebee;">
                            <i class="ri-map-pin-line"></i> {{ app()->getLocale() == 'ar' ? 'الصين' : 'China' }}
                            <button type="button" onclick="removeFilter('ship_from')"><i class="ri-close-line"></i></button>
                        </span>
                    @endif
                    @if(request('category_id'))
                        @php
                            $selectedCategory = $allCategories->firstWhere('aliexpress_category_id', request('category_id'));
                        @endphp
                        @if($selectedCategory)
                            <span class="filter-tag">
                                <i class="ri-folder-line"></i> {{ app()->getLocale() == 'ar' && $selectedCategory->name_ar ? $selectedCategory->name_ar : $selectedCategory->name }}
                                <button type="button" onclick="removeFilter('category_id')"><i class="ri-close-line"></i></button>
                            </span>
                        @endif
                    @endif
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="clearAllFilters()">
                        {{ app()->getLocale() == 'ar' ? 'مسح الكل' : 'Clear All' }}
                    </button>
                </div>
            @endif

            <!-- Results Count -->
            @if(isset($products) && count($products) > 0)
                <div class="results-info mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="results-count">
                                {{ app()->getLocale() == 'ar' ? 'تم العثور على' : 'Found' }}
                                <strong>{{ count($products) }}</strong>
                                {{ app()->getLocale() == 'ar' ? 'منتج' : 'products' }}
                                @if(isset($total_count))
                                    ({{ app()->getLocale() == 'ar' ? 'الإجمالي:' : 'Total:' }} <strong>{{ number_format($total_count) }}</strong>)
                                @endif
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" style="width: auto;" onchange="changeSort(this.value)">
                                <option value="">{{ app()->getLocale() == 'ar' ? 'الترتيب' : 'Sort' }}</option>
                                <option value="orders,desc">{{ app()->getLocale() == 'ar' ? 'الأكثر مبيعاً' : 'Best Selling' }}</option>
                                <option value="min_price,asc">{{ app()->getLocale() == 'ar' ? 'السعر: الأقل' : 'Price: Low' }}</option>
                                <option value="min_price,desc">{{ app()->getLocale() == 'ar' ? 'السعر: الأعلى' : 'Price: High' }}</option>
                            </select>
                        </div>
                    </div>
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
                                                        data-title-ar="{{ addslashes($product['title_ar'] ?? $product['title']) }}"
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

                                    <!-- Source Badge (Dynamic based on source_country or ship_from) -->
                                    @php
                                        $sourceCountry = request('country_code') ?? (isset($source_country) ? $source_country : null);
                                        $isChina = request('ship_from') == 'CN' || empty($sourceCountry);

                                        // Get flag and name based on source
                                        $flagEmoji = '🇨🇳';
                                        $countryLabel = app()->getLocale() == 'ar' ? 'الصين' : 'China';

                                        if ($sourceCountry) {
                                            $countryFlags = [
                                                'AE' => '🇦🇪', 'SA' => '🇸🇦', 'KW' => '🇰🇼', 'QA' => '🇶🇦',
                                                'BH' => '🇧🇭', 'OM' => '🇴🇲', 'EG' => '🇪🇬', 'JO' => '🇯🇴', 'LB' => '🇱🇧'
                                            ];
                                            $countryLabelsAr = [
                                                'AE' => 'الإمارات', 'SA' => 'السعودية', 'KW' => 'الكويت', 'QA' => 'قطر',
                                                'BH' => 'البحرين', 'OM' => 'عمان', 'EG' => 'مصر', 'JO' => 'الأردن', 'LB' => 'لبنان'
                                            ];
                                            $countryLabelsEn = [
                                                'AE' => 'UAE', 'SA' => 'Saudi', 'KW' => 'Kuwait', 'QA' => 'Qatar',
                                                'BH' => 'Bahrain', 'OM' => 'Oman', 'EG' => 'Egypt', 'JO' => 'Jordan', 'LB' => 'Lebanon'
                                            ];
                                            $flagEmoji = $countryFlags[$sourceCountry] ?? '🏳️';
                                            $countryLabel = app()->getLocale() == 'ar'
                                                ? ($countryLabelsAr[$sourceCountry] ?? $sourceCountry)
                                                : ($countryLabelsEn[$sourceCountry] ?? $sourceCountry);
                                            $isChina = false;
                                        }
                                    @endphp
                                    <div class="position-absolute bottom-0 start-0 m-2" style="z-index: 5;">
                                        <div class="d-flex align-items-center bg-white rounded-pill px-2 py-1 shadow-sm" style="border: 1px solid #e0e0e0;">
                                            @if($isChina)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 32 32" style="margin-right: 4px;">
                                                    <rect x="1" y="4" width="30" height="24" rx="4" ry="4" fill="#de2910"></rect>
                                                    <g fill="#ffde00">
                                                        <path d="M7.5,9.5 l0.9,2.8 l2.9,0 l-2.4,1.7 l0.9,2.8 l-2.4,-1.7 l-2.4,1.7 l0.9,-2.8 l-2.4,-1.7 l2.9,0z"/>
                                                    </g>
                                                </svg>
                                            @else
                                                <span style="font-size: 0.8rem;">{{ $flagEmoji }}</span>
                                            @endif
                                            <span style="font-size: 0.7rem; color: #666; font-weight: 500; margin-left: 4px;">{{ $countryLabel }}</span>
                                        </div>
                                    </div>

                                    <!-- Discount Badge -->
                                    @if($product['discount'])
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-3 px-3 py-2 shadow-sm"
                                              style="font-size: 0.85rem; border-radius: 8px;">
                                            <i class="ri-percent-line me-1"></i>{{ $product['discount'] }} OFF
                                        </span>
                                    @endif
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <!-- Product Title -->
                                    <h6 class="card-title" style="height: 48px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                        {{ app()->getLocale() == 'ar' ? ($product['title_ar'] ?? $product['title']) : ($product['title_en'] ?? $product['title']) }}
                                    </h6>

                                    <!-- Price -->
                                    <div class="mb-2">
                                        <h5 class="text-primary mb-0 d-flex align-items-center" style="direction: ltr; justify-content: flex-start;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="me-1" style="vertical-align: middle;">
                                                <path d="M8 7V17H12C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7H8Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M6.5 11H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M6.5 13H12.5H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                            @if($product['sale_price_format'])
                                                {{ preg_replace('/[A-Z]{3}\s*/', '', $product['sale_price_format']) }}
                                            @else
                                                {{ number_format((float)$product['sale_price'], 2) }}
                                            @endif
                                        </h5>
                                        @if(isset($product['admin_profit']) && $product['admin_profit'] > 0)
                                            <small class="text-success d-block d-flex align-items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" class="me-1" style="vertical-align: middle;">
                                                    <path d="M8 7V17H12C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7H8Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6.5 11H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6.5 13H12.5H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                                {{ app()->getLocale() == 'ar' ? 'تشمل' : 'Includes' }} {{ number_format($product['admin_profit'], 2) }} {{ app()->getLocale() == 'ar' ? 'عمولة' : 'profit' }}
                                            </small>
                                        @endif
                                        @if($product['original_price'] > $product['sale_price'])
                                            <small class="text-muted text-decoration-line-through d-flex align-items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" class="me-1" style="vertical-align: middle;">
                                                    <path d="M8 7V17H12C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7H8Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6.5 11H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6.5 13H12.5H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                                @if($product['original_price_format'])
                                                    {{ preg_replace('/[A-Z]{3}\s*/', '', $product['original_price_format']) }}
                                                @else
                                                    {{ number_format((float)$product['original_price'], 2) }}
                                                @endif
                                            </small>
                                        @endif
                                    </div>

                                    <!-- Stats -->
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        @if($product['evaluate_rate'])
                                            <span class="badge px-3 py-1" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #000; font-weight: 600; box-shadow: 0 2px 6px rgba(255, 215, 0, 0.3);">
                                                <i class="ri-star-fill" style="color: #8B4513;"></i> {{ $product['evaluate_rate'] }}
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
                                        @auth
                                            @if(auth()->user()->user_type === 'admin' && !empty($product['item_url']))
                                                <a href="{{ $product['item_url'] }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="btn btn-sm btn-outline-primary w-100 mb-2">
                                                    <i class="ri-external-link-line me-1"></i>
                                                    {{ app()->getLocale() == 'ar' ? 'عرض على الصين' : 'View on China' }}
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
                                                        onclick="assignProduct('{{ $product['item_id'] }}', '{{ addslashes($product['title_en'] ?? $product['title']) }}', '{{ addslashes($product['title_ar'] ?? $product['title']) }}', '{{ $product['item_main_pic'] }}', {{ $product['original_sale_price'] ?? $product['sale_price'] }}, '{{ request('currency', 'AED') }}', '{{ $localCategoryId ?? '' }}', this)"
                                                        data-product-id="{{ $product['item_id'] }}"
                                                        data-title-en="{{ addslashes($product['title_en'] ?? $product['title']) }}"
                                                        data-title-ar="{{ addslashes($product['title_ar'] ?? $product['title']) }}"
                                                    >
                                                        <i class="ri-pushpin-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'إسناد لي' : 'Assign to Me' }}
                                                    </button>
                                                @endif
                                            @endif
                                        @endauth
                                    </div>
                                </div>

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
                                <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?{{ http_build_query(array_merge(request()->except('page'), ['page' => $currentPage - 1])) }}">
                                        <i class="ri-arrow-left-s-line"></i> {{ app()->getLocale() == 'ar' ? 'السابق' : 'Previous' }}
                                    </a>
                                </li>

                                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                    <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="?{{ http_build_query(array_merge(request()->except('page'), ['page' => $i])) }}">
                                            {{ $i }}
                                        </a>
                                    </li>
                                @endfor

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
                        <li>{{ app()->getLocale() == 'ar' ? 'استخدم كلمات مختلفة' : 'Use different keywords' }}</li>
                        <li>{{ app()->getLocale() == 'ar' ? 'جرب البحث بالإنجليزية' : 'Try searching in English' }}</li>
                    </ul>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ri-shopping-bag-line" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3">{{ app()->getLocale() == 'ar' ? 'اختر فئة أو ابحث عن منتج' : 'Select a category or search for products' }}</h5>
                    <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'اضغط على فئة أعلاه أو استخدم شريط البحث' : 'Click on a category above or use the search bar' }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Category data for subcategories
    const categoryData = {
        @if(isset($categories) && count($categories) > 0)
            @foreach($categories as $category)
                '{{ $category->aliexpress_category_id }}': {
                    name: '{{ addslashes($category->name) }}',
                    name_ar: '{{ addslashes($category->name_ar ?? '') }}',
                    children: [
                        @if(isset($category->children) && count($category->children) > 0)
                            @foreach($category->children as $child)
                                {
                                    id: '{{ $child->aliexpress_category_id }}',
                                    name: '{{ addslashes($child->name) }}',
                                    name_ar: '{{ addslashes($child->name_ar ?? '') }}',
                                    image: '{{ $child->image ?? '' }}',
                                    photo: '{{ $child->photo ? asset("storage/" . $child->photo) : "" }}'
                                },
                            @endforeach
                        @endif
                    ]
                },
            @endforeach
        @endif
    };

    const isArabic = '{{ app()->getLocale() }}' === 'ar';

    // Scroll categories
    function scrollCategories(direction) {
        const container = document.getElementById('categoriesScroll');
        const scrollAmount = 200;
        if (direction === 'left') {
            container.scrollBy({ left: isArabic ? scrollAmount : -scrollAmount, behavior: 'smooth' });
        } else {
            container.scrollBy({ left: isArabic ? -scrollAmount : scrollAmount, behavior: 'smooth' });
        }
    }

    // Select main category
    function selectMainCategory(categoryId) {
        // Update active state
        document.querySelectorAll('.category-item').forEach(item => item.classList.remove('active'));
        document.querySelector(`[data-category-id="${categoryId}"]`)?.classList.add('active');

        // Clear source cards selection
        document.querySelectorAll('.source-card').forEach(card => card.classList.remove('active'));

        const category = categoryData[categoryId];
        if (category && category.children && category.children.length > 0) {
            // Show subcategories
            showSubcategories(categoryId, category);
        } else {
            // No subcategories, search directly - reset to AliExpress search
            document.getElementById('searchForm').action = '{{ route("products.search-text") }}';
            document.getElementById('categoryIdInput').value = categoryId;
            document.getElementById('shipFromInput').value = '';
            document.getElementById('choiceOnlyInput').value = '';
            document.getElementById('countryCodeInput').value = '';
            document.getElementById('distributorIdInput').value = '';
            document.getElementById('searchForm').submit();
        }
    }

    // Subcategory icons mapping based on keywords
    const subcategoryIcons = {
        // Fashion & Clothing
        'dress': 'ri-t-shirt-line', 'فستان': 'ri-t-shirt-line',
        'shirt': 'ri-t-shirt-line', 'قميص': 'ri-t-shirt-line',
        'pants': 'ri-t-shirt-line', 'بنطلون': 'ri-t-shirt-line',
        'shoes': 'ri-footprint-line', 'حذاء': 'ri-footprint-line', 'أحذية': 'ri-footprint-line',
        'bag': 'ri-handbag-line', 'حقيبة': 'ri-handbag-line', 'حقائب': 'ri-handbag-line',
        'watch': 'ri-time-line', 'ساعة': 'ri-time-line', 'ساعات': 'ri-time-line',
        'jewelry': 'ri-vip-diamond-line', 'مجوهرات': 'ri-vip-diamond-line',
        'ring': 'ri-vip-diamond-line', 'خاتم': 'ri-vip-diamond-line',
        'necklace': 'ri-vip-diamond-line', 'قلادة': 'ri-vip-diamond-line',
        'glasses': 'ri-glasses-line', 'نظارة': 'ri-glasses-line', 'نظارات': 'ri-glasses-line',
        'hat': 'ri-baseball-cap-line', 'قبعة': 'ri-baseball-cap-line',
        // Electronics
        'phone': 'ri-smartphone-line', 'هاتف': 'ri-smartphone-line', 'جوال': 'ri-smartphone-line',
        'laptop': 'ri-macbook-line', 'لابتوب': 'ri-macbook-line',
        'computer': 'ri-computer-line', 'كمبيوتر': 'ri-computer-line',
        'tablet': 'ri-tablet-line', 'تابلت': 'ri-tablet-line',
        'headphone': 'ri-headphone-line', 'سماعة': 'ri-headphone-line', 'سماعات': 'ri-headphone-line',
        'camera': 'ri-camera-line', 'كاميرا': 'ri-camera-line',
        'tv': 'ri-tv-line', 'تلفزيون': 'ri-tv-line',
        'speaker': 'ri-speaker-line', 'مكبر': 'ri-speaker-line',
        'charger': 'ri-battery-charge-line', 'شاحن': 'ri-battery-charge-line',
        'cable': 'ri-plug-line', 'كابل': 'ri-plug-line',
        // Home & Garden
        'furniture': 'ri-hotel-bed-line', 'أثاث': 'ri-hotel-bed-line',
        'bed': 'ri-hotel-bed-line', 'سرير': 'ri-hotel-bed-line',
        'sofa': 'ri-sofa-line', 'كنب': 'ri-sofa-line',
        'table': 'ri-layout-grid-line', 'طاولة': 'ri-layout-grid-line',
        'chair': 'ri-armchair-line', 'كرسي': 'ri-armchair-line',
        'lamp': 'ri-lightbulb-line', 'مصباح': 'ri-lightbulb-line', 'إضاءة': 'ri-lightbulb-line',
        'kitchen': 'ri-restaurant-line', 'مطبخ': 'ri-restaurant-line',
        'bathroom': 'ri-water-flash-line', 'حمام': 'ri-water-flash-line',
        'garden': 'ri-plant-line', 'حديقة': 'ri-plant-line',
        'decoration': 'ri-home-smile-line', 'ديكور': 'ri-home-smile-line',
        // Sports & Outdoors
        'sport': 'ri-basketball-line', 'رياضة': 'ri-basketball-line', 'رياضي': 'ri-basketball-line',
        'fitness': 'ri-run-line', 'لياقة': 'ri-run-line',
        'yoga': 'ri-mental-health-line', 'يوغا': 'ri-mental-health-line',
        'cycling': 'ri-riding-line', 'دراجة': 'ri-riding-line',
        'camping': 'ri-camping-line', 'تخييم': 'ri-camping-line',
        'fishing': 'ri-anchor-line', 'صيد': 'ri-anchor-line',
        // Kids & Toys
        'toy': 'ri-gamepad-line', 'لعبة': 'ri-gamepad-line', 'ألعاب': 'ri-gamepad-line',
        'baby': 'ri-emotion-happy-line', 'طفل': 'ri-emotion-happy-line', 'أطفال': 'ri-emotion-happy-line',
        'kids': 'ri-emotion-happy-line',
        // Beauty & Health
        'beauty': 'ri-sparkling-line', 'جمال': 'ri-sparkling-line',
        'makeup': 'ri-brush-line', 'مكياج': 'ri-brush-line',
        'skincare': 'ri-heart-pulse-line', 'عناية': 'ri-heart-pulse-line',
        'perfume': 'ri-spray-line', 'عطر': 'ri-spray-line', 'عطور': 'ri-spray-line',
        'hair': 'ri-scissors-line', 'شعر': 'ri-scissors-line',
        // Cars & Motorcycles
        'car': 'ri-car-line', 'سيارة': 'ri-car-line', 'سيارات': 'ri-car-line',
        'motorcycle': 'ri-motorbike-line', 'دراجة نارية': 'ri-motorbike-line',
        'auto': 'ri-car-washing-line',
        // Office & School
        'office': 'ri-briefcase-line', 'مكتب': 'ri-briefcase-line',
        'school': 'ri-book-line', 'مدرسة': 'ri-book-line',
        'stationery': 'ri-pencil-line', 'قرطاسية': 'ri-pencil-line',
        'book': 'ri-book-open-line', 'كتاب': 'ri-book-open-line',
        // Food & Drinks
        'food': 'ri-restaurant-2-line', 'طعام': 'ri-restaurant-2-line',
        'drink': 'ri-cup-line', 'مشروب': 'ri-cup-line',
        'coffee': 'ri-cup-line', 'قهوة': 'ri-cup-line',
        // Security
        'security': 'ri-shield-check-line', 'أمن': 'ri-shield-check-line', 'حماية': 'ri-shield-check-line',
        'lock': 'ri-lock-line', 'قفل': 'ri-lock-line',
        // Default
        'default': 'ri-price-tag-3-line'
    };

    function getSubcategoryIcon(name) {
        const lowerName = name.toLowerCase();
        for (const [keyword, icon] of Object.entries(subcategoryIcons)) {
            if (lowerName.includes(keyword)) {
                return icon;
            }
        }
        return subcategoryIcons['default'];
    }

    // Show subcategories
    function showSubcategories(categoryId, category) {
        const container = document.getElementById('subcategoriesContainer');
        const grid = document.getElementById('subcategoriesGrid');
        const nameSpan = document.getElementById('selectedCategoryName');

        nameSpan.textContent = isArabic && category.name_ar ? category.name_ar : category.name;

        let html = '';

        category.children.forEach(child => {
            const name = isArabic && child.name_ar ? child.name_ar : child.name;
            // Prioritize photo (from database storage) over image (external URL)
            const imageUrl = child.photo || child.image || '';
            const hasImage = imageUrl && imageUrl.length > 0;
            const icon = getSubcategoryIcon(child.name);

            html += `
                <div class="subcategory-item" onclick="searchByCategory('${child.id}')">
                    <div class="subcategory-icon ${hasImage ? 'has-image' : ''}">
                        ${hasImage ? `<img src="${imageUrl}" alt="${name}" onerror="this.style.display='none'; this.parentElement.classList.remove('has-image'); this.nextElementSibling.style.display='flex';">` : ''}
                        <i class="${icon}" ${hasImage ? 'style="display:none;"' : ''}></i>
                    </div>
                    <span class="subcategory-name">${name}</span>
                </div>
            `;
        });

        grid.innerHTML = html;
        container.style.display = 'block';
    }

    // Search by category
    function searchByCategory(categoryId) {
        // Reset to AliExpress search when selecting category
        document.getElementById('searchForm').action = '{{ route("products.search-text") }}';
        document.getElementById('categoryIdInput').value = categoryId;
        document.getElementById('shipFromInput').value = '';
        document.getElementById('choiceOnlyInput').value = '';
        document.getElementById('countryCodeInput').value = '';
        document.getElementById('distributorIdInput').value = '';
        document.getElementById('searchForm').submit();
    }

    // Clear category selection
    function clearCategorySelection() {
        document.getElementById('subcategoriesContainer').style.display = 'none';
        document.querySelectorAll('.category-item').forEach(item => item.classList.remove('active'));
        document.getElementById('categoryIdInput').value = '';
    }

    // Check if we're currently viewing distributor products
    const currentSourceCountry = '{{ isset($source_country) ? $source_country : "" }}';
    const currentDistributorId = '{{ request("distributor_id", "") }}';

    // Current selected country for dropdown - initialize with current source country if viewing distributor products
    let selectedCountryCode = currentSourceCountry || null;

    // Toggle distributors dropdown
    function toggleDistributors(countryCode) {
        const dropdown = document.getElementById('distributorsDropdown');
        const isCurrentlyOpen = dropdown.style.display !== 'none' && selectedCountryCode === countryCode;

        // If clicking same country and dropdown is open, just close dropdown (don't affect products)
        if (isCurrentlyOpen) {
            dropdown.style.display = 'none';
            // Keep the active state if we're viewing products from this country
            if (currentSourceCountry !== countryCode) {
                document.querySelectorAll('.source-card-mini').forEach(card => card.classList.remove('active'));
            }
            selectedCountryCode = null;
            return;
        }

        selectedCountryCode = countryCode;

        // Remove active from all cards first
        document.querySelectorAll('.source-card-mini').forEach(card => card.classList.remove('active'));

        // Add active to clicked card
        document.querySelector(`[data-source="${countryCode}"]`)?.classList.add('active');

        // Get country info
        const countryInfo = countryNames[countryCode] || { ar: countryCode, en: countryCode, flag: '🏳️' };
        const countryName = isArabic ? countryInfo.ar : countryInfo.en;

        // Update dropdown header
        document.getElementById('dropdownCountryName').innerHTML =
            `${countryInfo.flag} ${isArabic ? 'متاجر ' + countryName : countryName + ' Stores'}`;

        // Get distributors for this country
        const distributors = distributorsByCountry[countryCode] || [];

        // Build distributors list HTML (inline horizontal cards)
        let html = '';
        if (distributors.length === 0) {
            html = `<div class="text-center py-3">
                <p class="text-muted mb-0">${isArabic ? 'لا يوجد موزعين حالياً' : 'No distributors available'}</p>
            </div>`;
        } else {
            distributors.forEach(dist => {
                const avatar = dist.avatar ? `{{ asset('storage') }}/${dist.avatar}` : null;
                const name = dist.store_name || dist.name;
                html += `
                    <div class="distributor-card" onclick="viewDistributorProducts(${dist.id}, '${countryCode}')">
                        <div class="distributor-card-avatar">
                            ${avatar
                                ? `<img src="${avatar}" alt="${name}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`
                                : ''
                            }
                            <div class="avatar-placeholder" ${avatar ? 'style="display:none;"' : ''}>
                                <i class="ri-store-2-line"></i>
                            </div>
                        </div>
                        <span class="distributor-card-name">${name}</span>
                    </div>
                `;
            });
        }

        document.getElementById('distributorsList').innerHTML = html;

        // Show dropdown
        dropdown.style.display = 'block';
    }

    // Hide distributors dropdown
    function hideDistributors() {
        document.getElementById('distributorsDropdown').style.display = 'none';
        document.querySelectorAll('.source-card-mini').forEach(card => card.classList.remove('active'));
        selectedCountryCode = null;
    }

    // View products from a specific distributor
    function viewDistributorProducts(distributorId, countryCode) {
        const keywordInput = document.getElementById('keyword');
        const keyword = keywordInput ? keywordInput.value.trim() : '';

        let url = '{{ route("products.search-distributor") }}?country_code=' + countryCode + '&distributor_id=' + distributorId;
        if (keyword) {
            url += '&keyword=' + encodeURIComponent(keyword);
        }

        document.getElementById('loadingSpinner').style.display = 'block';
        window.location.href = url;
    }

    // View all products from a country
    function viewAllCountryProducts(countryCode) {
        const country = countryCode || selectedCountryCode;
        if (!country) return;

        const keywordInput = document.getElementById('keyword');
        const keyword = keywordInput ? keywordInput.value.trim() : '';

        let url = '{{ route("products.search-distributor") }}?country_code=' + country;
        if (keyword) {
            url += '&keyword=' + encodeURIComponent(keyword);
        }

        document.getElementById('loadingSpinner').style.display = 'block';
        window.location.href = url;
    }

    // Select source (China, All)
    function selectSource(source) {
        // Clear all selections
        document.querySelectorAll('.source-card-mini').forEach(card => card.classList.remove('active'));
        document.querySelectorAll('.category-item').forEach(item => item.classList.remove('active'));
        document.getElementById('subcategoriesContainer').style.display = 'none';
        hideChinaStores();
        hideDistributors();

        // Get keyword value
        const keywordInput = document.getElementById('keyword');
        const keyword = keywordInput.value.trim();

        // China: Use AliExpress API
        if (source === 'china') {
            document.querySelector('[data-source="china"]').classList.add('active');

            // Reset form to AliExpress search
            document.getElementById('searchForm').action = '{{ route("products.search-text") }}';
            document.getElementById('shipFromInput').value = 'CN';
            document.getElementById('choiceOnlyInput').value = '';
            document.getElementById('categoryIdInput').value = '';
            document.getElementById('countryInput').value = 'AE';
            document.getElementById('currencyInput').value = 'USD';
            document.getElementById('countryCodeInput').value = '';
            document.getElementById('distributorIdInput').value = '';

            // Set default keyword if empty (required for AliExpress search)
            if (!keyword) {
                keywordInput.value = isArabic ? 'منتجات' : 'products';
            }

            document.getElementById('searchForm').submit();
            return;
        }

        // All: Reset and show all
        if (source === 'all') {
            document.querySelector('[data-source="all"]')?.classList.add('active');

            // Reset form to AliExpress search
            document.getElementById('searchForm').action = '{{ route("products.search-text") }}';
            document.getElementById('shipFromInput').value = '';
            document.getElementById('choiceOnlyInput').value = '';
            document.getElementById('categoryIdInput').value = '';
            document.getElementById('countryCodeInput').value = '';
            document.getElementById('distributorIdInput').value = '';

            // Set default keyword if empty
            if (!keyword) {
                keywordInput.value = isArabic ? 'منتجات' : 'products';
            }

            document.getElementById('searchForm').submit();
        }
    }

    // Toggle China stores dropdown
    function toggleChinaStores() {
        const dropdown = document.getElementById('chinaStoresDropdown');
        const isCurrentlyOpen = dropdown.style.display !== 'none';

        // Hide distributors dropdown first
        hideDistributors();

        // If already open, close it
        if (isCurrentlyOpen) {
            hideChinaStores();
            return;
        }

        // Remove active from all cards first, then activate China
        document.querySelectorAll('.source-card-mini').forEach(card => card.classList.remove('active'));
        document.querySelector('[data-source="china"]')?.classList.add('active');

        // Show dropdown
        dropdown.style.display = 'block';

        // Load stores if not loaded
        if (!chinaStoresLoaded) {
            loadChinaStores();
        }
    }

    // Load China stores from API
    function loadChinaStores() {
        const loadingEl = document.getElementById('chinaStoresLoading');
        const listEl = document.getElementById('chinaStoresList');

        fetch('{{ route("products.china-stores") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.stores) {
                chinaStoresData = data.stores;
                chinaStoresLoaded = true;
                renderChinaStores(data.stores);
            } else {
                listEl.innerHTML = `<div class="text-center py-3">
                    <p class="text-muted mb-0">${isArabic ? 'لا توجد متاجر متاحة' : 'No stores available'}</p>
                </div>`;
            }
        })
        .catch(error => {
            console.error('Error loading China stores:', error);
            listEl.innerHTML = `<div class="text-center py-3">
                <p class="text-danger mb-0">${isArabic ? 'فشل تحميل المتاجر' : 'Failed to load stores'}</p>
            </div>`;
        });
    }

    // Render China stores in dropdown
    function renderChinaStores(stores) {
        const listEl = document.getElementById('chinaStoresList');

        if (!stores || stores.length === 0) {
            listEl.innerHTML = `<div class="text-center py-3">
                <p class="text-muted mb-0">${isArabic ? 'لا توجد متاجر متاحة' : 'No stores available'}</p>
            </div>`;
            return;
        }

        let html = '';
        stores.forEach(store => {
            const name = isArabic && store.name_ar ? store.name_ar : store.name;
            const description = isArabic && store.description_ar ? store.description_ar : store.description;
            const storeType = store.type === 'feed' ? 'feed' : 'category';
            const icon = store.type === 'feed' ? 'ri-fire-line' : 'ri-folder-line';
            const iconColor = store.type === 'feed' ? '#de2910' : '#e56300';

            html += `
                <div class="china-store-card" onclick="viewChinaStoreProducts('${store.id}', '${storeType}')" title="${description}">
                    <div class="china-store-icon" style="background: linear-gradient(135deg, ${store.type === 'feed' ? '#de2910' : '#561C04'} 0%, ${store.type === 'feed' ? '#ff5722' : '#e56300'} 100%);">
                        <i class="${icon}" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <span class="china-store-name">${name}</span>
                    <span class="china-store-type">${store.type === 'feed' ? (isArabic ? 'مجموعة' : 'Collection') : (isArabic ? 'فئة' : 'Category')}</span>
                </div>
            `;
        });

        listEl.innerHTML = html;
    }

    // Hide China stores dropdown
    function hideChinaStores() {
        const dropdown = document.getElementById('chinaStoresDropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
        // Only remove active if not on china store products page
        if (!window.location.search.includes('store_id=')) {
            document.querySelector('[data-source="china"]')?.classList.remove('active');
        }
    }

    // View products from a specific China store
    function viewChinaStoreProducts(storeId, storeType) {
        const keywordInput = document.getElementById('keyword');
        const keyword = keywordInput ? keywordInput.value.trim() : '';

        let url = '{{ route("products.china-store-products") }}?store_id=' + encodeURIComponent(storeId);
        if (keyword) {
            url += '&keyword=' + encodeURIComponent(keyword);
        }

        document.getElementById('loadingSpinner').style.display = 'block';
        window.location.href = url;
    }

    // Remove filter
    function removeFilter(filterName) {
        const url = new URL(window.location.href);
        url.searchParams.delete(filterName);
        window.location.href = url.toString();
    }

    // Clear all filters
    function clearAllFilters() {
        const url = new URL(window.location.href);
        url.searchParams.delete('ship_from');
        url.searchParams.delete('category_id');
        window.location.href = url.toString();
    }

    // Change sort
    function changeSort(value) {
        if (value) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort_by', value);
            window.location.href = url.toString();
        }
    }

    // Show loading on form submit
    document.getElementById('searchForm')?.addEventListener('submit', function() {
        document.getElementById('loadingSpinner').style.display = 'block';
    });

    // Assign product function
    function assignProduct(productId, productTitle, productTitleAr, productImage, productPrice, currency, categoryId, buttonElement) {
        const originalHtml = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="ri-loader-4-line me-1"></i> ' + (isArabic ? 'جاري...' : 'Assigning...');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const requestData = {
            aliexpress_product_id: productId,
            product_title: productTitle,
            product_title_ar: productTitleAr,
            product_image: productImage,
            product_price: productPrice,
            currency: currency,
            is_choice: document.getElementById('choiceOnlyInput')?.value === '1'
        };

        if (categoryId && categoryId.trim() !== '') {
            requestData.category_id = categoryId;
        }

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
                buttonElement.classList.remove('btn-warning');
                buttonElement.classList.add('btn-secondary');
                buttonElement.innerHTML = '<i class="ri-check-line me-1"></i> ' + (isArabic ? 'تم الإسناد' : 'Assigned');
                buttonElement.disabled = true;
                showToast('success', data.message || (isArabic ? 'تم الإسناد بنجاح!' : 'Product assigned successfully!'));
            } else {
                buttonElement.disabled = false;
                buttonElement.innerHTML = originalHtml;
                showToast('error', data.message || (isArabic ? 'فشل الإسناد' : 'Failed to assign product'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalHtml;
            showToast('error', isArabic ? 'حدث خطأ' : 'An error occurred');
        });
    }

    // Toast notification
    function showToast(type, message) {
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        toast.style.cssText = 'min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        toast.innerHTML = `
            <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-line me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Bulk selection functionality
    document.addEventListener('DOMContentLoaded', function() {
        const bulkActionsBar = document.getElementById('bulkActionsBar');
        const selectedCountSpan = document.getElementById('selectedCount');
        const selectAllBtn = document.getElementById('selectAllBtn');
        const deselectAllBtn = document.getElementById('deselectAllBtn');
        const bulkAssignBtn = document.getElementById('bulkAssignBtn');

        function updateSelectionUI() {
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const count = selectedCheckboxes.length;

            if (selectedCountSpan) selectedCountSpan.textContent = count;
            if (bulkActionsBar) bulkActionsBar.style.display = count > 0 ? 'block' : 'none';

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

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('product-checkbox')) {
                updateSelectionUI();
            }
        });

        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.product-checkbox').forEach(checkbox => {
                    if (!checkbox.disabled) checkbox.checked = true;
                });
                updateSelectionUI();
            });
        }

        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.product-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateSelectionUI();
            });
        }

        if (bulkAssignBtn) {
            bulkAssignBtn.addEventListener('click', function() {
                const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');

                if (selectedCheckboxes.length === 0) {
                    showToast('error', isArabic ? 'يرجى اختيار منتج واحد على الأقل' : 'Please select at least one product');
                    return;
                }

                if (!confirm(isArabic ? `هل تريد إسناد ${selectedCheckboxes.length} منتج؟` : `Assign ${selectedCheckboxes.length} product(s)?`)) {
                    return;
                }

                bulkAssignBtn.disabled = true;
                bulkAssignBtn.innerHTML = `<i class="ri-loader-4-line me-1 spinner-border spinner-border-sm"></i> ${isArabic ? 'جاري...' : 'Assigning...'}`;

                const urlParams = new URLSearchParams(window.location.search);
                const isChoiceFilter = urlParams.get('choice_only') === '1';

                const products = Array.from(selectedCheckboxes).map(checkbox => ({
                    aliexpress_product_id: checkbox.value,
                    product_title: checkbox.dataset.title,
                    product_title_ar: checkbox.dataset.titleAr || checkbox.dataset.title,
                    product_image: checkbox.dataset.image,
                    product_price: checkbox.dataset.price,
                    currency: checkbox.dataset.currency,
                    is_choice: isChoiceFilter,
                    ...(checkbox.dataset.categoryId && { category_id: checkbox.dataset.categoryId })
                }));

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

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
                        showToast('success', data.message || `${products.length} ${isArabic ? 'منتج تم إسناده' : 'product(s) assigned'}`);

                        selectedCheckboxes.forEach(checkbox => {
                            const card = checkbox.closest('.product-card');
                            checkbox.closest('.form-check')?.remove();

                            const assignBtn = card.querySelector('.assign-product-btn');
                            if (assignBtn) {
                                assignBtn.classList.remove('btn-warning');
                                assignBtn.classList.add('btn-secondary');
                                assignBtn.innerHTML = `<i class="ri-check-line me-1"></i> ${isArabic ? 'تم الإسناد' : 'Assigned'}`;
                                assignBtn.disabled = true;
                            }

                            card.style.border = '1px solid #e9ecef';
                            card.style.backgroundColor = 'white';
                        });

                        updateSelectionUI();
                    } else {
                        showToast('error', data.message || (isArabic ? 'فشل الإسناد' : 'Failed to assign'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', isArabic ? 'حدث خطأ' : 'An error occurred');
                })
                .finally(() => {
                    bulkAssignBtn.disabled = false;
                    bulkAssignBtn.innerHTML = `<i class="ri-pushpin-2-line me-1"></i> ${isArabic ? 'إسناد المنتجات المحددة' : 'Assign Selected Products'}`;
                });
            });
        }
    });
</script>

<style>
    /* Categories Scroll */
    .categories-scroll-container {
        position: relative;
    }

    .categories-scroll {
        display: flex;
        gap: 15px;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding: 10px 0;
    }

    .categories-scroll::-webkit-scrollbar {
        display: none;
    }

    .category-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 80px;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 10px;
        border-radius: 12px;
    }

    .category-item:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
    }

    .category-item.active {
        background: #fff3e0;
    }

    .category-item.active .category-icon {
        border-color: #e56300;
        box-shadow: 0 4px 12px rgba(229, 99, 0, 0.3);
    }

    .category-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        border: 2px solid transparent;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .category-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .category-icon i {
        font-size: 24px;
        color: #666;
    }

    .category-name {
        font-size: 0.75rem;
        text-align: center;
        color: #333;
        font-weight: 500;
        max-width: 90px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.3;
        height: 2.6em;
    }

    .scroll-btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #ddd;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .scroll-btn:hover {
        background: #f8f9fa;
    }

    /* Source Cards Mini */
    .source-cards-container {
        padding: 10px 0;
    }

    .source-card-mini {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .source-card-mini:hover {
        background: #fff3e0;
        border-color: #e56300;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(229, 99, 0, 0.2);
    }

    .source-card-mini.active {
        background: linear-gradient(135deg, #561C04 0%, #e56300 100%);
        border-color: #561C04;
        color: white;
    }

    .source-card-mini.active .country-name,
    .source-card-mini.active .distributor-count {
        color: white;
    }

    .source-card-mini.china {
        background: #fff5f5;
        border-color: #ffcdd2;
    }

    .source-card-mini.china:hover {
        background: #ffebee;
        border-color: #de2910;
    }

    .source-card-mini.china.active {
        background: linear-gradient(135deg, #de2910 0%, #ff5722 100%);
        border-color: #de2910;
    }

    .country-flag {
        font-size: 1.3rem;
    }

    .country-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: #333;
    }

    .distributor-count {
        background: #e56300;
        color: white;
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
    }

    .source-card-mini.active .distributor-count {
        background: rgba(255,255,255,0.3);
    }

    /* Distributors Dropdown */
    .distributors-dropdown {
        margin-top: 12px;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        animation: slideDown 0.2s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .distributors-dropdown-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f0f0f0;
        font-weight: 600;
        color: #333;
    }

    .distributors-inline-list {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .distributor-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 12px;
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 90px;
    }

    .distributor-card:hover {
        background: #fff3e0;
        border-color: #e56300;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(229, 99, 0, 0.15);
    }

    .distributor-card.active {
        background: #fff3e0;
        border-color: #e56300;
        box-shadow: 0 4px 12px rgba(229, 99, 0, 0.25);
    }

    .distributor-card-avatar.all-avatar {
        background: linear-gradient(135deg, #561C04 0%, #e56300 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .distributor-card-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        overflow: hidden;
        margin-bottom: 8px;
    }

    .distributor-card-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .distributor-card-avatar .avatar-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #561C04 0%, #e56300 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .distributor-card-name {
        font-size: 0.8rem;
        font-weight: 500;
        color: #333;
        text-align: center;
        max-width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* China Stores Dropdown */
    .china-stores-dropdown {
        border-color: #ffcdd2;
        background: #fff5f5;
    }

    .china-store-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 12px 16px;
        background: white;
        border: 2px solid #ffcdd2;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 100px;
        max-width: 120px;
    }

    .china-store-card:hover {
        background: #ffebee;
        border-color: #de2910;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(222, 41, 16, 0.2);
    }

    .china-store-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        box-shadow: 0 2px 8px rgba(222, 41, 16, 0.2);
    }

    .china-store-name {
        font-size: 0.8rem;
        font-weight: 600;
        color: #333;
        text-align: center;
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-bottom: 4px;
    }

    .china-store-type {
        font-size: 0.65rem;
        color: #de2910;
        background: #ffebee;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 500;
    }

    /* Legacy Source Cards (kept for compatibility) */
    .source-card {
        background: #2d3748;
        border-radius: 16px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        min-height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .source-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    }

    .source-card.active {
        border: 3px solid #e56300;
        box-shadow: 0 8px 25px rgba(229, 99, 0, 0.3);
    }

    .source-checkbox {
        position: absolute;
        top: 10px;
        left: 10px;
    }

    .source-checkbox input {
        width: 24px;
        height: 24px;
        cursor: pointer;
    }

    .source-content {
        text-align: center;
    }

    .source-flag {
        width: 100px;
        height: 70px;
        margin-bottom: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .source-title {
        color: white;
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .china-card .source-content {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .china-card .source-flag {
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(222, 41, 16, 0.3);
    }

    .china-card.active {
        border-color: #de2910;
        box-shadow: 0 8px 25px rgba(222, 41, 16, 0.3);
    }

    /* Subcategories */
    #subcategoriesContainer {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
    }

    .subcategories-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .subcategories-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        padding: 10px 0;
    }

    .subcategory-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 12px 16px;
        background: white;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 100px;
        max-width: 120px;
        border: 2px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .subcategory-item:hover {
        background: #fff3e0;
        border-color: #e56300;
        transform: translateY(-4px);
        box-shadow: 0 6px 16px rgba(229, 99, 0, 0.15);
    }

    .subcategory-item.active {
        background: #fff3e0;
        border-color: #e56300;
        box-shadow: 0 6px 16px rgba(229, 99, 0, 0.2);
    }

    .subcategory-item.active .subcategory-icon {
        border-color: #e56300;
        box-shadow: 0 4px 12px rgba(229, 99, 0, 0.25);
    }

    .subcategory-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        overflow: hidden;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .subcategory-icon.has-image {
        background: white;
        border-color: #ddd;
        padding: 4px;
    }

    .subcategory-icon.all-icon {
        background: linear-gradient(135deg, #561C04 0%, #e56300 100%);
        border-color: #561C04;
    }

    .subcategory-icon.all-icon i {
        color: white;
        font-size: 24px;
    }

    .subcategory-item:hover .subcategory-icon {
        border-color: #e56300;
        box-shadow: 0 4px 12px rgba(229, 99, 0, 0.2);
        transform: scale(1.05);
    }

    .subcategory-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }

    .subcategory-icon i {
        font-size: 24px;
        color: #561C04;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .subcategory-item span {
        font-size: 0.8rem;
        text-align: center;
        color: #333;
        font-weight: 500;
        max-width: 90px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Search Box */
    .search-box-container {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 15px;
    }

    /* Active Filters */
    .active-filters {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-label {
        font-weight: 600;
        color: #666;
    }

    .filter-tag {
        background: #e9ecef;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .filter-tag button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        margin-left: 5px;
        opacity: 0.7;
    }

    .filter-tag button:hover {
        opacity: 1;
    }

    /* Results Info */
    .results-info {
        background: #f8f9fa;
        padding: 12px 15px;
        border-radius: 10px;
    }

    /* Product Card */
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

    /* Button Styles */
    .btn-primary {
        background: #561C04;
        border: none;
    }

    .btn-primary:hover {
        background: #3d1503;
    }

    .btn-warning {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        border: none;
        color: #000;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #FFA500 0%, #e59400 100%);
    }

    /* Pagination */
    .pagination .page-item.active .page-link {
        background-color: #561C04;
        border-color: #561C04;
    }

    .pagination .page-link {
        color: #561C04;
    }

    .pagination .page-link:hover {
        background-color: #561C04;
        border-color: #561C04;
        color: white;
    }

    /* Header Gradient */
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

    /* Form Controls */
    .form-control:focus,
    .form-select:focus {
        border-color: #561C04;
        box-shadow: 0 0 0 0.2rem rgba(86, 28, 4, 0.25);
    }
</style>

@endsection
