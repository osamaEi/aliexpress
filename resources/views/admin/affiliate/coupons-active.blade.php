@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'الكوبونات الفعالة' : 'Active Coupons' }}</h4>
            <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'جميع الكوبونات الفعالة حالياً' : 'All currently active coupons' }}</p>
        </div>
        <a href="{{ route('admin.affiliate.coupons.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i>
            {{ app()->getLocale() == 'ar' ? 'إضافة كوبون' : 'Add Coupon' }}
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.affiliate.coupons.active') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">{{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}</label>
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ app()->getLocale() == 'ar' ? 'كود الكوبون أو العنوان...' : 'Coupon code or title...' }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="store_id" class="form-label">{{ app()->getLocale() == 'ar' ? 'المتجر' : 'Store' }}</label>
                        <select class="form-select" id="store_id" name="store_id">
                            <option value="">{{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->store_name ?? $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-search-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}
                        </button>
                        <a href="{{ route('admin.affiliate.coupons.active') }}" class="btn btn-outline-secondary">
                            {{ app()->getLocale() == 'ar' ? 'إعادة تعيين' : 'Reset' }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Coupons Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'قائمة الكوبونات' : 'Coupons List' }} ({{ $coupons->total() }})</h5>
            <div class="btn-group">
                <a href="{{ route('admin.affiliate.coupons.active') }}" class="btn btn-sm btn-success">
                    {{ app()->getLocale() == 'ar' ? 'الفعالة' : 'Active' }}
                </a>
                <a href="{{ route('admin.affiliate.coupons.expired') }}" class="btn btn-sm btn-outline-danger">
                    {{ app()->getLocale() == 'ar' ? 'المنتهية' : 'Expired' }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() == 'ar' ? 'الكوبون' : 'Coupon' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'المتجر' : 'Store' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الكود' : 'Code' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'النوع' : 'Type' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الخصم' : 'Discount' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'العمولة' : 'Commission' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الاستخدام' : 'Usage' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الأرباح' : 'Earnings' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'المتبقي' : 'Remaining' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                        <tr>
                            <td>
                                <strong>{{ app()->getLocale() == 'ar' ? $coupon->title_ar : $coupon->title_en }}</strong>
                            </td>
                            <td>
                                @if($coupon->store)
                                    <div class="d-flex align-items-center">
                                        @if($coupon->store->avatar)
                                            <img src="{{ asset('storage/' . $coupon->store->avatar) }}" class="rounded-circle me-2" style="width: 25px; height: 25px; object-fit: cover;">
                                        @endif
                                        <small>{{ $coupon->store->store_name ?? $coupon->store->name }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $coupon->code }}</code>
                            </td>
                            <td>
                                @if($coupon->valid_for === 'website')
                                    <span class="badge bg-primary">{{ app()->getLocale() == 'ar' ? 'الموقع' : 'Website' }}</span>
                                @elseif($coupon->valid_for === 'store')
                                    <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? 'المتجر' : 'Store' }}</span>
                                @else
                                    <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'الكل' : 'Both' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-danger">
                                    @if($coupon->discount_type === 'percentage')
                                        {{ $coupon->discount_value }}%
                                    @else
                                        {{ number_format($coupon->discount_value, 2) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-success">
                                    @if($coupon->commission_type === 'percentage')
                                        {{ $coupon->commission_value }}%
                                    @else
                                        {{ number_format($coupon->commission_value, 2) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $coupon->used_count }}{{ $coupon->max_uses ? '/' . $coupon->max_uses : '' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-success fw-bold">{{ number_format($coupon->total_commission_earned, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    {{ $coupon->remaining_days }} {{ app()->getLocale() == 'ar' ? 'يوم' : 'days' }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.affiliate.coupons.show', $coupon) }}">
                                                <i class="ri-eye-line me-2"></i>
                                                {{ app()->getLocale() == 'ar' ? 'عرض' : 'View' }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.affiliate.coupons.edit', $coupon) }}">
                                                <i class="ri-edit-line me-2"></i>
                                                {{ app()->getLocale() == 'ar' ? 'تعديل' : 'Edit' }}
                                            </a>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" onclick="toggleStatus({{ $coupon->id }})">
                                                <i class="ri-toggle-line me-2"></i>
                                                {{ app()->getLocale() == 'ar' ? 'تبديل الحالة' : 'Toggle Status' }}
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.affiliate.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد من الحذف؟' : 'Are you sure you want to delete?' }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="ri-delete-bin-line me-2"></i>
                                                    {{ app()->getLocale() == 'ar' ? 'حذف' : 'Delete' }}
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ri-coupon-2-line" style="font-size: 3rem;"></i>
                                    <p class="mt-2">{{ app()->getLocale() == 'ar' ? 'لا توجد كوبونات فعالة' : 'No active coupons found' }}</p>
                                    <a href="{{ route('admin.affiliate.coupons.create') }}" class="btn btn-primary btn-sm">
                                        {{ app()->getLocale() == 'ar' ? 'إضافة كوبون' : 'Add Coupon' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($coupons->hasPages())
        <div class="card-footer">
            {{ $coupons->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function toggleStatus(couponId) {
    fetch(`/admin/affiliate/coupons/${couponId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
@endpush
@endsection
