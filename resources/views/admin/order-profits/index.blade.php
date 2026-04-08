@extends('dashboard')

@section('title', app()->getLocale() == 'ar' ? 'أرباح الطلبات' : 'Order Profits')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">
                <i class="ri-money-dollar-circle-line me-2 text-primary"></i>
                {{ app()->getLocale() == 'ar' ? 'أرباح الطلبات' : 'Order Profits' }}
            </h4>
            <p class="text-muted mb-0">{{ app()->getLocale() == 'ar' ? 'تحليل أرباح الطلبات: شحن، علي إكسبريس، الإدارة، البائع' : 'Order profit breakdown: shipping, AliExpress, admin, seller' }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="badge rounded-pill bg-label-primary p-2"><i class="ri-money-dollar-circle-line"></i></div>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'إجمالي الإيرادات' : 'Total Revenue' }}</small>
                    </div>
                    <h5 class="mb-0 text-primary">{{ number_format($totals->total_revenue ?? 0, 2) }}</h5>
                    <small class="text-muted">AED</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="badge rounded-pill bg-label-secondary p-2"><i class="ri-truck-line"></i></div>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'تكلفة الشحن' : 'Shipping' }}</small>
                    </div>
                    <h5 class="mb-0 text-secondary">{{ number_format($totals->total_shipping ?? 0, 2) }}</h5>
                    <small class="text-muted">AED</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="badge rounded-pill bg-label-info p-2"><i class="ri-global-line"></i></div>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'ربح علي إكسبريس' : 'AliExpress Profit' }}</small>
                    </div>
                    <h5 class="mb-0 text-info">{{ number_format($totals->total_aliexpress_profit ?? 0, 2) }}</h5>
                    <small class="text-muted">AED</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="badge rounded-pill bg-label-success p-2"><i class="ri-shield-star-line"></i></div>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'ربح الإدارة' : 'Admin Profit' }}</small>
                    </div>
                    <h5 class="mb-0 text-success">{{ number_format($totals->total_admin_profit ?? 0, 2) }}</h5>
                    <small class="text-muted">AED</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="badge rounded-pill bg-label-warning p-2"><i class="ri-store-line"></i></div>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'ربح البائع' : 'Seller Profit' }}</small>
                    </div>
                    <h5 class="mb-0 text-warning">{{ number_format($totals->total_seller_profit ?? 0, 2) }}</h5>
                    <small class="text-muted">AED</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="badge rounded-pill bg-label-danger p-2"><i class="ri-wallet-3-line"></i></div>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'صافي الربح' : 'Net Profit' }}</small>
                    </div>
                    <h5 class="mb-0 text-danger">{{ number_format($totals->total_profit ?? 0, 2) }}</h5>
                    <small class="text-muted">AED</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.order-profits.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                            placeholder="{{ app()->getLocale() == 'ar' ? 'رقم الطلب، اسم العميل، رقم علي إكسبريس' : 'Order #, customer, AliExpress ID' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'حالة الطلب' : 'Order Status' }}</label>
                        <select class="form-select" name="status">
                            <option value="all">{{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}</option>
                            @foreach(['pending','processing','placed','shipped','delivered','cancelled'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'حالة الدفع' : 'Payment' }}</label>
                        <select class="form-select" name="payment_status">
                            <option value="all">{{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}</option>
                            @foreach(['pending','paid','failed','refunded'] as $p)
                            <option value="{{ $p }}" {{ request('payment_status') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'من تاريخ' : 'From' }}</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'إلى تاريخ' : 'To' }}</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-search-line"></i>
                        </button>
                        <a href="{{ route('admin.order-profits.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-refresh-line"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'تفاصيل أرباح الطلبات' : 'Order Profit Details' }}</h5>
            <small class="text-muted">{{ $orders->total() }} {{ app()->getLocale() == 'ar' ? 'طلب' : 'orders' }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <!-- Order -->
                        <th class="border-end" colspan="3" style="background:#f0f4ff;">
                            <i class="ri-shopping-bag-line me-1 text-primary"></i>
                            {{ app()->getLocale() == 'ar' ? 'الطلب' : 'Order' }}
                        </th>
                        <!-- Shipping -->
                        <th class="border-end" colspan="3" style="background:#f0fff4;">
                            <i class="ri-truck-line me-1 text-success"></i>
                            {{ app()->getLocale() == 'ar' ? 'الشحن' : 'Shipping' }}
                        </th>
                        <!-- AliExpress -->
                        <th class="border-end" colspan="3" style="background:#fff8e1;">
                            <i class="ri-global-line me-1 text-warning"></i>
                            {{ app()->getLocale() == 'ar' ? 'علي إكسبريس' : 'AliExpress' }}
                        </th>
                        <!-- Profits -->
                        <th colspan="3" style="background:#fff0f0;">
                            <i class="ri-wallet-3-line me-1 text-danger"></i>
                            {{ app()->getLocale() == 'ar' ? 'الأرباح' : 'Profits' }}
                        </th>
                    </tr>
                    <tr class="text-nowrap small">
                        <!-- Order cols -->
                        <th style="background:#f0f4ff;">{{ app()->getLocale() == 'ar' ? 'رقم الطلب' : 'Order #' }}</th>
                        <th style="background:#f0f4ff;">{{ app()->getLocale() == 'ar' ? 'العميل / البائع' : 'Customer / Seller' }}</th>
                        <th class="border-end" style="background:#f0f4ff;">{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                        <!-- Shipping cols -->
                        <th style="background:#f0fff4;">{{ app()->getLocale() == 'ar' ? 'رقم التتبع' : 'Tracking' }}</th>
                        <th style="background:#f0fff4;">{{ app()->getLocale() == 'ar' ? 'الناقل' : 'Carrier' }}</th>
                        <th class="border-end" style="background:#f0fff4;">{{ app()->getLocale() == 'ar' ? 'تكلفة الشحن' : 'Cost' }}</th>
                        <!-- AliExpress cols -->
                        <th style="background:#fff8e1;">{{ app()->getLocale() == 'ar' ? 'رقم الطلب' : 'Order ID' }}</th>
                        <th style="background:#fff8e1;">{{ app()->getLocale() == 'ar' ? 'سعر الوحدة' : 'Unit Price' }}</th>
                        <th class="border-end" style="background:#fff8e1;">{{ app()->getLocale() == 'ar' ? 'ربح AliEx' : 'AliEx Profit' }}</th>
                        <!-- Profit cols -->
                        <th style="background:#fff0f0;">{{ app()->getLocale() == 'ar' ? 'ربح الإدارة' : 'Admin' }}</th>
                        <th style="background:#fff0f0;">{{ app()->getLocale() == 'ar' ? 'ربح البائع' : 'Seller' }}</th>
                        <th style="background:#fff0f0;">{{ app()->getLocale() == 'ar' ? 'الإجمالي' : 'Total' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <!-- Order Info -->
                        <td style="background:#fafbff;">
                            <a href="{{ route('admin.orders.index', ['search' => $order->order_number]) }}" class="fw-semibold text-primary text-decoration-none">
                                {{ $order->order_number }}
                            </a>
                            <div class="text-muted small">{{ $order->created_at->format('d M Y') }}</div>
                        </td>
                        <td style="background:#fafbff;">
                            <div class="fw-medium">{{ $order->customer_name ?? '—' }}</div>
                            @if($order->user)
                            <small class="text-muted">
                                <i class="ri-store-line me-1"></i>{{ $order->user->name }}
                            </small>
                            @endif
                        </td>
                        <td class="border-end" style="background:#fafbff;">
                            <span class="badge bg-{{ $order->getStatusBadgeColor() }} mb-1 d-block">
                                {{ $order->getStatusName(app()->getLocale()) }}
                            </span>
                            <span class="badge bg-{{ $order->getPaymentStatusBadgeColor() }}">
                                {{ $order->getPaymentStatusName(app()->getLocale()) }}
                            </span>
                        </td>

                        <!-- Shipping -->
                        <td style="background:#f8fff8;">
                            @if($order->shipping?->tracking_number)
                                <code class="small">{{ $order->shipping->tracking_number }}</code>
                            @elseif($order->tracking_number)
                                <code class="small">{{ $order->tracking_number }}</code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="background:#f8fff8;">
                            @if($order->shipping?->carrier_name)
                                <span class="badge bg-label-success">{{ $order->shipping->carrier_name }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                            @if($order->shipping?->status)
                                <div><small class="text-muted">{{ ucfirst($order->shipping->status) }}</small></div>
                            @endif
                        </td>
                        <td class="border-end text-end fw-semibold" style="background:#f8fff8;">
                            @if($order->freight_amount > 0)
                                <span class="text-success">{{ number_format($order->freight_amount, 2) }}</span>
                                <small class="text-muted d-block">AED</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <!-- AliExpress -->
                        <td style="background:#fffdf0;">
                            @if($order->aliexpress_order_id)
                                <span class="badge bg-label-warning">{{ $order->aliexpress_order_id }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end" style="background:#fffdf0;">
                            <span class="fw-semibold">{{ number_format($order->unit_price ?? 0, 2) }}</span>
                            <small class="text-muted d-block">{{ $order->currency ?? 'AED' }} × {{ $order->quantity }}</small>
                        </td>
                        <td class="border-end text-end" style="background:#fffdf0;">
                            @if(($order->aliexpress_profit ?? 0) > 0)
                                <span class="badge bg-warning text-dark">+{{ number_format($order->aliexpress_profit, 2) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <!-- Profits -->
                        <td class="text-end" style="background:#fff8f8;">
                            @if(($order->admin_category_profit ?? 0) > 0)
                                <span class="badge bg-success">+{{ number_format($order->admin_category_profit, 2) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end" style="background:#fff8f8;">
                            @if(($order->seller_profit ?? 0) > 0)
                                <span class="badge bg-info">+{{ number_format($order->seller_profit, 2) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold" style="background:#fff8f8;">
                            @php
                                $net = ($order->aliexpress_profit ?? 0) + ($order->admin_category_profit ?? 0) + ($order->seller_profit ?? 0);
                            @endphp
                            <span class="text-{{ $net > 0 ? 'danger' : 'muted' }}">
                                {{ $net > 0 ? '+' : '' }}{{ number_format($net, 2) }}
                            </span>
                            <small class="text-muted d-block">AED</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-5">
                            <i class="ri-inbox-line text-muted" style="font-size:3rem;"></i>
                            <p class="text-muted mt-2">{{ app()->getLocale() == 'ar' ? 'لا توجد طلبات' : 'No orders found' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">
                {{ app()->getLocale() == 'ar'
                    ? "عرض {$orders->firstItem()} إلى {$orders->lastItem()} من {$orders->total()}"
                    : "Showing {$orders->firstItem()} to {$orders->lastItem()} of {$orders->total()}" }}
            </small>
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
