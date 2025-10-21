<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AliExpress Dropshipping - Product Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">AliExpress Dropshipping</h1>
            <p class="text-gray-600">Search and import products from AliExpress</p>
        </div>

        <!-- Enrollment Status Alert -->
        @if(!$enrollment_status['enrolled'])
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Action Required:</strong> {{ $enrollment_status['message'] ?? 'Your account needs to be enrolled in the AliExpress Dropshipping Program.' }}
                        @if(isset($enrollment_status['action_required']))
                        <br>
                        <a href="https://ds.aliexpress.com/" target="_blank" class="underline font-medium">Click here to enroll now</a>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @else
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        <strong>Enrolled!</strong> {{ $enrollment_status['message'] ?? 'Your account is active.' }}
                        @if(isset($enrollment_status['product_count']))
                        ({{ $enrollment_status['product_count'] }} products available)
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Search Bar -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex gap-4">
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search products (optional - leave empty to see bestselling)..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                <button
                    onclick="searchProducts()"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Search Products
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading" class="hidden text-center py-12">
            <svg class="animate-spin h-12 w-12 mx-auto text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-4 text-gray-600">Loading products...</p>
        </div>

        <!-- Error Message -->
        <div id="error" class="hidden bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-red-700" id="errorMessage"></p>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div id="productsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Products will be inserted here -->
        </div>

        <!-- No Products Message -->
        <div id="noProducts" class="hidden text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
            <p class="mt-1 text-sm text-gray-500">Try searching with different keywords or check your enrollment status above.</p>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        async function searchProducts(page = 1) {
            const keyword = document.getElementById('searchInput').value;
            const loading = document.getElementById('loading');
            const error = document.getElementById('error');
            const productsGrid = document.getElementById('productsGrid');
            const noProducts = document.getElementById('noProducts');

            // Show loading
            loading.classList.remove('hidden');
            error.classList.add('hidden');
            productsGrid.innerHTML = '';
            noProducts.classList.add('hidden');

            try {
                const response = await fetch('/aliexpress/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ keyword, page })
                });

                const data = await response.json();

                loading.classList.add('hidden');

                if (data.success && data.products && data.products.length > 0) {
                    displayProducts(data.products);
                } else {
                    if (data.error || data.message) {
                        document.getElementById('errorMessage').textContent = data.error || data.message;
                        error.classList.remove('hidden');
                    } else {
                        noProducts.classList.remove('hidden');
                    }
                }
            } catch (err) {
                loading.classList.add('hidden');
                document.getElementById('errorMessage').textContent = 'Failed to fetch products: ' + err.message;
                error.classList.remove('hidden');
            }
        }

        function displayProducts(products) {
            const grid = document.getElementById('productsGrid');
            grid.innerHTML = products.map(product => `
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden">
                    <img
                        src="${product.product_main_image_url || product.product_image || ''}"
                        alt="${product.product_title || product.subject || 'Product'}"
                        class="w-full h-48 object-cover"
                        onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'"
                    >
                    <div class="p-4">
                        <h3 class="text-sm font-medium text-gray-900 line-clamp-2 mb-2">
                            ${product.product_title || product.subject || 'Untitled Product'}
                        </h3>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-lg font-bold text-blue-600">
                                    $${product.target_sale_price || product.sale_price || '0.00'}
                                </p>
                                ${product.target_original_price ? `
                                <p class="text-sm text-gray-500 line-through">
                                    $${product.target_original_price}
                                </p>
                                ` : ''}
                            </div>
                            <button
                                onclick="importProduct('${product.product_id}')"
                                class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                            >
                                Import
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function importProduct(productId) {
            alert('Import functionality - Product ID: ' + productId + '\nThis will be implemented to import the product to your store.');
            // You can implement the actual import logic here
        }

        // Allow search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });

        // Auto-search on page load to show bestselling products
        window.addEventListener('load', function() {
            setTimeout(() => searchProducts(), 500);
        });
    </script>
</body>
</html>
