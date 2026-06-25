@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">

    <div class="mb-4 d-flex align-items-center gap-3">
        <span class="mk-header-icon"><i class="ri-megaphone-line"></i></span>
        <div>
            <h4 class="mb-0 fw-bold">{{ $ar ? 'لوحة المسوّق' : 'Marketer Dashboard' }}</h4>
            <span class="text-muted small">{{ $ar ? 'روّج الكوبونات واربح عمولة' : 'Promote coupons and earn commissions' }}</span>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => $ar ? 'كوبونات مفعّلة' : 'Active Coupons', 'value' => number_format($stats['active_coupons']), 'icon' => 'ri-coupon-3-line', 'color' => 'success'],
                ['label' => $ar ? 'طلبات معلّقة' : 'Pending Requests', 'value' => number_format($stats['pending_coupons']), 'icon' => 'ri-time-line', 'color' => 'warning'],
                ['label' => $ar ? 'رصيد المحفظة' : 'Wallet Balance', 'value' => number_format($stats['wallet_balance'], 2), 'icon' => 'ri-wallet-3-line', 'color' => 'primary'],
                ['label' => $ar ? 'إجمالي الأرباح' : 'Total Earnings', 'value' => number_format($stats['total_earnings'], 2), 'icon' => 'ri-money-dollar-circle-line', 'color' => 'info'],
            ];
        @endphp
        @foreach($cards as $c)
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

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ $ar ? 'كوبوناتي الأخيرة' : 'My Recent Coupons' }}</h6>
                    <a href="{{ route('marketer.coupons.index') }}" class="btn btn-sm btn-outline-primary">{{ $ar ? 'عرض الكل' : 'View all' }}</a>
                </div>
                <div class="card-body">
                    @forelse($recentCoupons as $coupon)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong>{{ $ar && $coupon->title_ar ? $coupon->title_ar : $coupon->title_en }}</strong>
                                @if($coupon->pivot->tracking_code)
                                    <code class="ms-2 small">{{ $coupon->pivot->tracking_code }}</code>
                                @endif
                            </div>
                            @php
                                $st = $coupon->pivot->status;
                                $stColor = $st === 'active' ? 'success' : ($st === 'pending' ? 'warning' : 'danger');
                                $stLabel = $st === 'active' ? ($ar ? 'مفعّل' : 'Active') : ($st === 'pending' ? ($ar ? 'معلّق' : 'Pending') : ($ar ? 'مرفوض' : 'Rejected'));
                            @endphp
                            <span class="badge bg-label-{{ $stColor }}">{{ $stLabel }}</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            {{ $ar ? 'لا توجد كوبونات بعد' : 'No coupons yet' }} —
                            <a href="{{ route('marketer.coupons.index') }}">{{ $ar ? 'تصفّح الكوبونات' : 'browse coupons' }}</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <span class="stat-icon bg-label-info mx-auto mb-3" style="width:56px;height:56px;font-size:28px;"><i class="ri-hand-coin-line"></i></span>
                    <div class="text-muted small">{{ $ar ? 'أرباح معلّقة' : 'Pending Earnings' }}</div>
                    <h3 class="text-info mb-3">{{ number_format($stats['pending_earnings'], 2) }}</h3>
                    <a href="{{ route('wallet.withdrawal.create') }}" class="btn btn-primary w-100">
                        <i class="ri-bank-card-line me-1"></i>{{ $ar ? 'طلب سحب الأرباح' : 'Request Withdrawal' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mk-header-icon { width:46px;height:46px;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;background:linear-gradient(135deg,#561C04 0%,#7A3206 100%);color:#fff;font-size:22px;box-shadow:0 4px 12px rgba(86,28,4,.25); }
.stat-card { border:1px solid #f0eae6;transition:all .2s; }
.stat-card:hover { box-shadow:0 4px 14px rgba(86,28,4,.1);transform:translateY(-2px); }
.stat-icon { width:44px;height:44px;border-radius:10px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:22px; }
</style>
@endsection
