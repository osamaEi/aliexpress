@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ri-shopping-cart-line me-2"></i> Import Products from AliExpress
            </h5>
            <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">
                <i class="ri-arrow-left-line me-1"></i> Back to Products
            </a>
        </div>

        <div class="card-body">
            <!-- Search Form -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="input-group">
                        <input type="text" id="searchKeyword" class="form-control form-control-lg" placeholder="Search AliExpress products (e.g., phone case, watch, headphones)..." />
                        <button class="btn btn-primary" type="button" id="searchBtn">
                            <i class="ri-search-line me-1"></i> Search
                        </button>
                    </div>
                    <small class="text-muted">Enter keywords to search for products on AliExpress</small>
                </div>
            </div>

            <!-- Import Settings -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="importCategory" class="form-label">Import to Category</label>
                    <select id="importCategory" class="form-select">
                        <option value="">Select Category (Optional)</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="profitMargin" class="form-label">Profit Margin (%)</label>
                    <input type="number" id="profitMargin" class="form-control" value="30" min="0" max="100" step="0.1" />
                    <small class="text-muted">Your profit margin on top of AliExpress price + shipping</small>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Searching AliExpress...</p>
            </div>

            <!-- Search Results -->
            <div id="searchResults" class="row g-4"></div>

            <!-- No Results -->
            <div id="noResults" class="text-center py-5" style="display: none;">
                <i class="ri-search-line" style="font-size: 64px; color: #ccc;"></i>
                <p class="text-muted mt-3">No products found. Try different keywords.</p>
            </div>

            <!-- Initial State -->
            <div id="initialState" class="text-center py-5">
                <i class="ri-shopping-cart-2-line" style="font-size: 64px; color: #ccc;"></i>
                <p class="text-muted mt-3">Search for products to import from AliExpress</p>
            </div>
        </div>
    </div>
</div>

<!-- Product Card Template -->
<template id="productCardTemplate">
    <div class="col-md-6 col-lg-4 product-card">
        <div class="card h-100">
            <img class="card-img-top product-image" alt="Product Image">
            <div class="card-body">
                <h5 class="card-title product-title"></h5>
                <div class="mb-2">
                    <span class="badge bg-info product-price"></span>
                    <span class="badge bg-secondary ms-2 product-shipping"></span>
                </div>
                <p class="card-text text-muted small product-description"></p>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Your Price:</small>
                        <strong class="d-block product-your-price"></strong>
                    </div>
                    <button class="btn btn-primary btn-sm import-btn" data-product-id="">
                        <i class="ri-download-line me-1"></i> Import
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.product-card .card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.product-card .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.product-image {
    height: 250px;
    object-fit: cover;
}
.product-title {
    font-size: 14px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.product-description {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('searchBtn');
    const searchKeyword = document.getElementById('searchKeyword');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const searchResults = document.getElementById('searchResults');
    const noResults = document.getElementById('noResults');
    const initialState = document.getElementById('initialState');
    const productTemplate = document.getElementById('productCardTemplate');

    // Search on button click
    searchBtn.addEventListener('click', searchProducts);

    // Search on Enter key
    searchKeyword.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchProducts();
        }
    });

    function searchProducts() {
        const keyword = searchKeyword.value.trim();

        if (!keyword) {
            alert('Please enter a search keyword');
            return;
        }

        // Show loading
        initialState.style.display = 'none';
        noResults.style.display = 'none';
        searchResults.innerHTML = '';
        loadingIndicator.style.display = 'block';

        // Make API request
        fetch('{{ route('products.aliexpress.search') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ keyword: keyword })
        })
        .then(response => response.json())
        .then(data => {
            loadingIndicator.style.display = 'none';

            if (data.success && data.products && data.products.length > 0) {
                displayProducts(data.products);
            } else {
                noResults.style.display = 'block';
            }
        })
        .catch(error => {
            loadingIndicator.style.display = 'none';
            alert('Error searching products: ' + error.message);
            console.error('Error:', error);
        });
    }

    function displayProducts(products) {
        searchResults.innerHTML = '';

        products.forEach(product => {
            const card = productTemplate.content.cloneNode(true);
            const profitMargin = parseFloat(document.getElementById('profitMargin').value) || 30;

            // Calculate prices
            const aliexpressPrice = parseFloat(product.target_sale_price || product.sale_price || 0);
            const shipping = parseFloat(product.estimated_delivery_fee || 0);
            const cost = aliexpressPrice + shipping;
            const yourPrice = cost * (1 + (profitMargin / 100));

            // Populate card
            card.querySelector('.product-image').src = product.product_main_image_url || 'https://via.placeholder.com/250';
            card.querySelector('.product-title').textContent = product.product_title || 'Unnamed Product';
            card.querySelector('.product-price').textContent = `$${aliexpressPrice.toFixed(2)}`;
            card.querySelector('.product-shipping').textContent = `+$${shipping.toFixed(2)} shipping`;
            card.querySelector('.product-your-price').textContent = `$${yourPrice.toFixed(2)}`;
            card.querySelector('.product-description').textContent = product.product_title || '';

            const importBtn = card.querySelector('.import-btn');
            importBtn.setAttribute('data-product-id', product.product_id || '');
            importBtn.addEventListener('click', () => importProduct(product.product_id));

            searchResults.appendChild(card);
        });
    }

    function importProduct(productId) {
        if (!productId) {
            alert('Invalid product ID');
            return;
        }

        const categoryId = document.getElementById('importCategory').value;
        const profitMargin = document.getElementById('profitMargin').value;

        const btn = event.target.closest('.import-btn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importing...';

        fetch('{{ route('products.aliexpress.import-product') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                aliexpress_id: productId,
                category_id: categoryId,
                profit_margin: profitMargin
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="ri-check-line me-1"></i> Imported!';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');

                setTimeout(() => {
                    window.location.href = '{{ route('products.index') }}';
                }, 1500);
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            alert('Error importing product: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
            console.error('Error:', error);
        });
    }
});
</script>
@endsection
