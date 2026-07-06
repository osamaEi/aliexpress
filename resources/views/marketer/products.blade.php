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
                $discount = $coupon->discount_type === 'percentage' ? $coupon->discount_value.'%' : number_format($coupon->discount_value, 0).' '.($ar ? 'ر.س' : 'SAR');
                $commission = $coupon->commission_type === 'percentage' ? $coupon->commission_value.'%' : number_format($coupon->commission_value, 0).' '.($ar ? 'ر.س' : 'SAR');
                $code = $coupon->pivot->tracking_code;
                $daysLeft = max(0, (int) ceil(now()->diffInDays(\Carbon\Carbon::parse($coupon->end_date), false)));
            @endphp
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card h-100 coupon-card">
                    {{-- Expiry badge --}}
                    <div class="coupon-expiry">
                        <i class="ri-time-line me-1"></i>{{ $ar ? "ينتهي بعد {$daysLeft} يوم" : "Ends in {$daysLeft} days" }}
                    </div>

                    @if($img)
                        <img src="{{ $img }}" class="coupon-img" alt="{{ $title }}">
                    @else
                        <div class="coupon-img coupon-img-placeholder"><i class="ri-coupon-3-line"></i></div>
                    @endif

                    <div class="card-body p-0 d-flex flex-column">
                        <div class="text-center fw-bold px-2 pt-2 pb-1 coupon-title" title="{{ $title }}">{{ $title }}</div>

                        {{-- 3-column stat row: discount | commission | free shipping --}}
                        <div class="coupon-stats">
                            <div class="coupon-stat">
                                <div class="coupon-stat-label">{{ $ar ? 'الخصم' : 'Discount' }}</div>
                                <div class="coupon-stat-value text-success">{{ $discount }}</div>
                            </div>
                            <div class="coupon-stat">
                                <div class="coupon-stat-label">{{ $ar ? 'العمولة' : 'Commission' }}</div>
                                <div class="coupon-stat-value text-success">{{ $commission }}</div>
                            </div>
                            <div class="coupon-stat">
                                <div class="coupon-stat-label">{{ $ar ? 'شحن مجاني' : 'Free Shipping' }}</div>
                                <div class="coupon-stat-value {{ $coupon->free_shipping ? 'text-success' : 'text-muted' }}">
                                    {{ $coupon->free_shipping ? ($ar ? 'نعم' : 'Yes') : ($ar ? 'لا' : 'No') }}
                                </div>
                            </div>
                        </div>

                        {{-- Marketer code + copy --}}
                        <div class="px-2 pb-2 pt-1 mt-auto">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control text-center fw-bold" style="font-family:monospace;" value="{{ $code }}" id="code-{{ $coupon->id }}" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="copyCode('code-{{ $coupon->id }}')" title="{{ $ar ? 'نسخ الكود' : 'Copy code' }}">
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
.coupon-card { border:1px solid #f0eae6;transition:all .2s;overflow:hidden; }
.coupon-card:hover { box-shadow:0 6px 18px rgba(86,28,4,.12);transform:translateY(-3px); }
.coupon-expiry { background:#e9f7ef;color:#0f9d58;font-size:12px;font-weight:600;text-align:center;padding:5px 8px;border-bottom:1px solid #f0eae6; }
.coupon-img { width:100%;height:150px;object-fit:contain;background:#fff;padding:6px; }
.coupon-img-placeholder { display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#f5e6d3,#e8d5bd);color:#561C04;font-size:48px;padding:0; }
.coupon-title { font-size:14px;color:#333;line-height:1.4;min-height:40px; }
.coupon-stats { display:flex;border-top:1px solid #f0eae6; }
.coupon-stat { flex:1;text-align:center;padding:8px 4px;border-inline-start:1px solid #f0eae6; }
.coupon-stat:first-child { border-inline-start:0; }
.coupon-stat-label { font-size:12px;color:#8a8a8a;margin-bottom:2px; }
.coupon-stat-value { font-size:13px;font-weight:700; }
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
