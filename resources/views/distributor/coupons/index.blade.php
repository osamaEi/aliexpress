@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'الكوبونات' : 'Coupons' }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('distributor.dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ app()->getLocale() == 'ar' ? 'الكوبونات' : 'Coupons' }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('distributor.coupons.create') }}" class="btn btn-primary">
            <i class="ri-add-line"></i> {{ app()->getLocale() == 'ar' ? 'إضافة كوبون' : 'Create Coupon' }}
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('distributor.coupons.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="{{ app()->getLocale() == 'ar' ? 'ابحث بالكود أو العنوان' : 'Search by code or title' }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">{{ app()->getLocale() == 'ar' ? 'جميع الحالات' : 'All Status' }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                            {{ app()->getLocale() == 'ar' ? 'نشط' : 'Active' }}
                        </option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>
                            {{ app()->getLocale() == 'ar' ? 'منتهي الصلاحية' : 'Expired' }}
                        </option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                            {{ app()->getLocale() == 'ar' ? 'معطل' : 'Inactive' }}
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri-search-line"></i> {{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Coupons Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ app()->getLocale() == 'ar' ? 'الكود' : 'Code' }}</th>
                        <th>{{ app()->getLocale() == 'ar' ? 'العنوان' : 'Title' }}</th>
                        <th>{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th>{{ app()->getLocale() == 'ar' ? 'الاستخدام' : 'Usage' }}</th>
                        <th>{{ app()->getLocale() == 'ar' ? 'الخصم' : 'Discount' }}</th>
                        <th>{{ app()->getLocale() == 'ar' ? 'العمولة' : 'Commission' }}</th>
                        <th>{{ app()->getLocale() == 'ar' ? 'انتهاء الصلاحية' : 'Expiry' }}</th>
                        <th>{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark" style="font-family: monospace; font-weight: bold;">
                                    {{ $coupon->code }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ app()->getLocale() == 'ar' ? $coupon->title_ar : $coupon->title_en }}</strong>
                                </div>
                            </td>
                            <td>
                                @if($coupon->is_active && $coupon->end_date >= now())
                                    <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'نشط' : 'Active' }}</span>
                                @elseif($coupon->end_date < now())
                                    <span class="badge bg-danger">{{ app()->getLocale() == 'ar' ? 'منتهي' : 'Expired' }}</span>
                                @else
                                    <span class="badge bg-warning">{{ app()->getLocale() == 'ar' ? 'معطل' : 'Inactive' }}</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $coupon->usage_count ?? 0 }}/{{ $coupon->max_uses ?? '∞' }}
                                </small>
                            </td>
                            <td>
                                @if($coupon->discount_type === 'percentage')
                                    {{ $coupon->discount_value }}%
                                @else
                                    {{ $coupon->discount_value }}
                                @endif
                            </td>
                            <td>
                                @if($coupon->commission_type === 'percentage')
                                    {{ $coupon->commission_value }}%
                                @else
                                    {{ $coupon->commission_value }}
                                @endif
                            </td>
                            <td>
                                <small>{{ $coupon->end_date->format('Y-m-d') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('distributor.coupons.activation-requests', $coupon) }}" class="btn btn-outline-primary position-relative" title="{{ app()->getLocale() == 'ar' ? 'طلبات التفعيل من المسوقين' : 'Marketer Activation Requests' }}">
                                        <i class="ri-user-follow-line"></i>
                                        @if(($pendingCounts[$coupon->id] ?? 0) > 0)
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                {{ $pendingCounts[$coupon->id] }}
                                                <span class="visually-hidden">{{ app()->getLocale() == 'ar' ? 'طلبات معلّقة' : 'pending requests' }}</span>
                                            </span>
                                        @endif
                                    </a>
                                    <a href="{{ route('distributor.coupons.show', $coupon) }}" class="btn btn-outline-info" title="{{ app()->getLocale() == 'ar' ? 'عرض' : 'View' }}">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('distributor.coupons.edit', $coupon) }}" class="btn btn-outline-warning" title="{{ app()->getLocale() == 'ar' ? 'تعديل' : 'Edit' }}">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteCoupon({{ $coupon->id }})" title="{{ app()->getLocale() == 'ar' ? 'حذف' : 'Delete' }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="mt-2">{{ app()->getLocale() == 'ar' ? 'لا توجد كوبونات' : 'No coupons found' }}</p>
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
        <div class="mt-4">
            {{ $coupons->links() }}
        </div>
    @endif
</div>

<script>
function deleteCoupon(id) {
    if (confirm('{{ app()->getLocale() == "ar" ? "هل أنت متأكد من حذف هذا الكوبون؟" : "Are you sure you want to delete this coupon?" }}')) {
        fetch(`/distributor/coupons/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(() => location.reload())
        .catch(error => console.error('Error:', error));
    }
}
</script>
@endsection
