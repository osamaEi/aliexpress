@extends('dashboard')

@section('content')
<div class="container-fluid" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="ri-shopping-bag-3-line me-2 text-primary"></i>
                                {{ app()->getLocale() == 'ar' ? 'طلبات منتجاتي' : 'My Products Orders' }}
                            </h4>
                            <small class="text-muted">
                                <i class="ri-file-list-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'عرض وإدارة طلبات منتجاتك' : 'View and manage your products orders' }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Success/Error Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ri-checkbox-circle-line me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Statistics Cards --}}
                    @php
                        $statsQuery = App\Models\Order::whereIn('product_id', auth()->user()->assignedProducts()->pluck('products.id'));
                    @endphp
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-white border-0">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ (clone $statsQuery)->where('status', 'pending')->count() }}</h3>
                                    <small>{{ app()->getLocale() == 'ar' ? 'قيد الانتظار' : 'Pending' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white border-0">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ (clone $statsQuery)->where('status', 'processing')->count() }}</h3>
                                    <small>{{ app()->getLocale() == 'ar' ? 'قيد المعالجة' : 'Processing' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white border-0">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ (clone $statsQuery)->where('status', 'shipped')->count() }}</h3>
                                    <small>{{ app()->getLocale() == 'ar' ? 'تم الشحن' : 'Shipped' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white border-0">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ (clone $statsQuery)->where('status', 'delivered')->count() }}</h3>
                                    <small>{{ app()->getLocale() == 'ar' ? 'تم التوصيل' : 'Delivered' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Search and Filter Form --}}
                    <form method="GET" action="{{ route('distributor.orders') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="{{ app()->getLocale() == 'ar' ? 'بحث برقم الطلب أو اسم العميل' : 'Search by order number or customer name' }}"
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'جميع الحالات' : 'All Status' }}
                                    </option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'قيد الانتظار' : 'Pending' }}
                                    </option>
                                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'قيد المعالجة' : 'Processing' }}
                                    </option>
                                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'تم الشحن' : 'Shipped' }}
                                    </option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'تم التوصيل' : 'Delivered' }}
                                    </option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? 'ملغي' : 'Cancelled' }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-search-line me-1"></i>
                                    {{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}
                                </button>
                            </div>
                            @if(request('search') || request('status') != 'all')
                                <div class="col-md-2">
                                    <a href="{{ route('distributor.orders') }}" class="btn btn-outline-secondary w-100">
                                        <i class="ri-close-line me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'مسح' : 'Clear' }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </form>

                    {{-- Orders Table --}}
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ app()->getLocale() == 'ar' ? 'رقم الطلب' : 'Order #' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'المتجر' : 'Store' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'المنتج' : 'Product' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'الكمية' : 'Qty' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'الإجمالي' : 'Total' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'التاريخ' : 'Date' }}</th>
                                        <th>{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('orders.show', $order) }}" class="text-primary fw-semibold">
                                                    {{ $order->order_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($order->user && $order->user->logo)
                                                        <img src="{{ asset('storage/' . $order->user->logo) }}"
                                                             alt="{{ $order->user->company_name ?? $order->user->name }}"
                                                             class="rounded me-2"
                                                             style="width: 35px; height: 35px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded bg-light text-secondary d-flex align-items-center justify-content-center me-2"
                                                             style="width: 35px; height: 35px; font-size: 16px;">
                                                            <i class="ri-store-2-line"></i>
                                                        </div>
                                                    @endif
                                                    <span class="fw-medium">{{ $order->user->company_name ?? $order->user->name ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('products.show', $order->product) }}" class="text-decoration-none">
                                                    {{ Str::limit($order->product->name ?? 'N/A', 30) }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $order->quantity }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    // The distributor's share = their own product price × qty + the shipping
                                                    // they fulfil. The seller/admin markup is NOT part of the distributor payout.
                                                    $distributorShare = ((float) ($order->product->price ?? 0) * $order->quantity)
                                                        + (float) ($order->freight_amount ?? 0);
                                                @endphp
                                                <strong>{{ $order->currency }} {{ number_format($distributorShare, 2) }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $order->getStatusBadgeColor() }}">
                                                    {{ $order->getStatusName() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>{{ $order->created_at->format('d M Y') }}</div>
                                                <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 align-items-center">
                                                    {{-- View Order --}}
                                                    <a href="{{ route('orders.show', $order) }}"
                                                       class="btn btn-sm btn-info"
                                                       title="{{ app()->getLocale() == 'ar' ? 'عرض' : 'View' }}">
                                                        <i class="ri-eye-line"></i>
                                                    </a>

                                                    {{-- Change Status Dropdown --}}
                                                    <div class="dropdown">
                                                        <button type="button"
                                                                class="btn btn-sm btn-warning dropdown-toggle"
                                                                data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                            <i class="ri-refresh-line me-1"></i>
                                                            {{ app()->getLocale() == 'ar' ? 'تغيير الحالة' : 'Change Status' }}
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @if($order->status !== 'pending')
                                                                <li>
                                                                    <a class="dropdown-item" href="#"
                                                                       onclick="updateStatus({{ $order->id }}, 'pending'); return false;">
                                                                        <span class="badge bg-warning me-2">●</span>
                                                                        {{ app()->getLocale() == 'ar' ? 'قيد الانتظار' : 'Pending' }}
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if($order->status !== 'processing')
                                                                <li>
                                                                    <a class="dropdown-item" href="#"
                                                                       onclick="updateStatus({{ $order->id }}, 'processing'); return false;">
                                                                        <span class="badge bg-info me-2">●</span>
                                                                        {{ app()->getLocale() == 'ar' ? 'قيد المعالجة' : 'Processing' }}
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if($order->status !== 'shipped')
                                                                <li>
                                                                    <a class="dropdown-item" href="#"
                                                                       onclick="updateStatus({{ $order->id }}, 'shipped'); return false;">
                                                                        <span class="badge bg-primary me-2">●</span>
                                                                        {{ app()->getLocale() == 'ar' ? 'تم الشحن' : 'Shipped' }}
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if($order->status !== 'delivered')
                                                                <li>
                                                                    <a class="dropdown-item" href="#"
                                                                       onclick="updateStatus({{ $order->id }}, 'delivered'); return false;">
                                                                        <span class="badge bg-success me-2">●</span>
                                                                        {{ app()->getLocale() == 'ar' ? 'تم التوصيل' : 'Delivered' }}
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if($order->status !== 'cancelled')
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item text-danger" href="#"
                                                                       onclick="updateStatus({{ $order->id }}, 'cancelled'); return false;">
                                                                        <span class="badge bg-danger me-2">●</span>
                                                                        {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-3">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri-inbox-line" style="font-size: 4rem; color: #ccc;"></i>
                            <h5 class="mt-3">{{ app()->getLocale() == 'ar' ? 'لا توجد طلبات' : 'No orders found' }}</h5>
                            <p class="text-muted">
                                @if(request('search') || request('status') != 'all')
                                    {{ app()->getLocale() == 'ar' ? 'لا توجد طلبات تطابق معايير البحث' : 'No orders match your search criteria' }}
                                @else
                                    {{ app()->getLocale() == 'ar' ? 'لم يتم طلب أي من منتجاتك بعد' : 'No orders for your products yet' }}
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function updateStatus(orderId, newStatus) {
    const locale = '{{ app()->getLocale() }}';
    const confirmMsg = locale === 'ar'
        ? 'هل أنت متأكد من تغيير حالة الطلب؟'
        : 'Are you sure you want to change the order status?';

    const ok = await confirmAction(confirmMsg, { icon: 'question' });
    if (!ok) return;

    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/distributor/orders/${orderId}/update-status`;

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    // Add status
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = newStatus;
    form.appendChild(statusInput);

    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush

<style>
.card {
    border: none;
    border-radius: 12px;
}

.table-responsive {
    border-radius: 8px;
}

.dropdown-item {
    cursor: pointer;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}
</style>
@endsection
