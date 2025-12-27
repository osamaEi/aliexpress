@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.affiliate.stores') }}">{{ app()->getLocale() == 'ar' ? 'المتاجر' : 'Stores' }}</a></li>
                <li class="breadcrumb-item active">{{ $store->store_name ?? $store->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <!-- Store Info Card -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    @if($store->avatar)
                        <img src="{{ asset('storage/' . $store->avatar) }}" alt="{{ $store->store_name }}" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-primary" style="width: 120px; height: 120px; font-size: 3rem; display: flex; align-items: center; justify-content: center;">
                                {{ strtoupper(substr($store->store_name ?? $store->name, 0, 2)) }}
                            </span>
                        </div>
                    @endif
                    <h4 class="mb-1">{{ $store->store_name ?? '-' }}</h4>
                    <p class="text-muted mb-2">{{ $store->name }}</p>
                    @php
                        $countryFlags = [
                            'AE' => '🇦🇪',
                            'SA' => '🇸🇦',
                            'KW' => '🇰🇼',
                            'QA' => '🇶🇦',
                            'BH' => '🇧🇭',
                            'OM' => '🇴🇲',
                            'EG' => '🇪🇬',
                            'JO' => '🇯🇴',
                            'LB' => '🇱🇧',
                        ];
                    @endphp
                    <p class="mb-3">{{ $countryFlags[$store->country] ?? '' }} {{ $store->country }}</p>

                    @if(empty($store->password))
                        <span class="badge bg-secondary mb-3">
                            <i class="ri-store-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'بدون حساب' : 'No Account' }}
                        </span>
                    @else
                        <span class="badge bg-success mb-3">
                            <i class="ri-user-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'لديه حساب' : 'Has Account' }}
                        </span>
                    @endif

                    @if($store->phone)
                        <p class="text-muted mb-0">
                            <i class="ri-phone-line me-1"></i>
                            {{ $store->phone }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Stats Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الإحصائيات' : 'Statistics' }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ app()->getLocale() == 'ar' ? 'إجمالي الكوبونات' : 'Total Coupons' }}</span>
                            <strong>{{ $stats['total_coupons'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ app()->getLocale() == 'ar' ? 'الكوبونات الفعالة' : 'Active Coupons' }}</span>
                            <strong class="text-success">{{ $stats['active_coupons'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ app()->getLocale() == 'ar' ? 'الكوبونات المنتهية' : 'Expired Coupons' }}</span>
                            <strong class="text-danger">{{ $stats['expired_coupons'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ app()->getLocale() == 'ar' ? 'العمولة المعلقة' : 'Pending Commission' }}</span>
                            <strong class="text-warning">{{ number_format($stats['pending_commission'], 2) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ app()->getLocale() == 'ar' ? 'إجمالي الأرباح' : 'Total Earnings' }}</span>
                            <strong class="text-success">{{ number_format($stats['total_earnings'], 2) }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Coupons List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'كوبونات المتجر' : 'Store Coupons' }}</h5>
                    <a href="{{ route('admin.affiliate.coupons.create') }}?store_id={{ $store->id }}" class="btn btn-primary btn-sm">
                        <i class="ri-add-line me-1"></i>
                        {{ app()->getLocale() == 'ar' ? 'إضافة كوبون' : 'Add Coupon' }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ app()->getLocale() == 'ar' ? 'الكوبون' : 'Coupon' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'الكود' : 'Code' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'الخصم' : 'Discount' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'العمولة' : 'Commission' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($store->coupons as $coupon)
                                <tr>
                                    <td>
                                        <strong>{{ app()->getLocale() == 'ar' ? $coupon->title_ar : $coupon->title_en }}</strong>
                                    </td>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded">{{ $coupon->code }}</code>
                                    </td>
                                    <td>
                                        @if($coupon->discount_type === 'percentage')
                                            {{ $coupon->discount_value }}%
                                        @else
                                            {{ number_format($coupon->discount_value, 2) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($coupon->commission_type === 'percentage')
                                            {{ $coupon->commission_value }}%
                                        @else
                                            {{ number_format($coupon->commission_value, 2) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($coupon->isValid())
                                            <span class="badge bg-success">
                                                {{ app()->getLocale() == 'ar' ? 'فعال' : 'Active' }}
                                            </span>
                                            <small class="d-block text-muted">
                                                {{ $coupon->remaining_days }} {{ app()->getLocale() == 'ar' ? 'يوم متبقي' : 'days left' }}
                                            </small>
                                        @elseif($coupon->isExpired())
                                            <span class="badge bg-danger">
                                                {{ app()->getLocale() == 'ar' ? 'منتهي' : 'Expired' }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                {{ app()->getLocale() == 'ar' ? 'غير فعال' : 'Inactive' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.affiliate.coupons.show', $coupon) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <a href="{{ route('admin.affiliate.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-coupon-2-line" style="font-size: 3rem;"></i>
                                            <p class="mt-2">{{ app()->getLocale() == 'ar' ? 'لا توجد كوبونات' : 'No coupons found' }}</p>
                                            <a href="{{ route('admin.affiliate.coupons.create') }}?store_id={{ $store->id }}" class="btn btn-primary btn-sm">
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
            </div>
        </div>
    </div>
</div>
@endsection
