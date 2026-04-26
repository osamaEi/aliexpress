@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'المتاجر' : 'Stores' }}</h4>
            <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'إدارة متاجر الموزعين' : 'Manage distributor stores' }}</p>
        </div>
        <a href="{{ route('admin.affiliate.stores.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i>
            {{ app()->getLocale() == 'ar' ? 'إضافة متجر' : 'Add Store' }}
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">{{ app()->getLocale() == 'ar' ? 'إجمالي المتاجر' : 'Total Stores' }}</h6>
                            <h3 class="mb-0">{{ $stores->total() }}</h3>
                        </div>
                        <i class="ri-store-2-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">{{ app()->getLocale() == 'ar' ? 'الكوبونات الفعالة' : 'Active Coupons' }}</h6>
                            <h3 class="mb-0">{{ $stores->sum('active_coupons_count') }}</h3>
                        </div>
                        <i class="ri-coupon-2-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">{{ app()->getLocale() == 'ar' ? 'العمولات المعلقة' : 'Pending Commissions' }}</h6>
                            <h3 class="mb-0">{{ number_format($stores->sum('pending_commission'), 2) }}</h3>
                        </div>
                        <i class="ri-time-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">{{ app()->getLocale() == 'ar' ? 'إجمالي الأرباح' : 'Total Earnings' }}</h6>
                            <h3 class="mb-0">{{ number_format($stores->sum('coupons_sum_total_commission_earned') ?? 0, 2) }}</h3>
                        </div>
                        <i class="ri-money-dollar-circle-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.affiliate.stores') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">{{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}</label>
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ app()->getLocale() == 'ar' ? 'اسم المتجر أو المالك...' : 'Store name or owner...' }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="country" class="form-label">{{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }}</label>
                        <select class="form-select" id="country" name="country">
                            <option value="">{{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" {{ request('country') === $country ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-search-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}
                        </button>
                        <a href="{{ route('admin.affiliate.stores') }}" class="btn btn-outline-secondary">
                            {{ app()->getLocale() == 'ar' ? 'إعادة تعيين' : 'Reset' }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stores Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'قائمة المتاجر' : 'Stores List' }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() == 'ar' ? 'المتجر' : 'Store' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'المالك' : 'Owner' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الحساب' : 'Account' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الكوبونات' : 'Coupons' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'العمولة المعلقة' : 'Pending' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'إجمالي الأرباح' : 'Total Earnings' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stores as $store)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        @if($store->avatar)
                                            <img src="{{ asset('storage/' . $store->avatar) }}" alt="{{ $store->store_name }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($store->store_name ?? $store->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $store->store_name ?? '-' }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $store->name }}</td>
                            <td>
                                @if($store->country)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="https://flagcdn.com/w40/{{ strtolower($store->country) }}.png"
                                             alt="{{ $store->country }}"
                                             style="width:28px; height:20px; object-fit:cover; border-radius:3px; border:1px solid #eee;"
                                             onerror="this.style.display='none'">
                                        <span>{{ $store->country }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(empty($store->password))
                                    <span class="badge bg-secondary">
                                        <i class="ri-store-line me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'بدون حساب' : 'No Account' }}
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="ri-user-line me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'لديه حساب' : 'Has Account' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <span class="badge bg-success" title="{{ app()->getLocale() == 'ar' ? 'فعال' : 'Active' }}">
                                        {{ $store->active_coupons_count }}
                                    </span>
                                    <span class="badge bg-danger" title="{{ app()->getLocale() == 'ar' ? 'منتهي' : 'Expired' }}">
                                        {{ $store->expired_coupons_count }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($store->pending_commission > 0)
                                    <span class="text-warning fw-bold">{{ number_format($store->pending_commission, 2) }}</span>
                                @else
                                    <span class="text-muted">0.00</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-success fw-bold">{{ number_format($store->coupons_sum_total_commission_earned ?? 0, 2) }}</span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.affiliate.stores.show', $store) }}">
                                                <i class="ri-eye-line me-2"></i>
                                                {{ app()->getLocale() == 'ar' ? 'عرض التفاصيل' : 'View Details' }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.affiliate.coupons.create') }}?store_id={{ $store->id }}">
                                                <i class="ri-coupon-2-line me-2"></i>
                                                {{ app()->getLocale() == 'ar' ? 'إضافة كوبون' : 'Add Coupon' }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ri-store-2-line" style="font-size: 3rem;"></i>
                                    <p class="mt-2">{{ app()->getLocale() == 'ar' ? 'لا توجد متاجر' : 'No stores found' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($stores->hasPages())
        <div class="card-footer">
            {{ $stores->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
