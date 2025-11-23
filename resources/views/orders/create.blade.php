@extends('dashboard')

@section('content')
<div class="col-12">
    <!-- Progress Tracker -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="step-item active" id="step-product">
                    <div class="step-icon"><i class="ri-shopping-bag-line"></i></div>
                    <div class="step-text">Product</div>
                </div>
                <div class="step-line" id="line-sku"></div>
                <div class="step-item" id="step-sku">
                    <div class="step-icon"><i class="ri-list-check"></i></div>
                    <div class="step-text">SKU</div>
                </div>
                <div class="step-line" id="line-customer"></div>
                <div class="step-item" id="step-customer">
                    <div class="step-icon"><i class="ri-user-line"></i></div>
                    <div class="step-text">Customer</div>
                </div>
                <div class="step-line" id="line-shipping"></div>
                <div class="step-item" id="step-shipping">
                    <div class="step-icon"><i class="ri-map-pin-line"></i></div>
                    <div class="step-text">Shipping</div>
                </div>
                <div class="step-line" id="line-review"></div>
                <div class="step-item" id="step-review">
                    <div class="step-icon"><i class="ri-check-line"></i></div>
                    <div class="step-text">Review</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="ri-add-circle-line me-2"></i>Create New Order</h5>
            <small>Fill in the details below to create a new order</small>
        </div>

        <div class="card-body">
            <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                @csrf

                <!-- Product Selection -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="product_id" class="form-label fw-semibold">
                            <i class="ri-shopping-bag-line text-primary me-1"></i> Product *
                        </label>
                        @if(isset($product))
                            <input type="hidden" name="product_id" id="product_id" value="{{ $product->id }}">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        @if($product->images && count($product->images) > 0)
                                            <div class="col-auto">
                                                <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="rounded" style="width: 100px; height: 100px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                            </div>
                                        @endif
                                        <div class="col">
                                            <h6 class="mb-1">{{ $product->name }}</h6>
                                            <p class="mb-1 text-primary"><strong>{{ $product->currency }} {{ number_format($product->price, 2) }}</strong></p>
                                            @if($product->isAliexpressProduct())
                                                <span class="badge bg-info"><i class="ri-global-line me-1"></i>AliExpress Product</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                <option value="">-- Select a product --</option>
                                @foreach(App\Models\Product::active()->get() as $prod)
                                    <option value="{{ $prod->id }}" data-is-aliexpress="{{ $prod->isAliexpressProduct() ? '1' : '0' }}">
                                        {{ $prod->name }} - {{ $prod->currency }} {{ number_format($prod->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                <!-- SKU Selection Section -->
                <div class="row mb-4" id="sku-selection-section" style="display: none;">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">
                            <i class="ri-list-check text-primary me-1"></i> Product SKU / Variant
                        </label>
                        <input type="hidden" name="selected_sku_attr" id="selected_sku_attr">
                        <input type="hidden" name="selected_sku_id" id="selected_sku_id">

                        <div class="card border-secondary" id="sku-display-card" style="display: none;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto" id="sku-image-container" style="display: none;">
                                        <img id="sku-selected-image" src="" alt="SKU" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                    <div class="col">
                                        <h6 class="mb-1" id="sku-selected-name">No SKU selected</h6>
                                        <p class="mb-1"><strong id="sku-selected-price"></strong></p>
                                        <span id="sku-selected-stock" class="badge bg-success"></span>
                                        <span id="sku-selected-type" class="badge bg-info"></span>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-outline-primary" id="change-sku-btn">
                                            <i class="ri-edit-line me-1"></i> Change SKU
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary w-100" id="select-sku-btn" style="display: none;">
                            <i class="ri-list-check me-1"></i> Select Product SKU / Variant
                        </button>

                        <div class="alert alert-info mt-2" id="sku-loading-message" style="display: none;">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-info me-2"></div>
                                <span>Loading available SKUs...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label for="quantity" class="form-label fw-semibold">
                            <i class="ri-hashtag text-primary me-1"></i> Quantity *
                        </label>
                        <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <!-- Customer Information -->
                <h5 class="mb-3">
                    <i class="ri-user-line text-primary me-2"></i> Customer Information
                </h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label">Full Name *</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" required>
                        @error('customer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="customer_email" class="form-label">Email</label>
                        <input type="email" name="customer_email" id="customer_email" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email') }}">
                        @error('customer_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label for="phone_country" class="form-label">Phone Country Code *</label>
                        <select name="phone_country" id="phone_country" class="form-select @error('phone_country') is-invalid @enderror" required>
                            <option value="971" {{ old('phone_country', '971') == '971' ? 'selected' : '' }}>+971 (UAE)</option>
                            <option value="966" {{ old('phone_country') == '966' ? 'selected' : '' }}>+966 (Saudi Arabia)</option>
                            <option value="20" {{ old('phone_country') == '20' ? 'selected' : '' }}>+20 (Egypt)</option>
                        </select>
                        @error('phone_country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="customer_phone" class="form-label">Phone Number * <small class="text-muted">(without country code or leading zero)</small></label>
                        <input type="text" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" placeholder="e.g., 501234567" required>
                        @error('customer_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <strong>Format examples:</strong><br>
                            • UAE: 501234567 (9 digits starting with 5)<br>
                            • Saudi Arabia: 501234567 (9 digits starting with 5)<br>
                            • Egypt: 1001234567 (10 digits)
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Shipping Information -->
                <h5 class="mb-3">
                    <i class="ri-map-pin-line text-primary me-2"></i> Shipping Information
                </h5>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="shipping_address" class="form-label">Address *</label>
                        <input type="text" name="shipping_address" id="shipping_address" class="form-control @error('shipping_address') is-invalid @enderror" value="{{ old('shipping_address') }}" required>
                        @error('shipping_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="shipping_address2" class="form-label">Address Line 2</label>
                        <input type="text" name="shipping_address2" id="shipping_address2" class="form-control @error('shipping_address2') is-invalid @enderror" value="{{ old('shipping_address2') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="shipping_city" class="form-label">City *</label>
                        <input type="text" name="shipping_city" id="shipping_city" class="form-control @error('shipping_city') is-invalid @enderror" value="{{ old('shipping_city') }}" required>
                        @error('shipping_city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="shipping_province" class="form-label">Emirate/Province *</label>
                        <select name="shipping_province" id="shipping_province" class="form-select @error('shipping_province') is-invalid @enderror" required>
                            <option value="">Select Emirate/Province</option>
                            <!-- UAE Emirates -->
                            <optgroup label="UAE Emirates" id="uae-provinces">
                                <option value="Abu Dhabi" {{ old('shipping_province') == 'Abu Dhabi' ? 'selected' : '' }}>Abu Dhabi</option>
                                <option value="Dubai" {{ old('shipping_province') == 'Dubai' ? 'selected' : '' }}>Dubai</option>
                                <option value="Sharjah" {{ old('shipping_province') == 'Sharjah' ? 'selected' : '' }}>Sharjah</option>
                                <option value="Ajman" {{ old('shipping_province') == 'Ajman' ? 'selected' : '' }}>Ajman</option>
                                <option value="Umm Al Quwain" {{ old('shipping_province') == 'Umm Al Quwain' ? 'selected' : '' }}>Umm Al Quwain</option>
                                <option value="Ras Al Khaimah" {{ old('shipping_province') == 'Ras Al Khaimah' ? 'selected' : '' }}>Ras Al Khaimah</option>
                                <option value="Fujairah" {{ old('shipping_province') == 'Fujairah' ? 'selected' : '' }}>Fujairah</option>
                            </optgroup>
                            <!-- Saudi Arabia Provinces -->
                            <optgroup label="Saudi Arabia Provinces" id="saudi-provinces" style="display:none;">
                                <option value="Riyadh" {{ old('shipping_province') == 'Riyadh' ? 'selected' : '' }}>Riyadh</option>
                                <option value="Makkah" {{ old('shipping_province') == 'Makkah' ? 'selected' : '' }}>Makkah</option>
                                <option value="Madinah" {{ old('shipping_province') == 'Madinah' ? 'selected' : '' }}>Madinah</option>
                                <option value="Eastern Province" {{ old('shipping_province') == 'Eastern Province' ? 'selected' : '' }}>Eastern Province</option>
                                <option value="Asir" {{ old('shipping_province') == 'Asir' ? 'selected' : '' }}>Asir</option>
                                <option value="Tabuk" {{ old('shipping_province') == 'Tabuk' ? 'selected' : '' }}>Tabuk</option>
                                <option value="Qassim" {{ old('shipping_province') == 'Qassim' ? 'selected' : '' }}>Qassim</option>
                                <option value="Ha\'il" {{ old('shipping_province') == 'Ha\'il' ? 'selected' : '' }}>Ha'il</option>
                                <option value="Northern Borders" {{ old('shipping_province') == 'Northern Borders' ? 'selected' : '' }}>Northern Borders</option>
                                <option value="Jazan" {{ old('shipping_province') == 'Jazan' ? 'selected' : '' }}>Jazan</option>
                                <option value="Najran" {{ old('shipping_province') == 'Najran' ? 'selected' : '' }}>Najran</option>
                                <option value="Al Bahah" {{ old('shipping_province') == 'Al Bahah' ? 'selected' : '' }}>Al Bahah</option>
                                <option value="Al Jawf" {{ old('shipping_province') == 'Al Jawf' ? 'selected' : '' }}>Al Jawf</option>
                            </optgroup>
                        </select>
                        @error('shipping_province')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="shipping_country" class="form-label">Country *</label>
                        <select name="shipping_country" id="shipping_country" class="form-select @error('shipping_country') is-invalid @enderror" required>
                            <option value="AE" {{ old('shipping_country', 'AE') == 'AE' ? 'selected' : '' }}>UAE (AE)</option>
                            <option value="SA" {{ old('shipping_country') == 'SA' ? 'selected' : '' }}>Saudi Arabia (SA)</option>
                        </select>
                        @error('shipping_country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="shipping_zip" class="form-label">Postal Code <span class="text-danger">*</span></label>
                        <input type="text" name="shipping_zip" id="shipping_zip" class="form-control @error('shipping_zip') is-invalid @enderror" value="{{ old('shipping_zip') }}" placeholder="e.g., 00000" required>
                        @error('shipping_zip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">For UAE, you can use "00000" if no postal code</small>
                    </div>
                </div>

                <!-- Customer Notes -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="customer_notes" class="form-label">Customer Notes</label>
                        <textarea name="customer_notes" id="customer_notes" class="form-control @error('customer_notes') is-invalid @enderror" rows="3">{{ old('customer_notes') }}</textarea>
                        @error('customer_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Shipping Cost Information - Displayed Before Submit -->
                <div class="row mb-4" id="freight-section" style="display: none;">
                    <div class="col-md-12">
                        <div id="freight-info-container" class="card border-primary shadow-sm" style="display: none;">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="ri-ship-line me-2"></i>Shipping Cost Details</h5>
                                <small>Please review the shipping information before placing your order</small>
                            </div>
                            <div class="card-body">
                                <div id="freight-loading" style="display: none;">
                                    <div class="d-flex align-items-center justify-content-center py-3">
                                        <div class="spinner-border text-primary me-3" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <span class="h6 mb-0">Calculating shipping cost...</span>
                                    </div>
                                </div>
                                <div id="freight-result" style="display: none;"></div>
                                <div id="freight-error" class="alert alert-danger mb-0" style="display: none;">
                                    <i class="ri-error-warning-line me-2"></i>
                                    <span id="freight-error-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Instruction to complete address -->
                        <div id="freight-instruction" class="alert alert-info mt-3">
                            <i class="ri-information-line me-2"></i>
                            <strong>Please complete the shipping address above</strong> to calculate shipping cost and see estimated delivery time.
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back to Orders
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg" id="submit-order-btn">
                        <i class="ri-save-line me-1"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SKU Selection Modal -->
    <div class="modal fade" id="skuModal" tabindex="-1" aria-labelledby="skuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="skuModalLabel">
                        <i class="ri-list-check me-2"></i>Select Product SKU / Variant
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="sku-modal-loading" class="text-center py-5">
                        <div class="spinner-border text-primary mb-3"></div>
                        <p>Loading SKUs...</p>
                    </div>

                    <div id="sku-modal-error" class="alert alert-danger" style="display: none;">
                        <i class="ri-error-warning-line me-2"></i>
                        <span id="sku-modal-error-message"></span>
                    </div>

                    <div id="sku-modal-content" style="display: none;">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Note:</strong> Only <strong class="text-success">numeric SKU IDs</strong> work with freight calculation.
                            Property combinations won't calculate shipping correctly.
                        </div>
                        <div id="sku-list-container"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Progress Tracker Styles */
.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
}

.step-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 8px;
    transition: all 0.3s;
    border: 3px solid #e9ecef;
}

.step-item.active .step-icon {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25);
}

.step-item.completed .step-icon {
    background: #198754;
    color: white;
    border-color: #198754;
}

.step-text {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-align: center;
}

.step-item.active .step-text {
    color: #0d6efd;
}

.step-item.completed .step-text {
    color: #198754;
}

.step-line {
    height: 3px;
    background: #e9ecef;
    flex: 1;
    margin: 0 10px;
    margin-top: -30px;
}

.step-line.active {
    background: #0d6efd;
}

/* SKU Card Styles */
.sku-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s;
}

.sku-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.sku-card.numeric {
    border-color: #198754;
    background: #f8fff8;
}

.sku-card.property-combo {
    border-color: #ffc107;
    background: #fffcf0;
}

.sku-card.out-of-stock {
    opacity: 0.6;
    cursor: not-allowed;
}

.sku-card.selected {
    border-color: #0d6efd;
    background: #e7f1ff;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('customer_phone');
    const phoneCountrySelect = document.getElementById('phone_country');
    const shippingCountrySelect = document.getElementById('shipping_country');
    const shippingProvinceSelect = document.getElementById('shipping_province');
    const uaeProvinces = document.getElementById('uae-provinces');
    const saudiProvinces = document.getElementById('saudi-provinces');

    // SKU Selection Elements
    const skuSelectionSection = document.getElementById('sku-selection-section');
    const skuDisplayCard = document.getElementById('sku-display-card');
    const selectSkuBtn = document.getElementById('select-sku-btn');
    const changeSkuBtn = document.getElementById('change-sku-btn');
    const skuLoadingMessage = document.getElementById('sku-loading-message');
    const skuModal = new bootstrap.Modal(document.getElementById('skuModal'));

    let availableSkus = [];
    let selectedSkuData = null;

    // Freight calculation for AliExpress products
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const cityInput = document.getElementById('shipping_city');
    const provinceInput = document.getElementById('shipping_province');
    const freightSection = document.getElementById('freight-section');
    const freightInfoContainer = document.getElementById('freight-info-container');
    const freightLoading = document.getElementById('freight-loading');
    const freightResult = document.getElementById('freight-result');
    const freightError = document.getElementById('freight-error');
    const freightErrorMessage = document.getElementById('freight-error-message');
    const freightInstruction = document.getElementById('freight-instruction');

    let currentProductId = @if(isset($product)) {{ $product->id }} @else null @endif;
    let freightCalculationTimeout = null;
    let freightCalculated = false;

    // Product data cache (to store if product is from AliExpress)
    const productData = {
        @if(isset($product))
        {{ $product->id }}: {
            isAliexpress: {{ $product->isAliexpressProduct() ? 'true' : 'false' }}
        }
        @endif
    };

    // Show/hide freight section based on product selection
    function updateFreightSectionVisibility() {
        if (!currentProductId) {
            if (freightSection) freightSection.style.display = 'none';
            return;
        }

        // Check if product is from AliExpress
        if (productData[currentProductId] && productData[currentProductId].isAliexpress) {
            if (freightSection) freightSection.style.display = 'block';
        } else {
            if (freightSection) freightSection.style.display = 'none';
        }
    }

    // Helper function to clean up alert messages
    function clearSkuAlerts() {
        const alerts = skuLoadingMessage.parentElement.querySelectorAll('.alert-warning, .alert-danger, .alert-info');
        alerts.forEach(alert => {
            if (alert !== skuLoadingMessage) {
                alert.remove();
            }
        });
    }

    // Function to load SKUs for selected product
    function loadProductSkus(productId) {
        // Clear any existing alerts
        clearSkuAlerts();

        skuLoadingMessage.style.display = 'block';
        skuDisplayCard.style.display = 'none';
        selectSkuBtn.style.display = 'none';

        const aliexpressId = productData[productId]?.aliexpressId;

        if (!aliexpressId) {
            console.error('No AliExpress ID found for product');
            skuLoadingMessage.style.display = 'none';
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning mt-2';
            alertDiv.innerHTML = '<i class="ri-error-warning-line me-2"></i>Product SKU information is not available. The product may not be synced with AliExpress.';
            skuLoadingMessage.parentElement.appendChild(alertDiv);
            return;
        }

        fetch('{{ route("shipping.test.product-details") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                product_id: aliexpressId
            })
        })
        .then(response => response.json())
        .then(data => {
            skuLoadingMessage.style.display = 'none';

            if (data.success && data.skus && data.skus.length > 0) {
                availableSkus = data.skus;

                // Auto-select first numeric SKU if available
                if (data.first_available_sku) {
                    const firstSku = data.skus.find(s => s.id === data.first_available_sku);
                    if (firstSku) {
                        selectSku(firstSku);
                    } else {
                        selectSkuBtn.style.display = 'block';
                    }
                } else {
                    // No numeric SKU found - show warning
                    selectSkuBtn.style.display = 'block';
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-warning mt-2';
                    alertDiv.innerHTML = '<i class="ri-error-warning-line me-2"></i><strong>No numeric SKUs found.</strong> This product only has property combinations, which may not work with freight calculation.';
                    const existingAlert = skuLoadingMessage.parentElement.querySelector('.alert-warning');
                    if (!existingAlert) {
                        skuLoadingMessage.parentElement.appendChild(alertDiv);
                    }
                }
            } else {
                selectSkuBtn.style.display = 'none';
                // Product might not have SKUs or is single-variant
                console.log('No SKUs found for product');
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-info mt-2';
                alertDiv.innerHTML = '<i class="ri-information-line me-2"></i>This product appears to be a single-variant item (no SKU selection needed).';
                skuLoadingMessage.parentElement.appendChild(alertDiv);
            }
        })
        .catch(error => {
            console.error('Error loading SKUs:', error);
            skuLoadingMessage.style.display = 'none';
            selectSkuBtn.style.display = 'none';
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger mt-2';
            alertDiv.innerHTML = '<i class="ri-error-warning-line me-2"></i>Error loading SKU information: ' + error.message;
            skuLoadingMessage.parentElement.appendChild(alertDiv);
        });
    }

    // Function to select a SKU
    function selectSku(sku) {
        selectedSkuData = sku;

        // Update hidden form fields
        document.getElementById('selected_sku_attr').value = sku.sku_attr || sku.id;
        document.getElementById('selected_sku_id').value = sku.id;

        // Update display
        document.getElementById('sku-selected-name').textContent = sku.sku_attr ?
            (sku.sku_attr.includes('#') ? sku.sku_attr.split('#')[1] : sku.sku_attr) : sku.id;
        document.getElementById('sku-selected-price').textContent = sku.price ?
            `$${parseFloat(sku.price).toFixed(2)}` : '';

        const stockBadge = document.getElementById('sku-selected-stock');
        if (sku.available && sku.stock > 0) {
            stockBadge.className = 'badge bg-success';
            stockBadge.textContent = `${sku.stock} in stock`;
        } else {
            stockBadge.className = 'badge bg-danger';
            stockBadge.textContent = 'Out of stock';
        }

        const typeBadge = document.getElementById('sku-selected-type');
        if (sku.is_numeric) {
            typeBadge.className = 'badge bg-success';
            typeBadge.innerHTML = '<i class="ri-check-line me-1"></i>Numeric SKU';
        } else {
            typeBadge.className = 'badge bg-warning';
            typeBadge.innerHTML = '<i class="ri-error-warning-line me-1"></i>Property Combo';
        }

        // Show/hide image
        const imageContainer = document.getElementById('sku-image-container');
        if (sku.raw_sku && sku.raw_sku.ae_sku_property_dtos) {
            const props = sku.raw_sku.ae_sku_property_dtos.ae_sku_property_d_t_o;
            if (props && props[0] && props[0].sku_image) {
                document.getElementById('sku-selected-image').src = props[0].sku_image;
                imageContainer.style.display = 'block';
            } else {
                imageContainer.style.display = 'none';
            }
        } else {
            imageContainer.style.display = 'none';
        }

        skuDisplayCard.style.display = 'block';
        selectSkuBtn.style.display = 'none';

        // Update step progress
        updateStepProgress('sku', true);
    }

    // Function to show SKU modal
    function showSkuModal() {
        document.getElementById('sku-modal-loading').style.display = 'block';
        document.getElementById('sku-modal-error').style.display = 'none';
        document.getElementById('sku-modal-content').style.display = 'none';

        skuModal.show();

        if (availableSkus.length > 0) {
            renderSkuList();
        } else {
            // Load SKUs if not already loaded
            const productId = currentProductId;
            const aliexpressId = productData[productId]?.aliexpressId;

            if (!aliexpressId) {
                showSkuModalError('Product does not have AliExpress ID. Cannot load SKUs.');
                return;
            }

            fetch('{{ route("shipping.test.product-details") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    product_id: aliexpressId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.skus) {
                    availableSkus = data.skus;
                    renderSkuList();
                } else {
                    showSkuModalError('No SKUs available for this product');
                }
            })
            .catch(error => {
                showSkuModalError('Error loading SKUs: ' + error.message);
            });
        }
    }

    // Function to render SKU list in modal
    function renderSkuList() {
        document.getElementById('sku-modal-loading').style.display = 'none';
        document.getElementById('sku-modal-content').style.display = 'block';

        const container = document.getElementById('sku-list-container');
        let html = '';

        availableSkus.forEach((sku, index) => {
            const isNumeric = sku.is_numeric === true;
            const isAvailable = sku.available && sku.stock > 0;
            const isSelected = selectedSkuData && selectedSkuData.id === sku.id;

            const cardClass = `sku-card ${isNumeric ? 'numeric' : 'property-combo'} ${!isAvailable ? 'out-of-stock' : ''} ${isSelected ? 'selected' : ''}`;

            html += `<div class="${cardClass}" onclick="${isAvailable ? `selectSkuFromModal(${index})` : ''}" style="${!isAvailable ? 'cursor: not-allowed;' : ''}">`;
            html += '<div class="row align-items-center">';

            // SKU Image
            if (sku.raw_sku && sku.raw_sku.ae_sku_property_dtos) {
                const props = sku.raw_sku.ae_sku_property_dtos.ae_sku_property_d_t_o;
                if (props && props[0] && props[0].sku_image) {
                    html += '<div class="col-auto">';
                    html += `<img src="${props[0].sku_image}" alt="SKU" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">`;
                    html += '</div>';
                }
            }

            html += '<div class="col">';

            // SKU Name/Attr
            const displayName = sku.sku_attr ?
                (sku.sku_attr.includes('#') ? sku.sku_attr.split('#')[1] : sku.sku_attr) :
                `SKU: ${sku.id}`;
            html += `<h6 class="mb-1">${displayName}</h6>`;

            // Price
            if (sku.price) {
                html += `<p class="mb-2 text-primary"><strong>$${parseFloat(sku.price).toFixed(2)}</strong></p>`;
            }

            // Badges
            html += '<div class="d-flex gap-2 flex-wrap">';
            if (isNumeric) {
                html += '<span class="badge bg-success"><i class="ri-check-line me-1"></i>Numeric SKU</span>';
            } else {
                html += '<span class="badge bg-warning"><i class="ri-error-warning-line me-1"></i>Property Combo</span>';
            }

            if (isAvailable) {
                html += `<span class="badge bg-info">${sku.stock} in stock</span>`;
            } else {
                html += '<span class="badge bg-danger">Out of stock</span>';
            }

            if (isSelected) {
                html += '<span class="badge bg-primary"><i class="ri-check-line me-1"></i>Selected</span>';
            }

            html += '</div>';

            // SKU ID
            html += `<small class="text-muted mt-1 d-block">ID: <code>${sku.id}</code></small>`;

            html += '</div>';
            html += '</div>';
            html += '</div>';
        });

        if (availableSkus.length === 0) {
            html = '<p class="text-center text-muted">No SKUs available</p>';
        }

        container.innerHTML = html;
    }

    // Function to select SKU from modal
    window.selectSkuFromModal = function(index) {
        const sku = availableSkus[index];
        if (sku && sku.available && sku.stock > 0) {
            selectSku(sku);
            skuModal.hide();
        }
    };

    // Function to show error in SKU modal
    function showSkuModalError(message) {
        document.getElementById('sku-modal-loading').style.display = 'none';
        document.getElementById('sku-modal-error').style.display = 'block';
        document.getElementById('sku-modal-error-message').textContent = message;
    }

    // Event listeners for SKU buttons
    if (selectSkuBtn) {
        selectSkuBtn.addEventListener('click', showSkuModal);
    }

    if (changeSkuBtn) {
        changeSkuBtn.addEventListener('click', showSkuModal);
    }

    // Function to update step progress
    function updateStepProgress(step, completed) {
        const stepElement = document.getElementById('step-' + step);
        const lineElement = document.getElementById('line-' + step);

        if (completed) {
            stepElement.classList.add('completed');
            if (lineElement) lineElement.classList.add('active');
        } else {
            stepElement.classList.remove('completed');
            if (lineElement) lineElement.classList.remove('active');
        }
    }

    // Handle product selection change
    if (productSelect) {
        productSelect.addEventListener('change', function() {
            const selectedProductId = this.value;

            if (!selectedProductId) {
                currentProductId = null;
                skuSelectionSection.style.display = 'none';
                updateFreightSectionVisibility();
                updateStepProgress('product', false);
                return;
            }

            updateStepProgress('product', true);

            // Fetch product details to check if it's from AliExpress
            fetch(`/orders/product/${selectedProductId}/info`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentProductId = selectedProductId;

                        // Store product data in cache
                        productData[selectedProductId] = {
                            isAliexpress: data.product.is_aliexpress,
                            aliexpressId: data.product.aliexpress_id
                        };

                        updateFreightSectionVisibility();

                        // Show SKU selection if AliExpress product
                        if (data.product.is_aliexpress) {
                            skuSelectionSection.style.display = 'block';
                            loadProductSkus(selectedProductId);
                        } else {
                            skuSelectionSection.style.display = 'none';
                        }
                    } else {
                        console.error('Failed to fetch product info');
                        currentProductId = null;
                        skuSelectionSection.style.display = 'none';
                        updateFreightSectionVisibility();
                    }
                })
                .catch(error => {
                    console.error('Error fetching product:', error);
                    currentProductId = null;
                    skuSelectionSection.style.display = 'none';
                    updateFreightSectionVisibility();
                });
        });
    }

    // Auto-load SKUs if product is pre-selected
    @if(isset($product) && $product->isAliexpressProduct())
        skuSelectionSection.style.display = 'block';
        productData[{{ $product->id }}] = {
            isAliexpress: true,
            aliexpressId: '{{ $product->aliexpress_id }}'
        };
        loadProductSkus({{ $product->id }});
    @endif

    // Initialize visibility on page load
    updateFreightSectionVisibility();

    // Function to calculate freight
    function calculateFreight() {
        // Check if we have a product selected
        if (!currentProductId) {
            return;
        }

        // Get current values
        const country = shippingCountrySelect.value;
        const city = cityInput.value.trim();
        const province = provinceInput.value;
        const quantity = parseInt(quantityInput.value) || 1;

        // Only calculate if we have required fields
        if (!country || !city || !province || quantity < 1) {
            freightInfoContainer.style.display = 'none';
            if (freightInstruction) {
                freightInstruction.style.display = 'block';
            }
            freightCalculated = false;
            return;
        }

        // Hide instruction
        if (freightInstruction) {
            freightInstruction.style.display = 'none';
        }

        // Show container and loading state
        freightInfoContainer.style.display = 'block';
        freightLoading.style.display = 'block';
        freightResult.style.display = 'none';
        freightError.style.display = 'none';
        freightCalculated = false;

        // Get selected SKU ID if available
        const selectedSkuId = document.getElementById('selected_sku_id')?.value;

        // Prepare request data
        const data = {
            product_id: currentProductId,
            quantity: quantity,
            country: country,
            city: city,
            province: province,
            sku_id: selectedSkuId || null, // Pass selected SKU ID
            _token: '{{ csrf_token() }}'
        };

        // Make AJAX request
        fetch('{{ route("orders.calculate-freight") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            freightLoading.style.display = 'none';

            if (data.success) {
                // Display freight information with enhanced styling
                let html = '';

                // Success header with checkmark
                html += '<div class="alert alert-success border-0 shadow-sm mb-4">';
                html += '<div class="d-flex align-items-center">';
                html += '<i class="ri-checkbox-circle-fill text-success me-3" style="font-size: 32px;"></i>';
                html += '<div>';
                html += '<h5 class="mb-0 text-success">Shipping Available!</h5>';
                html += '<small class="text-muted">Freight calculated successfully for your location</small>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                html += '<div class="row g-3">';

                // Shipping cost - prominent display with better design
                html += '<div class="col-md-6">';
                html += '<div class="card border-primary shadow-sm h-100">';
                html += '<div class="card-body text-center d-flex flex-column justify-content-center" style="min-height: 140px;">';
                html += '<div class="mb-2"><i class="ri-money-dollar-circle-line text-primary" style="font-size: 36px;"></i></div>';
                html += '<p class="text-muted mb-2 fw-semibold">Shipping Cost</p>';
                html += '<h2 class="text-primary mb-0 fw-bold">' + data.freight_currency + ' ' + parseFloat(data.freight_amount).toFixed(2) + '</h2>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                // Estimated delivery
                if (data.estimated_delivery_time || (data.delivery_days_min && data.delivery_days_max)) {
                    html += '<div class="col-md-6">';
                    html += '<div class="card border-info shadow-sm h-100">';
                    html += '<div class="card-body text-center d-flex flex-column justify-content-center" style="min-height: 140px;">';
                    html += '<div class="mb-2"><i class="ri-calendar-line text-info" style="font-size: 36px;"></i></div>';
                    html += '<p class="text-muted mb-2 fw-semibold">Estimated Delivery</p>';
                    if (data.estimated_delivery_time) {
                        html += '<p class="mb-1 fw-bold">' + data.estimated_delivery_time + '</p>';
                    }
                    if (data.delivery_days_min && data.delivery_days_max) {
                        html += '<p class="mb-0 text-muted"><small>' + data.delivery_days_min + '-' + data.delivery_days_max + ' business days</small></p>';
                    }
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                }

                // Service name & shipping method
                if (data.service_name || data.shipping_method) {
                    html += '<div class="col-md-12">';
                    html += '<div class="card border-secondary shadow-sm">';
                    html += '<div class="card-body">';
                    html += '<div class="row align-items-center">';
                    html += '<div class="col-auto">';
                    html += '<i class="ri-truck-line text-secondary" style="font-size: 32px;"></i>';
                    html += '</div>';
                    html += '<div class="col">';
                    if (data.service_name) {
                        html += '<h6 class="mb-1">Shipping Carrier</h6>';
                        html += '<p class="mb-0 text-muted">' + data.service_name + '</p>';
                    }
                    if (data.shipping_method && data.shipping_method !== data.service_name) {
                        html += '<small class="text-muted">Method: ' + data.shipping_method + '</small>';
                    }
                    html += '</div>';
                    html += '<div class="col-auto">';

                    // Badges for additional info
                    if (data.free_shipping) {
                        html += '<span class="badge bg-success me-1 mb-1"><i class="ri-gift-line me-1"></i>Free Shipping</span>';
                    }
                    if (data.tracking) {
                        html += '<span class="badge bg-info mb-1"><i class="ri-map-pin-line me-1"></i>Tracking</span>';
                    }

                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                }

                html += '</div>';

                freightResult.innerHTML = html;
                freightResult.style.display = 'block';
                freightError.style.display = 'none';
                freightCalculated = true;

                // Scroll to freight result
                setTimeout(() => {
                    freightInfoContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            } else {
                // Display error with better messaging
                let errorMsg = data.error || 'Unable to calculate shipping cost.';

                // Log raw response to console for debugging
                if (data.raw_response) {
                    console.log('AliExpress Raw Response:', data.raw_response);
                    console.log('AliExpress Raw Response (JSON):', JSON.stringify(data.raw_response, null, 2));
                }

                // Handle specific error codes
                if (data.error_code === 'INVALID_SKU_FORMAT') {
                    freightErrorMessage.innerHTML = `
                        <strong><i class="ri-error-warning-line me-2"></i>Invalid SKU Format</strong><br><br>
                        ${errorMsg}<br><br>
                        <div class="alert alert-warning mb-0 mt-2">
                            <i class="ri-information-line me-1"></i>
                            <strong>What to do:</strong> Click "Change SKU" above and select a SKU with the
                            <span class="badge bg-success">Numeric SKU</span> badge (shown in green).
                        </div>
                    `;
                } else if (data.error_code === 'NO_NUMERIC_SKU') {
                    freightErrorMessage.innerHTML = `
                        <strong><i class="ri-error-warning-line me-2"></i>No Valid SKU Found</strong><br><br>
                        ${errorMsg}<br><br>
                        <div class="alert alert-info mb-0 mt-2">
                            <i class="ri-information-line me-1"></i>
                            This product only has property combinations (like "Color:Red#Size:Large"),
                            which don't work with the AliExpress freight API. Shipping cost cannot be calculated automatically.
                        </div>
                    `;
                } else if (data.raw_response && data.raw_response.code === 501) {
                    // DELIVERY_INFO_EMPTY error
                    freightErrorMessage.innerHTML = `
                        <strong><i class="ri-error-warning-line me-2"></i>Shipping Not Available</strong><br><br>
                        ${errorMsg}<br><br>
                        <div class="alert alert-warning mb-0 mt-2">
                            <i class="ri-alert-line me-1"></i>
                            <strong>Possible reasons:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Product doesn't ship to this location</li>
                                <li>Selected SKU is out of stock on AliExpress</li>
                                <li>Invalid SKU format (property combination instead of numeric ID)</li>
                            </ul>
                        </div>
                        <details class="mt-3">
                            <summary class="text-muted" style="cursor: pointer;">Show Debug Information</summary>
                            <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto; font-size: 11px; margin-top: 10px;">${JSON.stringify(data.raw_response, null, 2)}</pre>
                        </details>
                    `;
                } else if (data.raw_response) {
                    freightErrorMessage.innerHTML = `
                        <strong><i class="ri-error-warning-line me-2"></i>Error</strong><br><br>
                        ${errorMsg}<br><br>
                        <details class="mt-2">
                            <summary class="text-muted" style="cursor: pointer;">Show Details</summary>
                            <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto; font-size: 11px; margin-top: 10px;">${JSON.stringify(data.raw_response, null, 2)}</pre>
                        </details>
                    `;
                } else {
                    freightErrorMessage.innerHTML = `<strong><i class="ri-error-warning-line me-2"></i>Error</strong><br><br>${errorMsg}`;
                }

                freightError.style.display = 'block';
                freightResult.style.display = 'none';
                freightCalculated = false;
            }
        })
        .catch(error => {
            console.error('Freight calculation error:', error);
            freightLoading.style.display = 'none';
            freightErrorMessage.textContent = 'An error occurred while calculating shipping cost. Please try again or contact support.';
            freightError.style.display = 'block';
            freightResult.style.display = 'none';
            freightCalculated = false;
        });
    }

    // Debounced freight calculation
    function debouncedCalculateFreight() {
        if (freightCalculationTimeout) {
            clearTimeout(freightCalculationTimeout);
        }
        freightCalculationTimeout = setTimeout(calculateFreight, 800);
    }

    // Add event listeners to trigger freight calculation and update progress
    if (cityInput) {
        cityInput.addEventListener('blur', debouncedCalculateFreight);
        cityInput.addEventListener('blur', () => checkShippingComplete());
    }
    if (provinceInput) {
        provinceInput.addEventListener('change', debouncedCalculateFreight);
        provinceInput.addEventListener('change', () => checkShippingComplete());
    }
    if (shippingCountrySelect) {
        shippingCountrySelect.addEventListener('change', debouncedCalculateFreight);
        shippingCountrySelect.addEventListener('change', () => checkShippingComplete());
    }
    if (quantityInput) quantityInput.addEventListener('change', debouncedCalculateFreight);

    // Check if customer information is complete
    function checkCustomerComplete() {
        const name = document.getElementById('customer_name').value.trim();
        const phone = document.getElementById('customer_phone').value.trim();

        if (name && phone) {
            updateStepProgress('customer', true);
        } else {
            updateStepProgress('customer', false);
        }
    }

    // Check if shipping information is complete
    function checkShippingComplete() {
        const address = document.getElementById('shipping_address').value.trim();
        const city = cityInput.value.trim();
        const province = provinceInput.value;
        const country = shippingCountrySelect.value;

        if (address && city && province && country) {
            updateStepProgress('shipping', true);
            updateStepProgress('review', true);
        } else {
            updateStepProgress('shipping', false);
            updateStepProgress('review', false);
        }
    }

    // Add listeners to customer fields
    document.getElementById('customer_name').addEventListener('blur', checkCustomerComplete);
    document.getElementById('customer_phone').addEventListener('blur', checkCustomerComplete);

    // Add listeners to shipping fields
    document.getElementById('shipping_address').addEventListener('blur', checkShippingComplete);

    // Function to update province options based on country
    function updateProvinceOptions(country) {
        if (country === 'AE') {
            uaeProvinces.style.display = '';
            saudiProvinces.style.display = 'none';
            // Clear selection if it was a Saudi province
            const currentValue = shippingProvinceSelect.value;
            const validUAEValues = ['Abu Dhabi', 'Dubai', 'Sharjah', 'Ajman', 'Umm Al Quwain', 'Ras Al Khaimah', 'Fujairah'];
            if (!validUAEValues.includes(currentValue)) {
                shippingProvinceSelect.value = '';
            }
        } else if (country === 'SA') {
            uaeProvinces.style.display = 'none';
            saudiProvinces.style.display = '';
            // Clear selection if it was a UAE emirate
            const currentValue = shippingProvinceSelect.value;
            const validSAValues = ['Riyadh', 'Makkah', 'Madinah', 'Eastern Province', 'Asir', 'Tabuk', 'Qassim', "Ha'il", 'Northern Borders', 'Jazan', 'Najran', 'Al Bahah', 'Al Jawf'];
            if (!validSAValues.includes(currentValue)) {
                shippingProvinceSelect.value = '';
            }
        }
    }

    // Initialize province options on page load
    updateProvinceOptions(shippingCountrySelect.value);

    // Auto-fill postal code for UAE if empty
    const zipInput = document.getElementById('shipping_zip');
    if (shippingCountrySelect.value === 'AE' && !zipInput.value) {
        zipInput.value = '00000';
    }

    // Auto-sync phone country with shipping country and update provinces
    shippingCountrySelect.addEventListener('change', function() {
        const countryMap = {
            'AE': '971',  // UAE
            'SA': '966',  // Saudi Arabia
            'EG': '20'    // Egypt
        };

        const phoneCode = countryMap[this.value];
        if (phoneCode && phoneCountrySelect) {
            phoneCountrySelect.value = phoneCode;
        }

        // Update province options
        updateProvinceOptions(this.value);

        // Auto-fill default postal code for UAE
        if (this.value === 'AE' && !zipInput.value) {
            zipInput.value = '00000';
        }
    });

    // Clean phone number on input
    phoneInput.addEventListener('blur', function() {
        let phone = this.value.trim();

        // Remove any spaces or dashes
        phone = phone.replace(/[\s\-]/g, '');

        // Remove leading zeros
        phone = phone.replace(/^0+/, '');

        // Remove country code if accidentally included
        const countryCode = phoneCountrySelect.value;
        if (phone.startsWith(countryCode)) {
            phone = phone.substring(countryCode.length);
        }

        // Remove plus sign if present
        phone = phone.replace(/^\+/, '');

        this.value = phone;
    });
});
</script>

@endsection
