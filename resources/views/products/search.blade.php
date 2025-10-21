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
                        <label for="keyword" class="form-label">Search Keyword</label>
                        <input
                            type="text"
                            id="keyword"
                            name="keyword"
                            class="form-control form-control-lg"
                            placeholder="Search products... (phone, laptop, watch, etc.)"
                            value="{{ old('keyword', $keyword ?? '') }}"
                            required
                        >
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

                <!-- Advanced Options -->
                <div class="mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="debug" id="debug" value="1">
                        <label class="form-check-label text-muted small" for="debug">
                            Show Debug Information
                        </label>
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
                                            AED {{ number_format($product['sale_price'], 2) }}
                                        </h5>
                                        @if($product['original_price'] > $product['sale_price'])
                                            <small class="text-muted text-decoration-line-through">
                                                AED {{ number_format($product['original_price'], 2) }}
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
                                                <i class="ri-shopping-cart-line"></i> {{ number_format($product['orders']) }} orders
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
            @elseif(isset($keyword))
                <div class="text-center py-5">
                    <i class="ri-search-line" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3 text-muted">No products found</h5>
                    <p class="text-muted">Try a different search term or check Debug mode</p>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ri-shopping-bag-line" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3">Welcome to AliExpress Product Search</h5>
                    <p class="text-muted">Search for any product using the search bar above or click on quick links</p>
                    <small class="text-muted">Powered by aliexpress.ds.text.search API</small>
                </div>
            @endif

            <!-- Debug Information -->
            @if(isset($debug) && request('debug'))
                <div class="mt-4">
                    <div class="card bg-dark text-light">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-warning">Debug Information</h6>
                            <button class="btn btn-sm btn-outline-warning" onclick="toggleDebug()">Toggle</button>
                        </div>
                        <div class="card-body" id="debugInfo" style="display: block;">
                            <h6 class="text-warning">Request Info</h6>
                            <pre class="text-success">{{ json_encode($debug['debug'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

                            <h6 class="text-warning mt-3">HTTP Response</h6>
                            <p class="text-info">Status Code: {{ $debug['raw']['http_code'] ?? 'N/A' }}</p>

                            <h6 class="text-warning mt-3">Raw Response (first 3000 chars)</h6>
                            <pre class="text-light">{{ substr($debug['raw']['response'] ?? '', 0, 3000) }}</pre>

                            <h6 class="text-warning mt-3">Full Decoded Response</h6>
                            <pre class="text-light">{{ json_encode($debug['raw']['decoded'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Quick search buttons
    document.querySelectorAll('.quick-search').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('keyword').value = this.dataset.keyword;
            document.getElementById('searchForm').submit();
        });
    });

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
@endpush

@push('styles')
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
@endpush
@endsection
