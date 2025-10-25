@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">My Assigned Products</h5>
                <small class="text-muted">Products you have assigned from AliExpress</small>
            </div>
            <div>
                <a href="{{ route('products.search-page') }}" class="btn btn-primary btn-sm">
                    <i class="ri-search-line me-1"></i> Search More Products
                </a>
            </div>
        </div>

        <div class="card-body">
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

            @if($assignedProducts->count() > 0)
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h3 class="mb-0">{{ $assignedProducts->total() }}</h3>
                                <small>Total Assigned</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h3 class="mb-0">{{ $assignedProducts->where('pivot.status', 'assigned')->count() }}</h3>
                                <small>Pending Import</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h3 class="mb-0">{{ $assignedProducts->where('pivot.status', 'imported')->count() }}</h3>
                                <small>Imported</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h3 class="mb-0">{{ $assignedProducts->where('pivot.status', 'published')->count() }}</h3>
                                <small>Published</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Image</th>
                                <th>Product Info</th>
                                <th style="width: 200px;">AliExpress ID</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 150px;">Assigned Date</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignedProducts as $assignedProduct)
                                @php
                                    $aliexpressProductId = $assignedProduct->pivot->aliexpress_product_id;
                                    $status = $assignedProduct->pivot->status;
                                    $createdAt = $assignedProduct->pivot->created_at;
                                @endphp
                                <tr>
                                    <td>
                                        @php
                                            $productImage = null;
                                            if($assignedProduct->images && is_array($assignedProduct->images) && count($assignedProduct->images) > 0) {
                                                $productImage = $assignedProduct->images[0];
                                            }
                                        @endphp
                                        @if($productImage)
                                            <img src="{{ $productImage }}"
                                                 alt="Product"
                                                 class="img-thumbnail"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                 style="width: 60px; height: 60px; border-radius: 8px;">
                                                <i class="ri-image-line text-muted" style="font-size: 24px;"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $assignedProduct->name }}</strong>
                                        <br>
                                        @if($assignedProduct->price)
                                            <span class="text-primary fw-bold">AED {{ number_format($assignedProduct->price, 2) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="small">{{ $aliexpressProductId }}</code>
                                    </td>
                                    <td>
                                        @if($status === 'assigned')
                                            <span class="badge bg-warning">
                                                <i class="ri-time-line me-1"></i> Assigned
                                            </span>
                                        @elseif($status === 'imported')
                                            <span class="badge bg-info">
                                                <i class="ri-download-line me-1"></i> Imported
                                            </span>
                                        @elseif($status === 'published')
                                            <span class="badge bg-success">
                                                <i class="ri-check-line me-1"></i> Published
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $createdAt->format('M d, Y') }}
                                            <br>
                                            {{ $createdAt->format('h:i A') }}
                                        </small>
                                    </td>
                                    <td>
                                        <a href="{{ route('products.show', $assignedProduct->id) }}"
                                           class="btn btn-sm btn-outline-primary mb-1">
                                            <i class="ri-eye-line me-1"></i> View
                                        </a>
                                        <a href="{{ route('products.edit', $assignedProduct->id) }}"
                                           class="btn btn-sm btn-outline-secondary mb-1">
                                            <i class="ri-edit-line me-1"></i> Edit
                                        </a>
                                        <a href="https://www.aliexpress.com/item/{{ $aliexpressProductId }}.html"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-info mb-1">
                                            <i class="ri-external-link-line me-1"></i> AliExpress
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $assignedProducts->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="ri-inbox-line" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3">No Assigned Products Yet</h5>
                    <p class="text-muted">Start by searching AliExpress products and assigning them to your account.</p>
                    <a href="{{ route('products.search-page') }}" class="btn btn-primary mt-3">
                        <i class="ri-search-line me-1"></i> Search AliExpress Products
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>


<style>
    .card-body h3 {
        font-size: 2rem;
        font-weight: 700;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        transition: background-color 0.2s;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
