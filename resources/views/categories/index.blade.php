@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Categories</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('categories.fetch-tree') }}" class="btn btn-success">
                    <i class="ri-download-cloud-line me-1"></i> Fetch Category Tree
                </a>
                <a href="{{ route('categories.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> Add Category
                </a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-line me-2"></i>
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

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Image</th>
                            <th>Name</th>
                            <th>Arabic Name</th>
                            <th>Slug</th>
                            <th>AliExpress ID</th>
                            <th>Products</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    @if($category->photo)
                                        <img src="{{ asset('storage/' . $category->photo) }}" alt="{{ $category->name }}" style="width: 40px; height: 40px; object-fit: contain;">
                                    @elseif($category->image)
                                        <img src="{{ $category->image }}" alt="{{ $category->name }}" style="width: 40px; height: 40px; object-fit: contain;">
                                    @else
                                        <div style="width: 40px; height: 40px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ri-shopping-bag-line" style="font-size: 20px; color: #999;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $category->name }}</strong>
                                    @if($category->parent)
                                        <br><small class="text-muted">Parent: {{ $category->parent->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($category->name_ar)
                                        <span dir="rtl">{{ $category->name_ar }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>
                                    @if($category->aliexpress_category_id)
                                        <span class="badge bg-info">{{ $category->aliexpress_category_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $category->products_count }}</span>
                                </td>
                                <td>{{ $category->order }}</td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($category->aliexpress_category_id)
                                        <a href="{{ route('categories.fetch-subcategories', $category) }}" class="btn btn-sm btn-icon btn-success" title="Fetch Subcategories">
                                            <i class="ri-download-cloud-line"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-icon btn-primary" title="Edit">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-danger" title="Delete">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="ri-inbox-line" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">No categories found</p>
                                    <a href="{{ route('categories.create') }}" class="btn btn-primary">
                                        <i class="ri-add-line me-1"></i> Create First Category
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
