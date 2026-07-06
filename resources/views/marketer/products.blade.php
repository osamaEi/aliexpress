@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">

    <div class="mb-4">
        <h4 class="mb-1 fw-bold">{{ $ar ? 'كوبوناتي الفعّالة' : 'My Active Coupons' }}</h4>
        <p class="text-muted mb-0">{{ $ar ? 'الكوبونات التي تمت الموافقة على تفعيلها لك — روّج لها باستخدام الكود الخاص بك' : 'Coupons whose activation was approved for you — promote them using your code' }}</p>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('marketer.products') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="{{ $ar ? 'ابحث في كوبوناتي...' : 'Search my coupons...' }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary"><i class="ri-search-line me-1"></i>{{ $ar ? 'بحث' : 'Search' }}</button>
                    <a href="{{ route('marketer.products') }}" class="btn btn-outline-secondary">{{ $ar ? 'إعادة' : 'Reset' }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse($coupons as $coupon)
            @php
                $title = $ar && $coupon->title_ar ? $coupon->title_ar : $coupon->title_en;
                $img = $coupon->image
                    ? asset('storage/'.$coupon->image)
                    : (($coupon->promo_images && count((array)$coupon->promo_images)) ? asset('storage/'.((array)$coupon->promo_images)[0]) : null);
                $discount = $coupon->discount_type === 'percentage' ? $coupon->discount_value.'%' : number_format($coupon->discount_value, 0).' '.($ar ? 'د.إ' : 'AED');
                $commission = $coupon->commission_type === 'percentage' ? $coupon->commission_value.'%' : number_format($coupon->commission_value, 0).' '.($ar ? 'د.إ' : 'AED');
                $code = $coupon->pivot->tracking_code;
            @endphp
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card h-100 coupon-card">
                    @if($img)
                        <img src="{{ $img }}" class="card-img-top" style="height:150px;object-fit:cover;" alt="{{ $title }}">
                    @else
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:150px;"><i class="ri-coupon-3-line text-muted fs-2"></i></div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-label-success mb-2 align-self-start">{{ $ar ? 'فعّال' : 'Active' }}</span>
                        <h6 class="mb-2 text-truncate" title="{{ $title }}">{{ $title }}</h6>

                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">{{ $ar ? 'الخصم' : 'Discount' }}</span>
                            <span class="fw-bold text-primary">{{ $discount }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-3">
                            <span class="text-muted">{{ $ar ? 'عمولتك' : 'Commission' }}</span>
                            <span class="fw-bold text-success">{{ $commission }}</span>
                        </div>

                        <div class="mt-auto">
                            <label class="text-muted small mb-1 d-block">{{ $ar ? 'الكود الخاص بك' : 'Your code' }}</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control text-center fw-bold" style="font-family:monospace;" value="{{ $code }}" id="code-{{ $coupon->id }}" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="copyCode('code-{{ $coupon->id }}')" title="{{ $ar ? 'نسخ' : 'Copy' }}">
                                    <i class="ri-file-copy-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center py-5 text-muted">
                    <i class="ri-coupon-3-line d-block mb-2" style="font-size:3rem;"></i>
                    {{ $ar ? 'لا توجد كوبونات فعّالة بعد' : 'No active coupons yet' }} —
                    <a href="{{ route('marketer.coupons.index') }}">{{ $ar ? 'تصفّح الكوبونات واطلب التفعيل' : 'browse coupons and request activation' }}</a>
                </div></div>
            </div>
        @endforelse
    </div>

    @if($coupons->hasPages())
        <div class="mt-4">{{ $coupons->links() }}</div>
    @endif
</div>

<style>
.coupon-card { border:1px solid #f0eae6;transition:all .2s; }
.coupon-card:hover { box-shadow:0 4px 14px rgba(86,28,4,.1);transform:translateY(-2px); }
</style>

<script>
function copyCode(id) {
    const el = document.getElementById(id);
    el.select();
    el.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(el.value);
}
</script>
@endsection
