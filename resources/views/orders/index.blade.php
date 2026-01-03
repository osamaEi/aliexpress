@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ __('messages.order_management') }}</h5>
                @if(auth()->user()->user_type === 'admin')
                    <small class="text-muted">
                        <i class="ri-global-line me-1"></i>
                        {{ app()->getLocale() == 'ar' ? 'عرض جميع الطلبات' : 'Viewing all orders' }}
                    </small>
                @else
                    <small class="text-muted">
                        <i class="ri-user-line me-1"></i>
                        {{ app()->getLocale() == 'ar' ? 'عرض طلباتك فقط' : 'Viewing your orders only' }}
                    </small>
                @endif
            </div>
        </div>

        <div class="card-body">
            <!-- Orders Statistics - Moved to Top -->
            @php
                // Filter statistics based on user role
                $statsQuery = auth()->user()->user_type === 'admin'
                    ? App\Models\Order::query()
                    : App\Models\Order::where('user_id', auth()->id());
            @endphp
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ (clone $statsQuery)->where('status', 'pending')->count() }}</h3>
                            <small>{{ __('messages.pending_orders') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ (clone $statsQuery)->where('status', 'placed')->count() }}</h3>
                            <small>{{ __('messages.placed_orders') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ (clone $statsQuery)->where('status', 'shipped')->count() }}</h3>
                            <small>{{ __('messages.shipped_orders') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ (clone $statsQuery)->where('status', 'delivered')->count() }}</h3>
                            <small>{{ __('messages.delivered_orders') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Form -->
            <form method="GET" action="{{ route('orders.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search_orders_placeholder') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>{{ __('messages.all_status') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>{{ __('messages.processing') }}</option>
                            <option value="placed" {{ request('status') == 'placed' ? 'selected' : '' }}>{{ __('messages.placed') }}</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>{{ __('messages.shipped') }}</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('messages.delivered') }}</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>{{ __('messages.failed') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-search-line me-1"></i> {{ __('messages.search') }}
                        </button>
                    </div>
                    @if(request('search') || request('status') != 'all')
                        <div class="col-md-2">
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="ri-close-line me-1"></i> {{ __('messages.clear') }}
                            </a>
                        </div>
                    @endif
                </div>
            </form>

            <!-- Orders Table -->
            @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('messages.order_number') }}</th>
                                @if(auth()->user()->user_type === 'admin')
                                    <th>{{ app()->getLocale() == 'ar' ? 'البائع' : 'Seller' }}</th>
                                @endif
                                <th>{{ __('messages.customer') }}</th>
                                <th>{{ __('messages.product') }}</th>
                                <th>{{ __('messages.quantity') }}</th>
                                <th>{{ __('messages.total') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php
                                    // Get country code for flag
                                    $customerCountry = strtolower($order->shipping_country ?? 'ae');

                                    // Convert currency using Currency model
                                    $displayAmount = $currentCurrency->convertFrom($order->total_price, $order->currency ?? 'USD');
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('orders.show', $order) }}" class="text-primary fw-semibold">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    @if(auth()->user()->user_type === 'admin')
                                        <td>
                                            <div>{{ $order->user->name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                        </td>
                                    @endif
                                    <td>
                                        <div>
                                            {{ $order->customer_name }}
                                            <img src="https://flagcdn.com/w20/{{ $customerCountry }}.png"
                                                 alt="{{ strtoupper($customerCountry) }}"
                                                 class="ms-1"
                                                 style="width:20px;height:14px;object-fit:cover;vertical-align:middle;border-radius:2px;"
                                                 onerror="this.style.display='none'" />
                                        </div>
                                        <small class="text-muted">{{ $order->customer_phone }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('products.show', $order->product) }}" class="text-decoration-none">
                                            {{ $order->product->name }}
                                        </a>
                                        @if($order->product->isAliexpressProduct())
                                            <br><small class="text-muted">
                                                <img src="https://flagcdn.com/w20/cn.png" alt="CN" style="width:16px;height:12px;object-fit:cover;vertical-align:middle;border-radius:2px;" />
                                                {{ __('messages.from_china') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $order->quantity }}</td>
                                    <td style="direction: ltr; text-align: left;">
                                        <span class="fw-bold d-inline-flex align-items-center gap-1" style="color: #561C04;">
                                            <x-session-currency-icon width="16" height="16" />
                                            {{ number_format($displayAmount, 2) }}
                                        </span>
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
                                        <div class="btn-group">
                                            <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-info" title="{{ __('messages.view') }}">
                                                <i class="ri-eye-line"></i>
                                            </a>

                                            <a href="{{ route('products.detail', $order->product) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.view_product') }}">
                                                <i class="ri-product-hunt-line"></i>
                                            </a>

                                            @if($order->canBePlaced())
                                                <form action="{{ route('orders.place-on-aliexpress', $order) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="{{ app()->getLocale() == 'ar' ? 'طلب من المورد' : 'Place on Supplier' }}"
                                                            onclick="return confirm('{{ app()->getLocale() == 'ar' ? 'هل تريد طلب هذا المنتج من المورد؟' : 'Place this order with supplier?' }}')">
                                                        <i class="ri-shopping-cart-line"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($order->canBeCancelled())
                                                <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" title="{{ app()->getLocale() == 'ar' ? 'إلغاء الطلب' : 'Cancel Order' }}"
                                                            onclick="return confirm('{{ app()->getLocale() == 'ar' ? 'هل تريد إلغاء هذا الطلب؟' : 'Cancel this order?' }}')">
                                                        <i class="ri-close-circle-line"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ri-inbox-line" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3">{{ __('messages.no_orders_found') }}</h5>
                    <p class="text-muted">
                        @if(request('search') || request('status') != 'all')
                            {{ app()->getLocale() == 'ar' ? 'لا توجد طلبات تطابق معايير البحث' : 'No orders match your search criteria' }}
                        @else
                            {{ app()->getLocale() == 'ar' ? 'ابدأ بإنشاء طلبك الأول' : 'Start by creating your first order' }}
                        @endif
                    </p>
                    <!-- <a href="{{ route('orders.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> {{ __('messages.create_order') }}
                    </a> -->
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
