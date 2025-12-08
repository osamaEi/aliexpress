<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipping;
use App\Services\AliExpressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingTrackingController extends Controller
{
    protected $aliexpressService;

    public function __construct(AliExpressService $aliexpressService)
    {
        $this->aliexpressService = $aliexpressService;
    }

    /**
     * Display shipping tracking overview
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'product'])
            ->whereNotNull('aliexpress_order_id'); // Only orders placed on AliExpress

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by seller
        if ($request->has('seller_id') && $request->seller_id) {
            $query->where('user_id', $request->seller_id);
        }

        // Search by tracking number or order number
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhere('aliexpress_order_id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Filter by date
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        $orders = $query->latest('created_at')->paginate(20);

        // Get statistics
        $stats = [
            'total' => Order::whereNotNull('aliexpress_order_id')->count(),
            'pending' => Order::whereNotNull('aliexpress_order_id')
                ->whereIn('status', ['pending', 'placed'])
                ->count(),
            'shipped' => Order::whereNotNull('aliexpress_order_id')
                ->where('status', 'shipped')
                ->count(),
            'delivered' => Order::whereNotNull('aliexpress_order_id')
                ->where('status', 'delivered')
                ->count(),
        ];

        return view('admin.shipping.index', compact('orders', 'stats'));
    }

    /**
     * Show detailed tracking for a specific shipping
     */
    public function show(Shipping $shipping)
    {
        $shipping->load(['order.user', 'order.product']);

        return view('admin.shipping.show', compact('shipping'));
    }

    /**
     * Sync tracking information for a specific order
     */
    public function syncTracking(Order $order)
    {
        try {
            if (!$order->aliexpress_order_id) {
                return redirect()->back()->with('error', 'This order has not been placed on AliExpress yet.');
            }

            $oldStatus = $order->status;

            // Get order details with full timeline information
            $orderDetails = $this->aliexpressService->getOrderDetails($order->aliexpress_order_id);

            if ($orderDetails) {
                // Extract order status from AliExpress
                $aliexpressStatus = $orderDetails['order_status'] ?? null;

                if ($aliexpressStatus) {
                    // Map AliExpress status to our internal status
                    $newStatus = $this->mapAliExpressStatus($aliexpressStatus);

                    if ($newStatus && $newStatus !== $oldStatus) {
                        $order->status = $newStatus;

                        // Update timestamps based on status
                        $this->updateOrderTimestamps($order, $newStatus);

                        // Dispatch event for status change
                        event(new \App\Events\OrderStatusUpdated($order, $oldStatus, $newStatus));
                    }
                }

                // Extract logistics information
                if (isset($orderDetails['logistics_status'])) {
                    $order->shipping_method = $orderDetails['logistics_service_name'] ?? $order->shipping_method;
                }
            }

            // Get tracking data from AliExpress
            $trackingData = $this->aliexpressService->getOrderShippingInfo($order->aliexpress_order_id);

            if ($trackingData) {
                // Update order with tracking info
                $order->tracking_number = $trackingData['tracking_number'] ?? $order->tracking_number;
                $order->shipping_method = $trackingData['shipping_method'] ?? $order->shipping_method;

                // Update order status based on shipping status
                if (isset($trackingData['status'])) {
                    $shippingStatus = $trackingData['status'];
                    $statusChanged = false;

                    if ($shippingStatus === 'delivered' && $order->status !== 'delivered') {
                        $order->status = 'delivered';
                        if (empty($order->delivered_at)) {
                            $order->delivered_at = now();
                        }
                        // Ensure shipped_at is set
                        if (empty($order->shipped_at)) {
                            $order->shipped_at = now()->subDays(3);
                        }
                        $statusChanged = true;
                    } elseif (in_array($shippingStatus, ['in_transit', 'shipped']) && in_array($order->status, ['placed', 'paid'])) {
                        $order->status = 'shipped';
                        if (empty($order->shipped_at)) {
                            $order->shipped_at = now();
                        }
                        $statusChanged = true;
                    }

                    if ($statusChanged && $oldStatus !== $order->status) {
                        event(new \App\Events\OrderStatusUpdated($order, $oldStatus, $order->status));
                    }
                }

                // Create or update Shipping record
                \App\Models\Shipping::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'tracking_number' => $trackingData['tracking_number'] ?? null,
                        'carrier_name' => $trackingData['carrier_name'] ?? null,
                        'carrier_code' => $trackingData['carrier_code'] ?? null,
                        'status' => $trackingData['status'] ?? 'pending',
                        'tracking_events' => $trackingData['tracking_events'] ?? [],
                        'shipped_at' => $order->shipped_at,
                        'delivered_at' => $order->delivered_at,
                        'last_synced_at' => now(),
                    ]
                );
            }

            $order->save();

            Log::info('Admin synced shipping tracking', [
                'admin_id' => auth()->id(),
                'order_id' => $order->id,
                'aliexpress_order_id' => $order->aliexpress_order_id,
                'old_status' => $oldStatus,
                'new_status' => $order->status,
                'tracking_number' => $order->tracking_number
            ]);

            return redirect()->back()->with('success', 'Tracking information and order status updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to sync tracking', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to sync tracking: ' . $e->getMessage());
        }
    }

    /**
     * Map AliExpress order status to internal status
     */
    protected function mapAliExpressStatus(string $aliexpressStatus): ?string
    {
        $statusMap = [
            'PLACE_ORDER_SUCCESS' => 'placed',
            'IN_CANCEL' => 'processing',
            'WAIT_SELLER_SEND_GOODS' => 'paid',
            'SELLER_PART_SEND_GOODS' => 'paid',
            'WAIT_BUYER_ACCEPT_GOODS' => 'shipped',
            'FUND_PROCESSING' => 'delivered',
            'FINISH' => 'delivered',
            'ORDER_PLACED' => 'placed',
            'ORDER_CONFIRMED' => 'paid',
            'PAYMENT_CONFIRMED' => 'paid',
            'ORDER_SHIPPED' => 'shipped',
            'ORDER_DELIVERED' => 'delivered',
            'SHIPPED' => 'shipped',
            'DELIVERED' => 'delivered',
        ];

        return $statusMap[strtoupper($aliexpressStatus)] ?? null;
    }

    /**
     * Update order timestamps based on status
     */
    protected function updateOrderTimestamps(Order $order, string $status): void
    {
        switch ($status) {
            case 'placed':
                if (empty($order->placed_at)) {
                    $order->placed_at = now();
                }
                break;

            case 'shipped':
                if (empty($order->shipped_at)) {
                    $order->shipped_at = now();
                }
                break;

            case 'delivered':
                if (empty($order->delivered_at)) {
                    $order->delivered_at = now();
                }
                // Ensure shipped_at is set
                if (empty($order->shipped_at)) {
                    $order->shipped_at = now()->subDays(3);
                }
                break;
        }
    }

    /**
     * Sync all active shipments
     */
    public function syncAll()
    {
        try {
            // Get all orders with AliExpress IDs that are not delivered
            $orders = Order::whereNotNull('aliexpress_order_id')
                ->whereIn('status', ['placed', 'shipped'])
                ->get();

            if ($orders->isEmpty()) {
                return redirect()->back()->with('warning', 'No orders to sync.');
            }

            $synced = 0;
            $failed = 0;

            foreach ($orders as $order) {
                try {
                    $trackingData = $this->aliexpressService->getOrderShippingInfo($order->aliexpress_order_id);

                    if ($trackingData) {
                        $order->update([
                            'tracking_number' => $trackingData['tracking_number'] ?? null,
                            'shipping_method' => $trackingData['shipping_method'] ?? null,
                        ]);

                        // Update status if delivered
                        if (isset($trackingData['status']) && $trackingData['status'] === 'delivered') {
                            $order->update([
                                'status' => 'delivered',
                                'delivered_at' => now(),
                            ]);
                        } elseif (isset($trackingData['status']) && $trackingData['status'] === 'shipped' && $order->status === 'placed') {
                            $order->update([
                                'status' => 'shipped',
                                'shipped_at' => now(),
                            ]);
                        }

                        $synced++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Failed to sync order shipping', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Add delay to avoid rate limiting
                usleep(500000); // 0.5 second delay
            }

            Log::info('Admin synced all shipping tracking', [
                'admin_id' => auth()->id(),
                'total_orders' => $orders->count(),
                'synced' => $synced,
                'failed' => $failed
            ]);

            $message = "Successfully synced {$synced} shipments.";
            if ($failed > 0) {
                $message .= " {$failed} failed.";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to sync all shipments', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to sync shipments: ' . $e->getMessage());
        }
    }
}
