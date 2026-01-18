@extends('dashboard')

@section('title')
    {{ app()->getLocale() == 'ar' && $product->name_ar ? $product->name_ar : $product->name }} - {{ setting('site_name', 'EcommAli') }}
@endsection

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
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
                   
                    
                    </div>

                    <h1 class="mb-3 fw-bold" style="font-size: 28px; line-height: 1.4;">
                        {{ app()->getLocale() == 'ar' && $product->name_ar ? $product->name_ar : $product->name }}
                    </h1>

                    @if($product->short_description || $product->short_description_ar)
                        <p class="text-muted mb-4" style="font-size: 15px;">
                            {{ app()->getLocale() == 'ar' && $product->short_description_ar ? $product->short_description_ar : $product->short_description }}
                        </p>
                    @endif

                    <!-- Rating & Sales -->
                    @if($aliexpressData && isset($aliexpressData['ae_item_base_info_dto']))
                        <div class="d-flex align-items-center mb-4 gap-3">
                            @if(isset($aliexpressData['ae_item_base_info_dto']['avg_evaluation_rating']))
                                <div class="d-flex align-items-center">
                                    <div class="text-warning me-2" style="font-size: 20px;">
                                        @for($i = 0; $i < 5; $i++)
                                            @if($i < floor((float)$aliexpressData['ae_item_base_info_dto']['avg_evaluation_rating']))
                                                ⭐
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="fw-bold">{{ number_format((float)$aliexpressData['ae_item_base_info_dto']['avg_evaluation_rating'], 1) }}</span>
                                    <span class="text-muted ms-1">({{ (int)($aliexpressData['ae_item_base_info_dto']['evaluation_count'] ?? 0) }} {{ __('messages.reviews') }})</span>
                                </div>
                            @endif
                            @if(isset($aliexpressData['ae_item_base_info_dto']['sales_count']))
                                <div class="text-muted">
                                    <i class="ri-shopping-cart-line me-1"></i>
                                    <strong>{{ number_format((float)$aliexpressData['ae_item_base_info_dto']['sales_count']) }}</strong> {{ __('messages.sold') }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Pricing -->
                    <div class="card text-white mb-4 border-0" style="border-radius: 16px; background: linear-gradient(135deg, #561C04 0%, #e56300 100%);">
                        <div class="card-body p-4">
                            @php
                                // Use seller's pivot price if available, otherwise use product price
                                $displayPrice = isset($sellerPivotData) && $sellerPivotData['price'] > 0
                                    ? $sellerPivotData['price']
                                    : $product->price;
                                $convertedPrice = $currentCurrency->convertFrom($displayPrice, $product->currency ?? 'USD');
                                $convertedComparePrice = $product->compare_price ? $currentCurrency->convertFrom($product->compare_price, $product->currency ?? 'USD') : null;

                                // Calculate supplier price (original_price + admin_amount)
                                $adminAmount = isset($sellerPivotData) ? ($sellerPivotData['admin_amount'] ?? 0) : 0;
                                $supplierPrice = ($product->original_price ?? 0) + $adminAmount;
                                $convertedSupplierPrice = $supplierPrice > 0 ? $currentCurrency->convertFrom($supplierPrice, $product->currency ?? 'USD') : null;

                                // Seller's profit from pivot table
                                $sellerProfit = isset($sellerPivotData) ? ($sellerPivotData['seller_amount'] ?? 0) : 0;
                                $convertedSellerProfit = $currentCurrency->convertFrom($sellerProfit, $product->currency ?? 'USD');
                            @endphp
                            <div class="d-flex align-items-baseline mb-2">
                                <h2 class="mb-0 me-3 fw-bold" style="font-size: 36px; color:white;" >{{ $currentCurrency->format($convertedPrice) }}</h2>
                                @if($convertedComparePrice && $convertedComparePrice > $convertedPrice)
                                    <span class="text-white-50 text-decoration-line-through me-2" style="font-size: 20px;">{{ $currentCurrency->format($convertedComparePrice) }}</span>
                                    <span class="badge bg-danger" style="font-size: 14px;">
                                        {{ round((($convertedComparePrice - $convertedPrice) / $convertedComparePrice) * 100) }}% OFF
                                    </span>
                                @endif
                            </div>

                            @if(isset($sellerPivotData) && $sellerProfit > 0)
                                {{-- Show seller's profit breakdown from pivot table --}}
                                <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                                    <small class="d-block mb-2 text-white">💰 {{ __('messages.profit_breakdown') }}</small>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <small class="text-white">{{ __('messages.supplier_price') }}</small>
                                            <div class="fw-bold text-white">{{ $currentCurrency->format($convertedSupplierPrice) }}</div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-white">{{ __('messages.your_profit') }}</small>
                                            <div class="fw-bold text-white">{{ $currentCurrency->format($convertedSellerProfit) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($convertedSupplierPrice && $convertedSupplierPrice > 0 && !isset($sellerPivotData))
                                {{-- Fallback for non-seller view or when no pivot data --}}
                                <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                                    <small class="d-block mb-2 text-white">💰 {{ __('messages.profit_breakdown') }}</small>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <small class="text-white">{{ __('messages.supplier_price') }}</small>
                                            <div class="fw-bold text-white">{{ $currentCurrency->format($convertedSupplierPrice) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-3">
                        @if($product->isAliexpressProduct())
                            {{-- All AliExpress products: Calculate shipping first --}}
                            <button type="button" class="btn btn-lg btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#shippingCalculatorModal" style="border-radius: 12px; padding: 16px;">
                                <i class="ri-ship-line me-2"></i> {{ __('messages.calculate_shipping_create_order') }}
                            </button>
                        @else
                            <a href="{{ route('orders.create', ['product_id' => $product->id]) }}" class="btn btn-lg btn-success shadow-sm" style="border-radius: 12px; padding: 16px;">
                                <i class="ri-shopping-bag-line me-2"></i> {{ __('messages.create_order') }}
                            </a>
                        @endif

                    </div>

                    @if($product->isAliexpressProduct() && $product->aliexpress_url)
                        <div class="mt-3 text-center">
                            <a href="{{ $product->aliexpress_url }}" target="_blank" class="text-muted text-decoration-none">
                                <i class="ri-external-link-line me-1"></i>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="12" viewBox="0 0 900 600" style="vertical-align:middle;border-radius:2px;">
                                    <rect fill="#de2910" width="900" height="600"/>
                                    <polygon fill="#ffde00" points="149.75,123.5 172.5,197.82 249.25,197.82 188.5,242.18 211.25,316.5 149.75,272.82 88.25,316.5 111,242.18 50.25,197.82 127,197.82"/>
                                    <polygon fill="#ffde00" points="299.75,54 311.18,89.09 348.25,89.09 318.75,110.91 330.18,146 299.75,124.82 269.32,146 280.75,110.91 251.25,89.09 288.32,89.09"/>
                                    <polygon fill="#ffde00" points="371.25,123.5 382.68,158.59 419.75,158.59 390.25,180.41 401.68,215.5 371.25,194.32 340.82,215.5 352.25,180.41 322.75,158.59 359.82,158.59"/>
                                    <polygon fill="#ffde00" points="371.25,243.5 382.68,278.59 419.75,278.59 390.25,300.41 401.68,335.5 371.25,314.32 340.82,335.5 352.25,300.41 322.75,278.59 359.82,278.59"/>
                                    <polygon fill="#ffde00" points="299.75,313 311.18,348.09 348.25,348.09 318.75,369.91 330.18,405 299.75,383.82 269.32,405 280.75,369.91 251.25,348.09 288.32,348.09"/>
                                </svg>
                                {{ app()->getLocale() == 'ar' ? 'عرض على موقع الصين' : 'View on China Store' }}
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
                <i class="ri-file-text-line me-2"></i>{{ __('messages.description') }}
            </button>
        </li>
        @if($aliexpressData && isset($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']))
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button">
                    <i class="ri-list-check me-2"></i>{{ __('messages.variants') }} ({{ count($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']) }})
                </button>
            </li>
        @endif
        @if($aliexpressData && isset($aliexpressData['ae_item_properties']['ae_item_property']))
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button">
                    <i class="ri-settings-3-line me-2"></i>{{ __('messages.specifications') }}
                </button>
            </li>
        @endif
        @if($aliexpressData && isset($aliexpressData['package_info_dto']))
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button">
                    <i class="ri-truck-line me-2"></i>{{ __('messages.shipping_info') }}
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
                    @if($product->description || $product->description_ar)
                        <div class="product-description">
                            {!! app()->getLocale() == 'ar' && $product->description_ar ? $product->description_ar : $product->description !!}
                        </div>
                    @elseif($aliexpressData && isset($aliexpressData['ae_item_base_info_dto']['detail']))
                        <div class="product-description">
                            {!! $aliexpressData['ae_item_base_info_dto']['detail'] !!}
                        </div>
                    @else
                        <p class="text-muted text-center py-5">{{ __('messages.no_description_available') }}</p>
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
                                                    <div class="text-primary fw-bold fs-5">{!! $currentCurrency->format($convertedPrice) !!}</div>
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
                                        @if(strtoupper($property['attr_name']) !== 'CHOICE')
                                            <tr>
                                                <th style="width: 35%;" class="border-end">{{ $property['attr_name'] }}</th>
                                                <td>{{ $property['attr_value'] }}</td>
                                            </tr>
                                        @endif
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
                                <h5 class="mb-3"><i class="ri-box-3-line me-2 text-primary"></i>{{ __('messages.package_dimensions') }}</h5>
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
                                    <h5 class="mb-3"><i class="ri-truck-line me-2 text-success"></i>{{ __('messages.delivery_information') }}</h5>
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
            <div class="modal-header  text-white" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title">
                    <i class="ri-ship-line me-2"></i>{{ __('messages.calculate_shipping') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Step Indicator -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="step-indicator active" id="step-1">
                            <div class="step-circle">1</div>
                            <small>{{ __('messages.variant') }}</small>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-indicator" id="step-2">
                            <div class="step-circle">2</div>
                            <small>{{ __('messages.destination') }}</small>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-indicator" id="step-3">
                            <div class="step-circle">3</div>
                            <small>{{ __('messages.result') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Select Variant & Quantity -->
                <div id="shipping-step-1">
                    @if($aliexpressData && isset($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']))
                        <h6 class="mb-3"><i class="ri-palette-line me-2"></i>{{ __('messages.select_product_variant') }}</h6>
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
                                                                    {!! $currentCurrency->format($convertedPrice) !!}
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
                        <label class="form-label fw-semibold"><i class="ri-shopping-cart-line me-2"></i>{{ __('messages.quantity') }}</label>
                        <input type="number" class="form-control form-control-lg" id="shipping-quantity" value="1" min="1" max="999">
                    </div>

                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="goToStep(2)" id="continueToDestination">
                        {{ __('messages.continue_to_destination') }} <i class="ri-arrow-right-line ms-2"></i>
                    </button>
                </div>

                <!-- Step 2: Destination -->
                <div id="shipping-step-2" style="display: none;">
                    <h6 class="mb-3"><i class="ri-map-pin-line me-2"></i>{{ __('messages.enter_shipping_destination') }}</h6>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.country') }}</label>
                        <select class="form-select form-select-lg" id="shipping-country">
                            <option value="AE">{{ __('messages.united_arab_emirates') }}</option>
                            <option value="SA">{{ __('messages.saudi_arabia') }}</option>
                            <option value="US">United States</option>
                            <option value="GB">United Kingdom</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.city') }}</label>
                        <input type="text" class="form-control form-control-lg" id="shipping-city" placeholder="e.g., Dubai">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">{{ __('messages.province_state') }}</label>
                        <input type="text" class="form-control form-control-lg" id="shipping-province" placeholder="e.g., Dubai">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="goToStep(1)">
                            <i class="ri-arrow-left-line me-2"></i>{{ __('messages.back') }}
                        </button>
                        <button type="button" class="btn btn-primary btn-lg flex-grow-1" onclick="calculateShipping()">
                            <i class="ri-calculator-line me-2"></i>{{ __('messages.calculate_shipping') }}
                        </button>
                    </div>
                </div>

                <!-- Step 3: Results -->
                <div id="shipping-step-3" style="display: none;">
                    <div id="shipping-result-container">
                        <!-- Loading State -->
                        <div id="shipping-loading" class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                            <p class="text-muted">{{ __('messages.calculating_shipping_cost') }}</p>
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
                            <i class="ri-arrow-left-line me-2"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-lg flex-grow-1" id="proceedToOrderBtn" style="display: none;" onclick="goToStep(4)">
                            <i class="ri-shopping-bag-line me-2"></i>{{ __('messages.create_order') }}
                        </button>
                    </div>
                </div>

                <!-- Step 4: Order Form -->
                <div id="shipping-step-4" style="display: none;">
                    <h5 class="mb-4">{{ __('messages.order_details') }}</h5>

                    <!-- Order Summary -->
                    <div class="card border-0 bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3">{{ __('messages.order_summary') }}</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('messages.product_price') }}:</span>
                                <strong id="order-product-price"></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('messages.shipping_cost') }}:</span>
                                <strong id="order-shipping-price"></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('messages.quantity') }}:</span>
                                <strong id="order-quantity"></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>{{ __('messages.total_amount') }}:</strong>
                                <strong class="text-primary fs-5" id="order-total-price"></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ app()->getLocale() == 'ar' ? 'رصيد المحفظة:' : 'Wallet Balance:' }}</span>
                                <strong id="order-wallet-balance" class="text-success"></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>{{ app()->getLocale() == 'ar' ? 'الرصيد المتبقي:' : 'Remaining Balance:' }}</span>
                                <strong id="order-remaining-balance"></strong>
                            </div>
                            <div id="insufficient-balance-warning" class="alert alert-danger mt-3 mb-0" style="display: none;">
                                <i class="ri-error-warning-line me-2"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'رصيد المحفظة غير كافٍ. يرجى شحن محفظتك قبل إنشاء الطلب.' : 'Insufficient wallet balance. Please top up your wallet before creating the order.' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information Form -->
                    <form id="orderForm">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" id="order-form-quantity">
                        <input type="hidden" name="selected_sku_attr" id="order-form-sku-attr">
                        <input type="hidden" name="shipping_country" id="order-form-country">
                        <input type="hidden" name="shipping_city" id="order-form-city">
                        <input type="hidden" name="shipping_province" id="order-form-province">
                        <input type="hidden" name="freight_amount" id="order-form-freight">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">{{ __('messages.full_name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('messages.email') }}</label>
                                <input type="email" class="form-control" name="customer_email">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.phone_code') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone_country" placeholder="+971" required>
                            </div>

                            <div class="col-md-9">
                                <label class="form-label">{{ __('messages.phone_number') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_phone" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('messages.street_address') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="shipping_address" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('messages.apartment_optional') }}</label>
                                <input type="text" class="form-control" name="shipping_address2">
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('messages.postal_zip_code') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="shipping_zip" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('messages.order_notes_optional') }}</label>
                                <textarea class="form-control" name="customer_notes" rows="3" placeholder="{{ __('messages.any_special_instructions') }}"></textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-lg" onclick="goToStep(3)">
                                <i class="ri-arrow-left-line me-2"></i>{{ __('messages.back') }}
                            </button>
                            <button type="submit" class="btn btn-success btn-lg flex-grow-1">
                                <i class="ri-shopping-bag-line me-2"></i>{{ __('messages.confirm_create_order') }}
                            </button>
                        </div>
                    </form>

                    <!-- Order Creation Result -->
                    <div id="order-creation-result" style="display: none;">
                        <div id="order-creation-loading" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;"></div>
                            <p class="text-muted">Creating your order...</p>
                        </div>

                        <div id="order-creation-success" style="display: none;" class="alert alert-success">
                            <!-- Will be populated dynamically -->
                        </div>

                        <div id="order-creation-error" style="display: none;" class="alert alert-danger">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Direct Order Modal for Choice Products -->
<div class="modal fade" id="directOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header text-white" style="border-radius: 20px 20px 0 0; background: linear-gradient(135deg, #561C04 0%, #e56300 100%);">
                <h5 class="modal-title">
                    <i class="ri-vip-crown-fill me-2"></i>{{ __('messages.create_order') }} - Choice Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Choice Product Notice -->
                <div class="alert alert-success border-0 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="ri-vip-crown-line me-2" style="font-size: 24px;"></i>
                        <div>
                            <strong>{{ app()->getLocale() == 'ar' ? 'منتج Choice - شحن مجاني!' : 'Choice Product - Free Shipping!' }}</strong>
                            <p class="mb-0 small">{{ app()->getLocale() == 'ar' ? 'منتجات Choice تأتي بشحن مجاني وسريع' : 'Choice products come with free and fast shipping' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Form -->
                <form id="directOrderForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="is_choice" value="1">
                    <input type="hidden" name="freight_amount" value="0">

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="ri-shopping-cart-line me-1"></i>{{ __('messages.quantity') }} <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control form-control-lg" name="quantity" value="1" min="1" max="999" required>
                    </div>

                    <!-- Product Variant (if available) -->
                    @if($aliexpressData && isset($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']) && count($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']) > 0)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="ri-palette-line me-1"></i>{{ __('messages.select_product_variant') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" name="variant_index" id="directVariantSelect" required>
                                <option value="">{{ app()->getLocale() == 'ar' ? 'اختر النوع' : 'Select Variant' }}</option>
                                @foreach($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] as $index => $sku)
                                    <option value="{{ $index }}"
                                            data-price="{{ $sku['offer_sale_price'] ?? $sku['sku_price'] ?? 0 }}"
                                            data-stock="{{ $sku['sku_available_stock'] ?? 0 }}">
                                        @foreach($sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'] as $property)
                                            {{ $property['sku_property_name'] }}: {{ $property['sku_property_value'] }}
                                        @endforeach
                                        - ${{ $sku['offer_sale_price'] ?? $sku['sku_price'] ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <hr class="my-4">

                    <!-- Customer Information -->
                    <h6 class="mb-3">{{ __('messages.customer_information') }}</h6>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'الاسم الكامل' : 'Full Name' }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="customer_name" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
                            <input type="email" class="form-control" name="customer_email">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'كود الهاتف' : 'Phone Code' }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone_country" placeholder="+971" required>
                        </div>

                        <div class="col-md-9">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'رقم الهاتف' : 'Phone Number' }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="customer_phone" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="shipping_country" required>
                                <option value="AE">{{ __('messages.united_arab_emirates') }}</option>
                                <option value="SA">{{ __('messages.saudi_arabia') }}</option>
                                <option value="US">United States</option>
                                <option value="GB">United Kingdom</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'المدينة' : 'City' }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="shipping_city" placeholder="e.g., Dubai" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'العنوان' : 'Street Address' }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="shipping_address" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'شقة/جناح (اختياري)' : 'Apartment, Suite, etc. (Optional)' }}</label>
                            <input type="text" class="form-control" name="shipping_address2">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'المقاطعة/الولاية' : 'Province/State' }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="shipping_province" placeholder="e.g., Dubai" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'الرمز البريدي' : 'Postal/ZIP Code' }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="shipping_zip" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'ملاحظات الطلب (اختياري)' : 'Order Notes (Optional)' }}</label>
                            <textarea class="form-control" name="customer_notes" rows="3" placeholder="{{ app()->getLocale() == 'ar' ? 'أي تعليمات خاصة؟' : 'Any special instructions?' }}"></textarea>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="ri-shopping-bag-line me-2"></i>{{ app()->getLocale() == 'ar' ? 'تأكيد وإنشاء الطلب' : 'Confirm & Create Order' }}
                        </button>
                    </div>
                </form>

                <!-- Order Result -->
                <div id="direct-order-result" style="display: none;">
                    <div id="direct-order-loading" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;"></div>
                        <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'جاري إنشاء طلبك...' : 'Creating your order...' }}</p>
                    </div>

                    <div id="direct-order-success" style="display: none;" class="alert alert-success"></div>
                    <div id="direct-order-error" style="display: none;" class="alert alert-danger"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #561C04 0%, #e56300 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #561C04 0%, #e56300 100%);
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
    border-bottom-color: #561C04;
    color: #561C04;
    background: none;
}

/* Variant Option Styles */
.variant-option {
    cursor: pointer;
    transition: all 0.3s ease;
}

.variant-option:hover {
    border-color: #561C04 !important;
    box-shadow: 0 4px 12px rgba(86, 28, 4, 0.2);
    transform: translateY(-2px);
}

.variant-option.selected {
    border-color: #561C04 !important;
    background-color: #fff9f5;
    box-shadow: 0 0 0 3px rgba(86, 28, 4, 0.2);
}

.variant-option .form-check-input:checked {
    background-color: #561C04;
    border-color: #561C04;
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
    background: #561C04;
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

/* Button Hover Styles */
.btn-primary {
    background-color: #561C04;
    border-color: #561C04;
}

.btn-primary:hover {
    background-color: #561C04 !important;
    border-color: #561C04 !important;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(86, 28, 4, 0.4);
}

.btn-outline-primary:hover {
    background-color: #561C04 !important;
    border-color: #561C04 !important;
    color: white !important;
}

.btn-outline-secondary:hover {
    background-color: #561C04 !important;
    border-color: #561C04 !important;
    color: white !important;
}

.btn-outline-info:hover {
    background-color: #561C04 !important;
    border-color: #561C04 !important;
    color: white !important;
}

.btn-success:hover {
    background-color: #e56300 !important;
    border-color: #e56300 !important;
    color: white !important;
}

/* Text Colors */
.text-primary {
    color: #561C04 !important;
}

.ri-box-3-line.text-primary,
.ri-money-dollar-circle-line.text-primary,
.ri-truck-line.text-success {
    color: #561C04 !important;
}
</style>

<script>
// Variant data for shipping calculator
const variantsData = @json($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? []);
let selectedVariantIndex = null;
let selectedVariantData = null;
let calculatedShippingData = null;

// Currency data for conversion
const currentCurrency = {
    code: '{{ $currentCurrency->code }}',
    symbol: '{{ $currentCurrency->symbol }}',
    exchangeRate: {{ $currentCurrency->exchange_rate }},
    name: '{{ $currentCurrency->name }}'
};

// Wallet balance (in AED - base currency)
const walletBalanceAED = {{ $walletBalance ?? 0 }};

// Product price from system (already converted to current currency in blade)
const productPrice = {{ $currentCurrency->convertFrom($product->price, $product->currency ?? 'USD') }};
const productCurrency = '{{ $currentCurrency->code }}';

// Currency icon SVGs
const currencyIcons = {
    'AED': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="inline-block" style="vertical-align: middle;"><path d="M8 7V17H12C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7H8Z" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 11H18.5" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 13H12.5H18.5" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
    'SAR': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="inline-block" style="vertical-align: middle;"><text x="4" y="17" font-size="16" fill="currentColor">﷼</text></svg>',
    'USD': ''
};

// Convert amount from USD to current currency
function convertToCurrentCurrency(amount, fromCurrency = 'USD') {
    if (fromCurrency === currentCurrency.code) {
        return amount;
    }

    // Exchange rates relative to USD
    const exchangeRates = {
        'USD': 1,
        'AED': 3.6725,
        'SAR': 3.75
    };

    // Convert to USD first
    const fromRate = exchangeRates[fromCurrency] || 1;
    const usdAmount = amount / fromRate;

    // Then convert to target currency
    return usdAmount * currentCurrency.exchangeRate;
}

// Format amount with currency icon
function formatCurrency(amount) {
    const formattedAmount = parseFloat(amount).toFixed(2);
    const icon = currencyIcons[currentCurrency.code] || currentCurrency.symbol;
    const isArabic = '{{ app()->getLocale() }}' === 'ar';

    if (isArabic) {
        return formattedAmount + ' ' + icon;
    }
    return icon + ' ' + formattedAmount;
}

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

    // DEBUG: Log full variant structure
    console.log('═══ VARIANT SELECTED ═══');
    console.log('Variant Index:', index);
    console.log('Variant Data (Full):', selectedVariantData);
    console.log('Variant Data (JSON):', JSON.stringify(selectedVariantData, null, 2));
    console.log('Available Fields:', Object.keys(selectedVariantData));
    console.log('ID Field:', selectedVariantData.id);
    console.log('ID Type:', typeof selectedVariantData.id);
    console.log('ID Contains #:', selectedVariantData.id ? selectedVariantData.id.toString().includes('#') : 'N/A');
    console.log('sku_id Field:', selectedVariantData.sku_id);
    console.log('sku_code Field:', selectedVariantData.sku_code);
    console.log('═══════════════════════');
}

function goToStep(step) {
    // Hide all steps
    document.getElementById('shipping-step-1').style.display = 'none';
    document.getElementById('shipping-step-2').style.display = 'none';
    document.getElementById('shipping-step-3').style.display = 'none';
    document.getElementById('shipping-step-4').style.display = 'none';

    // Update step indicators (only if they exist - step 4 doesn't have an indicator)
    if (step <= 3) {
        document.getElementById('step-1')?.classList.remove('active');
        document.getElementById('step-2')?.classList.remove('active');
        document.getElementById('step-3')?.classList.remove('active');

        // Show target step indicator
        document.getElementById(`step-${step}`)?.classList.add('active');

        // Mark previous steps as active too
        for (let i = 1; i < step; i++) {
            document.getElementById(`step-${i}`)?.classList.add('active');
        }
    } else if (step === 4) {
        // For step 4, keep all 3 indicators active
        document.getElementById('step-1')?.classList.add('active');
        document.getElementById('step-2')?.classList.add('active');
        document.getElementById('step-3')?.classList.add('active');
    }

    // Show target step content
    document.getElementById(`shipping-step-${step}`).style.display = 'block';

    // If moving to step 4, populate order summary
    if (step === 4) {
        populateOrderSummary();
    }
}

function populateOrderSummary() {
    console.log('=== POPULATING ORDER SUMMARY ===');

    if (!selectedVariantData || !calculatedShippingData) {
        console.error('Missing required data for order summary');
        console.log('selectedVariantData:', selectedVariantData);
        console.log('calculatedShippingData:', calculatedShippingData);
        return;
    }

    const quantity = parseInt(document.getElementById('shipping-quantity').value) || 1;
    const country = document.getElementById('shipping-country').value;
    const city = document.getElementById('shipping-city').value;
    const province = document.getElementById('shipping-province').value;

    console.log('Form values from Step 2:', {
        quantity,
        country,
        city,
        province
    });

    // Use product price from system (already in current currency)
    const unitPriceConverted = productPrice;
    const productTotalConverted = productPrice * quantity;

    // Get shipping cost (API returns in USD) and convert to current currency
    const shippingCostUSD = parseFloat(calculatedShippingData.freight_amount || 0);
    const freightCurrency = calculatedShippingData.freight_currency || 'USD';
    const shippingCostConverted = convertToCurrentCurrency(shippingCostUSD, freightCurrency);

    // Calculate total in selected currency
    const totalAmountConverted = productTotalConverted + shippingCostConverted;

    // Update order summary display with converted amounts and currency icons
    document.getElementById('order-product-price').innerHTML = formatCurrency(productTotalConverted) + ` (${formatCurrency(unitPriceConverted)} × ${quantity})`;
    document.getElementById('order-shipping-price').innerHTML = formatCurrency(shippingCostConverted);
    document.getElementById('order-quantity').textContent = quantity;
    document.getElementById('order-total-price').innerHTML = formatCurrency(totalAmountConverted);

    // Calculate wallet balance in selected currency (wallet is stored in AED)
    const walletBalanceConverted = convertToCurrentCurrency(walletBalanceAED, 'AED');
    const remainingBalanceConverted = walletBalanceConverted - totalAmountConverted;

    // Update wallet balance display
    document.getElementById('order-wallet-balance').innerHTML = formatCurrency(walletBalanceConverted);

    const remainingBalanceEl = document.getElementById('order-remaining-balance');
    const insufficientWarning = document.getElementById('insufficient-balance-warning');

    if (remainingBalanceConverted >= 0) {
        remainingBalanceEl.innerHTML = formatCurrency(remainingBalanceConverted);
        remainingBalanceEl.className = 'text-success';
        insufficientWarning.style.display = 'none';
    } else {
        remainingBalanceEl.innerHTML = formatCurrency(remainingBalanceConverted);
        remainingBalanceEl.className = 'text-danger';
        insufficientWarning.style.display = 'block';
    }

    // Populate hidden form fields
    const quantityField = document.getElementById('order-form-quantity');
    const countryField = document.getElementById('order-form-country');
    const cityField = document.getElementById('order-form-city');
    const provinceField = document.getElementById('order-form-province');
    const freightField = document.getElementById('order-form-freight');

    if (quantityField) quantityField.value = quantity;
    if (countryField) countryField.value = country;
    if (cityField) cityField.value = city;
    if (provinceField) provinceField.value = province;
    if (freightField) freightField.value = shippingCostUSD; // Send original USD amount to backend

    console.log('Hidden fields populated:', {
        'order-form-quantity': quantityField?.value,
        'order-form-country': countryField?.value,
        'order-form-city': cityField?.value,
        'order-form-province': provinceField?.value,
        'order-form-freight': freightField?.value
    });

    // Build SKU attributes
    let skuAttr = '';
    if (selectedVariantData.ae_sku_property_dtos && selectedVariantData.ae_sku_property_dtos.ae_sku_property_d_t_o) {
        skuAttr = selectedVariantData.ae_sku_property_dtos.ae_sku_property_d_t_o.map(prop => {
            return `${prop.sku_property_id}:${prop.property_value_id}`;
        }).join(';');
    }
    const skuAttrField = document.getElementById('order-form-sku-attr');
    if (skuAttrField) skuAttrField.value = skuAttr;

    console.log('Order Summary Populated:', {
        unit_price: unitPrice,
        quantity: quantity,
        product_total: productTotal,
        shipping_cost: shippingCost,
        total_amount: totalAmount,
        currency: currency,
        sku_attr: skuAttr
    });
    console.log('=== ORDER SUMMARY COMPLETE ===');
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

    // Extract SKU ID - try multiple fields (IMPORTANT: check sku_id first, not id!)
    // The 'id' field often contains property combinations like "10:365211#A35 5G"
    // The 'sku_id' field contains the actual numeric SKU like "12000040883098803"
    let skuId = selectedVariantData.sku_id || selectedVariantData.sku_code || selectedVariantData.id || null;

    // Validate that SKU ID is numeric (not property combination)
    if (skuId && skuId.toString().includes('#')) {
        // This is a property combination, not a numeric SKU ID
        displayShippingError({
            error: 'Invalid SKU format detected',
            error_code: 'INVALID_SKU_FORMAT',
            message: 'The selected variant uses property combinations instead of numeric SKU ID. Please try a different product or contact support.'
        });
        goToStep(3);
        return;
    }

    if (!skuId) {
        displayShippingError({
            error: 'No SKU ID found',
            error_code: 'NO_SKU_ID',
            message: 'Could not find a valid SKU ID for the selected variant.'
        });
        goToStep(3);
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

    console.log('=== FREIGHT CALCULATION START ===');
    console.log('Selected Variant Data:', selectedVariantData);
    console.log('SKU ID:', skuId);
    console.log('Product ID:', {{ $product->id }});
    console.log('China Product ID:', '{{ $product->aliexpress_id }}');
    console.log('Quantity:', quantity);
    console.log('Country:', country);
    console.log('City:', city);
    console.log('Province:', province);
    console.log('SKU Attributes:', skuAttr);

    const requestPayload = {
        product_id: {{ $product->id }},
        quantity: quantity,
        country: country,
        city: city,
        province: province,
        sku_id: skuId
    };

    console.log('Request Payload:', requestPayload);
    console.log('Request Payload JSON:', JSON.stringify(requestPayload, null, 2));

    // Call freight calculation API
    fetch('{{ route("orders.calculate-freight") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(requestPayload)
    })
    .then(response => {
        console.log('Response Status:', response.status);
        console.log('Response OK:', response.ok);
        return response.json();
    })
    .then(data => {
        console.log('Response Data:', data);
        console.log('Response JSON:', JSON.stringify(data, null, 2));
        document.getElementById('shipping-loading').style.display = 'none';

        if (data.success) {
            console.log('✅ Freight calculation successful');
            calculatedShippingData = data;
            displayShippingSuccess(data);
        } else {
            console.log('❌ Freight calculation failed');
            console.log('Error:', data.error);
            console.log('Error Code:', data.error_code);
            displayShippingError(data);
        }
        console.log('=== FREIGHT CALCULATION END ===');
    })
    .catch(error => {
        console.error('❌ Fetch Error:', error);
        console.error('Error Message:', error.message);
        console.error('Error Stack:', error.stack);
        document.getElementById('shipping-loading').style.display = 'none';
        displayShippingError({error: error.message});
        console.log('=== FREIGHT CALCULATION END (ERROR) ===');
    });
}

function displayShippingSuccess(data) {
    const container = document.getElementById('shipping-success');
    const isArabic = '{{ app()->getLocale() }}' === 'ar';

    // Convert shipping cost to selected currency
    const freightCurrency = data.freight_currency || 'USD';
    const freightAmountUSD = parseFloat(data.freight_amount);
    const freightAmountConverted = convertToCurrentCurrency(freightAmountUSD, freightCurrency);

    let html = `
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="ri-checkbox-circle-fill text-success" style="font-size: 64px;"></i>
            </div>
            <h4 class="text-success mb-2">{{ __('messages.shipping_available') }}</h4>
            <p class="text-muted">{{ __('messages.we_can_ship_to_destination') }}</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 bg-light text-center h-100">
                    <div class="card-body">
                        <h3 class="text-primary mb-1">${formatCurrency(freightAmountConverted)}</h3>
                        <small class="text-muted">{{ __('messages.shipping_cost') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-light text-center h-100">
                    <div class="card-body">
                        <i class="ri-time-line text-info mb-2" style="font-size: 32px;"></i>
                        <h5 class="mb-1">${data.delivery_time || 'N/A'}</h5>
                        <small class="text-muted">{{ __('messages.delivery_time') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-light text-center h-100">
                    <div class="card-body">
                        <i class="ri-truck-line text-success mb-2" style="font-size: 32px;"></i>

                        <small class="text-muted">{{ __('messages.carrier') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info border-0">
            <strong>{{ __('messages.note') }}:</strong> {{ __('messages.shipping_cost_note') }}
        </div>
    `;

    container.innerHTML = html;
    container.style.display = 'block';
    document.getElementById('proceedToOrderBtn').style.display = 'block';
}

function displayShippingError(data) {
    const container = document.getElementById('shipping-error');

    let errorMsg = data.error || '{{ __('messages.shipping_calculation_failed') }}';

    container.innerHTML = `
        <div class="text-center mb-3">
            <i class="ri-error-warning-line" style="font-size: 48px;"></i>
        </div>
        <h6 class="alert-heading">{{ __('messages.shipping_calculation_failed') }}</h6>
        <p class="mb-0">${errorMsg}</p>
    `;

    container.style.display = 'block';
}

// Handle order form submission
document.addEventListener('DOMContentLoaded', function() {
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitOrder();
        });
    }
});

function submitOrder() {
    console.log('=== ORDER CREATION START ===');

    // Hide form and show loading
    document.getElementById('orderForm').style.display = 'none';
    document.getElementById('order-creation-result').style.display = 'block';
    document.getElementById('order-creation-loading').style.display = 'block';
    document.getElementById('order-creation-success').style.display = 'none';
    document.getElementById('order-creation-error').style.display = 'none';

    // Collect form data
    const formData = new FormData(document.getElementById('orderForm'));
    const orderData = {};
    formData.forEach((value, key) => {
        orderData[key] = value;
    });

    console.log('Order Data:', orderData);
    console.log('Order Data JSON:', JSON.stringify(orderData, null, 2));

    // Submit order
    fetch('{{ route("orders.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(orderData)
    })
    .then(async response => {
        console.log('Response Status:', response.status);
        console.log('Response OK:', response.ok);
        console.log('Response Content-Type:', response.headers.get('content-type'));

        // Parse JSON response
        const data = await response.json();

        // Return both response status and data
        return { response, data };
    })
    .then(({ response, data }) => {
        console.log('Response Data:', data);
        document.getElementById('order-creation-loading').style.display = 'none';

        // Check if successful (status 200-299)
        if (response.ok && (data.success || data.order)) {
            console.log('✅ Order created successfully');
            displayOrderSuccess(data);
        } else {
            console.log('❌ Order creation failed');
            console.log('Validation Errors:', data.errors);
            displayOrderError(data);
        }
        console.log('=== ORDER CREATION END ===');
    })
    .catch(error => {
        console.error('❌ Order Creation Error:', error);
        document.getElementById('order-creation-loading').style.display = 'none';
        displayOrderError({ error: error.message });
        console.log('=== ORDER CREATION END (ERROR) ===');
    });
}

function displayOrderSuccess(data) {
    const container = document.getElementById('order-creation-success');
    const order = data.order || data;
    const isArabic = '{{ app()->getLocale() }}' === 'ar';

    // Convert total price to selected currency
    const orderCurrency = order.currency || 'USD';
    const totalPrice = parseFloat(order.total_price || 0);
    const totalPriceConverted = convertToCurrentCurrency(totalPrice, orderCurrency);

    container.innerHTML = `
        <div class="text-center mb-3">
            <i class="ri-checkbox-circle-fill text-success" style="font-size: 64px;"></i>
        </div>
        <h4 class="alert-heading text-center mb-3">${isArabic ? 'تم إنشاء الطلب بنجاح!' : 'Order Created Successfully!'}</h4>
        <div class="mb-3">
            <strong>${isArabic ? 'رقم الطلب:' : 'Order Number:'}</strong> ${order.order_number || 'N/A'}<br>
            <strong>${isArabic ? 'الحالة:' : 'Status:'}</strong> <span class="badge bg-warning">${order.status || 'Pending'}</span><br>
            <strong>${isArabic ? 'المبلغ الإجمالي:' : 'Total Amount:'}</strong> ${formatCurrency(totalPriceConverted)}
        </div>
        <p class="mb-3">${data.message || (isArabic ? 'تم إنشاء طلبك بنجاح وسيتم معالجته قريباً.' : 'Your order has been placed successfully and will be processed shortly.')}</p>
        <div class="d-grid gap-2">
            <a href="{{ route('seller.dashboard') }}" class="btn btn-primary">
                <i class="ri-dashboard-line me-2"></i>${isArabic ? 'عرض لوحة التحكم' : 'View Dashboard'}
            </a>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="ri-close-line me-2"></i>${isArabic ? 'إغلاق' : 'Close'}
            </button>
        </div>
    `;
    container.style.display = 'block';
}

function displayOrderError(data) {
    const container = document.getElementById('order-creation-error');
    let errorMsg = data.error || data.message || 'Failed to create order. Please try again.';

    // Handle validation errors
    if (data.errors) {
        errorMsg = '<ul class="mb-0">';
        Object.values(data.errors).forEach(errors => {
            errors.forEach(error => {
                errorMsg += `<li>${error}</li>`;
            });
        });
        errorMsg += '</ul>';
    }

    container.innerHTML = `
        <div class="text-center mb-3">
            <i class="ri-error-warning-line text-danger" style="font-size: 48px;"></i>
        </div>
        <h6 class="alert-heading">Order Creation Failed</h6>
        <div>${errorMsg}</div>
        <div class="d-grid gap-2 mt-3">
            <button type="button" class="btn btn-outline-secondary" onclick="retryOrder()">
                <i class="ri-refresh-line me-2"></i>Try Again
            </button>
        </div>
    `;
    container.style.display = 'block';
}

function retryOrder() {
    document.getElementById('order-creation-result').style.display = 'none';
    document.getElementById('orderForm').style.display = 'block';
}

// Open direct order modal for Choice products
function openDirectOrderForm() {
    const modal = new bootstrap.Modal(document.getElementById('directOrderModal'));
    modal.show();
}

// Handle direct order form submission
document.addEventListener('DOMContentLoaded', function() {
    const directOrderForm = document.getElementById('directOrderForm');
    if (directOrderForm) {
        directOrderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitDirectOrder();
        });
    }
});

function submitDirectOrder() {
    const isArabic = document.documentElement.lang === 'ar';

    // Hide form and show loading
    document.getElementById('directOrderForm').style.display = 'none';
    document.getElementById('direct-order-result').style.display = 'block';
    document.getElementById('direct-order-loading').style.display = 'block';
    document.getElementById('direct-order-success').style.display = 'none';
    document.getElementById('direct-order-error').style.display = 'none';

    // Collect form data
    const formData = new FormData(document.getElementById('directOrderForm'));
    const orderData = {};
    formData.forEach((value, key) => {
        orderData[key] = value;
    });

    // Add SKU attributes if variant is selected
    const variantSelect = document.getElementById('directVariantSelect');
    if (variantSelect && variantSelect.value !== '') {
        const variantIndex = parseInt(variantSelect.value);
        const selectedVariant = variantsData[variantIndex];

        if (selectedVariant && selectedVariant.ae_sku_property_dtos && selectedVariant.ae_sku_property_dtos.ae_sku_property_d_t_o) {
            const skuAttr = selectedVariant.ae_sku_property_dtos.ae_sku_property_d_t_o.map(prop => {
                return `${prop.sku_property_id}:${prop.property_value_id}`;
            }).join(';');
            orderData.selected_sku_attr = skuAttr;
        }
    }

    console.log('Direct Order Data:', orderData);

    // Submit order
    fetch('{{ route("orders.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(orderData)
    })
    .then(async response => {
        const data = await response.json();
        return { response, data };
    })
    .then(({ response, data }) => {
        document.getElementById('direct-order-loading').style.display = 'none';

        if (response.ok && (data.success || data.order)) {
            displayDirectOrderSuccess(data);
        } else {
            displayDirectOrderError(data);
        }
    })
    .catch(error => {
        console.error('Direct Order Error:', error);
        document.getElementById('direct-order-loading').style.display = 'none';
        displayDirectOrderError({ error: error.message });
    });
}

function displayDirectOrderSuccess(data) {
    const container = document.getElementById('direct-order-success');
    const order = data.order || data;
    const isArabic = document.documentElement.lang === 'ar';

    // Convert total price to selected currency
    const orderCurrency = order.currency || 'USD';
    const totalPrice = parseFloat(order.total_price || 0);
    const totalPriceConverted = convertToCurrentCurrency(totalPrice, orderCurrency);

    container.innerHTML = `
        <div class="text-center mb-3">
            <i class="ri-checkbox-circle-fill text-success" style="font-size: 64px;"></i>
        </div>
        <h4 class="alert-heading text-center mb-3">${isArabic ? 'تم إنشاء الطلب بنجاح!' : 'Order Created Successfully!'}</h4>
        <div class="mb-3">
            <strong>${isArabic ? 'رقم الطلب:' : 'Order Number:'}</strong> ${order.order_number || 'N/A'}<br>
            <strong>${isArabic ? 'الحالة:' : 'Status:'}</strong> <span class="badge bg-warning">${order.status || 'Pending'}</span><br>
            <strong>${isArabic ? 'المبلغ الإجمالي:' : 'Total Amount:'}</strong> ${formatCurrency(totalPriceConverted)}<br>
            <strong class="text-success">${isArabic ? 'الشحن: مجاني (منتج Choice)' : 'Shipping: Free (Choice Product)'}</strong>
        </div>
        <p class="mb-3">${data.message || (isArabic ? 'تم إنشاء طلبك بنجاح وسيتم معالجته قريباً.' : 'Your order has been placed successfully and will be processed shortly.')}</p>
        <div class="d-grid gap-2">
            <a href="{{ route('seller.dashboard') }}" class="btn btn-primary">
                <i class="ri-dashboard-line me-2"></i>${isArabic ? 'عرض لوحة التحكم' : 'View Dashboard'}
            </a>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="ri-close-line me-2"></i>${isArabic ? 'إغلاق' : 'Close'}
            </button>
        </div>
    `;
    container.style.display = 'block';
}

function displayDirectOrderError(data) {
    const container = document.getElementById('direct-order-error');
    const isArabic = document.documentElement.lang === 'ar';
    let errorMsg = data.error || data.message || (isArabic ? 'فشل إنشاء الطلب. يرجى المحاولة مرة أخرى.' : 'Failed to create order. Please try again.');

    if (data.errors) {
        errorMsg = '<ul class="mb-0">';
        Object.values(data.errors).forEach(errors => {
            errors.forEach(error => {
                errorMsg += `<li>${error}</li>`;
            });
        });
        errorMsg += '</ul>';
    }

    container.innerHTML = `
        <div class="text-center mb-3">
            <i class="ri-error-warning-line text-danger" style="font-size: 48px;"></i>
        </div>
        <h6 class="alert-heading">${isArabic ? 'فشل إنشاء الطلب' : 'Order Creation Failed'}</h6>
        <div>${errorMsg}</div>
        <div class="d-grid gap-2 mt-3">
            <button type="button" class="btn btn-outline-secondary" onclick="retryDirectOrder()">
                <i class="ri-refresh-line me-2"></i>${isArabic ? 'حاول مرة أخرى' : 'Try Again'}
            </button>
        </div>
    `;
    container.style.display = 'block';
}

function retryDirectOrder() {
    document.getElementById('direct-order-result').style.display = 'none';
    document.getElementById('directOrderForm').style.display = 'block';
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

    // Reset order form
    document.getElementById('orderForm')?.reset();
    document.getElementById('orderForm').style.display = 'block';
    document.getElementById('order-creation-result').style.display = 'none';
});
</script>
@endsection
