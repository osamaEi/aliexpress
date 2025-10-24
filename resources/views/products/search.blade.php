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

                <!-- Hidden input for category filter (always present) -->
                <input type="hidden" name="category_id" id="category_id" value="{{ request('category_id') }}">

                <!-- Categories -->
                @if(isset($categories) && count($categories) > 0)
                <div class="mt-4">
                    <label class="form-label text-muted small">Main Categories:</label>
                    <div class="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px;">
                        <!-- All Categories Option -->
                        <div class="category-card text-center p-3 rounded border {{ !request('category_id') ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary' }}"
                             style="cursor: pointer; transition: all 0.3s;"
                             onclick="selectCategory(null, this); clearSubcategories();">
                            <div class="category-icon mb-2" style="width: 56px; height: 56px; margin: 0 auto; display: flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 8px;">
                                <i class="ri-apps-line" style="font-size: 28px; color: #666;"></i>
                            </div>
                            <div class="category-name small fw-semibold">All Categories</div>
                        </div>

                        @foreach($categories as $mainCategory)
                        <!-- Main Category Card -->
                        <div class="category-card main-category-card text-center p-3 rounded border {{ request('category_id') == $mainCategory->aliexpress_category_id ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary' }}"
                             style="cursor: pointer; transition: all 0.3s;"
                             onclick="showSubcategories('{{ $mainCategory->id }}', this, '{{ $mainCategory->aliexpress_category_id }}')"
                             data-category-id="{{ $mainCategory->id }}"
                             data-has-children="{{ count($mainCategory->children) > 0 ? 'true' : 'false' }}"
                             onmouseover="this.style.borderColor='#007bff'; this.style.backgroundColor='rgba(0,123,255,0.05)';"
                             onmouseout="if(!this.classList.contains('border-primary')) { this.style.borderColor='#dee2e6'; this.style.backgroundColor='transparent'; }">
                            <div class="category-icon mb-2" style="width: 56px; height: 56px; margin: 0 auto;">
                                @if($mainCategory->photo)
                                    <img src="{{ asset('storage/' . $mainCategory->photo) }}" alt="{{ $mainCategory->name }}" style="width: 56px; height: 56px; object-fit: contain; border-radius: 8px;">
                                @elseif($mainCategory->image)
                                    <img src="{{ $mainCategory->image }}" alt="{{ $mainCategory->name }}" style="width: 56px; height: 56px; object-fit: contain; border-radius: 8px;">
                                @else
                                    <div style="width: 56px; height: 56px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-folder-line" style="font-size: 28px; color: #666;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="category-name small fw-semibold" style="line-height: 1.2; font-size: 11px;">
                                {{ $mainCategory->name }}
                                @if($mainCategory->name_ar)
                                    <div class="text-muted mt-1" dir="rtl" style="font-size: 10px;">{{ $mainCategory->name_ar }}</div>
                                @endif
                            </div>
                            @if(count($mainCategory->children) > 0)
                                <div class="mt-1">
                                    <span class="badge bg-info" style="font-size: 9px;">{{ count($mainCategory->children) }} sub</span>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <!-- Subcategories Box (Hidden by default) -->
                    @foreach($categories as $mainCategory)
                        @if(count($mainCategory->children) > 0)
                        <div id="subcategories-{{ $mainCategory->id }}" class="subcategories-box mt-3" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label text-muted small mb-0">
                                    <i class="ri-arrow-right-s-line"></i> {{ $mainCategory->name }} Subcategories:
                                </label>
                                <button type="button" class="btn btn-sm btn-link text-muted" onclick="clearSubcategories()">
                                    <i class="ri-close-line"></i> Hide
                                </button>
                            </div>
                            <div class="subcategories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                                @foreach($mainCategory->children as $subCategory)
                                <div class="subcategory-card text-center p-2 rounded border {{ request('category_id') == $subCategory->aliexpress_category_id ? 'border-primary bg-white' : 'bg-white border-light' }}"
                                     style="cursor: pointer; transition: all 0.3s;"
                                     onclick="selectCategory('{{ $subCategory->aliexpress_category_id }}', this)"
                                     onmouseover="this.style.borderColor='#007bff'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)';"
                                     onmouseout="if(!this.classList.contains('border-primary')) { this.style.borderColor='#dee2e6'; } this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div class="subcategory-icon mb-1" style="width: 40px; height: 40px; margin: 0 auto; background: #e9ecef; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                        @if($subCategory->photo)
                                            <img src="{{ asset('storage/' . $subCategory->photo) }}" alt="{{ $subCategory->name }}" style="width: 32px; height: 32px; object-fit: contain;">
                                        @elseif($subCategory->image)
                                            <img src="{{ $subCategory->image }}" alt="{{ $subCategory->name }}" style="width: 32px; height: 32px; object-fit: contain;">
                                        @else
                                            <i class="ri-folder-2-line" style="font-size: 20px; color: #6c757d;"></i>
                                        @endif
                                    </div>
                                    <div class="subcategory-name" style="font-size: 10px; line-height: 1.2; font-weight: 500;">
                                        {{ $subCategory->name }}
                                        @if($subCategory->name_ar)
                                            <div class="text-muted mt-1" dir="rtl" style="font-size: 9px;">{{ $subCategory->name_ar }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach
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
                    for "<strong>{{ $keyword }}</strong>"
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

    // Show subcategories when main category clicked
    function showSubcategories(categoryId, element, aliexpressId) {
        // Hide all subcategory boxes
        document.querySelectorAll('.subcategories-box').forEach(box => {
            box.style.display = 'none';
        });

        // Show the clicked category's subcategories
        const subcatBox = document.getElementById('subcategories-' + categoryId);
        if (subcatBox) {
            subcatBox.style.display = 'block';
        }

        // Highlight selected main category
        document.querySelectorAll('.main-category-card').forEach(card => {
            card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            card.classList.add('border-secondary');
            card.style.borderColor = '#dee2e6';
            card.style.backgroundColor = 'transparent';
        });
        element.classList.remove('border-secondary');
        element.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        element.style.borderColor = '#007bff';
        element.style.backgroundColor = 'rgba(0,123,255,0.1)';

        // If category has no children, filter products directly
        const hasChildren = element.getAttribute('data-has-children') === 'true';
        if (!hasChildren) {
            selectCategory(aliexpressId, element);
        }
    }

    // Clear/hide all subcategory boxes
    function clearSubcategories() {
        document.querySelectorAll('.subcategories-box').forEach(box => {
            box.style.display = 'none';
        });

        // Remove highlights from main categories
        document.querySelectorAll('.main-category-card').forEach(card => {
            card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            card.classList.add('border-secondary');
            card.style.borderColor = '#dee2e6';
            card.style.backgroundColor = 'transparent';
        });

        // Clear subcategory highlights
        document.querySelectorAll('.subcategory-card').forEach(card => {
            card.classList.remove('border-primary', 'bg-white');
            card.classList.add('border-light', 'bg-white');
        });
    }

    // Category selection function
    function selectCategory(categoryId, element) {
        // Update hidden input
        document.getElementById('category_id').value = categoryId || '';

        // Remove active state from all category and subcategory cards
        document.querySelectorAll('.category-card, .subcategory-card').forEach(item => {
            item.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            if (item.classList.contains('category-card')) {
                item.classList.add('border-secondary');
            } else {
                item.classList.add('border-light');
            }
        });

        // Add active state to clicked category
        if (element) {
            element.classList.remove('border-secondary', 'border-light');
            element.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        }

        // Submit the form to search by category
        // Allow category-only searches or searches with keywords
        const keyword = document.getElementById('keyword').value;
        const hasProducts = document.getElementById('productsGrid') !== null;

        // Submit if: keyword exists, products are shown, or category is selected
        if ((keyword && keyword.trim() !== '') || hasProducts || categoryId) {
            document.getElementById('searchForm').submit();
        }
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
