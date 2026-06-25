@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">

    <div class="mb-4">
        <h4 class="mb-1 fw-bold">{{ $ar ? 'التقارير' : 'Reports' }}</h4>
        <p class="text-muted mb-0">{{ $ar ? 'أرباحك من كوبونات المتاجر العالمية' : 'Your earnings from Global Stores coupons' }}</p>
    </div>

    {{-- Totals --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => $ar ? 'إجمالي العمولات' : 'Total Commission', 'value' => number_format($totals['total_commission'], 2), 'icon' => 'ri-money-dollar-circle-line', 'color' => 'primary'],
                ['label' => $ar ? 'مدفوعة' : 'Paid', 'value' => number_format($totals['paid_commission'], 2), 'icon' => 'ri-checkbox-circle-line', 'color' => 'success'],
                ['label' => $ar ? 'معلّقة' : 'Pending', 'value' => number_format($totals['pending_commission'], 2), 'icon' => 'ri-time-line', 'color' => 'warning'],
                ['label' => $ar ? 'عدد الاستخدامات' : 'Total Uses', 'value' => number_format($totals['total_uses']), 'icon' => 'ri-shopping-cart-2-line', 'color' => 'info'],
            ];
        @endphp
        @foreach($cards as $c)
            <div class="col-6 col-md-3">
                <div class="card h-100" style="border:1px solid #f0eae6;">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <span class="bg-label-{{ $c['color'] }}" style="width:44px;height:44px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;"><i class="{{ $c['icon'] }}"></i></span>
                        <div><h5 class="mb-0">{{ $c['value'] }}</h5><small class="text-muted">{{ $c['label'] }}</small></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Usage table --}}
    <div class="card">
        <div class="card-header"><h6 class="mb-0">{{ $ar ? 'سجل الاستخدامات' : 'Usage Log' }}</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ $ar ? 'الكوبون' : 'Coupon' }}</th>
                            <th>{{ $ar ? 'قيمة الطلب' : 'Order Amount' }}</th>
                            <th>{{ $ar ? 'العمولة' : 'Commission' }}</th>
                            <th>{{ $ar ? 'الحالة' : 'Status' }}</th>
                            <th>{{ $ar ? 'التاريخ' : 'Date' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usages as $u)
                            @php
                                $st = $u->commission_status;
                                $stColor = ['pending'=>'warning','approved'=>'info','paid'=>'success','cancelled'=>'danger'][$st] ?? 'secondary';
                            @endphp
                            <tr>
                                <td>{{ optional($u->coupon)->title ?? '—' }}</td>
                                <td>{{ number_format($u->order_amount, 2) }}</td>
                                <td class="fw-semibold text-success">{{ number_format($u->commission_amount, 2) }}</td>
                                <td><span class="badge bg-label-{{ $stColor }}">{{ ucfirst($st) }}</span></td>
                                <td>{{ $u->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">{{ $ar ? 'لا توجد استخدامات بعد' : 'No usage yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($usages->hasPages())
            <div class="card-footer">{{ $usages->links() }}</div>
        @endif
    </div>
</div>
@endsection
