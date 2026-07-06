@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('distributor.coupons.index') }}">{{ $ar ? 'الكوبونات' : 'Coupons' }}</a></li>
                <li class="breadcrumb-item active">{{ $ar ? 'طلبات التفعيل' : 'Activation Requests' }}</li>
            </ol>
        </nav>
        <h4 class="mb-1 fw-bold">{{ $ar ? 'طلبات التفعيل من المسوقين' : 'Marketer Activation Requests' }}</h4>
        <p class="text-muted mb-0">
            {{ $ar ? 'الكوبون:' : 'Coupon:' }}
            <strong>{{ $ar && $coupon->title_ar ? $coupon->title_ar : $coupon->title_en }}</strong>
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ $ar ? 'المسوّق' : 'Marketer' }}</th>
                        <th>{{ $ar ? 'تاريخ الطلب' : 'Requested At' }}</th>
                        <th>{{ $ar ? 'الحالة' : 'Status' }}</th>
                        <th>{{ $ar ? 'الكود' : 'Code' }}</th>
                        <th class="text-end">{{ $ar ? 'الإجراء' : 'Action' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>
                                <div><strong>{{ $req->marketer_name }}</strong></div>
                                <small class="text-muted">{{ $req->marketer_email }}</small>
                            </td>
                            <td><small>{{ \Carbon\Carbon::parse($req->created_at)->format('Y-m-d H:i') }}</small></td>
                            <td>
                                @if($req->status === 'pending')
                                    <span class="badge bg-warning">{{ $ar ? 'معلّق' : 'Pending' }}</span>
                                @elseif($req->status === 'active')
                                    <span class="badge bg-success">{{ $ar ? 'مفعّل' : 'Active' }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $ar ? 'مرفوض' : 'Rejected' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($req->tracking_code)
                                    <span class="badge bg-light text-dark" style="font-family: monospace; font-weight: bold;">{{ $req->tracking_code }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($req->status === 'pending')
                                    <form action="{{ route('distributor.coupons.activation-requests.approve', [$coupon, $req->user_id]) }}"
                                          method="POST" class="d-inline-flex gap-2 align-items-center justify-content-end flex-wrap">
                                        @csrf
                                        <input type="text" name="code" class="form-control form-control-sm" style="max-width: 160px;"
                                               value="{{ strtoupper(\Illuminate\Support\Str::random(4)) . $coupon->id . strtoupper(\Illuminate\Support\Str::random(4)) }}"
                                               placeholder="{{ $ar ? 'كود المسوّق' : 'Marketer code' }}" required>
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="ri-check-line"></i> {{ $ar ? 'تفعيل' : 'Approve' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('distributor.coupons.activation-requests.reject', [$coupon, $req->user_id]) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('{{ $ar ? 'تأكيد رفض الطلب؟' : 'Confirm rejecting this request?' }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ri-close-line"></i> {{ $ar ? 'رفض' : 'Reject' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="mt-2">{{ $ar ? 'لا توجد طلبات تفعيل لهذا الكوبون' : 'No activation requests for this coupon' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('distributor.coupons.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-{{ $ar ? 'right' : 'left' }}-line"></i> {{ $ar ? 'رجوع للكوبونات' : 'Back to Coupons' }}
        </a>
    </div>
</div>
@endsection
