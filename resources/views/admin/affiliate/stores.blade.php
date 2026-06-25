@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">

    {{-- Page Header --}}
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-3">
            <span class="gs-header-icon"><i class="ri-store-3-line"></i></span>
            <div>
                <h4 class="mb-0 fw-bold">{{ $ar ? 'المتاجر العالمية' : 'Global Stores' }}</h4>
                <span class="text-muted small">{{ $ar ? 'متاجر يديرها الأدمن لكوبونات المسوّقين' : 'Admin-managed stores for marketer coupons' }}</span>
            </div>
        </div>
        <a href="{{ route('admin.affiliate.stores.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i>{{ $ar ? 'إضافة متجر عالمي' : 'Add Global Store' }}
        </a>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        @php
            $statCards = [
                ['label' => $ar ? 'إجمالي المتاجر' : 'Total Stores', 'value' => number_format($stores->total()), 'icon' => 'ri-store-2-line', 'color' => 'primary'],
                ['label' => $ar ? 'الكوبونات الفعالة' : 'Active Coupons', 'value' => number_format($stores->sum('active_coupons_count')), 'icon' => 'ri-coupon-2-line', 'color' => 'success'],
                ['label' => $ar ? 'العمولات المعلقة' : 'Pending Commissions', 'value' => number_format($stores->sum('pending_commission'), 2), 'icon' => 'ri-time-line', 'color' => 'warning'],
                ['label' => $ar ? 'إجمالي الأرباح' : 'Total Earnings', 'value' => number_format($stores->sum('coupons_sum_total_commission_earned') ?? 0, 2), 'icon' => 'ri-money-dollar-circle-line', 'color' => 'info'],
            ];
        @endphp
        @foreach($statCards as $c)
            <div class="col-6 col-md-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <span class="stat-icon bg-label-{{ $c['color'] }}"><i class="{{ $c['icon'] }}"></i></span>
                        <div>
                            <h5 class="mb-0">{{ $c['value'] }}</h5>
                            <small class="text-muted">{{ $c['label'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.affiliate.stores') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small mb-1">{{ $ar ? 'بحث' : 'Search' }}</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="{{ $ar ? 'اسم المتجر أو المالك...' : 'Store name or owner...' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">{{ $ar ? 'الدولة' : 'Country' }}</label>
                    <select class="form-select" name="country">
                        <option value="">{{ $ar ? 'الكل' : 'All' }}</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" {{ request('country') === $country ? 'selected' : '' }}>{{ strtoupper($country) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ri-filter-3-line me-1"></i>{{ $ar ? 'تطبيق' : 'Apply' }}</button>
                    <a href="{{ route('admin.affiliate.stores') }}" class="btn btn-outline-secondary"><i class="ri-refresh-line me-1"></i>{{ $ar ? 'إعادة تعيين' : 'Reset' }}</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Stores grid --}}
    <div class="row g-3">
        @forelse($stores as $store)
            <div class="col-md-6 col-xl-4">
                <div class="card store-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            @if($store->avatar)
                                <img src="{{ asset('storage/' . $store->avatar) }}" alt="{{ $store->store_name }}" class="store-avatar">
                            @else
                                <span class="store-avatar store-avatar-initial">{{ strtoupper(substr($store->store_name ?? $store->name, 0, 2)) }}</span>
                            @endif
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="mb-0 fw-semibold text-truncate">{{ $store->store_name ?? '—' }}</h6>
                                <small class="text-muted d-block text-truncate">{{ $store->name }}</small>
                            </div>
                            @if($store->country)
                                <img src="https://flagcdn.com/w40/{{ strtolower($store->country) }}.png"
                                     alt="{{ $store->country }}" title="{{ $store->country }}"
                                     style="width:28px;height:20px;object-fit:cover;border-radius:3px;border:1px solid #eee;flex-shrink:0;"
                                     onerror="this.style.display='none'">
                            @endif
                        </div>

                        <div class="row text-center g-2 mb-3">
                            <div class="col-4">
                                <div class="store-stat">
                                    <span class="badge bg-label-success w-100">{{ $store->active_coupons_count }}</span>
                                    <small class="text-muted d-block mt-1">{{ $ar ? 'فعّال' : 'Active' }}</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="store-stat">
                                    <span class="badge bg-label-danger w-100">{{ $store->expired_coupons_count }}</span>
                                    <small class="text-muted d-block mt-1">{{ $ar ? 'منتهي' : 'Expired' }}</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="store-stat">
                                    <span class="badge bg-label-warning w-100">{{ number_format($store->pending_commission, 0) }}</span>
                                    <small class="text-muted d-block mt-1">{{ $ar ? 'معلّق' : 'Pending' }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="small text-muted">{{ $ar ? 'إجمالي الأرباح' : 'Earnings' }}</span>
                            <strong class="text-success">{{ number_format($store->coupons_sum_total_commission_earned ?? 0, 2) }}</strong>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2 bg-transparent">
                        <a href="{{ route('admin.affiliate.stores.show', $store) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                            <i class="ri-eye-line me-1"></i>{{ $ar ? 'عرض' : 'View' }}
                        </a>
                        <a href="{{ route('admin.affiliate.coupons.create') }}?store_id={{ $store->id }}" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="ri-coupon-2-line me-1"></i>{{ $ar ? 'إضافة كوبون' : 'Add Coupon' }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="ri-store-2-line d-block mb-2" style="font-size:3rem;"></i>
                        <p class="mb-3">{{ $ar ? 'لا توجد متاجر عالمية بعد' : 'No global stores yet' }}</p>
                        <a href="{{ route('admin.affiliate.stores.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>{{ $ar ? 'إضافة متجر عالمي' : 'Add Global Store' }}
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($stores->hasPages())
        <div class="mt-4">{{ $stores->links() }}</div>
    @endif
</div>

<style>
.gs-header-icon {
    width: 46px; height: 46px; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 12px; background: linear-gradient(135deg, #561C04 0%, #7A3206 100%);
    color: #fff; font-size: 22px; box-shadow: 0 4px 12px rgba(86,28,4,.25);
}
.stat-card { border: 1px solid #f0eae6; transition: all .2s; }
.stat-card:hover { box-shadow: 0 4px 14px rgba(86,28,4,.1); transform: translateY(-2px); }
.stat-card .stat-icon {
    width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center; font-size: 22px;
}
.store-card { border: 1px solid #f0eae6; transition: all .2s; }
.store-card:hover { box-shadow: 0 6px 18px rgba(86,28,4,.12); transform: translateY(-3px); }
.store-avatar { width: 48px; height: 48px; border-radius: 12px; object-fit: cover; flex-shrink: 0; }
.store-avatar-initial {
    display: inline-flex; align-items: center; justify-content: center;
    background: #f5e6d3; color: #561C04; font-weight: 700; font-size: 16px;
}
.min-w-0 { min-width: 0; }
.store-stat .badge { padding: 6px 0; font-size: 14px; }
</style>
@endsection
