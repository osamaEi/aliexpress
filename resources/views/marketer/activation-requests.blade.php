@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">

    {{-- Stats --}}
    <div class="mb-3">
        <h5 class="fw-bold text-end mb-3">{{ $ar ? 'إحصائيات' : 'Statistics' }}</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <a href="{{ route('marketer.activation-requests', ['status' => 'pending']) }}" class="text-decoration-none">
                    <div class="card ar-stat h-100 {{ request('status') === 'pending' ? 'border-warning' : '' }}">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="text-end">
                                <div class="text-muted">{{ $ar ? 'معلّق' : 'Pending' }}</div>
                                <h4 class="mb-0 fw-bold">{{ $stats['pending'] }}</h4>
                            </div>
                            <span class="ar-stat-icon bg-label-warning"><i class="ri-time-line"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('marketer.activation-requests', ['status' => 'active']) }}" class="text-decoration-none">
                    <div class="card ar-stat h-100 {{ request('status') === 'active' ? 'border-success' : '' }}">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="text-end">
                                <div class="text-muted">{{ $ar ? 'مقبول' : 'Accepted' }}</div>
                                <h4 class="mb-0 fw-bold text-success">{{ $stats['active'] }}</h4>
                            </div>
                            <span class="ar-stat-icon bg-label-success"><i class="ri-check-line"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('marketer.activation-requests', ['status' => 'rejected']) }}" class="text-decoration-none">
                    <div class="card ar-stat h-100 {{ request('status') === 'rejected' ? 'border-danger' : '' }}">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="text-end">
                                <div class="text-muted">{{ $ar ? 'مرفوض' : 'Rejected' }}</div>
                                <h4 class="mb-0 fw-bold text-danger">{{ $stats['rejected'] }}</h4>
                            </div>
                            <span class="ar-stat-icon bg-label-danger"><i class="ri-close-line"></i></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <div class="mb-3 text-end">
        <h5 class="fw-bold mb-1">{{ $ar ? 'طلبات التفعيل' : 'Activation Requests' }}</h5>
        <p class="text-muted mb-0">{{ $ar ? 'هنا تظهر طلبات التفعيل وبيان حالتها، للكوبونات التي تود العمل على تسويقها.' : 'Your activation requests and their status for coupons you want to promote.' }}</p>
        @if(request('status'))
            <a href="{{ route('marketer.activation-requests') }}" class="btn btn-sm btn-outline-secondary mt-2">{{ $ar ? 'عرض الكل' : 'Show all' }}</a>
        @endif
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>{{ $ar ? 'معرف الطلب' : 'Request ID' }}</th>
                        <th>{{ $ar ? 'اسم العرض' : 'Offer Name' }}</th>
                        <th>{{ $ar ? 'اسم المتجر' : 'Store Name' }}</th>
                        <th>{{ $ar ? 'حالة الطلب' : 'Status' }}</th>
                        <th>{{ $ar ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                        <th>{{ $ar ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        @php
                            $title = $ar && $req->title_ar ? $req->title_ar : $req->title_en;
                            $store = $req->is_global
                                ? ($ar ? 'متجر عالمي' : 'Global Store')
                                : ($req->store_display_name ?: $req->store_name ?: '—');
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $req->coupon_id }}</td>
                            <td>{{ $title }}</td>
                            <td>{{ $store }}</td>
                            <td>
                                @if($req->status === 'pending')
                                    <span class="badge bg-warning">{{ $ar ? 'معلّق' : 'Pending' }}</span>
                                @elseif($req->status === 'active')
                                    <span class="badge bg-success">{{ $ar ? 'مقبول' : 'Accepted' }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $ar ? 'مرفوض' : 'Rejected' }}</span>
                                @endif
                            </td>
                            <td><small>{{ \Carbon\Carbon::parse($req->created_at)->format('Y-m-d h:i A') }}</small></td>
                            <td>
                                @if($req->status === 'active')
                                    <a href="{{ route('marketer.products') }}" class="btn btn-sm btn-success">
                                        <i class="ri-eye-line me-1"></i>{{ $ar ? 'عرض الكوبون الفعّال' : 'View Active Coupon' }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="ri-inbox-line d-block mb-2" style="font-size:3rem;opacity:.3;"></i>
                                {{ $ar ? 'لا توجد طلبات تفعيل' : 'No activation requests' }} —
                                <a href="{{ route('marketer.coupons.index') }}">{{ $ar ? 'تصفّح الكوبونات' : 'browse coupons' }}</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($requests->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>{{ $requests->links() }}</div>
            <small class="text-muted">{{ $requests->total() }} {{ $ar ? 'سجل تم إيجاده' : 'records found' }}</small>
        </div>
    @else
        <div class="mt-3 text-end">
            <small class="text-muted">{{ $requests->total() }} {{ $ar ? 'سجل تم إيجاده' : 'records found' }}</small>
        </div>
    @endif
</div>

<style>
.ar-stat { border:1px solid #e8e0da;transition:all .2s; }
.ar-stat:hover { box-shadow:0 4px 14px rgba(86,28,4,.1);transform:translateY(-2px); }
.ar-stat-icon { width:44px;height:44px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:22px; }
</style>
@endsection
