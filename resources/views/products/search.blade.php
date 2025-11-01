@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card mb-6 shadow-sm border-0">
        <div class="card-header bg-gradient d-flex justify-content-between align-items-center py-3"
             style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="d-flex align-items-center">
                <div class="bg-white rounded-circle p-2 me-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                    <i class="ri-search-2-line" style="font-size: 24px; color: #667eea;"></i>
                </div>
                <div>
                    <h5 class="mb-0 text-white fw-bold">AliExpress Product Search</h5>
                    <small class="text-white-50">Search millions of products from AliExpress</small>
                </div>
            </div>
            <div class="badge bg-white text-primary px-3 py-2">
                <i class="ri-code-s-slash-line me-1"></i>aliexpress.ds.text.search
            </div>
        </div>

        <div class="card-body">
            <!-- Search Form -->
            <form id="searchForm" method="GET" action="{{ route('products.search-text') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="keyword" class="form-label fw-semibold">
                            <i class="ri-search-line me-1"></i>Search Keyword
                        </label>
                        <input
                            type="text"
                            id="keyword"
                            name="keyword"
                            class="form-control form-control-lg shadow-sm"
                            placeholder="e.g., phone, laptop, watch..."
                            value="{{ old('keyword', $keyword ?? '') }}"
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="category_dropdown" class="form-label fw-semibold">
                            <i class="ri-folder-line me-1"></i>Category
                        </label>
                        <select name="category_id" id="category_dropdown" class="form-select form-select-lg shadow-sm">
                            <option value="">All Categories</option>
                            @if(isset($categories) && count($categories) > 0)
                                @foreach($categories as $category)
                                    <option value="{{ $category->aliexpress_category_id }}"
                                            {{ request('category_id') == $category->aliexpress_category_id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                        @if($category->name_ar)
                                            - {{ $category->name_ar }}
                                        @endif
                                    </option>
                                    @if(isset($category->children) && count($category->children) > 0)
                                        @foreach($category->children as $child)
                                            <option value="{{ $child->aliexpress_category_id }}"
                                                    {{ request('category_id') == $child->aliexpress_category_id ? 'selected' : '' }}>
                                                &nbsp;&nbsp;&nbsp;↳ {{ $child->name }}
                                                @if($child->name_ar)
                                                    - {{ $child->name_ar }}
                                                @endif
                                            </option>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="country" class="form-label fw-semibold">
                            <i class="ri-ship-line me-1"></i>Ship To
                        </label>
                        <select name="country" id="country" class="form-select form-select-lg shadow-sm">
                            <option value="AE" {{ request('country') == 'AE' ? 'selected' : '' }}>🇦🇪 UAE</option>
                            <option value="SA" {{ request('country') == 'SA' ? 'selected' : '' }}>🇸🇦 Saudi</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow">
                            <i class="ri-search-line me-1"></i> Search
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters - Collapsible -->
                <div class="mt-3">
                    <button class="btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                        <i class="ri-filter-3-line me-1"></i> Advanced Filters
                        <i class="ri-arrow-down-s-line"></i>
                    </button>
                </div>

                <div class="collapse mt-3" id="advancedFilters">
                    <div class="card card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="locale" class="form-label small">Language</label>
                                <select name="locale" id="locale" class="form-select form-select-sm">
                                    <option value="en_US" {{ request('locale', 'en_US') == 'en_US' ? 'selected' : '' }}>🇬🇧 English</option>
                                    <option value="ar_MA" {{ request('locale') == 'ar_MA' ? 'selected' : '' }}>🇦🇪 العربية</option>
                                    <option value="es_ES" {{ request('locale') == 'es_ES' ? 'selected' : '' }}>🇪🇸 Español</option>
                                    <option value="fr_FR" {{ request('locale') == 'fr_FR' ? 'selected' : '' }}>🇫🇷 Français</option>
                                    <option value="ru_RU" {{ request('locale') == 'ru_RU' ? 'selected' : '' }}>🇷🇺 Русский</option>
                                    <option value="pt_BR" {{ request('locale') == 'pt_BR' ? 'selected' : '' }}>🇧🇷 Português</option>
                                    <option value="de_DE" {{ request('locale') == 'de_DE' ? 'selected' : '' }}>🇩🇪 Deutsch</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="currency" class="form-label small">Currency</label>
                                <select name="currency" id="currency" class="form-select form-select-sm">
                                    <option value="AED" {{ request('currency', 'AED') == 'AED' ? 'selected' : '' }}>AED</option>
                                    <option value="SAR" {{ request('currency') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="min_price" class="form-label small">Min Price</label>
                                <input type="number" name="min_price" id="min_price" class="form-control form-control-sm"
                                       placeholder="0" value="{{ request('min_price') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="max_price" class="form-label small">Max Price</label>
                                <input type="number" name="max_price" id="max_price" class="form-control form-control-sm"
                                       placeholder="1000" value="{{ request('max_price') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="sort_by" class="form-label small">Sort By</label>
                                <select name="sort_by" id="sort_by" class="form-select form-select-sm">
                                    <option value="">Default</option>
                                    <option value="orders,desc" {{ request('sort_by') == 'orders,desc' ? 'selected' : '' }}>Most Orders</option>
                                    <option value="min_price,asc" {{ request('sort_by') == 'min_price,asc' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="min_price,desc" {{ request('sort_by') == 'min_price,desc' ? 'selected' : '' }}>Price: High to Low</option>
                                    <option value="comments,desc" {{ request('sort_by') == 'comments,desc' ? 'selected' : '' }}>Most Reviews</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Per Page</label>
                                <select name="per_page" class="form-select form-select-sm">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                    <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Search -->
                <div class="mt-3">
                    <label class="form-label fw-semibold small">
                        <i class="ri-flashlight-line me-1"></i>Quick Search:
                    </label>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary quick-search" data-keyword="phone">
                            <i class="ri-smartphone-line me-1"></i>Phones
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary quick-search" data-keyword="laptop">
                            <i class="ri-macbook-line me-1"></i>Laptops
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary quick-search" data-keyword="watch">
                            <i class="ri-time-line me-1"></i>Watches
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary quick-search" data-keyword="headphone">
                            <i class="ri-headphone-line me-1"></i>Headphones
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary quick-search" data-keyword="bag">
                            <i class="ri-handbag-line me-1"></i>Bags
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary quick-search" data-keyword="shoes">
                            <i class="ri-contrast-drop-line me-1"></i>Shoes
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary quick-search" data-keyword="camera">
                            <i class="ri-camera-line me-1"></i>Cameras
                        </button>
                    </div>
                </div>
            </form>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Searching products...</p>
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
                <!-- Shipping Filter Notice -->
                <div class="alert alert-success mb-3 d-flex align-items-center">
                    <i class="ri-ship-line me-2" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Shipping Filter Active:</strong> All products shown ship to
                        @if(request('country') == 'SA')
                            <strong>🇸🇦 Saudi Arabia</strong>
                        @else
                            <strong>🇦🇪 United Arab Emirates</strong>
                        @endif
                        <br>
                        <small class="text-muted">Only showing products available for delivery to your selected country</small>
                    </div>
                </div>

                <div class="alert alert-info mb-4">
                    <i class="ri-information-line me-2"></i>
                    Found <strong>{{ count($products) }}</strong> products
                    @if(isset($total_count))
                        (Total: <strong>{{ number_format($total_count) }}</strong>)
                    @endif
                    @if(!empty($keyword))
                        for "<strong>{{ $keyword }}</strong>"
                    @endif
                    @if(request('category_id'))
                        @php
                            $selectedCategory = $allCategories->firstWhere('aliexpress_category_id', request('category_id'));
                        @endphp
                        @if($selectedCategory)
                            in category: <strong>{{ $selectedCategory->name }}</strong>
                            @if($selectedCategory->name_ar)
                                <span dir="rtl">({{ $selectedCategory->name_ar }})</span>
                            @endif
                        @endif
                    @endif
                </div>
            @endif

            <!-- Products Grid -->
            @if(isset($products) && count($products) > 0)
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" id="productsGrid">
                    @foreach($products as $product)
                        <div class="col">
                            <div class="card h-100 product-card shadow-sm">
                                <!-- Product Image -->
                                <div class="position-relative overflow-hidden" style="background: #f8f9fa;">
                                    <img
                                        src="{{ $product['item_main_pic'] }}"
                                        class="card-img-top"
                                        alt="{{ $product['title'] }}"
                                        style="height: 280px; object-fit: contain; padding: 15px;"
                                        onerror="this.src='https://via.placeholder.com/280x280?text=No+Image'"
                                    >
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
                                                <i class="ri-fire-line"></i> {{ $product['orders'] }}+ sold
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-auto">
                                        <a
                                            href="{{ $product['item_url'] }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary w-100 mb-2"
                                        >
                                            <i class="ri-external-link-line me-1"></i> View on AliExpress
                                        </a>

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
                                                        <i class="ri-check-line me-1"></i> Already Assigned
                                                    </button>
                                                @else
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-warning w-100 mb-2 assign-product-btn"
                                                        onclick="assignProduct('{{ $product['item_id'] }}', '{{ addslashes($product['title']) }}', '{{ $product['item_main_pic'] }}', {{ $product['sale_price'] }}, '{{ request('currency', 'AED') }}', this)"
                                                        data-product-id="{{ $product['item_id'] }}"
                                                    >
                                                        <i class="ri-pushpin-line me-1"></i> Assign to Me
                                                    </button>
                                                @endif
                                            @endif
                                        @endauth

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success w-100"
                                            onclick="importProduct('{{ $product['item_id'] }}', this)"
                                        >
                                            <i class="ri-download-line me-1"></i> Import Product
                                        </button>
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
                                        <i class="ri-arrow-left-s-line"></i> Previous
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
                                        Next <i class="ri-arrow-right-s-line"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>

                    <div class="text-center text-muted small mb-4">
                        Showing page {{ $currentPage }} of {{ $totalPages }} ({{ number_format($total_count) }} total products)
                    </div>
                @endif
            @elseif(isset($keyword))
                <div class="text-center py-5">
                    <i class="ri-search-line" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3 text-muted">No products found for "{{ $keyword }}"</h5>
                    <p class="text-muted">Try these suggestions:</p>
                    <ul class="list-unstyled text-muted">
                        <li>Use different keywords (try: phone, laptop, dress, shoes)</li>
                        <li>Use more general terms instead of specific brand names</li>
                        <li>Try searching in English</li>
                        <li>Enable Debug mode to see API response</li>
                    </ul>
                    <div class="mt-4">
                        <p class="text-muted small">Popular searches that usually work:</p>
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
                    <h5 class="mt-3">Welcome to AliExpress Product Search</h5>
                    <p class="text-muted">Search for any product using the search bar above or click on quick links</p>
                    <small class="text-muted">Powered by aliexpress.ds.text.search API</small>
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

    // Category dropdown change handler
    document.getElementById('category_dropdown')?.addEventListener('change', function() {
        // Optionally auto-submit when category changes
        // document.getElementById('searchForm').submit();
    });

    // Assign product to seller function
    function assignProduct(productId, productTitle, productImage, productPrice, currency, buttonElement) {
        // Show loading state
        const originalHtml = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="ri-loader-4-line me-1"></i> Assigning...';

        // CSRF token for Laravel
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Make AJAX request
        fetch('{{ route("products.assign") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                aliexpress_product_id: productId,
                product_title: productTitle,
                product_image: productImage,
                product_price: productPrice,
                currency: currency
            })
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

    // Import product function
    function importProduct(productId, buttonElement) {
        if (!confirm('Import this product to your store?')) {
            return;
        }

        // Show loading state
        const originalHtml = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="ri-loader-4-line me-1"></i> Importing...';

        // CSRF token for Laravel
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Make AJAX request
        fetch('{{ route("products.aliexpress.import-product") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                aliexpress_id: productId,
                currency: '{{ request("currency", "AED") }}',
                country: '{{ request("country", "AE") }}',
                profit_margin: 30 // Default 30% profit margin
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Change button to success state
                buttonElement.classList.remove('btn-success');
                buttonElement.classList.add('btn-info');
                buttonElement.innerHTML = '<i class="ri-check-line me-1"></i> Imported';
                buttonElement.disabled = true;

                // Show success message
                showToast('success', data.message || 'Product imported successfully!');

                // Optional: redirect to product edit page
                if (data.product && data.product.id) {
                    setTimeout(() => {
                        window.location.href = '/products/' + data.product.id;
                    }, 1500);
                }
            } else {
                // Restore button
                buttonElement.disabled = false;
                buttonElement.innerHTML = originalHtml;

                // Show error message
                showToast('error', data.message || 'Failed to import product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalHtml;
            showToast('error', 'An error occurred. Please try again.');
        });
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
        border-color: #667eea;
    }

    .product-card img {
        transition: transform 0.3s ease;
    }

    .product-card:hover img {
        transform: scale(1.05);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-outline-primary {
        border-color: #667eea;
        color: #667eea;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: #667eea;
        border-color: #667eea;
        transform: translateY(-2px);
    }

    .quick-search {
        transition: all 0.2s ease;
    }

    .quick-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
</style>

@endsection
