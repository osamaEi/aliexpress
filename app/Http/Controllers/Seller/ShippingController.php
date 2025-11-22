<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipping;
use App\Services\AliExpressService;
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
     * Sync tracking information for seller's order
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

            // Get tracking data from AliExpress
            $trackingData = $this->aliexpressService->getOrderShippingInfo($order->aliexpress_order_id);

            if (!$trackingData) {
                return redirect()->back()->with('warning', 'No tracking information available yet. Please try again later.');
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

            Log::info('Seller synced shipping tracking', [
                'seller_id' => auth()->id(),
                'order_id' => $order->id,
                'aliexpress_order_id' => $order->aliexpress_order_id,
                'tracking_number' => $order->tracking_number
            ]);

            return redirect()->back()->with('success', 'Tracking information updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to sync tracking for seller', [
                'seller_id' => auth()->id(),
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to sync tracking: ' . $e->getMessage());
        }
    }

    /**
     * Sync all orders tracking from AliExpress
     */
    public function syncAll()
    {
        $user = auth()->user();

        try {
            $orders = Order::where('user_id', $user->id)
                ->whereNotNull('aliexpress_order_id')
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
                'failed' => $failed
            ]);

            return redirect()->back()->with('success', "Successfully synced {$synced} orders. {$failed} failed.");

        } catch (\Exception $e) {
            Log::error('Failed to sync all tracking for seller', [
                'seller_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to sync tracking: ' . $e->getMessage());
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
