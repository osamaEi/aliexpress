@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'مراجعة منتجات التجار' : 'Distributor Products Moderation' }}</h4>
            <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'موافقة أو رفض منتجات التجار' : 'Approve or reject distributor products' }}</p>
        </div>
    </div>

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

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-warning me-3 p-2">
                            <i class="ri-time-line ri-24px"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['pending'] }}</h5>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'قيد المراجعة' : 'Pending Review' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                            <i class="ri-check-line ri-24px"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['approved'] }}</h5>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'موافق عليها' : 'Approved' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-danger me-3 p-2">
                            <i class="ri-close-line ri-24px"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['rejected'] }}</h5>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'مرفوضة' : 'Rejected' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.distributor-products.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">{{ app()->getLocale() == 'ar' ? 'قيد المراجعة' : 'Pending' }}</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'موافق عليها' : 'Approved' }}</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'مرفوضة' : 'Rejected' }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="distributor_id" class="form-label">{{ app()->getLocale() == 'ar' ? 'التاجر' : 'Distributor' }}</label>
                        <select class="form-select" id="distributor_id" name="distributor_id">
                            <option value="">{{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}</option>
                            @foreach($distributors as $distributor)
                            <option value="{{ $distributor->id }}" {{ request('distributor_id') == $distributor->id ? 'selected' : '' }}>
                                {{ $distributor->name }} ({{ $distributor->country ?? 'N/A' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">{{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                            placeholder="{{ app()->getLocale() == 'ar' ? 'اسم المنتج أو ID' : 'Product name or ID' }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-search-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}
                        </button>
                        <a href="{{ route('admin.distributor-products.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-refresh-line"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(!request('status') || request('status') === 'pending')
    <div class="card mb-4" id="bulk-actions" style="display: none;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <span><strong id="selected-count">0</strong> {{ app()->getLocale() == 'ar' ? 'منتج محدد' : 'products selected' }}</span>
                <div>
                    <button type="button" class="btn btn-success" id="bulk-approve-btn">
                        <i class="ri-check-line me-1"></i>
                        {{ app()->getLocale() == 'ar' ? 'موافقة على الكل' : 'Approve All Selected' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            @if(!request('status') || request('status') === 'pending')
                            <th>
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            @endif
                            <th>{{ app()->getLocale() == 'ar' ? 'المنتج' : 'Product' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'التاجر' : 'Distributor' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'السعر' : 'Price' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'العمولة' : 'Commission' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'تاريخ الإضافة' : 'Added Date' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            @if(!request('status') || request('status') === 'pending')
                            <td>
                                <input type="checkbox" class="form-check-input product-checkbox"
                                    data-product-id="{{ $product->product_id }}"
                                    data-user-id="{{ $product->distributor_id }}">
                            </td>
                            @endif
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->main_image)
                                    <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->product_name }}"
                                        class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                        style="width: 50px; height: 50px;">
                                        <i class="ri-image-line text-muted"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <strong>{{ app()->getLocale() == 'ar' && $product->product_name_ar ? $product->product_name_ar : $product->product_name }}</strong>
                                        @if($product->aliexpress_id)
                                        <div class="text-muted small">
                                            <i class="ri-shopping-bag-line"></i> AliExpress: {{ $product->aliexpress_id }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $product->distributor_name }}</strong>
                                <div class="text-muted small">{{ $product->distributor_email }}</div>
                            </td>
                            <td>
                                @php
                                    $countryFlags = [
                                        'AE' => '🇦🇪',
                                        'SA' => '🇸🇦',
                                        'CN' => '🇨🇳',
                                        'KW' => '🇰🇼',
                                        'BH' => '🇧🇭',
                                        'QA' => '🇶🇦',
                                        'OM' => '🇴🇲',
                                        'EG' => '🇪🇬',
                                    ];
                                @endphp
                                @if($product->distributor_country)
                                <span>{{ $countryFlags[$product->distributor_country] ?? '' }} {{ $product->distributor_country }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{!! format_currency($product->price, 'AED', 2, true) !!}</td>
                            <td>
                                @if($product->commission_rate)
                                <span class="badge bg-info">{{ $product->commission_rate }}%</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ \Carbon\Carbon::parse($product->created_at)->format('Y-m-d') }}</small>
                                <div class="text-muted small">{{ \Carbon\Carbon::parse($product->created_at)->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($product->status === 'pending' || $product->status === 'assigned')
                                    <span class="badge bg-warning">{{ app()->getLocale() == 'ar' ? 'قيد المراجعة' : 'Pending' }}</span>
                                @elseif($product->status === 'approved' || $product->status === 'imported' || $product->status === 'published')
                                    <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'موافق عليه' : 'Approved' }}</span>
                                @else
                                    <span class="badge bg-danger">{{ app()->getLocale() == 'ar' ? 'مرفوض' : 'Rejected' }}</span>
                                    @if($product->rejection_reason)
                                    <div class="text-muted small mt-1" title="{{ $product->rejection_reason }}">
                                        {{ Str::limit($product->rejection_reason, 30) }}
                                    </div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($product->status === 'pending' || $product->status === 'assigned')
                                <div class="d-flex gap-1">
                                    <form action="{{ route('admin.distributor-products.approve', [$product->product_id, $product->distributor_id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="{{ app()->getLocale() == 'ar' ? 'موافقة' : 'Approve' }}">
                                            <i class="ri-check-line"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal{{ $product->product_id }}_{{ $product->distributor_id }}"
                                        title="{{ app()->getLocale() == 'ar' ? 'رفض' : 'Reject' }}">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $product->product_id }}_{{ $product->distributor_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.distributor-products.reject', [$product->product_id, $product->distributor_id]) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ app()->getLocale() == 'ar' ? 'رفض المنتج' : 'Reject Product' }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد من رفض هذا المنتج؟' : 'Are you sure you want to reject this product?' }}</p>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'سبب الرفض' : 'Rejection Reason' }} *</label>
                                                        <textarea class="form-control" name="rejection_reason" rows="3" required
                                                            placeholder="{{ app()->getLocale() == 'ar' ? 'يرجى ذكر سبب رفض المنتج' : 'Please provide a reason for rejection' }}"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                        {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}
                                                    </button>
                                                    <button type="submit" class="btn btn-danger">
                                                        {{ app()->getLocale() == 'ar' ? 'رفض' : 'Reject' }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ (!request('status') || request('status') === 'pending') ? 9 : 8 }}" class="text-center py-4">
                                {{ app()->getLocale() == 'ar' ? 'لا توجد منتجات' : 'No products found' }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($products, 'hasPages') && $products->hasPages())
        <div class="card-footer">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>

@if(!request('status') || request('status') === 'pending')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    const bulkApproveBtn = document.getElementById('bulk-approve-btn');

    // Select all
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });
    }

    // Individual checkbox change
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const selected = document.querySelectorAll('.product-checkbox:checked');
        if (bulkActions) {
            if (selected.length > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = selected.length;
            } else {
                bulkActions.style.display = 'none';
            }
        }
    }

    // Bulk approve
    if (bulkApproveBtn) {
        bulkApproveBtn.addEventListener('click', function() {
            const selected = document.querySelectorAll('.product-checkbox:checked');
            if (selected.length === 0) return;

            const items = [];
            selected.forEach(cb => {
                items.push({
                    product_id: cb.dataset.productId,
                    user_id: cb.dataset.userId
                });
            });

            fetch('{{ route("admin.distributor-products.bulk-approve") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ items: items })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    }
});
</script>
@endif
@endsection
