@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.affiliate.coupons.active') }}">{{ app()->getLocale() == 'ar' ? 'الكوبونات' : 'Coupons' }}</a></li>
                <li class="breadcrumb-item active">{{ $coupon->code }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <!-- Coupon Info -->
        <div class="col-md-8">
            <!-- Main Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? $coupon->title_ar : $coupon->title_en }}</h4>
                        <code class="bg-light px-3 py-1 rounded fs-5">{{ $coupon->code }}</code>
                    </div>
                    <div>
                        @if($coupon->isValid())
                            <span class="badge bg-success fs-6">
                                {{ app()->getLocale() == 'ar' ? 'فعال' : 'Active' }}
                            </span>
                        @elseif($coupon->isExpired())
                            <span class="badge bg-danger fs-6">
                                {{ app()->getLocale() == 'ar' ? 'منتهي' : 'Expired' }}
                            </span>
                        @else
                            <span class="badge bg-secondary fs-6">
                                {{ app()->getLocale() == 'ar' ? 'غير فعال' : 'Inactive' }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Description -->
                    @if($coupon->description_ar || $coupon->description_en)
                    <p class="text-muted mb-4">
                        {{ app()->getLocale() == 'ar' ? $coupon->description_ar : $coupon->description_en }}
                    </p>
                    @endif

                    <!-- Details Grid -->
                    <div class="row g-4">
                        <!-- Discount -->
                        <div class="col-md-6">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h6 class="text-danger mb-2">{{ app()->getLocale() == 'ar' ? 'الخصم' : 'Discount' }}</h6>
                                <h3 class="mb-0">
                                    @if($coupon->discount_type === 'percentage')
                                        {{ $coupon->discount_value }}%
                                    @else
                                        {{ number_format($coupon->discount_value, 2) }}
                                    @endif
                                </h3>
                                <small class="text-muted">
                                    {{ $coupon->discount_type === 'percentage'
                                        ? (app()->getLocale() == 'ar' ? 'نسبة مئوية' : 'Percentage')
                                        : (app()->getLocale() == 'ar' ? 'مبلغ ثابت' : 'Fixed Amount')
                                    }}
                                </small>
                            </div>
                        </div>

                        <!-- Commission -->
                        <div class="col-md-6">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h6 class="text-success mb-2">{{ app()->getLocale() == 'ar' ? 'العمولة' : 'Commission' }}</h6>
                                <h3 class="mb-0">
                                    @if($coupon->commission_type === 'percentage')
                                        {{ $coupon->commission_value }}%
                                    @else
                                        {{ number_format($coupon->commission_value, 2) }}
                                    @endif
                                </h3>
                                <small class="text-muted">
                                    {{ $coupon->commission_type === 'percentage'
                                        ? (app()->getLocale() == 'ar' ? 'نسبة مئوية' : 'Percentage')
                                        : (app()->getLocale() == 'ar' ? 'مبلغ ثابت' : 'Fixed Amount')
                                    }}
                                </small>
                            </div>
                        </div>

                        <!-- Valid For -->
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h6 class="text-muted mb-2">{{ app()->getLocale() == 'ar' ? 'صالح لـ' : 'Valid For' }}</h6>
                                @if($coupon->valid_for === 'website')
                                    <span class="badge bg-primary">{{ app()->getLocale() == 'ar' ? 'الموقع فقط' : 'Website Only' }}</span>
                                @elseif($coupon->valid_for === 'store')
                                    <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? 'المتجر فقط' : 'Store Only' }}</span>
                                @else
                                    <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'الكل' : 'Both' }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Usage -->
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h6 class="text-muted mb-2">{{ app()->getLocale() == 'ar' ? 'الاستخدام' : 'Usage' }}</h6>
                                <strong>{{ $coupon->used_count }}</strong>
                                @if($coupon->max_uses)
                                    <span class="text-muted">/ {{ $coupon->max_uses }}</span>
                                @else
                                    <span class="text-muted">/ {{ app()->getLocale() == 'ar' ? 'غير محدود' : 'Unlimited' }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Validity Period -->
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <h6 class="text-muted mb-2">{{ app()->getLocale() == 'ar' ? 'الفترة' : 'Period' }}</h6>
                                <small>
                                    {{ $coupon->start_date->format('Y-m-d') }}
                                    <br>
                                    {{ $coupon->end_date->format('Y-m-d') }}
                                </small>
                                @if($coupon->isValid())
                                    <div class="mt-1">
                                        <span class="badge bg-warning text-dark">
                                            {{ $coupon->remaining_days }} {{ app()->getLocale() == 'ar' ? 'يوم متبقي' : 'days left' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">{{ app()->getLocale() == 'ar' ? 'خيارات إضافية' : 'Additional Options' }}</h6>
                        <div class="d-flex gap-3 flex-wrap">
                            @if($coupon->free_shipping)
                                <span class="badge bg-info">
                                    <i class="ri-truck-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'شحن مجاني' : 'Free Shipping' }}
                                </span>
                            @endif
                            @if($coupon->exclude_discounted)
                                <span class="badge bg-warning text-dark">
                                    <i class="ri-price-tag-3-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'يستبعد المخفض' : 'Excludes Discounted' }}
                                </span>
                            @endif
                            @if($coupon->min_order_amount)
                                <span class="badge bg-secondary">
                                    <i class="ri-shopping-cart-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'الحد الأدنى:' : 'Min:' }} {{ number_format($coupon->min_order_amount, 2) }}
                                </span>
                            @endif
                            @if($coupon->max_uses_per_user)
                                <span class="badge bg-secondary">
                                    <i class="ri-user-line me-1"></i>
                                    {{ $coupon->max_uses_per_user }} {{ app()->getLocale() == 'ar' ? 'لكل مستخدم' : 'per user' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Features -->
                    @if($coupon->features && count($coupon->features) > 0)
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">{{ app()->getLocale() == 'ar' ? 'المميزات' : 'Features' }}</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($coupon->features as $feature)
                                @if($feature)
                                <li class="mb-1">
                                    <i class="ri-check-line text-success me-2"></i>
                                    {{ $feature }}
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Terms -->
                    @if($coupon->terms && count($coupon->terms) > 0)
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">{{ app()->getLocale() == 'ar' ? 'شروط الاستخدام' : 'Usage Terms' }}</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($coupon->terms as $term)
                                @if($term)
                                <li class="mb-1">
                                    <i class="ri-information-line text-info me-2"></i>
                                    {{ $term }}
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Commission Terms -->
                    @if($coupon->commission_terms && count($coupon->commission_terms) > 0)
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">{{ app()->getLocale() == 'ar' ? 'شروط العمولة' : 'Commission Terms' }}</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($coupon->commission_terms as $term)
                                @if($term)
                                <li class="mb-1">
                                    <i class="ri-money-dollar-circle-line text-success me-2"></i>
                                    {{ $term }}
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Promo Images -->
                    @if($coupon->promo_images && count($coupon->promo_images) > 0)
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">{{ app()->getLocale() == 'ar' ? 'الصور الترويجية' : 'Promo Images' }}</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($coupon->promo_images as $image)
                                <a href="{{ asset('storage/' . $image) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Promo" class="rounded" style="width: 120px; height: 120px; object-fit: cover;">
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.affiliate.coupons.edit', $coupon) }}" class="btn btn-primary">
                        <i class="ri-edit-line me-1"></i>
                        {{ app()->getLocale() == 'ar' ? 'تعديل' : 'Edit' }}
                    </a>
                    <form action="{{ route('admin.affiliate.coupons.destroy', $coupon) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد؟' : 'Are you sure?' }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="ri-delete-bin-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'حذف' : 'Delete' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Usage History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'سجل الاستخدام' : 'Usage History' }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ app()->getLocale() == 'ar' ? 'المستخدم' : 'User' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'قيمة الطلب' : 'Order Amount' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'الخصم' : 'Discount' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'العمولة' : 'Commission' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                                    <th>{{ app()->getLocale() == 'ar' ? 'التاريخ' : 'Date' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coupon->usages as $usage)
                                <tr>
                                    <td>{{ $usage->user->name ?? 'Guest' }}</td>
                                    <td>{{ number_format($usage->order_amount, 2) }}</td>
                                    <td class="text-danger">-{{ number_format($usage->discount_amount, 2) }}</td>
                                    <td class="text-success">+{{ number_format($usage->commission_amount, 2) }}</td>
                                    <td>
                                        @if($usage->commission_status === 'pending')
                                            <span class="badge bg-warning text-dark">{{ app()->getLocale() == 'ar' ? 'معلق' : 'Pending' }}</span>
                                        @elseif($usage->commission_status === 'approved')
                                            <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? 'موافق عليه' : 'Approved' }}</span>
                                        @elseif($usage->commission_status === 'paid')
                                            <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'مدفوع' : 'Paid' }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ app()->getLocale() == 'ar' ? 'ملغي' : 'Cancelled' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $usage->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        {{ app()->getLocale() == 'ar' ? 'لا يوجد سجل استخدام' : 'No usage history' }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Store Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'المتجر' : 'Store' }}</h5>
                </div>
                <div class="card-body text-center">
                    @if($coupon->store)
                        @if($coupon->store->avatar)
                            <img src="{{ asset('storage/' . $coupon->store->avatar) }}" alt="{{ $coupon->store->store_name }}" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="avatar avatar-lg mx-auto mb-3">
                                <span class="avatar-initial rounded-circle bg-label-primary" style="width: 80px; height: 80px; font-size: 2rem; display: flex; align-items: center; justify-content: center;">
                                    {{ strtoupper(substr($coupon->store->store_name ?? $coupon->store->name, 0, 2)) }}
                                </span>
                            </div>
                        @endif
                        <h5 class="mb-1">{{ $coupon->store->store_name ?? '-' }}</h5>
                        <p class="text-muted mb-0">{{ $coupon->store->name }}</p>
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
                        <p class="mb-0">{{ $countryFlags[$coupon->store->country] ?? '' }} {{ $coupon->store->country }}</p>
                        <a href="{{ route('admin.affiliate.stores.show', $coupon->store) }}" class="btn btn-sm btn-outline-primary mt-3">
                            {{ app()->getLocale() == 'ar' ? 'عرض المتجر' : 'View Store' }}
                        </a>
                    @else
                        <p class="text-muted mb-0">{{ app()->getLocale() == 'ar' ? 'غير محدد' : 'Not assigned' }}</p>
                    @endif
                </div>
            </div>

            <!-- Stats Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الإحصائيات' : 'Statistics' }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>{{ app()->getLocale() == 'ar' ? 'عدد الاستخدام' : 'Total Uses' }}</span>
                            <strong>{{ $stats['total_uses'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>{{ app()->getLocale() == 'ar' ? 'إجمالي الخصومات' : 'Total Discounts' }}</span>
                            <strong class="text-danger">{{ number_format($stats['total_discount'], 2) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>{{ app()->getLocale() == 'ar' ? 'إجمالي العمولات' : 'Total Commissions' }}</span>
                            <strong class="text-success">{{ number_format($stats['total_commission'], 2) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>{{ app()->getLocale() == 'ar' ? 'العمولات المعلقة' : 'Pending Commissions' }}</span>
                            <strong class="text-warning">{{ number_format($stats['pending_commission'], 2) }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Created By -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'معلومات الإنشاء' : 'Creation Info' }}</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>{{ app()->getLocale() == 'ar' ? 'أنشئ بواسطة:' : 'Created by:' }}</strong>
                        {{ $coupon->creator->name ?? 'N/A' }}
                    </p>
                    <p class="mb-0">
                        <strong>{{ app()->getLocale() == 'ar' ? 'تاريخ الإنشاء:' : 'Created at:' }}</strong>
                        {{ $coupon->created_at->format('Y-m-d H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
