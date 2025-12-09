<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipping;
use App\Services\AliExpressService;
use App\Events\OrderStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    protected $aliexpressService;

    public function __construct(AliExpressService $aliexpressService)
    {
        $this->aliexpressService = $aliexpressService;
    }

    /**
     * Display seller's shipping tracking overview
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Order::with(['product'])
            ->where('user_id', $user->id)
            ->whereNotNull('aliexpress_order_id'); // Only orders placed on AliExpress

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by tracking number or order number
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhere('aliexpress_order_id', 'like', "%{$search}%");
            });
        }

        // Filter by date
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        $orders = $query->latest('created_at')->paginate(15);

        // Get statistics for this seller
        $stats = [
            'total' => Order::where('user_id', $user->id)
                ->whereNotNull('aliexpress_order_id')
                ->count(),
            'pending' => Order::where('user_id', $user->id)
                ->whereNotNull('aliexpress_order_id')
                ->whereIn('status', ['pending', 'placed'])
                ->count(),
            'shipped' => Order::where('user_id', $user->id)
                ->whereNotNull('aliexpress_order_id')
                ->where('status', 'shipped')
                ->count(),
            'delivered' => Order::where('user_id', $user->id)
                ->whereNotNull('aliexpress_order_id')
                ->where('status', 'delivered')
                ->count(),
        ];

        return view('seller.shipping.index', compact('orders', 'stats'));
    }

    /**
     * Show detailed tracking for a specific shipping
     */
    public function show(Shipping $shipping)
    {
        // Ensure seller can only view their own shipments
        if ($shipping->order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        $shipping->load(['order.product']);

        return view('seller.shipping.show', compact('shipping'));
    }

    /**
     * Sync tracking information and status for seller's order
     */
    public function sync(Order $order)
    {
        // Ensure seller can only sync their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        try {
            if (!$order->aliexpress_order_id) {
                return redirect()->back()->with('error', 'This order has not been placed on AliExpress yet.');
            }

            $oldStatus = $order->status;
            $statusChanged = false;

            // Get order details from AliExpress
            $orderDetails = $this->aliexpressService->getOrderDetails($order->aliexpress_order_id);

            if ($orderDetails) {
                // Extract order status
                $aliexpressStatus = $orderDetails['order_status'] ?? null;

                if ($aliexpressStatus) {
                    $newStatus = $this->mapAliExpressStatus($aliexpressStatus);

                    if ($newStatus && $newStatus !== $oldStatus) {
                        $order->status = $newStatus;

                        // Update timestamps
                        $this->updateOrderTimestamps($order, $newStatus);

                        $order->save();

                        // Dispatch event
                        event(new OrderStatusUpdated($order, $oldStatus, $newStatus));

                        $statusChanged = true;

                        Log::info('Order status updated via seller sync', [
                            'seller_id' => auth()->id(),
                            'order_id' => $order->id,
                            'aliexpress_order_id' => $order->aliexpress_order_id,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                        ]);
                    }
                }
            }

            // Get tracking data from AliExpress
            $trackingData = $this->aliexpressService->getOrderShippingInfo($order->aliexpress_order_id);

            if ($trackingData) {
                $order->tracking_number = $trackingData['tracking_number'] ?? $order->tracking_number;
                $order->shipping_method = $trackingData['shipping_method'] ?? $order->shipping_method;

                // Update status based on shipping status if needed
                if (isset($trackingData['status'])) {
                    $shippingStatus = $trackingData['status'];

                    if ($shippingStatus === 'delivered' && $order->status !== 'delivered') {
                        $order->status = 'delivered';
                        if (empty($order->delivered_at)) {
                            $order->delivered_at = now();
                        }
                        if (empty($order->shipped_at)) {
                            $order->shipped_at = now()->subDays(3);
                        }
                        if ($oldStatus !== $order->status) {
                            event(new OrderStatusUpdated($order, $oldStatus, $order->status));
                            $statusChanged = true;
                        }
                    } elseif (in_array($shippingStatus, ['in_transit', 'shipped']) && in_array($order->status, ['placed', 'paid'])) {
                        $order->status = 'shipped';
                        if (empty($order->shipped_at)) {
                            $order->shipped_at = now();
                        }
                        if ($oldStatus !== $order->status) {
                            event(new OrderStatusUpdated($order, $oldStatus, $order->status));
                            $statusChanged = true;
                        }
                    }
                }

                $order->save();

                // Update Shipping record
                Shipping::updateOrCreate(
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

            Log::info('Seller synced shipping tracking', [
                'seller_id' => auth()->id(),
                'order_id' => $order->id,
                'aliexpress_order_id' => $order->aliexpress_order_id,
                'tracking_number' => $order->tracking_number,
                'status_changed' => $statusChanged,
            ]);

            $message = 'Order information updated successfully!';
            if ($statusChanged) {
                $message = "Order status updated from '{$oldStatus}' to '{$order->status}'!";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to sync tracking for seller', [
                'seller_id' => auth()->id(),
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to sync order: ' . $e->getMessage());
        }
    }

    /**
     * Sync all orders tracking and status from AliExpress
     */
    public function syncAll()
    {
        $user = auth()->user();

        try {
            $orders = Order::where('user_id', $user->id)
                ->whereNotNull('aliexpress_order_id')
                ->whereIn('status', ['placed', 'paid', 'shipped'])
                ->get();

            if ($orders->isEmpty()) {
                return redirect()->back()->with('warning', 'No orders to sync.');
            }

            $synced = 0;
            $failed = 0;
            $statusChangedCount = 0;

            foreach ($orders as $order) {
                try {
                    $oldStatus = $order->status;

                    // Get order details from AliExpress
                    $orderDetails = $this->aliexpressService->getOrderDetails($order->aliexpress_order_id);

                    if ($orderDetails) {
                        // Extract order status
                        $aliexpressStatus = $orderDetails['order_status'] ?? null;

                        if ($aliexpressStatus) {
                            $newStatus = $this->mapAliExpressStatus($aliexpressStatus);

                            if ($newStatus && $newStatus !== $oldStatus) {
                                $order->status = $newStatus;

                                // Update timestamps
                                $this->updateOrderTimestamps($order, $newStatus);

                                $order->save();

                                // Dispatch event
                                event(new OrderStatusUpdated($order, $oldStatus, $newStatus));

                                $statusChangedCount++;

                                Log::info('Order status updated via seller sync', [
                                    'order_id' => $order->id,
                                    'aliexpress_order_id' => $order->aliexpress_order_id,
                                    'old_status' => $oldStatus,
                                    'new_status' => $newStatus,
                                ]);
                            }
                        }
                    }

                    // Also try to sync tracking info
                    $trackingData = $this->aliexpressService->getOrderShippingInfo($order->aliexpress_order_id);

                    if ($trackingData) {
                        $order->tracking_number = $trackingData['tracking_number'] ?? $order->tracking_number;
                        $order->shipping_method = $trackingData['shipping_method'] ?? $order->shipping_method;

                        // Update status based on shipping status if needed
                        if (isset($trackingData['status'])) {
                            $shippingStatus = $trackingData['status'];
                            $statusChanged = false;

                            if ($shippingStatus === 'delivered' && $order->status !== 'delivered') {
                                $order->status = 'delivered';
                                if (empty($order->delivered_at)) {
                                    $order->delivered_at = now();
                                }
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
                                event(new OrderStatusUpdated($order, $oldStatus, $order->status));
                                $statusChangedCount++;
                            }
                        }

                        $order->save();

                        // Update Shipping record
                        Shipping::updateOrCreate(
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

                    $synced++;

                } catch (\Exception $e) {
                    $failed++;
                    Log::warning('Failed to sync individual order', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Seller synced all shipping tracking', [
                'seller_id' => $user->id,
                'total_orders' => $orders->count(),
                'synced' => $synced,
                'failed' => $failed,
                'status_changed_count' => $statusChangedCount,
            ]);

            $message = "Successfully synced {$synced} orders";
            if ($statusChangedCount > 0) {
                $message .= " ({$statusChangedCount} status updates)";
            }
            if ($failed > 0) {
                $message .= ". {$failed} failed.";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to sync all tracking for seller', [
                'seller_id' => $user->id,
                'error' => $e->getMessage()
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
            'IN_CANCEL' => 'cancelled',
            'CANCELED' => 'cancelled',
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
     * Get shipping statistics for dashboard widget
     */
    public function getDashboardStats()
    {
        $user = auth()->user();

        $stats = [
            'active_shipments' => Shipping::whereIn('status', ['pending', 'in_transit', 'out_for_delivery'])
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
            'in_transit' => Shipping::where('status', 'in_transit')
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
            'delivered_today' => Shipping::where('status', 'delivered')
                ->whereDate('delivered_at', today())
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
            'recent_shipments' => Shipping::with(['order'])
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->latest('updated_at')
                ->limit(5)
                ->get(),
        ];

        return $stats;
    }
}
