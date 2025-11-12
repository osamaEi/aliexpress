@extends('dashboard')

@section('content')
<div class="col-12">
    <!-- Hero Section with Product Images -->
    <div class="card mb-4" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="row g-0">
                <!-- Product Images Gallery -->
                <div class="col-md-6 bg-light p-4">
                    @if($product->images && count($product->images) > 0)
                        <div id="productGallery" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                @foreach($product->images as $index => $image)
                                    <button type="button" data-bs-target="#productGallery" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner" style="border-radius: 12px;">
                                @foreach($product->images as $index => $image)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ $image }}" class="d-block w-100" alt="{{ $product->name }}" style="height: 500px; object-fit: contain; background: white;">
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
                        <div class="row mt-3 g-2">
                            @foreach($product->images as $index => $image)
                                @if($index < 6)
                                    <div class="col-2">
                                        <img src="{{ $image }}" class="img-thumbnail" style="cursor: pointer; height: 70px; object-fit: cover;" onclick="document.querySelector('[data-bs-slide-to=\\\'{{ $index }}\\\']').click()">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 500px;">
                            <i class="ri-image-line" style="font-size: 64px; color: #ccc;"></i>
                        </div>
                    @endif
                </div>

                <!-- Product Info -->
                <div class="col-md-6 p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            @if($product->isAliexpressProduct())
                                <span class="badge bg-info mb-2">
                                    <i class="ri-shopping-cart-line me-1"></i> Dropship
                                </span>
                            @endif
                            <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }} mb-2">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                                <i class="ri-shopping-bag-line me-1"></i> Create Order
                            </button>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-primary">
                                <i class="ri-edit-line me-1"></i> Edit
                            </a>
                            @if($product->isAliexpressProduct())
                                <button type="button" class="btn btn-sm btn-info" id="syncProductBtn" onclick="syncProduct()">
                                    <i class="ri-refresh-line me-1"></i> Sync from Supplier
                                </button>
                            @endif
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                        </div>
                    </div>

                    <h2 class="mb-3">{{ $product->name }}</h2>

                    @if($product->short_description)
                        <p class="text-muted mb-4">{{ $product->short_description }}</p>
                    @endif

                    <!-- Pricing -->
                    <div class="mb-4">
                        <div class="d-flex align-items-baseline mb-2">
                            <h3 class="text-primary mb-0 me-3">{{ $product->currency ?? 'AED' }} {{ number_format($product->price, 2) }}</h3>
                            @if($product->compare_price && $product->compare_price > $product->price)
                                <span class="text-muted text-decoration-line-through me-2">{{ $product->currency }} {{ number_format($product->compare_price, 2) }}</span>
                                <span class="badge bg-danger">
                                    {{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}% OFF
                                </span>
                            @endif
                        </div>

                        @if($product->original_price && $product->original_price > 0)
                            <div class="card bg-light border-0">
                                <div class="card-body py-2 px-3">
                                    <small class="text-muted d-block mb-2"><strong>💰 Price Breakdown</strong></small>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small>Supplier Price:</small>
                                        <small><strong>{{ $product->currency }} {{ number_format($product->original_price, 2) }}</strong></small>
                                    </div>
                                    @if($product->seller_amount > 0)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small>+ Seller Profit:</small>
                                            <small class="text-success"><strong>+{{ $product->currency }} {{ number_format($product->seller_amount, 2) }}</strong></small>
                                        </div>
                                    @endif
                                    @if($product->admin_amount > 0)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small>+ Platform Fee:</small>
                                            <small class="text-info"><strong>+{{ $product->currency }} {{ number_format($product->admin_amount, 2) }}</strong></small>
                                        </div>
                                    @endif
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small><strong>Your Profit:</strong></small>
                                        <small class="text-success"><strong>{{ $product->currency }} {{ number_format($product->price - $product->original_price, 2) }}</strong></small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Stats -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-3">
                                    <div class="fs-4 fw-bold text-primary">{{ $product->stock_quantity }}</div>
                                    <small class="text-muted">Stock</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-3">
                                    <div class="fs-4 fw-bold text-success">
                                        @if($aliexpressData && isset($aliexpressData['ae_item_base_info_dto']['sales_count']))
                                            {{ $aliexpressData['ae_item_base_info_dto']['sales_count'] }}
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                    <small class="text-muted">Sales</small>
                                </div>
                            </div>
                        </div>
                        @if($aliexpressData && isset($aliexpressData['ae_item_base_info_dto']['avg_evaluation_rating']))
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center py-3">
                                        <div class="fs-4 fw-bold text-warning">
                                            ⭐ {{ $aliexpressData['ae_item_base_info_dto']['avg_evaluation_rating'] }}/5
                                        </div>
                                        <small class="text-muted">{{ $aliexpressData['ae_item_base_info_dto']['evaluation_count'] ?? 0 }} Reviews</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Product Info Table -->
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th style="width: 140px;">SKU:</th>
                                    <td>{{ $product->sku ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                </tr>
                                @if($product->isAliexpressProduct())
                                    <tr>
                                        <th>Product ID:</th>
                                        <td>
                                            <code>{{ $product->aliexpress_id }}</code>
                                            <a href="{{ $product->aliexpress_url }}" target="_blank" class="ms-2">
                                                <i class="ri-external-link-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $product->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $product->updated_at->diffForHumans() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SKU Variants Section -->
    @if($aliexpressData && isset($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']))
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="ri-list-check me-2"></i> Product Variants</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] as $sku)
                        <div class="col-md-4">
                            <div class="card border h-100 hover-shadow" style="transition: all 0.3s;">
                                <div class="card-body">
                                    @if(isset($sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'][0]['sku_image']))
                                        <img src="{{ $sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'][0]['sku_image'] }}"
                                             class="img-fluid rounded mb-3"
                                             style="height: 150px; object-fit: cover; width: 100%;">
                                    @endif

                                    <div class="mb-2">
                                        @foreach($sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'] as $property)
                                            <span class="badge bg-secondary mb-1">
                                                {{ $property['sku_property_name'] ?? 'Property' }}:
                                                <strong>{{ $property['sku_property_value'] ?? 'N/A' }}</strong>
                                            </span>
                                        @endforeach
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            @if(isset($sku['offer_sale_price']) && $sku['offer_sale_price'] < $sku['sku_price'])
                                                <div class="text-primary fw-bold">${{ $sku['offer_sale_price'] }}</div>
                                                <small class="text-muted text-decoration-line-through">${{ $sku['sku_price'] }}</small>
                                            @else
                                                <div class="text-primary fw-bold">${{ $sku['sku_price'] ?? 'N/A' }}</div>
                                            @endif
                                        </div>
                                        <span class="badge {{ $sku['sku_available_stock'] > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $sku['sku_available_stock'] > 0 ? 'In Stock' : 'Out of Stock' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Product Description -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="ri-file-text-line me-2"></i> Description</h5>
        </div>
        <div class="card-body">
            @if($product->description)
                <div class="product-description">
                    {!! $product->description !!}
                </div>
            @else
                <p class="text-muted">No description available.</p>
            @endif
        </div>
    </div>

    <!-- Product Specifications -->
    @if($aliexpressData && isset($aliexpressData['ae_item_properties']['ae_item_property']))
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="ri-settings-3-line me-2"></i> Specifications</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($aliexpressData['ae_item_properties']['ae_item_property'] as $property)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="fw-bold me-2" style="min-width: 150px;">{{ $property['attr_name'] }}:</div>
                                <div class="text-muted">{{ $property['attr_value'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Store Information -->
    @if($aliexpressData && isset($aliexpressData['ae_store_info']))
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="ri-store-2-line me-2"></i> Store Information</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h6 class="mb-1">{{ $aliexpressData['ae_store_info']['store_name'] }}</h6>
                        <small class="text-muted">
                            <i class="ri-map-pin-line me-1"></i>
                            {{ $aliexpressData['ae_store_info']['store_country_code'] }}
                        </small>
                    </div>
                    <div class="col-md-8">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="rating-badge">
                                    <div class="fs-5 fw-bold text-success">{{ $aliexpressData['ae_store_info']['item_as_described_rating'] }}</div>
                                    <small class="text-muted">Item as Described</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="rating-badge">
                                    <div class="fs-5 fw-bold text-info">{{ $aliexpressData['ae_store_info']['communication_rating'] }}</div>
                                    <small class="text-muted">Communication</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="rating-badge">
                                    <div class="fs-5 fw-bold text-warning">{{ $aliexpressData['ae_store_info']['shipping_speed_rating'] }}</div>
                                    <small class="text-muted">Shipping Speed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Package Information -->
    @if($aliexpressData && isset($aliexpressData['package_info_dto']))
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="ri-box-3-line me-2"></i> Package & Shipping</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Package Dimensions</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Length:</span>
                            <strong>{{ $aliexpressData['package_info_dto']['package_length'] ?? 'N/A' }} cm</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Width:</span>
                            <strong>{{ $aliexpressData['package_info_dto']['package_width'] ?? 'N/A' }} cm</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Height:</span>
                            <strong>{{ $aliexpressData['package_info_dto']['package_height'] ?? 'N/A' }} cm</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Weight:</span>
                            <strong>{{ $aliexpressData['package_info_dto']['gross_weight'] ?? 'N/A' }} kg</strong>
                        </div>
                    </div>
                    @if(isset($aliexpressData['logistics_info_dto']))
                        <div class="col-md-6">
                            <h6 class="mb-3">Shipping Information</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Delivery Time:</span>
                                <strong>{{ $aliexpressData['logistics_info_dto']['delivery_time'] ?? 'N/A' }} days</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Ships To:</span>
                                <strong>{{ $aliexpressData['logistics_info_dto']['ship_to_country'] ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Create Order Modal -->
<div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createOrderModalLabel">
                    <i class="ri-shopping-bag-line me-2"></i> Create Order for {{ $product->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createOrderForm" method="GET" action="{{ route('orders.create') }}">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="modal-body">
                    <!-- Product Variants Selection -->
                    @if($aliexpressData && isset($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']))
                        <div class="mb-4">
                            <h6 class="mb-3">
                                <i class="ri-palette-line me-2"></i> Select Variant
                            </h6>
                            <div class="row g-3" id="variantsList">
                                @foreach($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] as $index => $sku)
                                    <div class="col-md-6">
                                        <div class="variant-card card border h-100" onclick="selectVariant('{{ $index }}')">
                                            <div class="card-body p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="selected_variant" id="variant{{ $index }}" value="{{ $index }}">
                                                    <label class="form-check-label w-100" for="variant{{ $index }}">
                                                        <div class="d-flex align-items-center">
                                                            @if(isset($sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'][0]['sku_image']))
                                                                <img src="{{ $sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'][0]['sku_image'] }}"
                                                                     class="rounded me-3"
                                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                            @endif
                                                            <div class="flex-grow-1">
                                                                <div class="mb-1">
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
                                                                        {{ $sku['sku_available_stock'] > 0 ? 'In Stock' : 'Out of Stock' }}
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
                            <input type="hidden" name="selected_variant_index" id="selected_variant_index">
                            <input type="hidden" name="selected_sku_attr" id="selected_sku_attr">
                        </div>
                    @endif

                    <!-- Quantity Selection -->
                    <div class="mb-4">
                        <label for="quantity" class="form-label">
                            <i class="ri-shopping-cart-line me-2"></i> Quantity
                        </label>
                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="999" required>
                    </div>

                    <!-- Customer Notes -->
                    <div class="mb-3">
                        <label for="customer_notes" class="form-label">
                            <i class="ri-message-3-line me-2"></i> Additional Notes (Optional)
                        </label>
                        <textarea class="form-control" id="customer_notes" name="customer_notes" rows="3" placeholder="Any special instructions or preferences..."></textarea>
                    </div>

                    <!-- Selected Variant Details Display -->
                    <div id="selectedVariantDetails" class="alert alert-info d-none">
                        <h6 class="alert-heading mb-2">Selected Variant:</h6>
                        <div id="selectedVariantContent"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="proceedToOrderBtn">
                        <i class="ri-arrow-right-line me-1"></i> Proceed to Order Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        transform: translateY(-2px);
    }

    .product-description img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 10px 0;
    }

    .rating-badge {
        padding: 10px;
        border-radius: 8px;
        background: #f8f9fa;
    }

    .carousel-item img {
        border-radius: 12px;
    }

    .table th {
        font-weight: 600;
        color: #666;
    }

    .badge {
        font-weight: 500;
    }

    /* Variant Card Styles */
    .variant-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .variant-card:hover {
        border-color: #28a745 !important;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        transform: translateY(-2px);
    }

    .variant-card.selected {
        border-color: #28a745 !important;
        background-color: #f0fff4;
        box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.3);
    }

    .variant-card .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
</style>

<script>
function syncProduct() {
    const btn = document.getElementById('syncProductBtn');
    const originalContent = btn.innerHTML;

    if (!confirm('Sync this product with latest AliExpress data? This will update prices, description, and images.')) {
        return;
    }

    // Disable button and show loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line me-1"></i> Syncing...';

    // Send sync request
    fetch('{{ route("products.sync", $product) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            // Show success message
            btn.innerHTML = '<i class="ri-check-line me-1"></i> Synced!';
            btn.classList.remove('btn-info');
            btn.classList.add('btn-success');

            // Reload page after 1.5 seconds
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else if (data && data.error) {
            // Show error
            btn.innerHTML = originalContent;
            btn.disabled = false;
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Sync error:', error);
        btn.innerHTML = originalContent;
        btn.disabled = false;
        alert('Failed to sync product. Please try again.');
    });
}

// Variant selection and order creation
const variantsData = @json($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? []);

function selectVariant(index) {
    // Remove previous selection
    document.querySelectorAll('.variant-card').forEach(card => {
        card.classList.remove('selected');
    });

    // Add selection to clicked card
    const selectedCard = document.querySelector(`#variant${index}`).closest('.variant-card');
    selectedCard.classList.add('selected');

    // Check the radio button
    document.getElementById(`variant${index}`).checked = true;

    // Get variant data
    const variant = variantsData[index];

    // Store variant index
    document.getElementById('selected_variant_index').value = index;

    // Build SKU attributes string
    if (variant && variant.ae_sku_property_dtos && variant.ae_sku_property_dtos.ae_sku_property_d_t_o) {
        const skuAttrs = variant.ae_sku_property_dtos.ae_sku_property_d_t_o.map(prop => {
            return `${prop.sku_property_id}:${prop.property_value_id}`;
        }).join(';');

        document.getElementById('selected_sku_attr').value = skuAttrs;

        // Show selected variant details
        const detailsDiv = document.getElementById('selectedVariantDetails');
        const contentDiv = document.getElementById('selectedVariantContent');

        let detailsHtml = '<div class="row align-items-center">';

        // Add image if available
        if (variant.ae_sku_property_dtos.ae_sku_property_d_t_o[0].sku_image) {
            detailsHtml += `
                <div class="col-auto">
                    <img src="${variant.ae_sku_property_dtos.ae_sku_property_d_t_o[0].sku_image}"
                         class="rounded"
                         style="width: 50px; height: 50px; object-fit: cover;">
                </div>
            `;
        }

        detailsHtml += '<div class="col">';

        // Add properties
        variant.ae_sku_property_dtos.ae_sku_property_d_t_o.forEach(prop => {
            detailsHtml += `<span class="badge bg-secondary me-1">${prop.sku_property_name}: ${prop.sku_property_value}</span>`;
        });

        detailsHtml += `<div class="mt-2"><strong>Price:</strong> $${variant.offer_sale_price || variant.sku_price}</div>`;
        detailsHtml += '</div></div>';

        contentDiv.innerHTML = detailsHtml;
        detailsDiv.classList.remove('d-none');
    }
}

// Reset modal on close
document.getElementById('createOrderModal').addEventListener('hidden.bs.modal', function () {
    // Reset form
    document.getElementById('createOrderForm').reset();

    // Remove selections
    document.querySelectorAll('.variant-card').forEach(card => {
        card.classList.remove('selected');
    });

    // Hide details
    document.getElementById('selectedVariantDetails').classList.add('d-none');

    // Clear hidden fields
    document.getElementById('selected_variant_index').value = '';
    document.getElementById('selected_sku_attr').value = '';
});
</script>
@endsection
