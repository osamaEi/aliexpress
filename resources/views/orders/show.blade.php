@extends('dashboard')

@section('content')
<div class="col-12">
    <!-- Order Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-2">Order {{ $order->order_number }}</h4>
                    <span class="badge bg-{{ $order->getStatusBadgeColor() }} fs-6">{{ $order->getStatusName() }}</span>
                    @if($order->aliexpress_order_id)
                        <p class="text-muted mt-2 mb-0">Supplier Order ID: <strong>{{ $order->aliexpress_order_id }}</strong></p>
                    @endif
                </div>
                <div class="btn-group">
                    @if($order->canBePlaced() && empty($order->aliexpress_order_id))
                        <div class="alert alert-info mb-2" style="font-size: 0.875rem;">
                            <i class="ri-information-line me-1"></i>
                            This order will be automatically placed on AliExpress. If it doesn't happen automatically, you can use the button below.
                        </div>
                        <form action="{{ route('orders.place-on-aliexpress', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Place this order with supplier?\n\nNote: Orders are automatically placed. Only click if automatic placement failed.')">
                                <i class="ri-shopping-cart-line me-1"></i> Manually Place with Supplier
                            </button>
                        </form>
                    @endif

                    @if($order->aliexpress_order_id && $order->status == 'placed')
                        <form action="{{ route('orders.update-tracking', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info">
                                <i class="ri-refresh-line me-1"></i> Update Tracking
                            </button>
                        </form>
                    @endif

                    @if($order->canBeCancelled())
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Cancel this order?')">
                                <i class="ri-close-circle-line me-1"></i> Cancel
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Product & Pricing -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Order Items</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        @if($order->product->images && count($order->product->images) > 0)
                            <img src="{{ $order->product->images[0] }}" alt="{{ $order->product->name }}" class="me-3" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                        @endif
                        <div class="flex-grow-1">
                            <h6>{{ $order->product->name }}</h6>
                            <p class="text-muted mb-2">Quantity: {{ $order->quantity }}</p>
                            <p class="mb-0">
                                <strong>Unit Price:</strong> {{ $order->currency }} {{ number_format($order->unit_price, 2) }}<br>
                                <strong>Total:</strong> <span class="text-primary fs-5">{{ $order->currency }} {{ number_format($order->total_price, 2) }}</span>
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('products.show', $order->product) }}" class="btn btn-sm btn-outline-primary">View Product</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Name:</strong> {{ $order->customer_name }}</p>
                            @if($order->customer_email)
                                <p class="mb-2"><strong>Email:</strong> {{ $order->customer_email }}</p>
                            @endif
                            <p class="mb-0"><strong>Phone:</strong> +{{ $order->phone_country }} {{ $order->customer_phone }}</p>
                        </div>
                    </div>
                    @if($order->customer_notes)
                        <div class="mt-3">
                            <strong>Customer Notes:</strong>
                            <p class="mb-0 mt-1">{{ $order->customer_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Shipping Information</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1">{{ $order->shipping_address }}</p>
                    @if($order->shipping_address2)
                        <p class="mb-1">{{ $order->shipping_address2 }}</p>
                    @endif
                    <p class="mb-1">{{ $order->shipping_city }}{{ $order->shipping_province ? ', ' . $order->shipping_province : '' }} {{ $order->shipping_zip }}</p>
                    <p class="mb-0"><strong>{{ $order->shipping_country }}</strong></p>

                    @if($order->tracking_number)
                        <div class="mt-3 p-3 bg-light rounded">
                            <p class="mb-1"><strong>Tracking Number:</strong> {{ $order->tracking_number }}</p>
                            @if($order->shipping_method)
                                <p class="mb-0"><strong>Shipping Method:</strong> {{ $order->shipping_method }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Order Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <i class="ri-checkbox-circle-line text-success"></i>
                            <div>
                                <strong>Order Created</strong>
                                <p class="text-muted small mb-0">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>

                        @if($order->placed_at)
                            <div class="timeline-item">
                                <i class="ri-shopping-cart-line text-primary"></i>
                                <div>
                                    <strong>Placed with Supplier</strong>
                                    <p class="text-muted small mb-0">{{ $order->placed_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($order->shipped_at)
                            <div class="timeline-item">
                                <i class="ri-truck-line text-info"></i>
                                <div>
                                    <strong>Shipped</strong>
                                    <p class="text-muted small mb-0">{{ $order->shipped_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($order->delivered_at)
                            <div class="timeline-item">
                                <i class="ri-check-double-line text-success"></i>
                                <div>
                                    <strong>Delivered</strong>
                                    <p class="text-muted small mb-0">{{ $order->delivered_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($order->admin_notes)
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Admin Notes:</strong>
                            <p class="mb-0 mt-1 small">{{ $order->admin_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
    display: flex;
    gap: 15px;
}

.timeline-item i {
    font-size: 20px;
    flex-shrink: 0;
}
</style>
@endsection
