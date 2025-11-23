@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="ri-ship-line me-2"></i>Shipping Freight Test</h5>
            <small>Test AliExpress freight calculation API with custom parameters</small>
        </div>

        <div class="card-body">
            <!-- Test Form -->
            <form id="freightTestForm">
                <div class="row g-3">
                    <!-- Product ID -->
                    <div class="col-md-6">
                        <label for="product_id" class="form-label fw-semibold">Product ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="product_id" name="product_id"
                                   placeholder="e.g., 1005006886632074" required>
                            <button type="button" class="btn btn-outline-secondary" id="fetchSkusBtn">
                                <i class="ri-search-line"></i> Fetch SKUs
                            </button>
                        </div>
                        <small class="text-muted">AliExpress Product ID</small>
                    </div>

                    <!-- SKU ID -->
                    <div class="col-md-6">
                        <label for="sku_id" class="form-label fw-semibold">SKU ID</label>
                        <input type="text" class="form-control" id="sku_id" name="sku_id"
                               placeholder="e.g., 12000038618907652">
                        <small class="text-muted">Leave empty for auto-detection or click "Fetch SKUs"</small>
                    </div>

                    <!-- SKU List (hidden by default) -->
                    <div class="col-12" id="skuListSection" style="display: none;">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ri-list-check me-2"></i>Available SKUs for Product: <span id="skuProductTitle"></span></h6>
                            </div>
                            <div class="card-body">
                                <div id="skuListContent"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="col-md-4">
                        <label for="quantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity"
                               value="1" min="1" required>
                    </div>

                    <!-- Country -->
                    <div class="col-md-4">
                        <label for="country" class="form-label fw-semibold">Ship To Country <span class="text-danger">*</span></label>
                        <select class="form-select" id="country" name="country" required>
                            <option value="AE">🇦🇪 UAE</option>
                            <option value="SA">🇸🇦 Saudi Arabia</option>
                            <option value="US">🇺🇸 USA</option>
                            <option value="GB">🇬🇧 UK</option>
                            <option value="CA">🇨🇦 Canada</option>
                            <option value="AU">🇦🇺 Australia</option>
                        </select>
                    </div>

                    <!-- Currency -->
                    <div class="col-md-4">
                        <label for="currency" class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                        <select class="form-select" id="currency" name="currency" required>
                            <option value="AED">AED</option>
                            <option value="SAR">SAR</option>
                            <option value="USD">USD</option>
                            <option value="GBP">GBP</option>
                            <option value="CAD">CAD</option>
                            <option value="AUD">AUD</option>
                        </select>
                    </div>

                    <!-- City -->
                    <div class="col-md-6">
                        <label for="city" class="form-label fw-semibold">City</label>
                        <input type="text" class="form-control" id="city" name="city"
                               placeholder="e.g., Dubai, Riyadh">
                        <small class="text-muted">Optional - leave empty if not required</small>
                    </div>

                    <!-- Province/State -->
                    <div class="col-md-6">
                        <label for="province" class="form-label fw-semibold">Province/State</label>
                        <input type="text" class="form-control" id="province" name="province"
                               placeholder="e.g., Dubai, Riyadh Region">
                        <small class="text-muted">Optional - leave empty if not required</small>
                    </div>

                    <!-- Language -->
                    <div class="col-md-6">
                        <label for="language" class="form-label fw-semibold">Language</label>
                        <select class="form-select" id="language" name="language">
                            <option value="en_US">English</option>
                            <option value="ar_MA">Arabic</option>
                            <option value="es_ES">Spanish</option>
                            <option value="fr_FR">French</option>
                        </select>
                    </div>

                    <!-- Locale -->
                    <div class="col-md-6">
                        <label for="locale" class="form-label fw-semibold">Locale</label>
                        <select class="form-select" id="locale" name="locale">
                            <option value="en_US">en_US</option>
                            <option value="ar_MA">ar_MA</option>
                            <option value="es_ES">es_ES</option>
                            <option value="fr_FR">fr_FR</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="ri-search-line me-2"></i>Calculate Freight
                        </button>
                    </div>
                </div>
            </form>

            <hr class="my-4">

            <!-- Loading State -->
            <div id="loadingState" style="display: none;" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Calculating freight costs...</p>
            </div>

            <!-- Results Section -->
            <div id="resultsSection" style="display: none;">
                <h5 class="mb-3"><i class="ri-check-line text-success me-2"></i>Freight Calculation Results</h5>

                <!-- Success Results -->
                <div id="successResults" style="display: none;">
                    <div class="alert alert-success">
                        <h6 class="mb-3"><i class="ri-truck-line me-2"></i>Shipping Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Freight Amount:</strong> <span id="freightAmount"></span></p>
                                <p class="mb-1"><strong>Currency:</strong> <span id="freightCurrency"></span></p>
                                <p class="mb-1"><strong>Shipping Method:</strong> <span id="shippingMethod"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Delivery Time:</strong> <span id="deliveryTime"></span></p>
                                <p class="mb-1"><strong>Service Name:</strong> <span id="serviceName"></span></p>
                                <p class="mb-1"><strong>Company:</strong> <span id="companyName"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Results -->
                <div id="errorResults" style="display: none;">
                    <div class="alert alert-danger">
                        <h6 class="mb-2"><i class="ri-error-warning-line me-2"></i>Error</h6>
                        <p id="errorMessage" class="mb-0"></p>
                    </div>
                </div>

                <!-- Raw API Response -->
                <div class="card bg-dark text-white mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ri-code-line me-2"></i>Raw API Response</h6>
                    </div>
                    <div class="card-body">
                        <pre id="rawResponse" class="mb-0" style="color: #fff;"></pre>
                    </div>
                </div>

                <!-- Request Parameters -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="ri-file-list-line me-2"></i>Request Parameters</h6>
                    </div>
                    <div class="card-body">
                        <pre id="requestParams" class="mb-0"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Test Examples -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0"><i class="ri-flashlight-line me-2"></i>Quick Test Examples</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">UAE Example</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary quick-test"
                                    data-product-id="1005006886632074"
                                    data-country="AE"
                                    data-currency="AED"
                                    data-quantity="1">
                                <i class="ri-arrow-right-line me-1"></i>Load UAE Test
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">Saudi Example</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary quick-test"
                                    data-product-id="1005006886632074"
                                    data-country="SA"
                                    data-currency="SAR"
                                    data-quantity="1">
                                <i class="ri-arrow-right-line me-1"></i>Load Saudi Test
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">USA Example</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary quick-test"
                                    data-product-id="1005006886632074"
                                    data-country="US"
                                    data-currency="USD"
                                    data-quantity="1">
                                <i class="ri-arrow-right-line me-1"></i>Load USA Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('freightTestForm');
    const loadingState = document.getElementById('loadingState');
    const resultsSection = document.getElementById('resultsSection');
    const successResults = document.getElementById('successResults');
    const errorResults = document.getElementById('errorResults');

    // Fetch SKUs button
    document.getElementById('fetchSkusBtn').addEventListener('click', function() {
        const productId = document.getElementById('product_id').value;

        if (!productId) {
            alert('Please enter a Product ID first');
            return;
        }

        // Show loading state
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Fetching...';

        // Fetch product details
        fetch('{{ route("shipping.test.product-details") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show SKU list section
                document.getElementById('skuListSection').style.display = 'block';
                document.getElementById('skuProductTitle').textContent = data.product_title;

                // Build SKU list HTML
                let html = '';

                if (data.first_available_sku) {
                    html += `<div class="alert alert-success mb-3">
                        <strong>Recommended SKU (First Available):</strong>
                        <code class="text-dark">${data.first_available_sku}</code>
                        <button type="button" class="btn btn-sm btn-success ms-2" onclick="document.getElementById('sku_id').value='${data.first_available_sku}'">
                            <i class="ri-check-line"></i> Use This
                        </button>
                    </div>`;
                }

                if (data.skus && data.skus.length > 0) {
                    html += `<p class="mb-2"><strong>Total SKUs Found:</strong> ${data.total_skus}</p>`;

                    // Check if we have numeric IDs or just property combos (use is_numeric flag from backend)
                    const hasNumericIds = data.skus.some(sku => sku.is_numeric === true);

                    if (!hasNumericIds) {
                        html += '<div class="alert alert-warning small mb-2">';
                        html += '<strong>⚠️ Warning:</strong> No numeric SKU IDs found! The product API only returned property combinations. ';
                        html += 'The freight API may fail with DELIVERY_INFO_EMPTY. This often means the product doesn\'t support detailed shipping calculations.';
                        html += '</div>';
                    } else {
                        html += '<div class="alert alert-info small mb-2">';
                        html += '<strong>✅ Per Official Docs:</strong> Use the <strong>numeric SKU ID</strong> (shown in <span class="text-success fw-bold">GREEN</span>) for selectedSkuId parameter. ';
                        html += 'Property combinations (shown in <span class="text-warning">YELLOW</span>) will NOT work with the freight API.';
                        html += '</div>';
                    }

                    html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                    html += '<thead><tr><th>SKU ID</th><th>Type</th><th>Property Combo</th><th>Price</th><th>Stock</th><th>Available</th><th>Action</th></tr></thead><tbody>';

                    data.skus.forEach(sku => {
                        const available = sku.available ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
                        const skuAttr = sku.sku_attr ? `<small class="text-muted">${sku.sku_attr}</small>` : '<small class="text-muted">N/A</small>';
                        // Use is_numeric flag from backend validation
                        const isNumeric = sku.is_numeric === true;
                        const idDisplay = isNumeric ?
                            `<code class="text-success fw-bold">${sku.id}</code>` :
                            `<code class="text-warning">${sku.id}</code>`;
                        const typeDisplay = isNumeric ?
                            '<span class="badge bg-success"><i class="ri-check-line"></i> Numeric</span>' :
                            '<span class="badge bg-warning"><i class="ri-error-warning-line"></i> Property Combo</span>';

                        html += `<tr class="${isNumeric ? 'table-success' : 'table-warning'}">
                            <td>${idDisplay}</td>
                            <td>${typeDisplay}</td>
                            <td>${skuAttr}</td>
                            <td>${sku.price || 'N/A'}</td>
                            <td>${sku.stock || 'N/A'}</td>
                            <td>${available}</td>
                            <td>
                                <button type="button" class="btn btn-sm ${isNumeric ? 'btn-success' : 'btn-warning'}" onclick="document.getElementById('sku_id').value='${sku.id}'">
                                    <i class="ri-check-line"></i> Use
                                </button>
                            </td>
                        </tr>`;
                    });

                    html += '</tbody></table></div>';

                    // Debug section - show raw SKU structure for ALL SKUs
                    if (data.skus.length > 0 && data.skus[0].raw_sku) {
                        html += '<div class="mt-3">';
                        html += '<button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#debugSkuStructure">';
                        html += '🔍 Debug: View All SKU Fields (Click to find real SKU IDs)';
                        html += '</button>';
                        html += '<div class="collapse mt-2" id="debugSkuStructure">';
                        html += '<div class="alert alert-dark">';
                        html += '<h6 class="text-white">Raw SKU Data - All Fields:</h6>';
                        html += '<small class="text-warning">Look for fields like: id, sku_id, sku_code, or any numeric identifier</small>';

                        // Show each SKU's structure
                        data.skus.forEach((sku, index) => {
                            html += `<div class="mt-3">`;
                            html += `<strong class="text-info">SKU #${index + 1}:</strong>`;
                            html += '<pre class="mt-1 p-2 bg-secondary text-white" style="max-height: 400px; overflow: auto; font-size: 0.7rem;">';
                            html += JSON.stringify(sku.raw_sku, null, 2);
                            html += '</pre></div>';
                        });

                        html += '</div></div></div>';
                    }
                } else {
                    html += '<p class="text-warning">No SKUs found in product details. This might be a single-variant product.</p>';
                }

                document.getElementById('skuListContent').innerHTML = html;

                // Scroll to SKU list
                document.getElementById('skuListSection').scrollIntoView({ behavior: 'smooth' });
            } else {
                alert('Failed to fetch product details: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error fetching product details: ' + error.message);
        })
        .finally(() => {
            // Reset button
            this.disabled = false;
            this.innerHTML = '<i class="ri-search-line"></i> Fetch SKUs';
        });
    });

    // Auto-change currency based on country
    document.getElementById('country').addEventListener('change', function() {
        const country = this.value;
        const currencySelect = document.getElementById('currency');

        const countryToCurrency = {
            'AE': 'AED',
            'SA': 'SAR',
            'US': 'USD',
            'GB': 'GBP',
            'CA': 'CAD',
            'AU': 'AUD'
        };

        if (countryToCurrency[country]) {
            currencySelect.value = countryToCurrency[country];
        }
    });

    // Quick test buttons
    document.querySelectorAll('.quick-test').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('product_id').value = this.dataset.productId;
            document.getElementById('country').value = this.dataset.country;
            document.getElementById('currency').value = this.dataset.currency;
            document.getElementById('quantity').value = this.dataset.quantity;

            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Collect form data
        const formData = {
            product_id: document.getElementById('product_id').value,
            sku_id: document.getElementById('sku_id').value || null,
            quantity: document.getElementById('quantity').value,
            country: document.getElementById('country').value,
            currency: document.getElementById('currency').value,
            city: document.getElementById('city').value || null,
            province: document.getElementById('province').value || null,
            language: document.getElementById('language').value,
            locale: document.getElementById('locale').value
        };

        // Show loading state
        loadingState.style.display = 'block';
        resultsSection.style.display = 'none';

        // Make API request
        fetch('{{ route("shipping.test.calculate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading
            loadingState.style.display = 'none';
            resultsSection.style.display = 'block';

            // Display request parameters
            document.getElementById('requestParams').textContent = JSON.stringify(formData, null, 2);

            // Display raw response
            document.getElementById('rawResponse').textContent = JSON.stringify(data, null, 2);

            if (data.success) {
                // Show success results
                successResults.style.display = 'block';
                errorResults.style.display = 'none';

                document.getElementById('freightAmount').textContent = data.freight_amount || 'N/A';
                document.getElementById('freightCurrency').textContent = data.freight_currency || 'N/A';
                document.getElementById('shippingMethod').textContent = data.shipping_method || 'N/A';
                document.getElementById('deliveryTime').textContent = data.delivery_time || 'N/A';
                document.getElementById('serviceName').textContent = data.service_name || 'N/A';
                document.getElementById('companyName').textContent = data.company_name || 'N/A';
            } else {
                // Show error results
                successResults.style.display = 'none';
                errorResults.style.display = 'block';

                let errorMsg = data.message || 'Unknown error occurred';

                // Add error code if available
                if (data.raw_response && data.raw_response.code) {
                    errorMsg += ` (Code: ${data.raw_response.code})`;
                }

                // Add error details
                if (data.raw_response && data.raw_response.msg) {
                    errorMsg += `<br><strong>Details:</strong> ${data.raw_response.msg}`;
                }

                document.getElementById('errorMessage').innerHTML = errorMsg;
            }

            // Scroll to results
            resultsSection.scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            loadingState.style.display = 'none';
            resultsSection.style.display = 'block';
            successResults.style.display = 'none';
            errorResults.style.display = 'block';

            document.getElementById('errorMessage').textContent = 'Request failed: ' + error.message;
            document.getElementById('rawResponse').textContent = JSON.stringify({ error: error.message }, null, 2);
        });
    });
});
</script>

<style>
pre {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    max-height: 400px;
    overflow: auto;
    font-size: 0.875rem;
}

#rawResponse {
    background: #1a1a1a;
    color: #fff;
}

.card {
    border-radius: 10px;
}

.quick-test:hover {
    transform: translateY(-2px);
    transition: all 0.2s;
}
</style>
@endsection
