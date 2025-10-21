@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Edit Product
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
                            <i class="ri-refresh-line me-1"></i> Sync
                        </button>
                    </form>
                @endif
                <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body">
            @if($product->isAliexpressProduct())
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    <strong>AliExpress Product:</strong> This product was imported from AliExpress.
                    <a href="{{ $product->aliexpress_url }}" target="_blank">View on AliExpress</a>
                    @if($product->last_synced_at)
                        <br><small>Last synced: {{ $product->last_synced_at->diffForHumans() }}</small>
                    @endif
                </div>
            @endif

            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Product Name -->
                    <div class="col-md-8 mb-3">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- SKU -->
                    <div class="col-md-4 mb-3">
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Short Description -->
                    <div class="col-12 mb-3">
                        <label for="short_description" class="form-label">Short Description</label>
                        <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                        @error('short_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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

                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="col-md-4 mb-3">
                        <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @if($product->isAliexpressProduct())
                            <small class="text-muted">
                                AliExpress: ${{ number_format($product->aliexpress_price, 2) }}
                                (Margin: {{ number_format($product->getProfitMargin(), 1) }}%)
                            </small>
                        @endif
                    </div>

                    <!-- Compare Price -->
                    <div class="col-md-4 mb-3">
                        <label for="compare_price" class="form-label">Compare at Price</label>
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
                        <label for="cost" class="form-label">Cost per Item</label>
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
                        <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Track Inventory -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Inventory</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="track_inventory" name="track_inventory" value="1" {{ old('track_inventory', $product->track_inventory) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_inventory">Track inventory</label>
                        </div>
                        <small class="text-muted">Uncheck for unlimited stock (dropshipping)</small>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Update Product
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="ri-close-line me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
