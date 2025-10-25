@extends('dashboard')

@section('content')
<div class="col-12">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-checkbox-circle-line me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('products.update', $product) }}" method="POST" id="productForm">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <span id="viewModeTitle">Product Details</span>
                    <span id="editModeTitle" style="display: none;">Edit Product</span>
                    @if($product->isAliexpressProduct())
                        <span class="badge bg-info ms-2">
                            <i class="ri-shopping-cart-line"></i> AliExpress
                        </span>
                    @endif
                    <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }} ms-2">
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </h5>
                <div>
                    <button type="button" id="toggleEditBtn" class="btn btn-primary btn-sm me-2" onclick="toggleEditMode()">
                        <i class="ri-pencil-line me-1"></i> <span id="toggleBtnText">Edit</span>
                    </button>
                    <button type="submit" id="saveBtn" class="btn btn-success btn-sm me-2" style="display: none;">
                        <i class="ri-save-line me-1"></i> Save Changes
                    </button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary btn-sm me-2" style="display: none;" onclick="toggleEditMode()">
                        <i class="ri-close-line me-1"></i> Cancel
                    </button>
                    @if($product->isAliexpressProduct())
                        <a href="{{ $product->aliexpress_url }}" target="_blank" class="btn btn-outline-info btn-sm me-2">
                            <i class="ri-external-link-line me-1"></i> AliExpress
                        </a>
                    @endif
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- Product Images -->
                    <div class="col-md-5">
                        @if($product->images && count($product->images) > 0)
                            <div id="productCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                                <div class="carousel-inner rounded">
                                    @foreach($product->images as $index => $image)
                                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                            <img src="{{ $image }}" class="d-block w-100" alt="{{ $product->name }}" style="height: 400px; object-fit: contain; background: #f8f9fa;">
                                        </div>
                                    @endforeach
                                </div>
                                @if(count($product->images) > 1)
                                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                @endif
                            </div>
                            <div class="text-center">
                                <small class="text-muted">{{ count($product->images) }} image(s)</small>
                            </div>
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 400px;">
                                <i class="ri-image-line" style="font-size: 64px; color: #ccc;"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <div class="col-md-7">
                        <!-- Product Name -->
                        <div class="mb-3">
                            <h3 class="mb-2 view-mode">{{ $product->name }}</h3>
                            <div class="edit-mode" style="display: none;">
                                <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Short Description -->
                        @if($product->short_description)
                            <div class="mb-3">
                                <p class="text-muted view-mode">{{ $product->short_description }}</p>
                                <div class="edit-mode" style="display: none;">
                                    <label for="short_description" class="form-label">Short Description</label>
                                    <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                                    @error('short_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <!-- Pricing Section -->
                        <div class="mb-4">
                            <!-- View Mode Pricing -->
                            <div class="view-mode">
                                <h4 class="text-primary mb-2">{{ $product->currency ?? 'AED' }} {{ number_format($product->price, 2) }}</h4>
                                @if($product->compare_price && $product->compare_price > $product->price)
                                    <p class="text-muted mb-0">
                                        <s>{{ $product->currency ?? 'AED' }} {{ number_format($product->compare_price, 2) }}</s>
                                        <span class="badge bg-danger ms-2">
                                            {{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}% OFF
                                        </span>
                                    </p>
                                @endif

                                @if($product->original_price && $product->original_price > 0)
                                    <div class="mt-3">
                                        <div class="card bg-light">
                                            <div class="card-body py-2">
                                                <small class="text-muted d-block mb-1"><strong>Price Breakdown:</strong></small>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <small>Original Price (AliExpress):</small>
                                                    <small><strong>{{ $product->currency }} {{ number_format($product->original_price, 2) }}</strong></small>
                                                </div>
                                                @if($product->markup_amount > 0)
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <small>+ Markup Amount:</small>
                                                        <small class="text-success"><strong>+{{ $product->currency }} {{ number_format($product->markup_amount, 2) }}</strong></small>
                                                    </div>
                                                @endif
                                                @if($product->markup_percentage > 0)
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <small>+ Markup ({{ $product->markup_percentage }}%):</small>
                                                        <small class="text-success"><strong>+{{ $product->currency }} {{ number_format($product->original_price * ($product->markup_percentage / 100), 2) }}</strong></small>
                                                    </div>
                                                @endif
                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between">
                                                    <small><strong>Final Selling Price:</strong></small>
                                                    <small class="text-primary"><strong>{{ $product->currency }} {{ number_format($product->price, 2) }}</strong></small>
                                                </div>
                                                @if($product->original_price > 0)
                                                    <div class="d-flex justify-content-between mt-1">
                                                        <small>Your Profit:</small>
                                                        <small class="text-success"><strong>{{ $product->currency }} {{ number_format($product->price - $product->original_price, 2) }} ({{ number_format((($product->price - $product->original_price) / $product->original_price) * 100, 1) }}%)</strong></small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Edit Mode Pricing -->
                            <div class="edit-mode" style="display: none;">
                                <div class="row">
                                    <!-- Currency -->
                                    <div class="col-md-6 mb-3">
                                        <label for="currency" class="form-label">Currency</label>
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

                                    <!-- Original Price -->
                                    <div class="col-md-6 mb-3">
                                        <label for="original_price" class="form-label">Original Price (AliExpress)</label>
                                        <input type="number" class="form-control" id="original_price" name="original_price" value="{{ old('original_price', $product->original_price) }}" step="0.01" min="0" readonly style="background-color: #f5f5f5;">
                                        <small class="text-muted">From AliExpress</small>
                                    </div>

                                    <!-- Markup Amount -->
                                    <div class="col-md-6 mb-3">
                                        <label for="markup_amount" class="form-label">Add to Price (+)</label>
                                        <input type="number" class="form-control @error('markup_amount') is-invalid @enderror" id="markup_amount" name="markup_amount" value="{{ old('markup_amount', $product->markup_amount) }}" step="0.01" min="0">
                                        @error('markup_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Fixed amount to add</small>
                                    </div>

                                    <!-- Markup Percentage -->
                                    <div class="col-md-6 mb-3">
                                        <label for="markup_percentage" class="form-label">Markup (%)</label>
                                        <input type="number" class="form-control @error('markup_percentage') is-invalid @enderror" id="markup_percentage" name="markup_percentage" value="{{ old('markup_percentage', $product->markup_percentage) }}" step="0.01" min="0" max="1000">
                                        @error('markup_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Percentage markup</small>
                                    </div>

                                    <!-- Final Price -->
                                    <div class="col-12 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <label for="price" class="form-label">Final Selling Price <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text" id="currency-symbol">{{ $product->currency ?? 'AED' }}</span>
                                                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                                                    @error('price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    <strong>Auto-calculated:</strong> Original Price + Markup Amount + (Original Price × Markup %)
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Compare Price -->
                                    <div class="col-md-6 mb-3">
                                        <label for="compare_price" class="form-label">Compare at Price</label>
                                        <input type="number" class="form-control @error('compare_price') is-invalid @enderror" id="compare_price" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}" step="0.01" min="0">
                                        @error('compare_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">For showing discount</small>
                                    </div>

                                    <!-- Cost -->
                                    <div class="col-md-6 mb-3">
                                        <label for="cost" class="form-label">Cost per Item</label>
                                        <input type="number" class="form-control @error('cost') is-invalid @enderror" id="cost" name="cost" value="{{ old('cost', $product->cost) }}" step="0.01" min="0">
                                        @error('cost')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Details Table -->
                        <div class="table-responsive view-mode">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width: 150px;" class="bg-light">SKU:</th>
                                        <td>{{ $product->sku ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Category:</th>
                                        <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Stock:</th>
                                        <td>
                                            @if($product->track_inventory)
                                                <span class="badge {{ $product->stock_quantity > 10 ? 'bg-success' : ($product->stock_quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                                                    {{ $product->stock_quantity }} units
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Unlimited (Dropshipping)</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($product->isAliexpressProduct())
                                        <tr>
                                            <th class="bg-light">AliExpress ID:</th>
                                            <td>{{ $product->aliexpress_id }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th class="bg-light">Created:</th>
                                        <td>{{ $product->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Updated:</th>
                                        <td>{{ $product->updated_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Edit Mode Additional Fields -->
                        <div class="edit-mode" style="display: none;">
                            <div class="row">
                                <!-- Category -->
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                                        <option value="">Select Category</option>
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

                                <!-- SKU -->
                                <div class="col-md-6 mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Stock Quantity -->
                                <div class="col-md-6 mb-3">
                                    <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                                    @error('stock_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Track Inventory -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Inventory Tracking</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="track_inventory" name="track_inventory" value="1" {{ old('track_inventory', $product->track_inventory) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="track_inventory">Track inventory</label>
                                    </div>
                                    <small class="text-muted">Uncheck for unlimited stock</small>
                                </div>

                                <!-- Status -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="mb-3">Description</h5>
                        <div class="view-mode">
                            <div class="card bg-light">
                                <div class="card-body">
                                    {!! nl2br(e($product->description ?? 'No description available.')) !!}
                                </div>
                            </div>
                        </div>
                        <div class="edit-mode" style="display: none;">
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="6">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Specifications (View Only) -->
                @if($product->specifications && count($product->specifications) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="mb-3">Specifications</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($product->specifications as $spec)
                                            <div class="col-md-6 mb-2">
                                                <strong>{{ $spec['name'] ?? 'Spec' }}:</strong> {{ $spec['value'] ?? 'N/A' }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

<script>
    let isEditMode = false;

    function toggleEditMode() {
        isEditMode = !isEditMode;

        // Toggle visibility
        document.querySelectorAll('.view-mode').forEach(el => {
            el.style.display = isEditMode ? 'none' : '';
        });
        document.querySelectorAll('.edit-mode').forEach(el => {
            el.style.display = isEditMode ? '' : 'none';
        });

        // Toggle buttons
        document.getElementById('toggleEditBtn').style.display = isEditMode ? 'none' : '';
        document.getElementById('saveBtn').style.display = isEditMode ? '' : 'none';
        document.getElementById('cancelBtn').style.display = isEditMode ? '' : 'none';

        // Toggle titles
        document.getElementById('viewModeTitle').style.display = isEditMode ? 'none' : '';
        document.getElementById('editModeTitle').style.display = isEditMode ? '' : 'none';

        // If entering edit mode, calculate price
        if (isEditMode) {
            calculateFinalPrice();
        }
    }

    // Auto-calculate final price
    document.addEventListener('DOMContentLoaded', function() {
        const originalPriceInput = document.getElementById('original_price');
        const markupAmountInput = document.getElementById('markup_amount');
        const markupPercentageInput = document.getElementById('markup_percentage');
        const finalPriceInput = document.getElementById('price');
        const currencySelect = document.getElementById('currency');
        const currencySymbol = document.getElementById('currency-symbol');

        function calculateFinalPrice() {
            const originalPrice = parseFloat(originalPriceInput?.value) || 0;
            const markupAmount = parseFloat(markupAmountInput?.value) || 0;
            const markupPercentage = parseFloat(markupPercentageInput?.value) || 0;

            const percentageAmount = originalPrice * (markupPercentage / 100);
            const finalPrice = originalPrice + markupAmount + percentageAmount;

            if (finalPriceInput) {
                finalPriceInput.value = finalPrice.toFixed(2);
            }
        }

        function updateCurrencySymbol() {
            if (currencySymbol && currencySelect) {
                currencySymbol.textContent = currencySelect.value;
            }
        }

        // Add event listeners
        if (markupAmountInput) markupAmountInput.addEventListener('input', calculateFinalPrice);
        if (markupPercentageInput) markupPercentageInput.addEventListener('input', calculateFinalPrice);
        if (originalPriceInput) originalPriceInput.addEventListener('input', calculateFinalPrice);
        if (currencySelect) currencySelect.addEventListener('change', updateCurrencySymbol);

        // Make calculateFinalPrice globally accessible
        window.calculateFinalPrice = calculateFinalPrice;
    });
</script>

<style>
    .table th {
        font-weight: 600;
    }

    .carousel-item img {
        border-radius: 8px;
    }

    .card {
        border-radius: 12px;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .badge {
        font-weight: 500;
    }
</style>
@endsection
