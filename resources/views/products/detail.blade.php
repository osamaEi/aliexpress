@extends('dashboard')

@section('content')
<div class="col-12">
    <!-- Product Hero Section -->
    <div class="card shadow-lg mb-4" style="border-radius: 20px; overflow: hidden; border: none;">
        <div class="card-body p-0">
            <div class="row g-0">
                <!-- Product Images Gallery -->
                <div class="col-lg-5 bg-light p-4">
                    @if($product->images && count($product->images) > 0)
                        <div id="productGallery" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner" style="border-radius: 16px; overflow: hidden;">
                                @foreach($product->images as $index => $image)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ $image }}" class="d-block w-100" alt="{{ $product->name }}" style="height: 450px; object-fit: contain; background: white;">
                                    </div>
                                @endforeach
                            </div>
                            @if(count($product->images) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#productGallery" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#productGallery" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            @endif
                        </div>

                        <!-- Thumbnail Gallery -->
                        @if(count($product->images) > 1)
                            <div class="row mt-3 g-2">
                                @foreach($product->images as $index => $image)
                                    @if($index < 5)
                                        <div class="col">
                                            <img src="{{ $image }}"
                                                 class="img-thumbnail thumbnail-hover"
                                                 style="cursor: pointer; height: 70px; object-fit: cover; width: 100%;"
                                                 onclick="document.querySelector('#productGallery .carousel-control-next').click()">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 450px;">
                            <i class="ri-image-line" style="font-size: 64px; color: #ccc;"></i>
                        </div>
                    @endif
                </div>

                <!-- Product Details -->
                <div class="col-lg-7 p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            @if($product->isAliexpressProduct())
                                <span class="badge bg-gradient-info text-white mb-2 px-3 py-2">
                                    <i class="ri-global-line me-1"></i> Dropshipping Product
                                </span>
                            @endif
                            <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }} mb-2 px-3 py-2">
                                {{ $product->is_active ? 'Available' : 'Unavailable' }}
                            </span>
                        </div>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                    </div>

                    <h1 class="mb-3 fw-bold" style="font-size: 28px; line-height: 1.4;">{{ $product->name }}</h1>

                    @if($product->short_description)
                        <p class="text-muted mb-4" style="font-size: 15px;">{{ $product->short_description }}</p>
                    @endif

                    <!-- Rating & Sales -->
                    @if($aliexpressData && isset($aliexpressData['ae_item_base_info_dto']))
                        <div class="d-flex align-items-center mb-4 gap-3">
                            @if(isset($aliexpressData['ae_item_base_info_dto']['avg_evaluation_rating']))
                                <div class="d-flex align-items-center">
                                    <div class="text-warning me-2" style="font-size: 20px;">
                                        @for($i = 0; $i < 5; $i++)
                                            @if($i < floor($aliexpressData['ae_item_base_info_dto']['avg_evaluation_rating']))
                                                ⭐
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="fw-bold">{{ number_format($aliexpressData['ae_item_base_info_dto']['avg_evaluation_rating'], 1) }}</span>
                                    <span class="text-muted ms-1">({{ $aliexpressData['ae_item_base_info_dto']['evaluation_count'] ?? 0 }} reviews)</span>
                                </div>
                            @endif
                            @if(isset($aliexpressData['ae_item_base_info_dto']['sales_count']))
                                <div class="text-muted">
                                    <i class="ri-shopping-cart-line me-1"></i>
                                    <strong>{{ number_format($aliexpressData['ae_item_base_info_dto']['sales_count']) }}</strong> sold
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Pricing -->
                    <div class="card bg-gradient-primary text-white mb-4 border-0" style="border-radius: 16px;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-baseline mb-2">
                                <h2 class="mb-0 me-3 fw-bold" style="font-size: 36px;">{{ $product->currency ?? 'AED' }} {{ number_format($product->price, 2) }}</h2>
                                @if($product->compare_price && $product->compare_price > $product->price)
                                    <span class="text-white-50 text-decoration-line-through me-2" style="font-size: 20px;">{{ $product->currency }} {{ number_format($product->compare_price, 2) }}</span>
                                    <span class="badge bg-danger" style="font-size: 14px;">
                                        {{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}% OFF
                                    </span>
                                @endif
                            </div>

                            @if($product->original_price && $product->original_price > 0)
                                <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                                    <small class="d-block mb-2 text-white-50">💰 Your Profit Breakdown</small>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <small class="text-white-50">Supplier Price</small>
                                            <div class="fw-bold">{{ $product->currency }} {{ number_format($product->original_price, 2) }}</div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-white-50">Your Profit</small>
                                            <div class="fw-bold text-warning">{{ $product->currency }} {{ number_format($product->price - $product->original_price, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- SKU & Stock -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="ri-box-3-line text-primary mb-2" style="font-size: 28px;"></i>
                                    <div class="fs-3 fw-bold text-dark">{{ $product->stock_quantity }}</div>
                                    <small class="text-muted">Units in Stock</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="ri-price-tag-3-line text-success mb-2" style="font-size: 28px;"></i>
                                    <div class="fs-6 fw-bold text-dark">{{ $product->sku ?? 'N/A' }}</div>
                                    <small class="text-muted">Product SKU</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-3">
                        @if($product->isAliexpressProduct())
                            <button type="button" class="btn btn-lg btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#shippingCalculatorModal" style="border-radius: 12px; padding: 16px;">
                                <i class="ri-ship-line me-2"></i> Calculate Shipping & Create Order
                            </button>
                        @else
                            <a href="{{ route('orders.create', ['product_id' => $product->id]) }}" class="btn btn-lg btn-success shadow-sm" style="border-radius: 12px; padding: 16px;">
                                <i class="ri-shopping-bag-line me-2"></i> Create Order
                            </a>
                        @endif

                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary w-100" style="border-radius: 10px;">
                                    <i class="ri-edit-line me-1"></i> Edit Product
                                </a>
                            </div>
                            <div class="col-6">
                                @if($product->isAliexpressProduct())
                                    <button type="button" class="btn btn-outline-info w-100" id="syncProductBtn" onclick="syncProduct()" style="border-radius: 10px;">
                                        <i class="ri-refresh-line me-1"></i> Sync Data
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($product->isAliexpressProduct() && $product->aliexpress_url)
                        <div class="mt-3 text-center">
                            <a href="{{ $product->aliexpress_url }}" target="_blank" class="text-muted text-decoration-none">
                                <i class="ri-external-link-line me-1"></i> View on AliExpress
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Section -->
    <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">
                <i class="ri-file-text-line me-2"></i>Description
            </button>
        </li>
        @if($aliexpressData && isset($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']))
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button">
                    <i class="ri-list-check me-2"></i>Variants ({{ count($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']) }})
                </button>
            </li>
        @endif
        @if($aliexpressData && isset($aliexpressData['ae_item_properties']['ae_item_property']))
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button">
                    <i class="ri-settings-3-line me-2"></i>Specifications
                </button>
            </li>
        @endif
        @if($aliexpressData && isset($aliexpressData['package_info_dto']))
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button">
                    <i class="ri-truck-line me-2"></i>Shipping Info
                </button>
            </li>
        @endif
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Description Tab -->
        <div class="tab-pane fade show active" id="description">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    @if($product->description)
                        <div class="product-description">
                            {!! $product->description !!}
                        </div>
                    @else
                        <p class="text-muted text-center py-5">No description available.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Variants Tab -->
        @if($aliexpressData && isset($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']))
            <div class="tab-pane fade" id="variants">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            @foreach($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] as $sku)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border h-100 hover-lift">
                                        <div class="card-body">
                                            @if(isset($sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'][0]['sku_image']))
                                                <img src="{{ $sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'][0]['sku_image'] }}"
                                                     class="img-fluid rounded mb-3"
                                                     style="height: 180px; object-fit: cover; width: 100%;">
                                            @endif

                                            <div class="mb-3">
                                                @foreach($sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'] as $property)
                                                    <span class="badge bg-secondary mb-1 me-1">
                                                        {{ $property['sku_property_name'] }}: <strong>{{ $property['sku_property_value'] }}</strong>
                                                    </span>
                                                @endforeach
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    @if(isset($sku['offer_sale_price']) && $sku['offer_sale_price'] < $sku['sku_price'])
                                                        <div class="text-primary fw-bold fs-5">${{ $sku['offer_sale_price'] }}</div>
                                                        <small class="text-muted text-decoration-line-through">${{ $sku['sku_price'] }}</small>
                                                    @else
                                                        <div class="text-primary fw-bold fs-5">${{ $sku['sku_price'] ?? 'N/A' }}</div>
                                                    @endif
                                                </div>
                                                <span class="badge {{ $sku['sku_available_stock'] > 0 ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                                    {{ $sku['sku_available_stock'] > 0 ? $sku['sku_available_stock'] . ' in stock' : 'Out of Stock' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Specifications Tab -->
        @if($aliexpressData && isset($aliexpressData['ae_item_properties']['ae_item_property']))
            <div class="tab-pane fade" id="specs">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <tbody>
                                    @foreach($aliexpressData['ae_item_properties']['ae_item_property'] as $property)
                                        <tr>
                                            <th style="width: 35%;" class="border-end">{{ $property['attr_name'] }}</th>
                                            <td>{{ $property['attr_value'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Shipping Info Tab -->
        @if($aliexpressData && isset($aliexpressData['package_info_dto']))
            <div class="tab-pane fade" id="shipping">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="ri-box-3-line me-2 text-primary"></i>Package Dimensions</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th>Length:</th>
                                            <td>{{ $aliexpressData['package_info_dto']['package_length'] ?? 'N/A' }} cm</td>
                                        </tr>
                                        <tr>
                                            <th>Width:</th>
                                            <td>{{ $aliexpressData['package_info_dto']['package_width'] ?? 'N/A' }} cm</td>
                                        </tr>
                                        <tr>
                                            <th>Height:</th>
                                            <td>{{ $aliexpressData['package_info_dto']['package_height'] ?? 'N/A' }} cm</td>
                                        </tr>
                                        <tr>
                                            <th>Weight:</th>
                                            <td>{{ $aliexpressData['package_info_dto']['gross_weight'] ?? 'N/A' }} kg</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            @if(isset($aliexpressData['logistics_info_dto']))
                                <div class="col-md-6">
                                    <h5 class="mb-3"><i class="ri-truck-line me-2 text-success"></i>Delivery Information</h5>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th>Delivery Time:</th>
                                                <td>{{ $aliexpressData['logistics_info_dto']['delivery_time'] ?? 'N/A' }} days</td>
                                            </tr>
                                            <tr>
                                                <th>Ships To:</th>
                                                <td>{{ $aliexpressData['logistics_info_dto']['ship_to_country'] ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Shipping Calculator Modal -->
@if($product->isAliexpressProduct())
<div class="modal fade" id="shippingCalculatorModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header bg-primary text-white" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title">
                    <i class="ri-ship-line me-2"></i>Calculate Shipping Cost
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Step Indicator -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="step-indicator active" id="step-1">
                            <div class="step-circle">1</div>
                            <small>Variant</small>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-indicator" id="step-2">
                            <div class="step-circle">2</div>
                            <small>Destination</small>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-indicator" id="step-3">
                            <div class="step-circle">3</div>
                            <small>Result</small>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Select Variant & Quantity -->
                <div id="shipping-step-1">
                    @if($aliexpressData && isset($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']))
                        <h6 class="mb-3"><i class="ri-palette-line me-2"></i>Select Product Variant</h6>
                        <div class="row g-3 mb-4" id="shipping-variants-list">
                            @foreach($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] as $index => $sku)
                                <div class="col-md-6">
                                    <div class="variant-option card border h-100" onclick="selectShippingVariant({{ $index }})" data-variant-index="{{ $index }}">
                                        <div class="card-body p-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="shipping_variant" id="ship_variant{{ $index }}" value="{{ $index }}">
                                                <label class="form-check-label w-100" for="ship_variant{{ $index }}">
                                                    <div class="d-flex align-items-center">
                                                        @if(isset($sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'][0]['sku_image']))
                                                            <img src="{{ $sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'][0]['sku_image'] }}"
                                                                 class="rounded me-3"
                                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                                        @endif
                                                        <div class="flex-grow-1">
                                                            <div class="mb-2">
                                                                @foreach($sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'] as $property)
                                                                    <span class="badge bg-secondary me-1">
                                                                        {{ $property['sku_property_name'] }}: {{ $property['sku_property_value'] }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div class="text-primary fw-bold">
                                                                    ${{ $sku['offer_sale_price'] ?? $sku['sku_price'] ?? 'N/A' }}
                                                                </div>
                                                                <span class="badge {{ $sku['sku_available_stock'] > 0 ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ $sku['sku_available_stock'] > 0 ? 'In Stock' : 'Out' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mb-4">
                        <label class="form-label fw-semibold"><i class="ri-shopping-cart-line me-2"></i>Quantity</label>
                        <input type="number" class="form-control form-control-lg" id="shipping-quantity" value="1" min="1" max="999">
                    </div>

                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="goToStep(2)" id="continueToDestination">
                        Continue to Destination <i class="ri-arrow-right-line ms-2"></i>
                    </button>
                </div>

                <!-- Step 2: Destination -->
                <div id="shipping-step-2" style="display: none;">
                    <h6 class="mb-3"><i class="ri-map-pin-line me-2"></i>Enter Shipping Destination</h6>

                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <select class="form-select form-select-lg" id="shipping-country">
                            <option value="AE">United Arab Emirates</option>
                            <option value="SA">Saudi Arabia</option>
                            <option value="US">United States</option>
                            <option value="GB">United Kingdom</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control form-control-lg" id="shipping-city" placeholder="e.g., Dubai">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Province/State</label>
                        <input type="text" class="form-control form-control-lg" id="shipping-province" placeholder="e.g., Dubai">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="goToStep(1)">
                            <i class="ri-arrow-left-line me-2"></i>Back
                        </button>
                        <button type="button" class="btn btn-primary btn-lg flex-grow-1" onclick="calculateShipping()">
                            <i class="ri-calculator-line me-2"></i>Calculate Shipping
                        </button>
                    </div>
                </div>

                <!-- Step 3: Results -->
                <div id="shipping-step-3" style="display: none;">
                    <div id="shipping-result-container">
                        <!-- Loading State -->
                        <div id="shipping-loading" class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                            <p class="text-muted">Calculating shipping cost...</p>
                        </div>

                        <!-- Success State -->
                        <div id="shipping-success" style="display: none;">
                            <!-- Will be populated dynamically -->
                        </div>

                        <!-- Error State -->
                        <div id="shipping-error" style="display: none;" class="alert alert-danger">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="goToStep(2)">
                            <i class="ri-arrow-left-line me-2"></i>Back
                        </button>
                        <button type="button" class="btn btn-success btn-lg flex-grow-1" id="proceedToOrderBtn" style="display: none;" onclick="proceedToOrder()">
                            <i class="ri-shopping-bag-line me-2"></i>Create Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
}

.thumbnail-hover {
    transition: all 0.3s ease;
    opacity: 0.7;
}

.thumbnail-hover:hover {
    opacity: 1;
    transform: scale(1.05);
}

.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.product-description img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 15px 0;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 12px 20px;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #e9ecef;
    color: #495057;
}

.nav-tabs .nav-link.active {
    border-bottom-color: #667eea;
    color: #667eea;
    background: none;
}

/* Variant Option Styles */
.variant-option {
    cursor: pointer;
    transition: all 0.3s ease;
}

.variant-option:hover {
    border-color: #667eea !important;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    transform: translateY(-2px);
}

.variant-option.selected {
    border-color: #667eea !important;
    background-color: #f8f9ff;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.variant-option .form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

/* Step Indicator */
.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    opacity: 0.4;
}

.step-indicator.active {
    opacity: 1;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
}

.step-indicator.active .step-circle {
    background: #667eea;
    color: white;
}

.step-line {
    flex: 1;
    height: 2px;
    background: #e9ecef;
    margin: 0 10px;
    align-self: center;
    margin-bottom: 28px;
}
</style>

<script>
// Variant data for shipping calculator
const variantsData = @json($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? []);
let selectedVariantIndex = null;
let selectedVariantData = null;
let calculatedShippingData = null;

function selectShippingVariant(index) {
    // Remove previous selections
    document.querySelectorAll('.variant-option').forEach(card => {
        card.classList.remove('selected');
    });

    // Select new variant
    const card = document.querySelector(`.variant-option[data-variant-index="${index}"]`);
    card.classList.add('selected');
    document.getElementById(`ship_variant${index}`).checked = true;

    selectedVariantIndex = index;
    selectedVariantData = variantsData[index];
}

function goToStep(step) {
    // Hide all steps
    document.getElementById('shipping-step-1').style.display = 'none';
    document.getElementById('shipping-step-2').style.display = 'none';
    document.getElementById('shipping-step-3').style.display = 'none';

    // Update step indicators
    document.getElementById('step-1').classList.remove('active');
    document.getElementById('step-2').classList.remove('active');
    document.getElementById('step-3').classList.remove('active');

    // Show target step
    document.getElementById(`shipping-step-${step}`).style.display = 'block';
    document.getElementById(`step-${step}`).classList.add('active');

    // Mark previous steps as active too
    for (let i = 1; i < step; i++) {
        document.getElementById(`step-${i}`).classList.add('active');
    }
}

function calculateShipping() {
    if (!selectedVariantData) {
        alert('Please select a variant first');
        goToStep(1);
        return;
    }

    const country = document.getElementById('shipping-country').value;
    const city = document.getElementById('shipping-city').value.trim();
    const province = document.getElementById('shipping-province').value.trim();
    const quantity = parseInt(document.getElementById('shipping-quantity').value) || 1;

    if (!country || !city || !province) {
        alert('Please fill in all destination fields');
        return;
    }

    // Go to results step
    goToStep(3);

    // Show loading
    document.getElementById('shipping-loading').style.display = 'block';
    document.getElementById('shipping-success').style.display = 'none';
    document.getElementById('shipping-error').style.display = 'none';
    document.getElementById('proceedToOrderBtn').style.display = 'none';

    // Build SKU attributes
    let skuAttr = '';
    if (selectedVariantData.ae_sku_property_dtos && selectedVariantData.ae_sku_property_dtos.ae_sku_property_d_t_o) {
        skuAttr = selectedVariantData.ae_sku_property_dtos.ae_sku_property_d_t_o.map(prop => {
            return `${prop.sku_property_id}:${prop.property_value_id}`;
        }).join(';');
    }

    // Call freight calculation API
    fetch('{{ route("orders.calculate-freight") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            product_id: {{ $product->id }},
            quantity: quantity,
            country: country,
            city: city,
            province: province,
            sku_id: selectedVariantData.id || null
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('shipping-loading').style.display = 'none';

        if (data.success) {
            calculatedShippingData = data;
            displayShippingSuccess(data);
        } else {
            displayShippingError(data);
        }
    })
    .catch(error => {
        document.getElementById('shipping-loading').style.display = 'none';
        displayShippingError({error: error.message});
    });
}

function displayShippingSuccess(data) {
    const container = document.getElementById('shipping-success');

    let html = `
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="ri-checkbox-circle-fill text-success" style="font-size: 64px;"></i>
            </div>
            <h4 class="text-success mb-2">Shipping Available!</h4>
            <p class="text-muted">We can ship this product to your destination</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 bg-light text-center h-100">
                    <div class="card-body">
                        <i class="ri-money-dollar-circle-line text-primary mb-2" style="font-size: 32px;"></i>
                        <h3 class="text-primary mb-1">${data.freight_currency} ${parseFloat(data.freight_amount).toFixed(2)}</h3>
                        <small class="text-muted">Shipping Cost</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-light text-center h-100">
                    <div class="card-body">
                        <i class="ri-time-line text-info mb-2" style="font-size: 32px;"></i>
                        <h5 class="mb-1">${data.delivery_time || 'N/A'}</h5>
                        <small class="text-muted">Delivery Time</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-light text-center h-100">
                    <div class="card-body">
                        <i class="ri-truck-line text-success mb-2" style="font-size: 32px;"></i>
                        <h6 class="mb-1">${data.service_name || 'Standard'}</h6>
                        <small class="text-muted">Carrier</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info border-0">
            <strong>Note:</strong> Shipping cost will be added to your order total. The final price will be calculated during checkout.
        </div>
    `;

    container.innerHTML = html;
    container.style.display = 'block';
    document.getElementById('proceedToOrderBtn').style.display = 'block';
}

function displayShippingError(data) {
    const container = document.getElementById('shipping-error');

    let errorMsg = data.error || 'Failed to calculate shipping cost';

    container.innerHTML = `
        <div class="text-center mb-3">
            <i class="ri-error-warning-line" style="font-size: 48px;"></i>
        </div>
        <h6 class="alert-heading">Shipping Calculation Failed</h6>
        <p class="mb-0">${errorMsg}</p>
    `;

    container.style.display = 'block';
}

function proceedToOrder() {
    if (!selectedVariantData || !calculatedShippingData) {
        alert('Missing required data');
        return;
    }

    // Build SKU attributes
    let skuAttr = '';
    if (selectedVariantData.ae_sku_property_dtos && selectedVariantData.ae_sku_property_dtos.ae_sku_property_d_t_o) {
        skuAttr = selectedVariantData.ae_sku_property_dtos.ae_sku_property_d_t_o.map(prop => {
            return `${prop.sku_property_id}:${prop.property_value_id}`;
        }).join(';');
    }

    const country = document.getElementById('shipping-country').value;
    const city = document.getElementById('shipping-city').value;
    const province = document.getElementById('shipping-province').value;
    const quantity = document.getElementById('shipping-quantity').value;

    // Build URL with parameters
    const url = '{{ route("orders.create") }}?' +
        `product_id={{ $product->id }}` +
        `&selected_variant=${selectedVariantIndex}` +
        `&selected_variant_index=${selectedVariantIndex}` +
        `&selected_sku_attr=${encodeURIComponent(skuAttr)}` +
        `&quantity=${quantity}` +
        `&shipping_country=${country}` +
        `&shipping_city=${encodeURIComponent(city)}` +
        `&shipping_province=${encodeURIComponent(province)}`;

    window.location.href = url;
}

function syncProduct() {
    const btn = document.getElementById('syncProductBtn');
    const originalContent = btn.innerHTML;

    if (!confirm('Sync this product with latest AliExpress data?')) {
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line me-1"></i> Syncing...';

    fetch('{{ route("products.sync", $product) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            btn.innerHTML = '<i class="ri-check-line me-1"></i> Synced!';
            setTimeout(() => window.location.reload(), 1000);
        } else {
            btn.innerHTML = originalContent;
            btn.disabled = false;
            alert('Error: ' + (data.error || 'Sync failed'));
        }
    })
    .catch(error => {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        alert('Failed to sync product');
    });
}

// Reset modal on close
document.getElementById('shippingCalculatorModal')?.addEventListener('hidden.bs.modal', function () {
    goToStep(1);
    selectedVariantIndex = null;
    selectedVariantData = null;
    calculatedShippingData = null;

    // Reset selections
    document.querySelectorAll('.variant-option').forEach(card => {
        card.classList.remove('selected');
    });
    document.querySelectorAll('[name="shipping_variant"]').forEach(radio => {
        radio.checked = false;
    });
});
</script>
@endsection
