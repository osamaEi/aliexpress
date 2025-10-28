@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Orders Management</h5>
            <a href="{{ route('orders.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Create New Order
            </a>
        </div>

        <div class="card-body">
            <!-- Search and Filter Form -->
            <form method="GET" action="{{ route('orders.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by order number, customer name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="placed" {{ request('status') == 'placed' ? 'selected' : '' }}>Placed</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-search-line me-1"></i> Search
                        </button>
                    </div>
                    @if(request('search') || request('status') != 'all')
                        <div class="col-md-2">
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="ri-close-line me-1"></i> Clear
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
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('orders.show', $order) }}" class="text-primary fw-semibold">
                                            {{ $order->order_number }}
                                        </a>
                                        @if($order->aliexpress_order_id)
                                            <br>
                                            <small class="text-muted">AE: {{ $order->aliexpress_order_id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $order->customer_name }}</div>
                                        <small class="text-muted">{{ $order->customer_phone }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('products.show', $order->product) }}" class="text-decoration-none">
                                            {{ $order->product->name }}
                                        </a>
                                    </td>
                                    <td>{{ $order->quantity }}</td>
                                    <td>
                                        <strong>{{ $order->currency }} {{ number_format($order->total_price, 2) }}</strong>
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
                                            <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-info" title="View Details">
                                                <i class="ri-eye-line"></i>
                                            </a>

                                            @if($order->canBePlaced())
                                                <form action="{{ route('orders.place-on-aliexpress', $order) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Place on AliExpress"
                                                            onclick="return confirm('Place this order on AliExpress?')">
                                                        <i class="ri-shopping-cart-line"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($order->canBeCancelled())
                                                <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Cancel Order"
                                                            onclick="return confirm('Cancel this order?')">
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
                    <h5 class="mt-3">No Orders Found</h5>
                    <p class="text-muted">
                        @if(request('search') || request('status') != 'all')
                            No orders match your search criteria.
                        @else
                            Start by creating your first order.
                        @endif
                    </p>
                    <a href="{{ route('orders.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Create New Order
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Orders Statistics -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ App\Models\Order::pending()->count() }}</h3>
                    <small>Pending Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ App\Models\Order::placed()->count() }}</h3>
                    <small>Placed Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ App\Models\Order::shipped()->count() }}</h3>
                    <small>Shipped Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ App\Models\Order::where('status', 'delivered')->count() }}</h3>
                    <small>Delivered Orders</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
