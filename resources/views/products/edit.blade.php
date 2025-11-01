@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                {{ __('messages.edit_product') }}
                @if($product->isAliexpressProduct())
                    <span class="badge bg-info ms-2">
                        <i class="ri-shopping-cart-line"></i> AliExpress
                    </span>
                @endif
            </h5>
            <div>
                @if($product->isAliexpressProduct())
                    <form action="{{ route('products.sync', $product) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm me-2">
                            <i class="ri-refresh-line me-1"></i> {{ __('messages.sync') }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> {{ __('messages.back') }}
                </a>
            </div>
        </div>

        <div class="card-body">
            @if($product->isAliexpressProduct())
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    <strong>{{ __('messages.aliexpress_product') }}:</strong> {{ __('messages.product_imported_from_aliexpress') }}
                    <a href="{{ $product->aliexpress_url }}" target="_blank">{{ __('messages.view_on_aliexpress') }}</a>
                    @if($product->last_synced_at)
                        <br><small>{{ __('messages.last_synced') }}: {{ $product->last_synced_at->diffForHumans() }}</small>
                    @endif
                </div>
            @endif

            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Product Name -->
                    <div class="col-md-8 mb-3">
                        <label for="name" class="form-label">{{ __('messages.product_name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- SKU -->
                    <div class="col-md-4 mb-3">
                        <label for="sku" class="form-label">{{ __('messages.sku') }}</label>
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Short Description -->
                    <div class="col-12 mb-3">
                        <label for="short_description" class="form-label">{{ __('messages.short_description') }}</label>
                        <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                        @error('short_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">{{ __('messages.description') }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">{{ __('messages.category') }}</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                            <option value="">{{ __('messages.select_category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('messages.status') }}</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">{{ __('messages.active') }}</label>
                        </div>
                    </div>

                    <!-- Currency -->
                    <div class="col-md-3 mb-3">
                        <label for="currency" class="form-label">{{ __('messages.currency') }}</label>
                        <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                            <option value="AED" {{ old('currency', $product->currency) == 'AED' ? 'selected' : '' }}>AED</option>
                            <option value="USD" {{ old('currency', $product->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="EUR" {{ old('currency', $product->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                            <option value="GBP" {{ old('currency', $product->currency) == 'GBP' ? 'selected' : '' }}>GBP</option>
                            <option value="SAR" {{ old('currency', $product->currency) == 'SAR' ? 'selected' : '' }}>SAR</option>
                            <option value="EGP" {{ old('currency', $product->currency) == 'EGP' ? 'selected' : '' }}>EGP</option>
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Original Price (from AliExpress) -->
                    <div class="col-md-3 mb-3">
                        <label for="original_price" class="form-label">{{ __('messages.original_price') }}</label>
                        <input type="number" class="form-control @error('original_price') is-invalid @enderror" id="original_price" name="original_price" value="{{ old('original_price', $product->original_price) }}" step="0.01" min="0" readonly style="background-color: #f5f5f5;">
                        @error('original_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('messages.price_from_aliexpress') }}</small>
                    </div>

                    <!-- Markup Amount -->
                    <div class="col-md-3 mb-3">
                        <label for="markup_amount" class="form-label">{{ __('messages.add_to_price') }} (+)</label>
                        <input type="number" class="form-control @error('markup_amount') is-invalid @enderror" id="markup_amount" name="markup_amount" value="{{ old('markup_amount', $product->markup_amount) }}" step="0.01" min="0">
                        @error('markup_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('messages.extra_amount_to_add') }}</small>
                    </div>

                    <!-- Markup Percentage -->
                    <div class="col-md-3 mb-3">
                        <label for="markup_percentage" class="form-label">{{ __('messages.markup_percentage') }} (%)</label>
                        <input type="number" class="form-control @error('markup_percentage') is-invalid @enderror" id="markup_percentage" name="markup_percentage" value="{{ old('markup_percentage', $product->markup_percentage) }}" step="0.01" min="0" max="1000">
                        @error('markup_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('messages.percentage_markup') }}</small>
                    </div>

                    <!-- Final Price (Auto-calculated) -->
                    <div class="col-md-12 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <label for="price" class="form-label mb-0">Final Selling Price <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">{{ $product->currency ?? 'AED' }}</span>
                                            <input type="number" class="form-control form-control-lg @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                                            @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <strong>Calculation:</strong> Original Price + Markup Amount + (Original Price × Markup %)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Compare Price -->
                    <div class="col-md-4 mb-3">
                        <label for="compare_price" class="form-label">{{ __('messages.compare_at_price') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control @error('compare_price') is-invalid @enderror" id="compare_price" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}" step="0.01" min="0">
                            @error('compare_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Cost -->
                    <div class="col-md-4 mb-3">
                        <label for="cost" class="form-label">{{ __('messages.cost_per_item') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control @error('cost') is-invalid @enderror" id="cost" name="cost" value="{{ old('cost', $product->cost) }}" step="0.01" min="0">
                            @error('cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if($product->isAliexpressProduct())
                        <!-- Profit Margin for AliExpress Products -->
                        <div class="col-md-12 mb-3">
                            <label for="supplier_profit_margin" class="form-label">Profit Margin (%)</label>
                            <input type="number" class="form-control @error('supplier_profit_margin') is-invalid @enderror" id="supplier_profit_margin" name="supplier_profit_margin" value="{{ old('supplier_profit_margin', $product->supplier_profit_margin) }}" step="0.1" min="0" max="100">
                            @error('supplier_profit_margin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This margin is applied on top of AliExpress price + shipping</small>
                        </div>
                    @endif

                    <!-- Stock Quantity -->
                    <div class="col-md-6 mb-3">
                        <label for="stock_quantity" class="form-label">{{ __('messages.stock_quantity') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Track Inventory -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('messages.inventory') }}</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="track_inventory" name="track_inventory" value="1" {{ old('track_inventory', $product->track_inventory) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_inventory">{{ __('messages.track_inventory') }}</label>
                        </div>
                        <small class="text-muted">{{ __('messages.uncheck_for_unlimited_stock') }}</small>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> {{ __('messages.update_product') }}
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="ri-close-line me-1"></i> {{ __('messages.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Auto-calculate final price based on markup
    document.addEventListener('DOMContentLoaded', function() {
        const originalPriceInput = document.getElementById('original_price');
        const markupAmountInput = document.getElementById('markup_amount');
        const markupPercentageInput = document.getElementById('markup_percentage');
        const finalPriceInput = document.getElementById('price');
        const currencySelect = document.getElementById('currency');
        const currencySymbol = document.getElementById('currency-symbol');

        function calculateFinalPrice() {
            const originalPrice = parseFloat(originalPriceInput.value) || 0;
            const markupAmount = parseFloat(markupAmountInput.value) || 0;
            const markupPercentage = parseFloat(markupPercentageInput.value) || 0;

            // Calculate: Original Price + Markup Amount + (Original Price × Markup %)
            const percentageAmount = originalPrice * (markupPercentage / 100);
            const finalPrice = originalPrice + markupAmount + percentageAmount;

            // Update final price field
            finalPriceInput.value = finalPrice.toFixed(2);
        }

        // Update currency symbol
        function updateCurrencySymbol() {
            currencySymbol.textContent = currencySelect.value;
        }

        // Add event listeners
        markupAmountInput.addEventListener('input', calculateFinalPrice);
        markupPercentageInput.addEventListener('input', calculateFinalPrice);
        originalPriceInput.addEventListener('input', calculateFinalPrice);
        currencySelect.addEventListener('change', updateCurrencySymbol);

        // Calculate on page load
        calculateFinalPrice();
    });
</script>
@endsection



