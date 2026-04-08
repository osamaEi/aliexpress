@extends('dashboard')

@section('content')
@php $isAr = app()->getLocale() == 'ar'; @endphp

<div class="col-12" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">{{ $isAr ? 'إدارة المنتجات' : 'Product Management' }}</h4>
            <p class="text-muted small mb-0">
                {{ $isAr ? 'جميع المنتجات: الصين (AliExpress) + منتجات التجار والموزعين' : 'All products: China (AliExpress) + seller & distributor products' }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('products.china.import') }}" class="btn btn-outline-info btn-sm">
                <i class="ri-ship-line me-1"></i>{{ $isAr ? 'استيراد من الصين' : 'Import from China' }}
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                <i class="ri-add-line me-1"></i>{{ $isAr ? 'منتج جديد' : 'New Product' }}
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('products.index') }}">
                <div class="row g-2 align-items-end">

                    {{-- Search --}}
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" name="search"
                                value="{{ request('search') }}"
                                placeholder="{{ $isAr ? 'اسم المنتج، SKU، رقم AliExpress' : 'Name, SKU, AliExpress ID' }}">
                        </div>
                    </div>

                    {{-- Category --}}
                    <div class="col-md-2">
                        <select name="category" class="form-select form-select-sm">
                            <option value="">{{ $isAr ? '— الفئة —' : '— Category —' }}</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $isAr ? ($cat->name_ar ?: $cat->name) : $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Source --}}
                    <div class="col-md-2">
                        <select name="source" class="form-select form-select-sm">
                            <option value="">{{ $isAr ? '— المصدر —' : '— Source —' }}</option>
                            <option value="aliexpress" {{ request('source') == 'aliexpress' ? 'selected' : '' }}>
                                🇨🇳 {{ $isAr ? 'الصين (AliExpress)' : 'China (AliExpress)' }}
                            </option>
                            <option value="local" {{ request('source') == 'local' ? 'selected' : '' }}>
                                🏪 {{ $isAr ? 'محلي (تاجر/موزع)' : 'Local (Seller/Distributor)' }}
                            </option>
                        </select>
                    </div>

                    {{-- Seller / Distributor --}}
                    <div class="col-md-2">
                        <select name="seller" class="form-select form-select-sm">
                            <option value="">{{ $isAr ? '— التاجر/الموزع —' : '— Seller/Distributor —' }}</option>
                            @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->store_name ?: $seller->name }}
                                ({{ $seller->user_type == 'distributor' ? ($isAr ? 'موزع' : 'Dist.') : ($isAr ? 'تاجر' : 'Seller') }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">{{ $isAr ? '— الحالة —' : '— Status —' }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ $isAr ? 'نشط' : 'Active' }}</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ $isAr ? 'غير نشط' : 'Inactive' }}</option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="ri-search-line"></i>
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-close-line"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Actions Bar --}}
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <span class="text-muted small">
            {{ $isAr ? 'إجمالي:' : 'Total:' }}
            <strong>{{ $products->total() }}</strong>
            {{ $isAr ? 'منتج' : 'products' }}
            &nbsp;|&nbsp;
            {{ $isAr ? 'سعر التحويل: 1 USD =' : 'Rate: 1 USD =' }}
            <strong>{{ number_format($usdToAed, 2) }} AED</strong>
        </span>
        <form action="{{ route('products.sync-all') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-info btn-sm"
                onclick="return confirm('{{ $isAr ? 'مزامنة جميع منتجات الصين؟' : 'Sync all China products?' }}')">
                <i class="ri-refresh-line me-1"></i>{{ $isAr ? 'مزامنة منتجات الصين' : 'Sync China Products' }}
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                <thead class="table-light">
                    <tr class="text-nowrap">
                        <th class="ps-3" style="width:60px;">{{ $isAr ? 'صورة' : 'Image' }}</th>
                        <th>{{ $isAr ? 'المنتج' : 'Product' }}</th>
                        <th>{{ $isAr ? 'التاجر / الموزع' : 'Seller / Dist.' }}</th>
                        <th>{{ $isAr ? 'الفئة' : 'Category' }}</th>
                        <th>{{ $isAr ? 'المصدر' : 'Source' }}</th>
                        <th class="text-end">{{ $isAr ? 'السعر' : 'Price' }}</th>
                        <th class="text-center">{{ $isAr ? 'المخزون' : 'Stock' }}</th>
                        <th class="text-center">{{ $isAr ? 'الحالة' : 'Status' }}</th>
                        <th class="text-center pe-3">{{ $isAr ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    @php
                        $productCurrency = strtoupper($product->currency ?: 'USD');
                        $priceInUsd  = $productCurrency === 'USD' ? $product->price : $product->price / $usdToAed;
                        $priceInAed  = $productCurrency === 'AED' ? $product->price : $product->price * $usdToAed;
                        $displayName = $isAr && $product->name_ar ? $product->name_ar : $product->name;
                        $altName     = $isAr ? $product->name : $product->name_ar;
                        $sellers     = $product->assignedUsers ?? collect();
                    @endphp
                    <tr>

                        {{-- Image --}}
                        <td class="ps-3">
                            @if($product->getPrimaryImage())
                                <img src="{{ $product->getPrimaryImage() }}"
                                     alt="{{ $displayName }}"
                                     class="rounded"
                                     style="width:48px;height:48px;object-fit:cover;">
                            @else
                                <div class="rounded bg-label-secondary d-flex align-items-center justify-content-center"
                                     style="width:48px;height:48px;">
                                    <i class="ri-image-line text-muted"></i>
                                </div>
                            @endif
                        </td>

                        {{-- Product Name --}}
                        <td style="max-width:240px;">
                            <a href="{{ route('products.show', $product) }}"
                               class="fw-semibold text-body text-decoration-none d-block"
                               title="{{ $displayName }}">
                                {{ Str::limit($displayName, 40) }}
                            </a>
                            @if($altName)
                            <div class="text-muted small" dir="{{ $isAr ? 'ltr' : 'rtl' }}" title="{{ $altName }}">
                                {{ Str::limit($altName, 35) }}
                            </div>
                            @endif
                            @if($product->sku)
                            <code style="font-size:.72rem;color:#6c757d;">{{ $product->sku }}</code>
                            @endif
                        </td>

                        {{-- Seller / Distributor --}}
                        <td style="min-width:150px;">
                            @if($sellers->count())
                                @foreach($sellers->take(2) as $seller)
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    @if($seller->logo)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($seller->logo) }}"
                                             alt="{{ $seller->name }}"
                                             class="rounded-circle"
                                             style="width:28px;height:28px;object-fit:cover;">
                                    @else
                                        <span class="avatar-initial rounded-circle bg-label-primary"
                                              style="width:28px;height:28px;font-size:.65rem;display:flex;align-items:center;justify-content:center;">
                                            {{ strtoupper(substr($seller->store_name ?: $seller->name, 0, 2)) }}
                                        </span>
                                    @endif
                                    <div>
                                        <div class="fw-medium" style="font-size:.8rem;line-height:1.2;">
                                            {{ Str::limit($seller->store_name ?: $seller->name, 20) }}
                                        </div>
                                        <span class="badge bg-label-{{ $seller->user_type == 'distributor' ? 'info' : 'warning' }}"
                                              style="font-size:.65rem;">
                                            {{ $seller->user_type == 'distributor' ? ($isAr ? 'موزع' : 'Dist.') : ($isAr ? 'تاجر' : 'Seller') }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                                @if($sellers->count() > 2)
                                <small class="text-muted">+{{ $sellers->count() - 2 }} {{ $isAr ? 'آخرين' : 'more' }}</small>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        {{-- Category --}}
                        <td>
                            @if($product->category)
                                <span class="badge bg-label-secondary">
                                    {{ $isAr ? ($product->category->name_ar ?: $product->category->name) : $product->category->name }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Source --}}
                        <td>
                            @if($product->isAliexpressProduct())
                                <span class="badge bg-label-info">
                                    🇨🇳 {{ $isAr ? 'الصين' : 'China' }}
                                </span>
                                @if($product->aliexpress_id)
                                <div style="font-size:.7rem;color:#6c757d;margin-top:2px;">
                                    #{{ $product->aliexpress_id }}
                                </div>
                                @endif
                            @else
                                <span class="badge bg-label-success">
                                    🏪 {{ $isAr ? 'محلي' : 'Local' }}
                                </span>
                            @endif
                        </td>

                        {{-- Price --}}
                        <td class="text-end" style="min-width:110px;">
                            <div class="fw-semibold">
                                {{ number_format($priceInAed, 2) }}
                                <small class="text-muted">AED</small>
                            </div>
                            <div class="text-muted small">
                                ≈ {{ number_format($priceInUsd, 2) }}
                                <small>USD</small>
                            </div>
                            @if($product->compare_price && $product->compare_price > $product->price)
                            @php $compareAed = $productCurrency === 'AED' ? $product->compare_price : $product->compare_price * $usdToAed; @endphp
                            <div><small class="text-muted"><s>{{ number_format($compareAed, 2) }} AED</s></small></div>
                            @endif
                        </td>

                        {{-- Stock --}}
                        <td class="text-center">
                            @if(!$product->track_inventory)
                                <span class="badge bg-label-secondary">∞</span>
                            @elseif($product->stock_quantity > 10)
                                <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                            @elseif($product->stock_quantity > 0)
                                <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                            @else
                                <span class="badge bg-danger">0</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="text-center">
                            <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-label-secondary' }}">
                                {{ $product->is_active ? ($isAr ? 'نشط' : 'Active') : ($isAr ? 'معطّل' : 'Inactive') }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="text-center pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                    <i class="ri-more-2-line"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @if($product->isAliexpressProduct())
                                    <a class="dropdown-item text-primary fw-semibold" href="{{ route('products.detail', $product) }}">
                                        <i class="ri-ship-line me-2"></i>
                                        {{ $isAr ? 'عرض وحساب الشحن' : 'View & Calc Shipping' }}
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('products.show', $product) }}">
                                        <i class="ri-eye-line me-2"></i>{{ $isAr ? 'عرض' : 'View' }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('products.edit', $product) }}">
                                        <i class="ri-pencil-line me-2"></i>{{ $isAr ? 'تعديل' : 'Edit' }}
                                    </a>
                                    @if($product->isAliexpressProduct())
                                    <form action="{{ route('products.sync', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="ri-refresh-line me-2"></i>{{ $isAr ? 'مزامنة' : 'Sync' }}
                                        </button>
                                    </form>
                                    @endif
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST"
                                          class="d-inline delete-product-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger delete-product-btn"
                                                data-product-name="{{ $displayName }}">
                                            <i class="ri-delete-bin-line me-2"></i>{{ $isAr ? 'حذف' : 'Delete' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="ri-inbox-line text-muted" style="font-size:3rem;opacity:.3;"></i>
                            <p class="text-muted mt-2 mb-3">{{ $isAr ? 'لا توجد منتجات مطابقة' : 'No products found' }}</p>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-refresh-line me-1"></i>{{ $isAr ? 'إعادة تعيين الفلتر' : 'Reset Filters' }}
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
            <small class="text-muted">
                @if($isAr)
                    عرض {{ $products->firstItem() }}–{{ $products->lastItem() }} من {{ $products->total() }} منتج
                @else
                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products
                @endif
            </small>
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>

@push('scripts')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}">
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-product-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const name = this.dataset.productName;
            const form = this.closest('form');
            Swal.fire({
                title: '{{ $isAr ? "هل أنت متأكد؟" : "Are you sure?" }}',
                html: `{{ $isAr ? "سيتم حذف المنتج" : "You are about to delete" }}<br><strong>"${name}"</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '{{ $isAr ? "نعم، احذف" : "Yes, delete" }}',
                cancelButtonText: '{{ $isAr ? "إلغاء" : "Cancel" }}',
                customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-secondary' },
                buttonsStyling: false
            }).then(r => { if (r.isConfirmed) form.submit(); });
        });
    });
});
</script>
@endpush
@endsection
