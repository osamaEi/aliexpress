@extends('dashboard')

@section('title', app()->getLocale() == 'ar' ? 'أرباح الطلبات' : 'Order Profits')

@section('content')
@php $isAr = app()->getLocale() == 'ar'; @endphp

<div class="col-12" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">{{ $isAr ? 'أرباح الطلبات' : 'Order Profits' }}</h4>
            <p class="text-muted small mb-0">
                {{ $isAr
                    ? 'يوضح هذا التقرير ربح كل طلب مقسّماً على: ربح المورد (AliExpress) + عمولة المنصة + حصة البائع'
                    : 'Profit per order broken down into: supplier margin (AliExpress) + platform commission + seller share' }}
            </p>
        </div>
        <a href="{{ route('admin.order-profits.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri-refresh-line me-1"></i>{{ $isAr ? 'تحديث' : 'Refresh' }}
        </a>
    </div>

    {{-- Profit Explanation Banner --}}
    <div class="alert border-0 mb-4 p-0" style="background:transparent;">
        <div class="row g-3">

            <div class="col-md-4">
                <div class="card border-start border-4 border-info h-100">
                    <div class="card-body py-3">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="avatar avatar-sm flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ri-global-line"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-1">
                                    {{ $isAr ? 'ربح المورد (AliExpress)' : 'Supplier Margin (AliExpress)' }}
                                </p>
                                <p class="text-muted small mb-0">
                                    {{ $isAr
                                        ? 'الفرق بين سعر المنتج على AliExpress وسعر البيع بعد تطبيق نسبة هامش الربح المحددة في المنتج'
                                        : 'Difference between AliExpress cost and selling price based on product supplier margin %' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-start border-4 border-success h-100">
                    <div class="card-body py-3">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="avatar avatar-sm flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ri-shield-star-line"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-1">
                                    {{ $isAr ? 'عمولة المنصة (الإدارة)' : 'Platform Commission (Admin)' }}
                                </p>
                                <p class="text-muted small mb-0">
                                    {{ $isAr
                                        ? 'عمولة ثابتة أو نسبة محددة من قِبَل الإدارة لكل فئة منتجات'
                                        : 'Fixed or percentage commission set by admin per product category' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-start border-4 border-warning h-100">
                    <div class="card-body py-3">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="avatar avatar-sm flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ri-store-line"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-1">
                                    {{ $isAr ? 'حصة البائع' : 'Seller Share' }}
                                </p>
                                <p class="text-muted small mb-0">
                                    {{ $isAr
                                        ? 'المبلغ الذي يحصل عليه البائع من قيمة الطلب بناءً على إعدادات أرباحه لكل فئة فرعية'
                                        : 'Amount the seller earns per order based on their subcategory profit settings' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label_ar' => 'إجمالي الإيرادات',  'label_en' => 'Total Revenue',    'value' => $totals->total_revenue ?? 0,            'icon' => 'ri-money-dollar-circle-line', 'color' => 'primary'],
                ['label_ar' => 'تكلفة الشحن',        'label_en' => 'Shipping Cost',     'value' => $totals->total_shipping ?? 0,           'icon' => 'ri-truck-line',              'color' => 'secondary'],
                ['label_ar' => 'ربح المورد',          'label_en' => 'Supplier Margin',   'value' => $totals->total_aliexpress_profit ?? 0,  'icon' => 'ri-global-line',             'color' => 'info'],
                ['label_ar' => 'عمولة المنصة',       'label_en' => 'Platform Comm.',    'value' => $totals->total_admin_profit ?? 0,       'icon' => 'ri-shield-star-line',        'color' => 'success'],
                ['label_ar' => 'حصة البائعين',       'label_en' => 'Sellers\' Share',   'value' => $totals->total_seller_profit ?? 0,      'icon' => 'ri-store-line',              'color' => 'warning'],
                ['label_ar' => 'صافي الربح الكلي',   'label_en' => 'Total Net Profit',  'value' => $totals->total_profit ?? 0,             'icon' => 'ri-wallet-3-line',           'color' => 'danger'],
            ];
        @endphp
        @foreach($cards as $card)
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ $isAr ? $card['label_ar'] : $card['label_en'] }}</p>
                            <h5 class="fw-bold mb-0 text-{{ $card['color'] }}">{{ number_format($card['value'], 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <span class="avatar-initial rounded bg-label-{{ $card['color'] }} p-2">
                            <i class="{{ $card['icon'] }} ri-20px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.order-profits.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" name="search"
                                value="{{ request('search') }}"
                                placeholder="{{ $isAr ? 'رقم الطلب / العميل / AliExpress' : 'Order #, customer, AliExpress ID' }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="all">{{ $isAr ? '— حالة الطلب —' : '— Order Status —' }}</option>
                            @foreach([
                                'pending'    => ['ar'=>'قيد الانتظار',  'en'=>'Pending'],
                                'processing' => ['ar'=>'قيد المعالجة', 'en'=>'Processing'],
                                'placed'     => ['ar'=>'تم التقديم',   'en'=>'Placed'],
                                'shipped'    => ['ar'=>'تم الشحن',     'en'=>'Shipped'],
                                'delivered'  => ['ar'=>'تم التسليم',   'en'=>'Delivered'],
                                'cancelled'  => ['ar'=>'ملغي',         'en'=>'Cancelled'],
                            ] as $val => $lbl)
                            <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>
                                {{ $isAr ? $lbl['ar'] : $lbl['en'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="payment_status">
                            <option value="all">{{ $isAr ? '— حالة الدفع —' : '— Payment —' }}</option>
                            @foreach([
                                'paid'     => ['ar'=>'مدفوع',        'en'=>'Paid'],
                                'pending'  => ['ar'=>'قيد الانتظار', 'en'=>'Pending'],
                                'failed'   => ['ar'=>'فشل',          'en'=>'Failed'],
                                'refunded' => ['ar'=>'مسترد',        'en'=>'Refunded'],
                            ] as $val => $lbl)
                            <option value="{{ $val }}" {{ request('payment_status') == $val ? 'selected' : '' }}>
                                {{ $isAr ? $lbl['ar'] : $lbl['en'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from"
                            value="{{ request('date_from') }}"
                            placeholder="{{ $isAr ? 'من تاريخ' : 'From' }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to"
                            value="{{ request('date_to') }}"
                            placeholder="{{ $isAr ? 'إلى تاريخ' : 'To' }}">
                    </div>
                    <div class="col-md-1 d-flex gap-1">
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

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-semibold">
                {{ $isAr ? 'الطلبات' : 'Orders' }}
            </h6>
            <span class="badge bg-label-primary rounded-pill px-3">
                {{ $orders->total() }} {{ $isAr ? 'طلب' : 'orders' }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                <thead class="table-light">
                    <tr class="text-nowrap">
                        <th class="ps-3">{{ $isAr ? 'الطلب' : 'Order' }}</th>
                        <th>{{ $isAr ? 'المنتج' : 'Product' }}</th>
                        <th>{{ $isAr ? 'البائع' : 'Seller' }}</th>
                        <th>{{ $isAr ? 'الشحن' : 'Shipping' }}</th>
                        <th>{{ $isAr ? 'رقم AliExpress' : 'AliExpress' }}</th>

                        {{-- Profit columns with colored headers --}}
                        <th class="text-end" style="background:#e0f2fe;color:#0369a1;border-top:3px solid #38bdf8;">
                            <i class="ri-global-line me-1"></i>
                            {{ $isAr ? 'ربح المورد' : 'Supplier' }}
                        </th>
                        <th class="text-end" style="background:#dcfce7;color:#15803d;border-top:3px solid #4ade80;">
                            <i class="ri-shield-star-line me-1"></i>
                            {{ $isAr ? 'عمولة المنصة' : 'Platform' }}
                        </th>
                        <th class="text-end" style="background:#fef9c3;color:#a16207;border-top:3px solid #facc15;">
                            <i class="ri-store-line me-1"></i>
                            {{ $isAr ? 'حصة البائع' : 'Seller' }}
                        </th>
                        <th class="text-end pe-3" style="background:#fee2e2;color:#b91c1c;border-top:3px solid #f87171;">
                            <i class="ri-wallet-3-line me-1"></i>
                            {{ $isAr ? 'الصافي' : 'Net' }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    @php
                        $net = ($order->aliexpress_profit ?? 0)
                             + ($order->admin_category_profit ?? 0)
                             + ($order->seller_profit ?? 0);
                        $tracking = $order->shipping?->tracking_number ?? $order->tracking_number;
                    @endphp
                    <tr>

                        {{-- Order --}}
                        <td class="ps-3">
                            <a href="{{ route('admin.orders.index', ['search' => $order->order_number]) }}"
                               class="fw-semibold text-primary text-decoration-none d-block">
                                {{ $order->order_number }}
                            </a>
                            <div class="d-flex gap-1 mt-1 flex-wrap">
                                <span class="badge bg-{{ $order->getStatusBadgeColor() }}" style="font-size:.7rem;">
                                    {{ $order->getStatusName($isAr ? 'ar' : 'en') }}
                                </span>
                                <span class="badge bg-label-{{ $order->getPaymentStatusBadgeColor() }}" style="font-size:.7rem;">
                                    {{ $order->getPaymentStatusName($isAr ? 'ar' : 'en') }}
                                </span>
                            </div>
                            <div class="text-muted mt-1" style="font-size:.72rem;">
                                {{ $order->created_at->format('d M Y · H:i') }}
                            </div>
                        </td>

                        {{-- Product --}}
                        <td>
                            @if($order->product)
                                <span class="d-block fw-medium" title="{{ $order->product->name }}">
                                    {{ Str::limit($order->product->name, 28) }}
                                </span>
                                <small class="text-muted">
                                    {{ number_format($order->unit_price ?? 0, 2) }} {{ $order->currency ?? 'AED' }}
                                    × {{ $order->quantity }}
                                </small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Seller --}}
                        <td>
                            @if($order->user)
                                <span class="fw-medium">{{ $order->user->name }}</span>
                                <div class="text-muted small">{{ $order->customer_name }}</div>
                            @else
                                <span class="text-muted">{{ $order->customer_name ?? '—' }}</span>
                            @endif
                        </td>

                        {{-- Shipping --}}
                        <td>
                            @if($tracking)
                                <code style="font-size:.78rem;background:#f1f5f9;padding:2px 6px;border-radius:4px;display:block;">
                                    {{ $tracking }}
                                </code>
                            @endif
                            @if($order->shipping?->carrier_name)
                                <span class="badge bg-label-secondary mt-1">{{ $order->shipping->carrier_name }}</span>
                            @endif
                            @if(($order->freight_amount ?? 0) > 0)
                                <div class="text-muted mt-1" style="font-size:.75rem;">
                                    {{ $isAr ? 'تكلفة:' : 'Cost:' }}
                                    <strong>{{ number_format($order->freight_amount, 2) }} AED</strong>
                                </div>
                            @endif
                            @if(!$tracking && !$order->shipping?->carrier_name && !($order->freight_amount ?? 0))
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- AliExpress Order ID --}}
                        <td>
                            @if($order->aliexpress_order_id)
                                <span style="font-family:monospace;font-size:.8rem;">{{ $order->aliexpress_order_id }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Supplier Profit (AliExpress margin) --}}
                        <td class="text-end" style="background:#f0f9ff;">
                            @if(($order->aliexpress_profit ?? 0) > 0)
                                <span class="fw-semibold text-info">
                                    +{{ number_format($order->aliexpress_profit, 2) }}
                                </span>
                                <div class="text-muted" style="font-size:.72rem;">AED</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Platform Commission (Admin) --}}
                        <td class="text-end" style="background:#f0fdf4;">
                            @if(($order->admin_category_profit ?? 0) > 0)
                                <span class="fw-semibold text-success">
                                    +{{ number_format($order->admin_category_profit, 2) }}
                                </span>
                                <div class="text-muted" style="font-size:.72rem;">AED</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Seller Share --}}
                        <td class="text-end" style="background:#fefce8;">
                            @if(($order->seller_profit ?? 0) > 0)
                                <span class="fw-semibold text-warning">
                                    +{{ number_format($order->seller_profit, 2) }}
                                </span>
                                <div class="text-muted" style="font-size:.72rem;">AED</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Net Total --}}
                        <td class="text-end pe-3" style="background:#fff5f5;">
                            <span class="fw-bold {{ $net > 0 ? 'text-danger' : 'text-muted' }}" style="font-size:1rem;">
                                {{ $net > 0 ? '+' : '' }}{{ number_format($net, 2) }}
                            </span>
                            <div class="text-muted" style="font-size:.72rem;">AED</div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="ri-file-chart-line text-muted" style="font-size:3rem;opacity:.3;"></i>
                            <p class="text-muted mt-2 mb-3">{{ $isAr ? 'لا توجد طلبات مطابقة' : 'No orders found' }}</p>
                            <a href="{{ route('admin.order-profits.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-refresh-line me-1"></i>{{ $isAr ? 'إعادة تعيين' : 'Reset Filters' }}
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                @if($orders->count() > 0)
                <tfoot>
                    <tr class="fw-semibold" style="background:#f8fafc;font-size:.82rem;border-top:2px solid #e2e8f0;">
                        <td colspan="5" class="ps-3 text-muted">
                            {{ $isAr ? 'مجموع هذه الصفحة' : 'Page subtotal' }}
                            <span class="badge bg-label-secondary ms-1">{{ $orders->count() }}</span>
                        </td>
                        <td class="text-end text-info" style="background:#f0f9ff;">
                            +{{ number_format($orders->sum('aliexpress_profit'), 2) }}
                        </td>
                        <td class="text-end text-success" style="background:#f0fdf4;">
                            +{{ number_format($orders->sum('admin_category_profit'), 2) }}
                        </td>
                        <td class="text-end text-warning" style="background:#fefce8;">
                            +{{ number_format($orders->sum('seller_profit'), 2) }}
                        </td>
                        <td class="text-end pe-3 text-danger" style="background:#fff5f5;">
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

        @if($orders->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
            <small class="text-muted">
                @if($isAr)
                    عرض {{ $orders->firstItem() }}–{{ $orders->lastItem() }} من {{ $orders->total() }} طلب
                @else
                    Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }} orders
                @endif
            </small>
            {{ $orders->links() }}
        </div>
        @endif

    </div>

</div>
@endsection
