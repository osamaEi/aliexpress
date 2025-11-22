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

            // Get tracking data from AliExpress
            $trackingData = $this->aliexpressService->getOrderShippingInfo($order->aliexpress_order_id);

            if (!$trackingData) {
                return redirect()->back()->with('warning', 'No tracking information available yet.');
            }

            // Update order with tracking info
            $order->update([
                'tracking_number' => $trackingData['tracking_number'] ?? null,
                'shipping_method' => $trackingData['shipping_method'] ?? null,
            ]);

            // Update order status based on shipping status
            if (isset($trackingData['status'])) {
                if ($trackingData['status'] === 'delivered' && $order->status !== 'delivered') {
                    $order->update([
                        'status' => 'delivered',
                        'delivered_at' => now(),
                    ]);
                } elseif ($trackingData['status'] === 'shipped' && $order->status === 'placed') {
                    $order->update([
                        'status' => 'shipped',
                        'shipped_at' => now(),
                    ]);
                }
            }

            Log::info('Admin synced shipping tracking', [
                'admin_id' => auth()->id(),
                'order_id' => $order->id,
                'aliexpress_order_id' => $order->aliexpress_order_id,
                'tracking_number' => $order->tracking_number
            ]);

            return redirect()->back()->with('success', 'Tracking information updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to sync tracking', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to sync tracking: ' . $e->getMessage());
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
