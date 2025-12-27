@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('distributor.coupons.index') }}">{{ app()->getLocale() == 'ar' ? 'الكوبونات' : 'Coupons' }}</a></li>
                    <li class="breadcrumb-item active">{{ $coupon->code }}</li>
                </ol>
            </nav>
            <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? $coupon->title_ar : $coupon->title_en }}</h4>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('distributor.coupons.edit', $coupon) }}" class="btn btn-primary">
                <i class="ri-edit-line"></i> {{ app()->getLocale() == 'ar' ? 'تعديل' : 'Edit' }}
            </a>
            <button type="button" class="btn btn-danger" onclick="deleteCoupon()">
                <i class="ri-delete-bin-line"></i> {{ app()->getLocale() == 'ar' ? 'حذف' : 'Delete' }}
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Basic Info -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'معلومات أساسية' : 'Basic Information' }}</h5>
                    <span class="badge {{ $coupon->is_active && $coupon->end_date >= now() ? 'bg-success' : ($coupon->end_date < now() ? 'bg-danger' : 'bg-warning') }}">
                        @if($coupon->is_active && $coupon->end_date >= now())
                            {{ app()->getLocale() == 'ar' ? 'نشط' : 'Active' }}
                        @elseif($coupon->end_date < now())
                            {{ app()->getLocale() == 'ar' ? 'منتهي الصلاحية' : 'Expired' }}
                        @else
                            {{ app()->getLocale() == 'ar' ? 'معطل' : 'Inactive' }}
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>{{ app()->getLocale() == 'ar' ? 'الكود:' : 'Code:' }}</strong>
                            <p class="text-muted mb-0">
                                <span class="badge bg-light text-dark" style="font-family: monospace; font-size: 1.1rem;">
                                    {{ $coupon->code }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ app()->getLocale() == 'ar' ? 'صالح لـ:' : 'Valid For:' }}</strong>
                            <p class="text-muted mb-0">
                                @if($coupon->valid_for === 'both')
                                    {{ app()->getLocale() == 'ar' ? 'الموقع والمتجر' : 'Website & Store' }}
                                @elseif($coupon->valid_for === 'website')
                                    {{ app()->getLocale() == 'ar' ? 'الموقع فقط' : 'Website Only' }}
                                @else
                                    {{ app()->getLocale() == 'ar' ? 'المتجر فقط' : 'Store Only' }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ app()->getLocale() == 'ar' ? 'العنوان (عربي):' : 'Title (Arabic):' }}</strong>
                            <p class="text-muted mb-0">{{ $coupon->title_ar }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ app()->getLocale() == 'ar' ? 'العنوان (إنجليزي):' : 'Title (English):' }}</strong>
                            <p class="text-muted mb-0">{{ $coupon->title_en }}</p>
                        </div>
                    </div>

                    @if($coupon->description_ar || $coupon->description_en)
                        <div class="mt-3">
                            @if($coupon->description_ar)
                                <strong>{{ app()->getLocale() == 'ar' ? 'الوصف (عربي):' : 'Description (Arabic):' }}</strong>
                                <p class="text-muted mb-2">{{ $coupon->description_ar }}</p>
                            @endif
                            @if($coupon->description_en)
                                <strong>{{ app()->getLocale() == 'ar' ? 'الوصف (إنجليزي):' : 'Description (English):' }}</strong>
                                <p class="text-muted mb-0">{{ $coupon->description_en }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Discount & Commission -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'الخصم والعمولة' : 'Discount & Commission' }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>{{ app()->getLocale() == 'ar' ? 'نوع الخصم:' : 'Discount Type:' }}</strong>
                            <p class="text-muted mb-0">
                                {{ $coupon->discount_type === 'percentage' ? (app()->getLocale() == 'ar' ? 'نسبة مئوية' : 'Percentage') : (app()->getLocale() == 'ar' ? 'مبلغ ثابت' : 'Fixed Amount') }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>{{ app()->getLocale() == 'ar' ? 'قيمة الخصم:' : 'Discount Value:' }}</strong>
                            <p class="text-muted mb-0">
                                @if($coupon->discount_type === 'percentage')
                                    {{ $coupon->discount_value }}%
                                @else
                                    {{ $coupon->discount_value }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ app()->getLocale() == 'ar' ? 'نوع العمولة:' : 'Commission Type:' }}</strong>
                            <p class="text-muted mb-0">
                                {{ $coupon->commission_type === 'percentage' ? (app()->getLocale() == 'ar' ? 'نسبة مئوية' : 'Percentage') : (app()->getLocale() == 'ar' ? 'مبلغ ثابت' : 'Fixed Amount') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ app()->getLocale() == 'ar' ? 'قيمة العمولة:' : 'Commission Value:' }}</strong>
                            <p class="text-muted mb-0">
                                @if($coupon->commission_type === 'percentage')
                                    {{ $coupon->commission_value }}%
                                @else
                                    {{ $coupon->commission_value }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Limits -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'حدود الاستخدام' : 'Usage Limits' }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>{{ app()->getLocale() == 'ar' ? 'الحد الأقصى للاستخدام:' : 'Max Uses:' }}</strong>
                            <p class="text-muted mb-0">{{ $coupon->max_uses ?? (app()->getLocale() == 'ar' ? 'غير محدود' : 'Unlimited') }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>{{ app()->getLocale() == 'ar' ? 'الحد لكل مستخدم:' : 'Max Per User:' }}</strong>
                            <p class="text-muted mb-0">{{ $coupon->max_uses_per_user ?? (app()->getLocale() == 'ar' ? 'غير محدود' : 'Unlimited') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>{{ app()->getLocale() == 'ar' ? 'الحد الأدنى للطلب:' : 'Min Order Amount:' }}</strong>
                            <p class="text-muted mb-0">{{ $coupon->min_order_amount ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" disabled {{ $coupon->free_shipping ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    {{ app()->getLocale() == 'ar' ? 'شحن مجاني' : 'Free Shipping' }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" disabled {{ $coupon->exclude_discounted ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    {{ app()->getLocale() == 'ar' ? 'استبعاد المنتجات المخفضة' : 'Exclude Discounted' }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dates -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'تواريخ الصلاحية' : 'Validity Dates' }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ app()->getLocale() == 'ar' ? 'تاريخ البدء:' : 'Start Date:' }}</strong>
                            <p class="text-muted mb-0">{{ $coupon->start_date->format('Y-m-d') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ app()->getLocale() == 'ar' ? 'تاريخ الانتهاء:' : 'End Date:' }}</strong>
                            <p class="text-muted mb-0">{{ $coupon->end_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Stats -->
        <div class="col-md-4">
            <!-- Usage Stats -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">{{ app()->getLocale() == 'ar' ? 'إحصائيات الاستخدام' : 'Usage Statistics' }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong class="d-block text-muted">{{ app()->getLocale() == 'ar' ? 'عدد الاستخدامات:' : 'Total Uses:' }}</strong>
                        <h3 class="mb-0 text-primary">{{ $coupon->usage_count ?? 0 }}</h3>
                    </div>
                    <div class="mb-3">
                        <strong class="d-block text-muted">{{ app()->getLocale() == 'ar' ? 'العمولة المكتسبة:' : 'Commission Earned:' }}</strong>
                        <h3 class="mb-0 text-success">{{ $coupon->total_commission_earned ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Meta Info -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">{{ app()->getLocale() == 'ar' ? 'معلومات إضافية' : 'Additional Info' }}</h6>
                </div>
                <div class="card-body small text-muted">
                    <p class="mb-2">
                        <strong>{{ app()->getLocale() == 'ar' ? 'تم الإنشاء:' : 'Created:' }}</strong>
                        <br>{{ $coupon->created_at->format('Y-m-d H:i') }}
                    </p>
                    <p class="mb-2">
                        <strong>{{ app()->getLocale() == 'ar' ? 'آخر تحديث:' : 'Last Updated:' }}</strong>
                        <br>{{ $coupon->updated_at->format('Y-m-d H:i') }}
                    </p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="toggleStatus()">
                        <i class="ri-refresh-line"></i>
                        {{ $coupon->is_active ? (app()->getLocale() == 'ar' ? 'تعطيل' : 'Deactivate') : (app()->getLocale() == 'ar' ? 'تفعيل' : 'Activate') }}
                    </button>
                    <a href="{{ route('distributor.coupons.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="ri-arrow-left-line"></i> {{ app()->getLocale() == 'ar' ? 'العودة' : 'Back' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatus() {
    fetch(`{{ route('distributor.coupons.toggle-status', $coupon) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteCoupon() {
    if (confirm('{{ app()->getLocale() == "ar" ? "هل أنت متأكد من حذف هذا الكوبون؟" : "Are you sure you want to delete this coupon?" }}')) {
        fetch(`{{ route('distributor.coupons.destroy', $coupon) }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(() => window.location.href = '{{ route("distributor.coupons.index") }}')
        .catch(error => console.error('Error:', error));
    }
}
</script>
@endsection
