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
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small">Total Revenue</h6>
                            <h5 class="mb-0 text-primary">{{ number_format($totalRevenue, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="text-primary">
                            <i class="ri-money-dollar-circle-line" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small">Total Cost</h6>
                            <h5 class="mb-0 text-danger">{{ number_format($totalCost, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="text-danger">
                            <i class="ri-shopping-cart-line" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small">Admin Profit</h6>
                            <h5 class="mb-0 text-success">{{ number_format($totalAdminProfit, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="text-success">
                            <i class="ri-admin-line" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small">Seller Profit</h6>
                            <h5 class="mb-0 text-info">{{ number_format($totalSellerProfit, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="text-info">
                            <i class="ri-store-line" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-secondary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small">Shipping Cost</h6>
                            <h5 class="mb-0 text-secondary">{{ number_format($totalShippingCost, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="text-secondary">
                            <i class="ri-truck-line" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small">Total Profit</h6>
                            <h5 class="mb-0 text-warning">{{ number_format($totalProfit, 2) }}</h5>
                            <small class="text-muted">AED</small>
                        </div>
                        <div class="text-warning">
                            <i class="ri-wallet-line" style="font-size: 1.8rem;"></i>
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
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Seller</th>
                                    <th>Date</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">AliEx Price</th>
                                    <th class="text-end">Shipping</th>
                                    <th class="text-end">Admin Profit</th>
                                    <th class="text-end">Seller Profit</th>
                                    <th class="text-end">Total Cost</th>
                                    <th class="text-end">Final Price</th>
                                    <th class="text-end">Total Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($profits as $profit)
                                <tr>
                                    <td>
                                        @if($profit->order)
                                            <strong>{{ $profit->order->order_number }}</strong>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($profit->order)
                                            {{ $profit->order->customer_name }}<br>
                                            <small class="text-muted">{{ $profit->order->customer_email }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($profit->product)
                                            {{ Str::limit($profit->product->name, 30) }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($profit->order && $profit->order->user)
                                            {{ $profit->order->user->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $profit->created_at->format('Y-m-d') }}<br>
                                        <small class="text-muted">{{ $profit->created_at->format('H:i') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-secondary">{{ $profit->quantity }}</span>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($profit->aliexpress_price, 2) }}
                                        <small class="text-muted">{{ $profit->currency }}</small>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($profit->shipping_price, 2) }}
                                        <small class="text-muted">{{ $profit->currency }}</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">
                                            {{ number_format($profit->admin_profit, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info">
                                            {{ number_format($profit->seller_profit, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($profit->total_cost, 2) }}</strong>
                                        <small class="text-muted">{{ $profit->currency }}</small>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-primary">{{ number_format($profit->final_price, 2) }}</strong>
                                        <small class="text-muted">{{ $profit->currency }}</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-warning text-dark">
                                            {{ number_format($profit->total_profit, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center py-4">
                                        <i class="ri-inbox-line" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">No profit records found matching your criteria</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($profits->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Showing {{ $profits->firstItem() }} to {{ $profits->lastItem() }} of {{ $profits->total() }} profit records
                        </div>
                        <div>
                            {{ $profits->links() }}
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
