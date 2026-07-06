@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">

    <div class="mb-4">
        <h4 class="mb-1 fw-bold">{{ $ar ? 'الكوبونات المتاحة' : 'Available Coupons' }}</h4>
        <p class="text-muted mb-0">{{ $ar ? 'اطلب تفعيل أي كوبون لتروّج له وتربح عمولة عند الاستخدام' : 'Request activation of any coupon to promote it and earn commission on use' }}</p>
    </div>

    {{-- Type filter tabs --}}
    @php $type = request('type'); @endphp
    <ul class="nav nav-pills mb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link {{ !$type ? 'active' : '' }}" href="{{ route('marketer.coupons.index', array_filter(['search' => request('search')])) }}">
                <i class="ri-apps-2-line me-1"></i>{{ $ar ? 'الكل' : 'All' }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'store' ? 'active' : '' }}" href="{{ route('marketer.coupons.index', array_filter(['type' => 'store', 'search' => request('search')])) }}">
                <i class="ri-store-2-line me-1"></i>{{ $ar ? 'كوبونات المتاجر' : 'Store Coupons' }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'global' ? 'active' : '' }}" href="{{ route('marketer.coupons.index', array_filter(['type' => 'global', 'search' => request('search')])) }}">
                <i class="ri-global-line me-1"></i>{{ $ar ? 'المتاجر العالمية' : 'Global Stores' }}
            </a>
        </li>
    </ul>

    {{-- Search --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('marketer.coupons.index') }}" class="row g-2 align-items-end">
                @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="{{ $ar ? 'ابحث عن كوبون...' : 'Search coupons...' }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary"><i class="ri-search-line me-1"></i>{{ $ar ? 'بحث' : 'Search' }}</button>
                    <a href="{{ route('marketer.coupons.index', array_filter(['type' => $type])) }}" class="btn btn-outline-secondary">{{ $ar ? 'إعادة' : 'Reset' }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse($coupons as $coupon)
            @php
                $title = $ar && $coupon->title_ar ? $coupon->title_ar : $coupon->title_en;
                $img = ($coupon->promo_images && count((array)$coupon->promo_images)) ? ((array)$coupon->promo_images)[0] : null;
                $activation = $myActivations[$coupon->id] ?? null;
                $status = $activation->status ?? null;
                $trackingCode = $activation->tracking_code ?? null;
                $cur = currency_symbol();
                $discount = $coupon->discount_type === 'percentage' ? $coupon->discount_value.'%' : number_format($coupon->discount_value, 0).' '.$cur;
                $commission = $coupon->commission_type === 'percentage' ? $coupon->commission_value.'%' : number_format($coupon->commission_value, 0).' '.$cur;
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card coupon-card h-100">
                    @if($img)
                        <img src="{{ asset('storage/' . $img) }}" class="coupon-img" alt="{{ $title }}">
                    @else
                        <div class="coupon-img coupon-img-placeholder"><i class="ri-coupon-3-line"></i></div>
                    @endif
                    <div class="card-body">
                        <div class="d-flex gap-2 mb-2 flex-wrap">
                            @if($coupon->is_global)
                                <span class="badge bg-label-info"><i class="ri-global-line me-1"></i>{{ $ar ? 'متجر عالمي' : 'Global Store' }}</span>
                            @else
                                <span class="badge bg-label-secondary"><i class="ri-store-2-line me-1"></i>{{ $ar ? 'متجر' : 'Store' }}</span>
                            @endif
                            @if($coupon->coupon_method === 'link')
                                <span class="badge bg-label-primary"><i class="ri-links-line me-1"></i>{{ $ar ? 'كوبون بلينك' : 'Link Coupon' }}</span>
                            @endif
                        </div>
                        <h6 class="fw-semibold mb-2 text-truncate">{{ $title }}</h6>
                        <div class="d-flex gap-2 mb-3 flex-wrap">
                            <span class="badge bg-label-primary"><i class="ri-price-tag-3-line me-1"></i>{{ $ar ? 'خصم' : 'Discount' }} {{ $discount }}</span>
                            <span class="badge bg-label-success"><i class="ri-hand-coin-line me-1"></i>{{ $ar ? 'عمولتك' : 'Commission' }} {{ $commission }}</span>
                        </div>
                        <div class="small text-muted mb-3">
                            <i class="ri-calendar-line me-1"></i>{{ $ar ? 'ينتهي' : 'Ends' }} {{ \Carbon\Carbon::parse($coupon->end_date)->format('Y-m-d') }}
                        </div>

                        @if($coupon->coupon_method === 'link')
                            {{-- Link coupons need no activation: the marketer uses the direct link straight away --}}
                            <div class="tracking-box">
                                <small class="text-muted d-block mb-1"><i class="ri-links-line me-1"></i>{{ $ar ? 'الرابط المباشر (لا يتطلب تفعيل)' : 'Direct link (no activation needed)' }}</small>
                                @if($coupon->direct_link)
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ $coupon->direct_link }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary flex-grow-1">
                                            <i class="ri-external-link-line me-1"></i>{{ $ar ? 'فتح الرابط' : 'Open Link' }}
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyCode('{{ $coupon->direct_link }}', this)"><i class="ri-file-copy-line"></i></button>
                                    </div>
                                @else
                                    <span class="text-muted small">{{ $ar ? 'الرابط غير متوفر' : 'Link not available' }}</span>
                                @endif
                            </div>
                        @elseif($status === 'active')
                            <div class="tracking-box">
                                <small class="text-muted d-block mb-1"><i class="ri-links-line me-1"></i>{{ $ar ? 'رمز التتبع الخاص بك' : 'Your tracking code' }}</small>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="flex-grow-1 tracking-code">{{ $trackingCode }}</code>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyCode('{{ $trackingCode }}', this)"><i class="ri-file-copy-line"></i></button>
                                </div>
                            </div>
                        @elseif($status === 'pending')
                            <button class="btn btn-label-warning w-100" disabled><i class="ri-time-line me-1"></i>{{ $ar ? 'قيد المراجعة' : 'Pending Review' }}</button>
                        @elseif($status === 'rejected')
                            <button class="btn btn-label-danger w-100" disabled><i class="ri-close-line me-1"></i>{{ $ar ? 'مرفوض' : 'Rejected' }}</button>
                        @else
                            <form method="POST" action="{{ route('marketer.coupons.request-activation', $coupon) }}"
                                  data-confirm="{{ $ar ? 'إرسال طلب تفعيل هذا الكوبون؟' : 'Send activation request for this coupon?' }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100"><i class="ri-add-line me-1"></i>{{ $ar ? 'طلب التفعيل' : 'Request Activation' }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center py-5 text-muted">
                    <i class="ri-coupon-3-line d-block mb-2" style="font-size:3rem;"></i>
                    {{ $ar ? 'لا توجد كوبونات متاحة حالياً' : 'No coupons available right now' }}
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
.coupon-img { width:100%;height:150px;object-fit:cover; }
.coupon-img-placeholder { display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#f5e6d3,#e8d5bd);color:#561C04;font-size:48px; }
.tracking-box { background:#f8f6f4;border:1px dashed #c9b8aa;border-radius:10px;padding:10px; }
.tracking-code { background:#fff;border:1px solid #e8ddd6;border-radius:6px;padding:5px 8px;font-weight:700;color:#561C04;font-size:13px; }
</style>

@push('scripts')
<script>
function copyCode(code, btn) {
    navigator.clipboard.writeText(code).then(() => {
        const icon = btn.querySelector('i');
        const old = icon.className;
        icon.className = 'ri-check-line';
        if (window.showSuccess) showSuccess('{{ $ar ? "تم نسخ الرمز" : "Code copied" }}');
        setTimeout(() => icon.className = old, 1500);
    });
}
</script>
@endpush
@endsection
