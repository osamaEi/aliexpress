@extends('dashboard')

@section('title', 'Order Profit Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="ri-money-dollar-circle-line me-2"></i>
                        Order Profit Management
                    </h4>
                    <p class="mb-0 mt-2 small">
                        View and analyze profit from orders (AliExpress, Admin Category, and Seller profits)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">AliExpress Profit</h6>
                            <h4 class="mb-0 text-primary">{{ number_format($totalAliexpressProfit, 2) }} AED</h4>
                        </div>
                        <div class="text-primary">
                            <i class="ri-aliexpress-line" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Admin Category Profit</h6>
                            <h4 class="mb-0 text-success">{{ number_format($totalAdminCategoryProfit, 2) }} AED</h4>
                        </div>
                        <div class="text-success">
                            <i class="ri-admin-line" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Seller Profit</h6>
                            <h4 class="mb-0 text-info">{{ number_format($totalSellerProfit, 2) }} AED</h4>
                        </div>
                        <div class="text-info">
                            <i class="ri-store-line" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Profit</h6>
                            <h4 class="mb-0 text-warning">{{ number_format($totalProfit, 2) }} AED</h4>
                        </div>
                        <div class="text-warning">
                            <i class="ri-wallet-line" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.order-profits.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="Order number, customer name..." value="{{ request('search') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="status" class="form-label">Order Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="placed" {{ request('status') == 'placed' ? 'selected' : '' }}>Placed</option>
                                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="all" {{ request('payment_status') == 'all' ? 'selected' : '' }}>All</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-search-line"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Profit Details</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Order Number</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Seller</th>
                                    <th>Order Date</th>
                                    <th>Total Amount</th>
                                    <th class="text-center">AliExpress Profit</th>
                                    <th class="text-center">Admin Profit</th>
                                    <th class="text-center">Seller Profit</th>
                                    <th class="text-center">Total Profit</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                    </td>
                                    <td>
                                        {{ $order->customer_name }}<br>
                                        <small class="text-muted">{{ $order->customer_email }}</small>
                                    </td>
                                    <td>
                                        @if($order->product)
                                            {{ Str::limit($order->product->name, 30) }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->user)
                                            {{ $order->user->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->placed_at)
                                            {{ $order->placed_at->format('Y-m-d') }}<br>
                                            <small class="text-muted">{{ $order->placed_at->format('H:i') }}</small>
                                        @else
                                            <span class="text-muted">Not placed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ number_format($order->total_amount, 2) }}</strong> {{ $order->currency }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">
                                            {{ number_format($order->aliexpress_profit, 2) }} AED
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            {{ number_format($order->admin_category_profit, 2) }} AED
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">
                                            {{ number_format($order->seller_profit, 2) }} AED
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">
                                            {{ number_format($order->getTotalProfit(), 2) }} AED
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $order->getStatusBadgeColor() }}">
                                            {{ $order->getStatusName() }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">
                                        <i class="ri-inbox-line" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">No orders found matching your criteria</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($orders->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                        </div>
                        <div>
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .border-4 {
        border-width: 4px !important;
    }

    .table th {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .table td {
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .badge {
        font-weight: 500;
        padding: 0.4em 0.6em;
    }
</style>
@endsection
