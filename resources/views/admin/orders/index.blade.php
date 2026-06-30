@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">

    {{-- Page Header --}}
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="mb-1">{{ __('messages.order_management') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.manage_orders') }}</p>
        </div>
        <form method="POST" action="{{ route('admin.orders.bulk-sync') }}" id="bulk-sync-form">
            @csrf
            <button type="submit" class="btn btn-primary" id="bulk-sync-btn" disabled>
                <i class="ri-refresh-line me-1"></i>{{ __('messages.sync_selected') }}
            </button>
        </form>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => $ar ? 'كل الطلبات' : 'All Orders', 'value' => $stats['total'], 'icon' => 'ri-shopping-bag-3-line', 'color' => 'primary', 'status' => ''],
                ['label' => $ar ? 'قيد الانتظار' : 'Pending', 'value' => $stats['pending'], 'icon' => 'ri-time-line', 'color' => 'warning', 'status' => 'pending'],
                ['label' => $ar ? 'تم الشحن' : 'Shipped', 'value' => $stats['shipped'], 'icon' => 'ri-truck-line', 'color' => 'info', 'status' => 'shipped'],
                ['label' => $ar ? 'تم التوصيل' : 'Delivered', 'value' => $stats['delivered'], 'icon' => 'ri-checkbox-circle-line', 'color' => 'success', 'status' => 'delivered'],
                ['label' => $ar ? 'ملغي' : 'Cancelled', 'value' => $stats['cancelled'], 'icon' => 'ri-close-circle-line', 'color' => 'danger', 'status' => 'cancelled'],
            ];
        @endphp
        @foreach($cards as $c)
            <div class="col-6 col-md">
                <a href="{{ route('admin.orders.index', array_filter(['status' => $c['status']])) }}"
                   class="card stat-card h-100 text-decoration-none {{ request('status') === $c['status'] && $c['status'] !== '' ? 'stat-active' : '' }}">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <span class="stat-icon bg-label-{{ $c['color'] }}"><i class="{{ $c['icon'] }}"></i></span>
                        <div>
                            <h5 class="mb-0">{{ number_format($c['value']) }}</h5>
                            <small class="text-muted">{{ $c['label'] }}</small>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Filter bar --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">{{ $ar ? 'بحث' : 'Search' }}</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="{{ $ar ? 'رقم الطلب، العميل، البريد...' : 'Order #, customer, email...' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ __('messages.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ $ar ? 'الكل' : 'All' }}</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                                {{ ucfirst($st) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ $ar ? 'المزامنة' : 'Sync' }}</label>
                    <select name="sync" class="form-select">
                        <option value="">{{ $ar ? 'الكل' : 'All' }}</option>
                        <option value="synced" {{ request('sync') === 'synced' ? 'selected' : '' }}>{{ $ar ? 'متزامن' : 'Synced' }}</option>
                        <option value="not_synced" {{ request('sync') === 'not_synced' ? 'selected' : '' }}>{{ $ar ? 'غير متزامن' : 'Not synced' }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ $ar ? 'الدولة' : 'Country' }}</label>
                    <select name="country" class="form-select">
                        <option value="">{{ $ar ? 'الكل' : 'All' }}</option>
                        @foreach($countries as $co)
                            <option value="{{ $co }}" {{ request('country') === $co ? 'selected' : '' }}>{{ strtoupper($co) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ $ar ? 'من تاريخ' : 'From' }}</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ $ar ? 'إلى تاريخ' : 'To' }}</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label small mb-1">{{ $ar ? 'ترتيب' : 'Sort' }}</label>
                    <select name="sort" class="form-select">
                        <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>{{ $ar ? 'الأحدث' : 'Latest' }}</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>{{ $ar ? 'الأقدم' : 'Oldest' }}</option>
                        <option value="amount_high" {{ request('sort') === 'amount_high' ? 'selected' : '' }}>{{ $ar ? 'الأعلى قيمة' : 'Amount ↓' }}</option>
                        <option value="amount_low" {{ request('sort') === 'amount_low' ? 'selected' : '' }}>{{ $ar ? 'الأقل قيمة' : 'Amount ↑' }}</option>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ri-filter-3-line me-1"></i>{{ $ar ? 'تطبيق' : 'Apply' }}</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary"><i class="ri-refresh-line me-1"></i>{{ $ar ? 'إعادة تعيين' : 'Reset' }}</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Orders table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.total_orders') }}: {{ $orders->total() }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                            <th>{{ __('messages.order_number') }}</th>
                            <th>{{ __('messages.customer') }}</th>
                            <th>{{ __('messages.product') }}</th>
                            <th>{{ $ar ? 'الموزع' : 'Distributor' }}</th>
                            <th>{{ $ar ? 'وجهة الشحن' : 'Ship To' }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.total') }}</th>
                            <th>{{ $ar ? 'المزامنة' : 'Sync' }}</th>
                            <th class="text-{{ $ar ? 'start' : 'end' }}">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        @php
                            $statusMap = [
                                'pending'    => ['warning', $ar ? 'قيد الانتظار' : 'Pending'],
                                'processing' => ['info',    $ar ? 'قيد المعالجة' : 'Processing'],
                                'placed'     => ['primary', $ar ? 'تم التقديم' : 'Placed'],
                                'paid'       => ['success', $ar ? 'مدفوع' : 'Paid'],
                                'shipped'    => ['info',    $ar ? 'تم الشحن' : 'Shipped'],
                                'delivered'  => ['success', $ar ? 'تم التوصيل' : 'Delivered'],
                                'cancelled'  => ['danger',  $ar ? 'ملغي' : 'Cancelled'],
                            ];
                            [$stColor, $stLabel] = $statusMap[$order->status] ?? ['secondary', ucfirst($order->status)];
                            $cur = $order->currency ?: 'AED';
                        @endphp
                        <tr>
                            <td>
                                @if($order->status === 'pending' && !$order->aliexpress_order_id)
                                    <input type="checkbox" class="form-check-input order-checkbox" name="order_ids[]" value="{{ $order->id }}" form="bulk-sync-form">
                                @endif
                            </td>
                            <td>
                                <strong>{{ $order->order_number }}</strong>
                                <div class="text-muted small">{{ $order->created_at->format('Y-m-d H:i') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($order->user && $order->user->profile_image)
                                        <img src="{{ asset('storage/' . $order->user->profile_image) }}" alt="{{ $order->user->name }}"
                                             class="rounded-circle me-2" width="38" height="38" style="object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                             style="width: 38px; height: 38px; font-weight: bold;">
                                            {{ strtoupper(substr($order->user->name ?? '?', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $order->user->name ?? '—' }}</strong>
                                        <div class="text-muted small">{{ $order->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            {{-- Product --}}
                            <td>
                                @if($order->product)
                                    <div class="d-flex align-items-center">
                                        @php
                                            $pimg = $order->product->photo
                                                ? asset('storage/' . $order->product->photo)
                                                : (($order->product->images && count($order->product->images)) ? $order->product->images[0] : null);
                                        @endphp
                                        @if($pimg)
                                            <img src="{{ $pimg }}" class="rounded me-2" width="34" height="34" style="object-fit:cover;">
                                        @endif
                                        <div>
                                            <a href="{{ route('orders.show', $order) }}" class="text-decoration-none fw-semibold d-block text-truncate" style="max-width:160px;">
                                                {{ $ar && $order->product->name_ar ? $order->product->name_ar : $order->product->name }}
                                            </a>
                                            <small class="text-muted">×{{ $order->quantity }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            {{-- Distributor --}}
                            <td>
                                @php
                                    $dist = $order->product
                                        ? $order->product->assignedUsers->firstWhere('user_type', 'distributor')
                                        : null;
                                @endphp
                                @if($dist)
                                    <div class="text-truncate" style="max-width:140px;">
                                        <i class="ri-store-2-line me-1" style="color:#561C04;"></i>
                                        {{ $dist->company_name ?: ($dist->store_name ?: $dist->name) }}
                                    </div>
                                @else
                                    <span class="badge bg-label-secondary">🇨🇳 {{ $ar ? 'الصين' : 'China' }}</span>
                                @endif
                            </td>
                            {{-- Ship to (country / city / district) --}}
                            <td>
                                @if($order->shipping_country || $order->shipping_city)
                                    @if($order->shipping_country)
                                        <span class="fw-semibold">
                                            <img src="https://flagcdn.com/w20/{{ strtolower($order->shipping_country) }}.png"
                                                 style="width:18px;height:13px;object-fit:cover;border-radius:2px;vertical-align:middle;"
                                                 onerror="this.style.display='none'"> {{ strtoupper($order->shipping_country) }}
                                        </span>
                                    @endif
                                    <div class="text-muted small">
                                        {{ optional($order->shippingCity)->localized_name ?: $order->shipping_city }}
                                        @if($order->shippingDistrict) / {{ $order->shippingDistrict->localized_name }}@endif
                                    </div>
                                    @if($order->freight_amount > 0)
                                        <small class="text-success"><i class="ri-truck-line me-1"></i>{{ number_format($order->freight_amount, 2) }} {{ $cur }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span></td>
                            <td class="fw-semibold text-nowrap">{{ number_format($order->total_amount, 2) }} <small class="text-muted">{{ $cur }}</small></td>
                            <td>
                                @if($order->aliexpress_order_id)
                                    <span class="badge bg-label-success"><i class="ri-check-line me-1"></i>{{ __('messages.synced') }}</span>
                                    <div><code class="small">{{ $order->aliexpress_order_id }}</code></div>
                                @else
                                    <span class="badge bg-label-secondary"><i class="ri-close-line me-1"></i>{{ __('messages.not_synced') }}</span>
                                @endif
                            </td>
                            <td class="text-{{ $ar ? 'start' : 'end' }}">
                                <div class="d-inline-flex gap-1">
                                    {{-- View --}}
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-icon btn-outline-info" title="{{ $ar ? 'عرض' : 'View' }}">
                                        <i class="ri-eye-line"></i>
                                    </a>

                                    {{-- Change status --}}
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown" title="{{ $ar ? 'تغيير الحالة' : 'Change status' }}">
                                            <i class="ri-refresh-line"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @foreach(['pending','placed','paid','shipped','delivered','cancelled'] as $opt)
                                                @if($order->status !== $opt)
                                                    @php [$oc, $ol] = $statusMap[$opt] ?? ['secondary', ucfirst($opt)]; @endphp
                                                    <li>
                                                        <form method="POST" action="{{ route('admin.orders.update-status', $order) }}"
                                                              data-confirm="{{ $ar ? 'تغيير حالة الطلب إلى '.$ol.'؟' : 'Change status to '.$ol.'?' }}">
                                                            @csrf
                                                            <input type="hidden" name="status" value="{{ $opt }}">
                                                            <button type="submit" class="dropdown-item">
                                                                <span class="badge bg-{{ $oc }} me-2">●</span>{{ $ol }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>

                                    {{-- Sync with AliExpress --}}
                                    @if(!$order->aliexpress_order_id && $order->status === 'pending')
                                        <form method="POST" action="{{ route('admin.orders.sync', $order) }}" class="d-inline"
                                              data-confirm="{{ $ar ? 'مزامنة هذا الطلب مع ؟' : 'Sync this order with AliExpress?' }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-primary" title="{{ __('messages.sync_with_aliexpress') }}">
                                                <i class="ri-cloud-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="ri-inbox-line d-block mb-2" style="font-size:2.5rem;color:#ccc;"></i>
                                {{ __('messages.no_orders_yet') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Select All
    document.getElementById('select-all')?.addEventListener('change', function () {
        document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = this.checked);
        updateBulkSyncButton();
    });
    document.querySelectorAll('.order-checkbox').forEach(cb =>
        cb.addEventListener('change', updateBulkSyncButton));

    function updateBulkSyncButton() {
        const checked = document.querySelectorAll('.order-checkbox:checked').length;
        const btn = document.getElementById('bulk-sync-btn');
        const base = '{{ __('messages.sync_selected') }}';
        btn.disabled = checked === 0;
        btn.innerHTML = '<i class="ri-refresh-line me-1"></i> ' + base + (checked > 0 ? ` (${checked})` : '');
    }

    // Bulk sync: validate + confirm via SweetAlert (falls back gracefully)
    document.getElementById('bulk-sync-form')?.addEventListener('submit', async function (e) {
        if (this.dataset.confirmed === '1') return;
        e.preventDefault();
        const checked = document.querySelectorAll('.order-checkbox:checked').length;
        const isAr = {{ $ar ? 'true' : 'false' }};
        if (checked === 0) {
            window.showError ? showError(isAr ? 'اختر طلبات للمزامنة' : 'Select orders to sync') : alert('{{ __('messages.select_orders_to_sync') }}');
            return;
        }
        const ok = await confirmAction(
            (isAr ? 'مزامنة ' : 'Sync ') + checked + (isAr ? ' طلب؟' : ' orders?')
        );
        if (ok) { this.dataset.confirmed = '1'; this.submit(); }
    });
</script>
@endpush

<style>
.stat-card { transition: all .2s; border: 1px solid #f0eae6; }
.stat-card:hover { box-shadow: 0 4px 14px rgba(86,28,4,.12); transform: translateY(-2px); }
.stat-card.stat-active { border-color: #561C04; box-shadow: 0 4px 14px rgba(86,28,4,.18); }
.stat-card .stat-icon {
    width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center; font-size: 22px;
}
.btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
.dropdown-toggle.hide-arrow::after { display: none; }
.dropdown-item form { margin: 0; }
</style>
@endsection
