@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.product_management') }}</h5>
            <div>
                    <a href="{{ route('products.aliexpress.import') }}" class="btn btn-primary btn-sm me-2">
                        <i class="ri-shopping-cart-line me-1"></i> {{ __('messages.import_from_aliexpress') }}
                    </a>
                    <a href="{{ route('products.create') }}" class="btn btn-success btn-sm">
                        <i class="ri-add-line me-1"></i> {{ __('messages.add_product') }}
                    </a>

            </div>
        </div>

        <!-- Filters -->
        <div class="card-body">
            <form method="GET" action="{{ route('products.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search_products_placeholder') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value="">{{ __('messages.all_categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="source" class="form-select">
                            <option value="">{{ __('messages.all_sources') }}</option>
                            <option value="aliexpress" {{ request('source') == 'aliexpress' ? 'selected' : '' }}>AliExpress</option>
                            <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>{{ __('messages.manual') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">{{ __('messages.all_status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-search-line me-1"></i> {{ __('messages.filter') }}
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            <i class="ri-refresh-line me-1"></i> {{ __('messages.reset') }}
                        </a>
                    </div>
                </div>
            </form>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- AliExpress Actions -->
            <div class="mb-3">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#aliexpressSearchModal">
                        <i class="ri-search-line me-1"></i> {{ __('messages.search_aliexpress') }}
                    </button>

                    <form action="{{ route('products.sync-all') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm" onclick="return confirm('{{ __('messages.sync_all_confirm') }}')">
                            <i class="ri-refresh-line me-1"></i> {{ __('messages.sync_all') }}
                        </button>
                    </form>

            </div>
        </div>

        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('messages.image') }}</th>
                        <th>{{ __('messages.product') }}</th>
                        <th>{{ __('messages.sku') }}</th>
                        <th>{{ __('messages.category') }}</th>
                        <th>{{ __('messages.price') }}</th>
                        <th>{{ __('messages.stock') }}</th>
                        <th>{{ __('messages.source') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                @if($product->getPrimaryImage())
                                    <img src="{{ $product->getPrimaryImage() }}" alt="{{ $product->name }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="ri-image-line text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->aliexpress_id)
                                    <br><small class="text-muted">AE ID: {{ $product->aliexpress_id }}</small>
                                @endif
                            </td>
                            <td>{{ $product->sku ?? '-' }}</td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>
                                <strong>${{ number_format($product->price, 2) }}</strong>
                                @if($product->compare_price)
                                    <br><small class="text-muted"><s>${{ number_format($product->compare_price, 2) }}</s></small>
                                @endif
                            </td>
                            <td>
                                @if($product->track_inventory)
                                    <span class="badge {{ $product->stock_quantity > 10 ? 'bg-success' : ($product->stock_quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $product->stock_quantity }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.unlimited') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($product->isAliexpressProduct())
                                    <span class="badge bg-info">
                                        <i class="ri-shopping-cart-line"></i> AliExpress
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.manual') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $product->is_active ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-line"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="{{ route('products.show', $product) }}">
                                                <i class="ri-eye-line me-2"></i> {{ __('messages.view') }}
                                            </a>

                                            <a class="dropdown-item" href="{{ route('products.edit', $product) }}">
                                                <i class="ri-pencil-line me-2"></i> {{ __('messages.edit') }}
                                            </a>
                                            @if($product->isAliexpressProduct())
                                                <form action="{{ route('products.sync', $product) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="ri-refresh-line me-2"></i> {{ __('messages.sync') }}
                                                    </button>
                                                </form>
                                            @endif

                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('{{ __('messages.confirm_delete_product') }}')">
                                                    <i class="ri-delete-bin-line me-2"></i> {{ __('messages.delete') }}
                                                </button>
                                            </form>

                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="ri-inbox-line" style="font-size: 48px; color: #ccc;"></i>
                                <p class="text-muted mt-2">{{ __('messages.no_products_found') }}</p>
                                    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm mt-2">{{ __('messages.add_your_first_product') }}</a>

                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="card-footer">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

<!-- AliExpress Search Modal -->
<div class="modal fade" id="aliexpressSearchModal" tabindex="-1" aria-labelledby="aliexpressSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aliexpressSearchModalLabel">
                    <i class="ri-shopping-cart-line me-2"></i>{{ __('messages.search_aliexpress_products') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search Form -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="searchKeyword" class="form-label">{{ __('messages.search_keyword') }}</label>
                        <input type="text" class="form-control" id="searchKeyword" placeholder="{{ __('messages.enter_product_keywords') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="searchCategory" class="form-label">{{ __('messages.category') }}</label>
                        <select class="form-select" id="searchCategory">
                            <option value="">{{ __('messages.select_category_optional') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="profitMargin" class="form-label">{{ __('messages.profit_margin') }} (%)</label>
                        <input type="number" class="form-control" id="profitMargin" value="30" min="0" max="100">
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" id="searchBtn">
                            <i class="ri-search-line me-1"></i> {{ __('messages.search') }}
                        </button>
                        <button type="button" class="btn btn-secondary" id="clearSearchBtn">
                            <i class="ri-refresh-line me-1"></i> {{ __('messages.clear') }}
                        </button>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('messages.loading') }}</span>
                    </div>
                    <p class="mt-2 text-muted">{{ __('messages.searching_aliexpress') }}</p>
                </div>

                <!-- Alert Messages -->
                <div id="alertContainer"></div>

                <!-- Results -->
                <div id="searchResults"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('searchBtn');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const searchKeyword = document.getElementById('searchKeyword');
    const searchCategory = document.getElementById('searchCategory');
    const profitMargin = document.getElementById('profitMargin');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const searchResults = document.getElementById('searchResults');
    const alertContainer = document.getElementById('alertContainer');

    // Search functionality
    searchBtn.addEventListener('click', function() {
        const keyword = searchKeyword.value.trim();

        if (!keyword) {
            showAlert('Please enter a search keyword', 'warning');
            return;
        }

        performSearch(keyword);
    });

    // Enter key to search
    searchKeyword.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });

    // Clear search
    clearSearchBtn.addEventListener('click', function() {
        searchKeyword.value = '';
        searchCategory.value = '';
        profitMargin.value = '30';
        searchResults.innerHTML = '';
        alertContainer.innerHTML = '';
    });

    // Perform search
    function performSearch(keyword) {
        loadingSpinner.style.display = 'block';
        searchResults.innerHTML = '';
        alertContainer.innerHTML = '';

        fetch('{{ route('products.aliexpress.search') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                keyword: keyword
            })
        })
        .then(response => response.json())
        .then(data => {
            loadingSpinner.style.display = 'none';

            if (data.success && data.products && data.products.length > 0) {
                displayResults(data.products);
            } else {
                showAlert('No products found. Please try different keywords.', 'info');
            }
        })
        .catch(error => {
            loadingSpinner.style.display = 'none';
            showAlert('Error searching products. Please check your API credentials and try again.', 'danger');
            console.error('Search error:', error);
        });
    }

    // Display search results
    function displayResults(products) {
        let html = '<div class="row g-3">';

        products.forEach(product => {
            const price = parseFloat(product.price || 0);
            const margin = parseFloat(profitMargin.value || 30);
            const sellingPrice = price * (1 + (margin / 100));

            html += `
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="${product.image_url || '/images/placeholder.png'}" class="card-img-top" alt="${product.name}" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">${product.name}</h6>
                            <p class="card-text">
                                <small class="text-muted">AliExpress Price: $${price.toFixed(2)}</small><br>
                                <strong class="text-primary">Your Price: $${sellingPrice.toFixed(2)}</strong><br>
                                <small class="text-success">Profit: $${(sellingPrice - price).toFixed(2)} (${margin}%)</small>
                            </p>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-primary btn-sm w-100 import-btn"
                                    data-product-id="${product.product_id}"
                                    data-product-name="${product.name}">
                                <i class="ri-download-line me-1"></i> Import Product
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        searchResults.innerHTML = html;

        // Attach import event handlers
        document.querySelectorAll('.import-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                importProduct(this.dataset.productId, this.dataset.productName, this);
            });
        });
    }

    // Import product
    function importProduct(productId, productName, button) {
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importing...';

        fetch('{{ route('products.aliexpress.import-product') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                aliexpress_id: productId,
                category_id: searchCategory.value || null,
                profit_margin: parseFloat(profitMargin.value || 30)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.innerHTML = '<i class="ri-check-line me-1"></i> Imported!';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
                showAlert(`Product "${productName}" imported successfully!`, 'success');

                // Reload page after 2 seconds to show new product
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                button.disabled = false;
                button.innerHTML = originalHtml;
                showAlert(data.message || 'Failed to import product. Please try again.', 'danger');
            }
        })
        .catch(error => {
            button.disabled = false;
            button.innerHTML = originalHtml;
            showAlert('Error importing product. Please try again.', 'danger');
            console.error('Import error:', error);
        });
    }

    // Show alert message
    function showAlert(message, type) {
        const alert = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        alertContainer.innerHTML = alert;

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alertElement = alertContainer.querySelector('.alert');
            if (alertElement) {
                alertElement.remove();
            }
        }, 5000);
    }
});
</script>
@endpush

@endsection
