@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Product Details
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
                @can('edit-products')
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm me-2">
                        <i class="ri-pencil-line me-1"></i> Edit
                    </a>
                @endcan
                <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">
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
                                        <img src="{{ $image }}" class="d-block w-100" alt="{{ $product->name }}" style="height: 400px; object-fit: contain;">
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
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 400px;">
                            <i class="ri-image-line" style="font-size: 64px; color: #ccc;"></i>
                        </div>
                    @endif
                </div>

                <!-- Product Info -->
                <div class="col-md-7">
                    <h3 class="mb-3">{{ $product->name }}</h3>

                    @if($product->short_description)
                        <p class="text-muted">{{ $product->short_description }}</p>
                    @endif

                    <!-- Pricing -->
                    <div class="mb-4">
                        <h4 class="text-primary mb-2">${{ number_format($product->price, 2) }}</h4>
                        @if($product->compare_price && $product->compare_price > $product->price)
                            <p class="text-muted mb-0">
                                <s>${{ number_format($product->compare_price, 2) }}</s>
                                <span class="badge bg-danger ms-2">
                                    {{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}% OFF
                                </span>
                            </p>
                        @endif
                    </div>

                    <!-- Product Details -->
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th style="width: 150px;">SKU:</th>
                                    <td>{{ $product->sku ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                </tr>
                                <tr>
                                    <th>Stock:</th>
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
                                @if($product->cost)
                                    <tr>
                                        <th>Cost:</th>
                                        <td>
                                            ${{ number_format($product->cost, 2) }}
                                            @if($product->price && $product->cost)
                                                <span class="text-success ms-2">
                                                    ({{ number_format($product->getProfitMargin(), 1) }}% margin)
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                @if($product->isAliexpressProduct())
                                    <tr>
                                        <th>AliExpress ID:</th>
                                        <td>
                                            {{ $product->aliexpress_id }}
                                            @if($product->aliexpress_url)
                                                <a href="{{ $product->aliexpress_url }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                                    <i class="ri-external-link-line"></i> View on AliExpress
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>AliExpress Price:</th>
                                        <td>${{ number_format($product->aliexpress_price, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Shipping Cost:</th>
                                        <td>${{ number_format($product->shipping_cost, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Profit Margin:</th>
                                        <td>{{ number_format($product->supplier_profit_margin, 1) }}%</td>
                                    </tr>
                                    @if($product->processing_time_days)
                                        <tr>
                                            <th>Processing Time:</th>
                                            <td>{{ $product->processing_time_days }} days</td>
                                        </tr>
                                    @endif
                                    @if($product->last_synced_at)
                                        <tr>
                                            <th>Last Synced:</th>
                                            <td>
                                                {{ $product->last_synced_at->format('Y-m-d H:i:s') }}
                                                <small class="text-muted">({{ $product->last_synced_at->diffForHumans() }})</small>
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $product->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated:</th>
                                    <td>{{ $product->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4">
                        @can('edit-products')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                                <i class="ri-pencil-line me-1"></i> Edit Product
                            </a>
                            @if($product->isAliexpressProduct())
                                <form action="{{ route('products.sync', $product) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-info">
                                        <i class="ri-refresh-line me-1"></i> Sync with AliExpress
                                    </button>
                                </form>
                            @endif
                        @endcan
                        @can('delete-products')
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="ri-delete-bin-line me-1"></i> Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($product->description)
                <div class="row mt-5">
                    <div class="col-12">
                        <h5 class="mb-3">Description</h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Specifications (for AliExpress products) -->
            @if($product->specifications && count($product->specifications) > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="mb-3">Specifications</h5>
                        <div class="card">
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    @foreach($product->specifications as $spec)
                                        <li class="mb-2">
                                            <strong>{{ $spec['name'] ?? 'Spec' }}:</strong> {{ $spec['value'] ?? 'N/A' }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Variants (for AliExpress products) -->
            @if($product->aliexpress_variants && count($product->aliexpress_variants) > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="mb-3">Available Variants</h5>
                        <div class="card">
                            <div class="card-body">
                                <pre class="mb-0">{{ json_encode($product->aliexpress_variants, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
