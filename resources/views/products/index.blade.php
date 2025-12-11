@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.product_management') }}</h5>
        </div>

        <!-- Filters -->
        <div class="card-body">
            <form method="GET" action="{{ route('products.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search_products_placeholder') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value="">{{ __('messages.all_categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="source" class="form-select">
                            <option value="">{{ __('messages.all_sources') }}</option>
                            <option value="aliexpress" {{ request('source') == 'aliexpress' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'الصين' : 'China' }}</option>
                            <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>{{ __('messages.manual') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">{{ __('messages.all_status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-search-line me-1"></i> {{ __('messages.filter') }}
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            <i class="ri-refresh-line me-1"></i> {{ __('messages.reset') }}
                        </a>
                    </div>
                </div>
            </form>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- China Products Actions -->
            <div class="mb-3">
                    <form action="{{ route('products.sync-all') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm" onclick="return confirm('{{ app()->getLocale() == 'ar' ? 'هل تريد مزامنة جميع المنتجات من الصين؟' : 'Do you want to sync all products from China?' }}')">
                            <i class="ri-refresh-line me-1"></i> {{ app()->getLocale() == 'ar' ? 'مزامنة الكل من الصين' : 'Sync All from China' }}
                        </button>
                    </form>

            </div>
        </div>

        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('messages.image') }}</th>
                        <th>{{ __('messages.product') }}</th>
                        <th>{{ __('messages.sku') }}</th>
                        <th>{{ __('messages.category') }}</th>
                        <th>{{ __('messages.price') }}</th>
                        <th>{{ __('messages.stock') }}</th>
                        <th>{{ __('messages.source') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                @if($product->getPrimaryImage())
                                    <img src="{{ $product->getPrimaryImage() }}" alt="{{ $product->name }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="ri-image-line text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->aliexpress_id)
                                    <br><small class="text-muted">{{ app()->getLocale() == 'ar' ? 'معرف الصين' : 'China ID' }}: {{ $product->aliexpress_id }}</small>
                                @endif
                            </td>
                            <td>{{ $product->sku ?? '-' }}</td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>
                                <strong>${{ number_format($product->price, 2) }}</strong>
                                @if($product->compare_price)
                                    <br><small class="text-muted"><s>${{ number_format($product->compare_price, 2) }}</s></small>
                                @endif
                            </td>
                            <td>
                                @if($product->track_inventory)
                                    <span class="badge {{ $product->stock_quantity > 10 ? 'bg-success' : ($product->stock_quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $product->stock_quantity }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.unlimited') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($product->isAliexpressProduct())
                                    <span class="badge bg-info">
                                        🇨🇳 {{ app()->getLocale() == 'ar' ? 'الصين' : 'China' }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.manual') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $product->is_active ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-line"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                            @if($product->isAliexpressProduct())
                                                <a class="dropdown-item text-primary fw-semibold" href="{{ route('products.detail', $product) }}">
                                                    <i class="ri-ship-line me-2"></i> View & Calculate Shipping
                                                </a>
                                                <div class="dropdown-divider"></div>
                                            @endif
                                            <a class="dropdown-item" href="{{ route('products.show', $product) }}">
                                                <i class="ri-eye-line me-2"></i> {{ __('messages.view') }}
                                            </a>

                                            <a class="dropdown-item" href="{{ route('products.edit', $product) }}">
                                                <i class="ri-pencil-line me-2"></i> {{ __('messages.edit') }}
                                            </a>
                                            @if($product->isAliexpressProduct())
                                                <form action="{{ route('products.sync', $product) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="ri-refresh-line me-2"></i> {{ __('messages.sync') }}
                                                    </button>
                                                </form>
                                            @endif

                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline delete-product-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item text-danger delete-product-btn" data-product-name="{{ $product->name }}">
                                                    <i class="ri-delete-bin-line me-2"></i> {{ __('messages.delete') }}
                                                </button>
                                            </form>

                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="ri-inbox-line" style="font-size: 48px; color: #ccc;"></i>
                                <p class="text-muted mt-2">{{ __('messages.no_products_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="card-footer">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete product confirmation with SweetAlert
    document.querySelectorAll('.delete-product-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productName = this.dataset.productName;
            const form = this.closest('form');

            Swal.fire({
                title: '{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد؟' : 'Are you sure?' }}',
                html: '{{ app()->getLocale() == 'ar' ? 'سيتم حذف المنتج' : 'You are about to delete the product' }}<br><strong>"' + productName + '"</strong><br>{{ app()->getLocale() == 'ar' ? 'لا يمكن التراجع عن هذا الإجراء!' : 'This action cannot be undone!' }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '{{ app()->getLocale() == 'ar' ? 'نعم، احذف!' : 'Yes, delete it!' }}',
                cancelButtonText: '{{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}',
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
