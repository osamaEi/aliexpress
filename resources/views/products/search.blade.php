@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">AliExpress Product Search</h5>
            <div class="badge bg-success">aliexpress.ds.text.search</div>
        </div>

        <div class="card-body">
            <!-- Search Form -->
            <form id="searchForm" method="GET" action="{{ route('products.search-text') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="keyword" class="form-label">Search Keyword <span class="text-muted small">(optional if category selected)</span></label>
                        <input
                            type="text"
                            id="keyword"
                            name="keyword"
                            class="form-control form-control-lg"
                            placeholder="Search products... (phone, laptop, watch, etc.)"
                            value="{{ old('keyword', $keyword ?? '') }}"
                        >
                    </div>
                    <div class="col-md-2">
                        <label for="locale" class="form-label">Language</label>
                        <select name="locale" id="locale" class="form-select">
                            <option value="en_US" selected>🇬🇧 English</option>
                            <option value="ar_MA">🇦🇪 العربية</option>
                            <option value="es_ES">🇪🇸 Español</option>
                            <option value="fr_FR">🇫🇷 Français</option>
                            <option value="ru_RU">🇷🇺 Русский</option>
                            <option value="pt_BR">🇧🇷 Português</option>
                            <option value="de_DE">🇩🇪 Deutsch</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="country" class="form-label">Country</label>
                        <select name="country" id="country" class="form-select">
                            <option value="AE" selected>UAE</option>
                            <option value="US">United States</option>
                            <option value="GB">United Kingdom</option>
                            <option value="EG">Egypt</option>
                            <option value="SA">Saudi Arabia</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="currency" class="form-label">Currency</label>
                        <select name="currency" id="currency" class="form-select">
                            <option value="AED" selected>AED</option>
                            <option value="USD">USD</option>
                            <option value="GBP">GBP</option>
                            <option value="EGP">EGP</option>
                            <option value="SAR">SAR</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-search-line me-1"></i> Search
                        </button>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="mt-3">
                    <label class="form-label text-muted small">Quick Search:</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-search" data-keyword="phone">Phones</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-search" data-keyword="laptop">Laptops</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-search" data-keyword="watch">Watches</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-search" data-keyword="headphone">Headphones</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-search" data-keyword="bag">Bags</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-search" data-keyword="shoes">Shoes</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-search" data-keyword="camera">Cameras</button>
                    </div>
                </div>

                <!-- Hidden inputs for filters -->
                <input type="hidden" name="category_id" id="category_id" value="{{ request('category_id') }}">
                <input type="hidden" name="sort_filter" id="sort_filter" value="{{ request('sort_filter', 'orders') }}">

                <!-- Product Sort Filter Buttons -->
                <div class="mt-3">
                    <label class="form-label text-muted small">Sort By:</label>
                    <div class="btn-group" role="group">
                        <button type="button"
                                class="btn btn-sm {{ request('sort_filter') == 'orders' || !request('sort_filter') ? 'btn-primary' : 'btn-outline-primary' }}"
                                onclick="selectSortFilter('orders', this)">
                            <i class="ri-fire-line me-1"></i> Best Seller
                        </button>
                        <button type="button"
                                class="btn btn-sm {{ request('sort_filter') == 'newest' ? 'btn-primary' : 'btn-outline-primary' }}"
                                onclick="selectSortFilter('newest', this)">
                            <i class="ri-sparkling-line me-1"></i> Newest
                        </button>
                        <button type="button"
                                class="btn btn-sm {{ request('sort_filter') == 'price_low' ? 'btn-primary' : 'btn-outline-primary' }}"
                                onclick="selectSortFilter('price_low', this)">
                            <i class="ri-arrow-down-line me-1"></i> Price: Low to High
                        </button>
                        <button type="button"
                                class="btn btn-sm {{ request('sort_filter') == 'price_high' ? 'btn-primary' : 'btn-outline-primary' }}"
                                onclick="selectSortFilter('price_high', this)">
                            <i class="ri-arrow-up-line me-1"></i> Price: High to Low
                        </button>
                        <button type="button"
                                class="btn btn-sm {{ request('sort_filter') == 'rating' ? 'btn-primary' : 'btn-outline-primary' }}"
                                onclick="selectSortFilter('rating', this)">
                            <i class="ri-star-line me-1"></i> Top Rated
                        </button>
                    </div>
                </div>

                <!-- Main Categories Only -->
                @if(isset($categories) && count($categories) > 0)
                <div class="mt-4">
                    <label class="form-label text-muted small">Select Category:</label>

                    <!-- All Categories Option -->
                    <div class="mb-3">
                        <div class="category-card text-center p-2 rounded border d-inline-block {{ !request('category_id') ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary' }}"
                             style="cursor: pointer; transition: all 0.3s; min-width: 100px;"
                             onclick="selectCategory(null, this);">
                            <i class="ri-apps-line me-1" style="font-size: 18px;"></i>
                            <span class="small fw-semibold">All Categories</span>
                        </div>
                    </div>

                    <!-- Display only main categories as boxes -->
                    <div class="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                        @foreach($categories as $category)
                        <div class="category-card text-center p-3 rounded border {{ request('category_id') == $category->aliexpress_category_id ? 'border-primary bg-primary bg-opacity-10' : 'bg-white border-light' }}"
                             style="cursor: pointer; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"
                             onclick="selectCategory('{{ $category->aliexpress_category_id }}', this)"
                             onmouseover="this.style.borderColor='#007bff'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15)';"
                             onmouseout="if(!this.classList.contains('border-primary')) { this.style.borderColor='#dee2e6'; } this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">

                            <div class="category-icon mb-2" style="width: 60px; height: 60px; margin: 0 auto; background: #f8f9fa; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                @if($category->photo)
                                    <img src="{{ asset('storage/' . $category->photo) }}" alt="{{ $category->name }}" style="width: 48px; height: 48px; object-fit: contain;">
                                @elseif($category->image)
                                    <img src="{{ $category->image }}" alt="{{ $category->name }}" style="width: 48px; height: 48px; object-fit: contain;">
                                @else
                                    <i class="ri-folder-line" style="font-size: 28px; color: #6c757d;"></i>
                                @endif
                            </div>

                            <div class="category-name" style="font-size: 12px; line-height: 1.3; font-weight: 600;">
                                {{ $category->name }}
                                @if($category->name_ar)
                                    <div class="text-muted mt-1" dir="rtl" style="font-size: 11px; font-weight: 500;">{{ $category->name_ar }}</div>
                                @endif
                            </div>

                            @if(isset($category->children) && count($category->children) > 0)
                                <div class="mt-2">
                                    <span class="badge bg-info" style="font-size: 9px;">{{ count($category->children) }} subcategories</span>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Filters -->
                <div class="mt-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="sort_by" class="form-label small">Sort By</label>
                            <select name="sort_by" id="sort_by" class="form-select form-select-sm">
                                <option value="">Default</option>
                                <option value="orders,desc">Most Orders</option>
                                <option value="min_price,asc">Price: Low to High</option>
                                <option value="min_price,desc">Price: High to Low</option>
                                <option value="comments,desc">Most Reviews</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="min_price" class="form-label small">Min Price</label>
                            <input type="number" name="min_price" id="min_price" class="form-control form-control-sm" placeholder="0">
                        </div>
                        <div class="col-md-2">
                            <label for="max_price" class="form-label small">Max Price</label>
                            <input type="number" name="max_price" id="max_price" class="form-control form-control-sm" placeholder="1000">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Per Page</label>
                            <select name="per_page" class="form-select form-select-sm">
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="50">50</option>
                            </select>
                        </div>
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
                            <div class="card h-100 product-card">
                                <!-- Product Image -->
                                <div class="position-relative">
                                    <img
                                        src="{{ $product['item_main_pic'] }}"
                                        class="card-img-top"
                                        alt="{{ $product['title'] }}"
                                        style="height: 250px; object-fit: cover;"
                                        onerror="this.src='https://via.placeholder.com/250x250?text=No+Image'"
                                    >
                                    @if($product['discount'])
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                            {{ $product['discount'] }} OFF
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
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        @if($product['evaluate_rate'])
                                            <small class="text-muted">
                                                <i class="ri-star-fill text-warning"></i> {{ $product['evaluate_rate'] }}
                                            </small>
                                        @endif
                                        @if($product['orders'])
                                            <small class="text-muted">
                                                <i class="ri-shopping-cart-line"></i> {{ $product['orders'] }} orders
                                            </small>
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

                                        {{-- @auth
                                            @if(auth()->user()->user_type === 'seller') --}}
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
                                            {{-- @endif
                                        @endauth --}}

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success w-100"
                                            onclick="importProduct('{{ $product['item_id'] }}')"
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

    // Auto-change country and currency based on language selection
    document.getElementById('locale').addEventListener('change', function() {
        const locale = this.value;
        const countrySelect = document.getElementById('country');
        const currencySelect = document.getElementById('currency');

        // Map locales to country and currency
        const localeMap = {
            'en_US': { country: 'US', currency: 'USD' },
            'ar_MA': { country: 'AE', currency: 'AED' },
            'es_ES': { country: 'ES', currency: 'EUR' },
            'fr_FR': { country: 'FR', currency: 'EUR' },
            'ru_RU': { country: 'RU', currency: 'USD' },
            'pt_BR': { country: 'BR', currency: 'USD' },
            'de_DE': { country: 'DE', currency: 'EUR' },
        };

        if (localeMap[locale]) {
            countrySelect.value = localeMap[locale].country;
            currencySelect.value = localeMap[locale].currency;
        }
    });

    // Sort filter selection function
    function selectSortFilter(sortType, element) {
        // Update hidden input with selected sort filter
        document.getElementById('sort_filter').value = sortType;

        // Remove active state from all sort buttons
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
        });

        // Add active state to clicked button
        if (element) {
            element.classList.remove('btn-outline-primary');
            element.classList.add('btn-primary');
        }

        // Submit the form to fetch products with new sort
        document.getElementById('searchForm').submit();
    }

    // Category selection function - displays products from AliExpress for selected category
    function selectCategory(categoryId, element) {
        // Update hidden input with selected category ID
        document.getElementById('category_id').value = categoryId || '';

        // Remove active state from all category cards
        document.querySelectorAll('.category-card, .subcategory-card').forEach(item => {
            item.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            if (item.classList.contains('category-card')) {
                item.classList.add('border-secondary');
            } else {
                item.classList.add('border-light', 'bg-white');
            }
        });

        // Add active state to clicked category
        if (element) {
            element.classList.remove('border-secondary', 'border-light');
            element.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        }

        // Submit the form to fetch products from AliExpress
        // Works with or without keyword - category alone is enough
        document.getElementById('searchForm').submit();
    }

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
    function importProduct(productId) {
        if (!confirm('Import this product to your store?')) {
            return;
        }

        // You can implement AJAX import here
        // For now, redirect to import page with product ID
        alert('Import functionality will be implemented. Product ID: ' + productId);
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
    .product-card {
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    pre {
        max-height: 400px;
        overflow: auto;
        font-size: 0.85rem;
        padding: 1rem;
        border-radius: 0.5rem;
        background: rgba(0,0,0,0.3);
    }
</style>

@endsection
