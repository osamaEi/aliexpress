@extends('dashboard')

@section('title', app()->getLocale() == 'ar' ? 'أرباح الطلبات' : 'Order Profits')

@section('content')
@php $isAr = app()->getLocale() == 'ar'; @endphp

<div class="col-12" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

    {{-- ─── Page Header ─────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                {{ $isAr ? 'تقرير أرباح الطلبات' : 'Order Profits Report' }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                            {{ $isAr ? 'لوحة التحكم' : 'Dashboard' }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ $isAr ? 'أرباح الطلبات' : 'Order Profits' }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.order-profits.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ri-refresh-line me-1"></i>{{ $isAr ? 'تحديث' : 'Refresh' }}
            </a>
        </div>
    </div>

    {{-- ─── Summary Cards ───────────────────────────────────────── --}}
    <div class="row g-3 mb-4">

        {{-- Revenue --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ $isAr ? 'إجمالي الإيرادات' : 'Total Revenue' }}</p>
                            <h5 class="fw-bold mb-0 text-primary">{{ number_format($totals->total_revenue ?? 0, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ri-money-dollar-circle-line ri-20px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Shipping --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ $isAr ? 'تكلفة الشحن' : 'Shipping Cost' }}</p>
                            <h5 class="fw-bold mb-0 text-secondary">{{ number_format($totals->total_shipping ?? 0, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ri-truck-line ri-20px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- AliExpress --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ $isAr ? 'ربح علي إكسبريس' : 'AliExpress Profit' }}</p>
                            <h5 class="fw-bold mb-0 text-info">{{ number_format($totals->total_aliexpress_profit ?? 0, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ri-global-line ri-20px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admin Profit --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ $isAr ? 'ربح الإدارة' : 'Admin Profit' }}</p>
                            <h5 class="fw-bold mb-0 text-success">{{ number_format($totals->total_admin_profit ?? 0, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ri-shield-star-line ri-20px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seller Profit --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ $isAr ? 'ربح البائع' : 'Seller Profit' }}</p>
                            <h5 class="fw-bold mb-0 text-warning">{{ number_format($totals->total_seller_profit ?? 0, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ri-store-line ri-20px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Net Profit --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ $isAr ? 'صافي الربح' : 'Net Profit' }}</p>
                            <h5 class="fw-bold mb-0 text-danger">{{ number_format($totals->total_profit ?? 0, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ri-wallet-3-line ri-20px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ─── Filters ─────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header border-bottom pb-3 pt-3">
            <h6 class="mb-0 fw-semibold">
                <i class="ri-filter-3-line me-2 text-primary"></i>
                {{ $isAr ? 'فلترة النتائج' : 'Filter Results' }}
            </h6>
        </div>
        <div class="card-body pt-3">
            <form method="GET" action="{{ route('admin.order-profits.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label small fw-medium">
                            {{ $isAr ? 'بحث' : 'Search' }}
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" name="search"
                                value="{{ request('search') }}"
                                placeholder="{{ $isAr ? 'رقم الطلب / اسم العميل / AliExpress' : 'Order #, customer, AliExpress ID' }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-medium">{{ $isAr ? 'حالة الطلب' : 'Order Status' }}</label>
                        <select class="form-select" name="status">
                            <option value="all">{{ $isAr ? '— الكل —' : '— All —' }}</option>
                            @php
                                $statusOptions = [
                                    'pending'    => ['ar' => 'قيد الانتظار',  'en' => 'Pending'],
                                    'processing' => ['ar' => 'قيد المعالجة', 'en' => 'Processing'],
                                    'placed'     => ['ar' => 'تم التقديم',   'en' => 'Placed'],
                                    'shipped'    => ['ar' => 'تم الشحن',     'en' => 'Shipped'],
                                    'delivered'  => ['ar' => 'تم التسليم',   'en' => 'Delivered'],
                                    'cancelled'  => ['ar' => 'ملغي',         'en' => 'Cancelled'],
                                ];
                            @endphp
                            @foreach($statusOptions as $val => $label)
                            <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>
                                {{ $isAr ? $label['ar'] : $label['en'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-medium">{{ $isAr ? 'حالة الدفع' : 'Payment Status' }}</label>
                        <select class="form-select" name="payment_status">
                            <option value="all">{{ $isAr ? '— الكل —' : '— All —' }}</option>
                            @php
                                $paymentOptions = [
                                    'pending'  => ['ar' => 'قيد الانتظار', 'en' => 'Pending'],
                                    'paid'     => ['ar' => 'مدفوع',        'en' => 'Paid'],
                                    'failed'   => ['ar' => 'فشل',          'en' => 'Failed'],
                                    'refunded' => ['ar' => 'مسترد',        'en' => 'Refunded'],
                                ];
                            @endphp
                            @foreach($paymentOptions as $val => $label)
                            <option value="{{ $val }}" {{ request('payment_status') == $val ? 'selected' : '' }}>
                                {{ $isAr ? $label['ar'] : $label['en'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-medium">{{ $isAr ? 'من تاريخ' : 'From Date' }}</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-medium">{{ $isAr ? 'إلى تاريخ' : 'To Date' }}</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-1 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="ri-search-line"></i>
                        </button>
                        <a href="{{ route('admin.order-profits.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-close-line"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ─── Table ───────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="ri-list-check-2 me-2 text-primary"></i>
                {{ $isAr ? 'تفاصيل الطلبات والأرباح' : 'Orders & Profit Details' }}
            </h6>
            <span class="badge bg-label-primary rounded-pill">
                {{ $orders->total() }} {{ $isAr ? 'طلب' : 'orders' }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                <thead>
                    {{-- Group headers --}}
                    <tr class="text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">
                        <th colspan="4" class="text-center py-2 border-end"
                            style="background:#eef2ff;color:#4f46e5;border-bottom:2px solid #c7d2fe;">
                            <i class="ri-shopping-bag-3-line me-1"></i>
                            {{ $isAr ? 'معلومات الطلب' : 'Order Info' }}
                        </th>
                        <th colspan="3" class="text-center py-2 border-end"
                            style="background:#f0fdf4;color:#16a34a;border-bottom:2px solid #bbf7d0;">
                            <i class="ri-truck-line me-1"></i>
                            {{ $isAr ? 'الشحن' : 'Shipping' }}
                        </th>
                        <th colspan="3" class="text-center py-2 border-end"
                            style="background:#fffbeb;color:#d97706;border-bottom:2px solid #fde68a;">
                            <i class="ri-global-line me-1"></i>
                            {{ $isAr ? 'علي إكسبريس' : 'AliExpress' }}
                        </th>
                        <th colspan="3" class="text-center py-2"
                            style="background:#fff1f2;color:#e11d48;border-bottom:2px solid #fecdd3;">
                            <i class="ri-pie-chart-2-line me-1"></i>
                            {{ $isAr ? 'الأرباح' : 'Profits' }}
                        </th>
                    </tr>
                    {{-- Column headers --}}
                    <tr class="table-light text-nowrap" style="font-size:.8rem;">
                        {{-- Order --}}
                        <th class="ps-3">{{ $isAr ? 'رقم الطلب' : 'Order #' }}</th>
                        <th>{{ $isAr ? 'المنتج' : 'Product' }}</th>
                        <th>{{ $isAr ? 'العميل' : 'Customer' }}</th>
                        <th class="border-end">{{ $isAr ? 'الحالة' : 'Status' }}</th>
                        {{-- Shipping --}}
                        <th>{{ $isAr ? 'رقم التتبع' : 'Tracking #' }}</th>
                        <th>{{ $isAr ? 'الناقل' : 'Carrier' }}</th>
                        <th class="text-end border-end">{{ $isAr ? 'تكلفة الشحن' : 'Ship. Cost' }}</th>
                        {{-- AliExpress --}}
                        <th>{{ $isAr ? 'رقم AliEx' : 'AliEx Order' }}</th>
                        <th class="text-end">{{ $isAr ? 'سعر الوحدة' : 'Unit Price' }}</th>
                        <th class="text-end border-end">{{ $isAr ? 'ربح AliEx' : 'AliEx Profit' }}</th>
                        {{-- Profits --}}
                        <th class="text-end">{{ $isAr ? 'ربح الإدارة' : 'Admin' }}</th>
                        <th class="text-end">{{ $isAr ? 'ربح البائع' : 'Seller' }}</th>
                        <th class="text-end pe-3">{{ $isAr ? 'الصافي' : 'Net' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    @php
                        $net = ($order->aliexpress_profit ?? 0)
                             + ($order->admin_category_profit ?? 0)
                             + ($order->seller_profit ?? 0);
                    @endphp
                    <tr>

                        {{-- ── Order ───────────────────────────── --}}
                        <td class="ps-3">
                            <a href="{{ route('admin.orders.index', ['search' => $order->order_number]) }}"
                               class="fw-semibold text-primary text-decoration-none">
                                {{ $order->order_number }}
                            </a>
                            <div class="text-muted" style="font-size:.75rem;">
                                {{ $order->created_at->format('d M Y') }}
                                <span class="ms-1">{{ $order->created_at->format('H:i') }}</span>
                            </div>
                        </td>

                        <td>
                            @if($order->product)
                                <span class="text-body fw-medium" title="{{ $order->product->name }}">
                                    {{ Str::limit($order->product->name, 25) }}
                                </span>
                                <div class="text-muted" style="font-size:.75rem;">
                                    × {{ $order->quantity }}
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            <div class="fw-medium">{{ $order->customer_name ?? '—' }}</div>
                            @if($order->user)
                            <div style="font-size:.75rem;">
                                <i class="ri-store-line text-muted"></i>
                                <span class="text-muted">{{ $order->user->name }}</span>
                            </div>
                            @endif
                        </td>

                        <td class="border-end">
                            <span class="badge bg-{{ $order->getStatusBadgeColor() }} d-block mb-1" style="font-size:.72rem;">
                                {{ $order->getStatusName($isAr ? 'ar' : 'en') }}
                            </span>
                            <span class="badge bg-label-{{ $order->getPaymentStatusBadgeColor() }}" style="font-size:.72rem;">
                                {{ $order->getPaymentStatusName($isAr ? 'ar' : 'en') }}
                            </span>
                        </td>

                        {{-- ── Shipping ─────────────────────────── --}}
                        <td>
                            @php $tracking = $order->shipping?->tracking_number ?? $order->tracking_number; @endphp
                            @if($tracking)
                                <code style="font-size:.78rem;background:#f1f5f9;padding:2px 6px;border-radius:4px;">
                                    {{ $tracking }}
                                </code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($order->shipping?->carrier_name)
                                <span class="badge bg-label-success">{{ $order->shipping->carrier_name }}</span>
                                @if($order->shipping->status)
                                <div class="text-muted mt-1" style="font-size:.72rem;">
                                    {{ ucfirst($order->shipping->status) }}
                                </div>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td class="text-end border-end">
                            @if(($order->freight_amount ?? 0) > 0)
                                <span class="fw-semibold text-body">{{ number_format($order->freight_amount, 2) }}</span>
                                <div class="text-muted" style="font-size:.72rem;">AED</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- ── AliExpress ───────────────────────── --}}
                        <td>
                            @if($order->aliexpress_order_id)
                                <span class="text-body" style="font-size:.78rem;font-family:monospace;">
                                    {{ $order->aliexpress_order_id }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td class="text-end">
                            <span class="fw-semibold">{{ number_format($order->unit_price ?? 0, 2) }}</span>
                            <div class="text-muted" style="font-size:.72rem;">{{ $order->currency ?? 'AED' }}</div>
                        </td>

                        <td class="text-end border-end">
                            @if(($order->aliexpress_profit ?? 0) > 0)
                                <span class="fw-semibold text-info">+{{ number_format($order->aliexpress_profit, 2) }}</span>
                                <div class="text-muted" style="font-size:.72rem;">AED</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- ── Profits ──────────────────────────── --}}
                        <td class="text-end">
                            @if(($order->admin_category_profit ?? 0) > 0)
                                <span class="fw-semibold text-success">+{{ number_format($order->admin_category_profit, 2) }}</span>
                                <div class="text-muted" style="font-size:.72rem;">AED</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td class="text-end">
                            @if(($order->seller_profit ?? 0) > 0)
                                <span class="fw-semibold text-warning">+{{ number_format($order->seller_profit, 2) }}</span>
                                <div class="text-muted" style="font-size:.72rem;">AED</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td class="text-end pe-3">
                            @if($net > 0)
                                <span class="fw-bold text-danger fs-6">+{{ number_format($net, 2) }}</span>
                            @else
                                <span class="fw-bold text-muted">{{ number_format($net, 2) }}</span>
                            @endif
                            <div class="text-muted" style="font-size:.72rem;">AED</div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-5">
                            <div class="py-4">
                                <i class="ri-file-chart-line text-muted" style="font-size:3.5rem;opacity:.35;"></i>
                                <h6 class="text-muted mt-3 mb-1">
                                    {{ $isAr ? 'لا توجد طلبات مطابقة' : 'No orders found' }}
                                </h6>
                                <p class="text-muted small mb-3">
                                    {{ $isAr ? 'حاول تعديل معايير الفلترة' : 'Try adjusting your filter criteria' }}
                                </p>
                                <a href="{{ route('admin.order-profits.index') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="ri-refresh-line me-1"></i>
                                    {{ $isAr ? 'إعادة تعيين الفلتر' : 'Reset Filters' }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                {{-- Totals footer row --}}
                @if($orders->count() > 0)
                <tfoot>
                    <tr class="fw-bold" style="background:#f8fafc;font-size:.82rem;">
                        <td colspan="6" class="ps-3 border-end text-muted">
                            {{ $isAr ? 'مجموع الصفحة الحالية' : 'Page Totals' }}
                            ({{ $orders->count() }} {{ $isAr ? 'طلب' : 'orders' }})
                        </td>
                        <td class="text-end border-end text-body">
                            {{ number_format($orders->sum('freight_amount'), 2) }}
                        </td>
                        <td colspan="2" class="border-end"></td>
                        <td class="text-end border-end text-info">
                            +{{ number_format($orders->sum('aliexpress_profit'), 2) }}
                        </td>
                        <td class="text-end text-success">
                            +{{ number_format($orders->sum('admin_category_profit'), 2) }}
                        </td>
                        <td class="text-end text-warning">
                            +{{ number_format($orders->sum('seller_profit'), 2) }}
                        </td>
                        <td class="text-end pe-3 text-danger">
                            +{{ number_format(
                                $orders->sum('aliexpress_profit') +
                                $orders->sum('admin_category_profit') +
                                $orders->sum('seller_profit'), 2) }}
                        </td>
                    </tr>
                </tfoot>
                @endif

            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
        <div class="card-footer border-top d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
            <p class="mb-0 text-muted small">
                @if($isAr)
                    عرض <strong>{{ $orders->firstItem() }}</strong> إلى <strong>{{ $orders->lastItem() }}</strong>
                    من <strong>{{ $orders->total() }}</strong> طلب
                @else
                    Showing <strong>{{ $orders->firstItem() }}</strong> to <strong>{{ $orders->lastItem() }}</strong>
                    of <strong>{{ $orders->total() }}</strong> orders
                @endif
            </p>
            {{ $orders->links() }}
        </div>
        @endif

    </div>{{-- end card --}}

</div>
@endsection
