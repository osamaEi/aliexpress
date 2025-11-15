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

        $query = Shipping::with(['order.product'])
            ->whereHas('order', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by tracking number or order number
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function($orderQuery) use ($search) {
                      $orderQuery->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $shippings = $query->latest('updated_at')->paginate(15);

        // Get statistics for this seller
        $stats = [
            'total' => Shipping::whereHas('order', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
            'pending' => Shipping::where('status', 'pending')
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
            'in_transit' => Shipping::where('status', 'in_transit')
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
            'delivered' => Shipping::where('status', 'delivered')
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
        ];

        return view('seller.shipping.index', compact('shippings', 'stats'));
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
    public function syncTracking(Order $order)
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
            $trackingData = $this->aliexpressService->syncOrderShipping($order);

            if (!$trackingData) {
                return redirect()->back()->with('warning', 'No tracking information available yet. Please try again later.');
            }

            // Create or update shipping record
            $shipping = Shipping::updateOrCreate(
                ['order_id' => $order->id],
                array_merge($trackingData, [
                    'last_synced_at' => now(),
                ])
            );

            // Update order status based on shipping status
            if ($shipping->status === 'delivered' && $order->status !== 'delivered') {
                $order->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);
            } elseif ($shipping->status === 'in_transit' && $order->status === 'placed') {
                $order->update([
                    'status' => 'shipped',
                    'shipped_at' => $shipping->shipped_at ?? now(),
                    'tracking_number' => $shipping->tracking_number,
                ]);
            }

            Log::info('Seller synced shipping tracking', [
                'seller_id' => auth()->id(),
                'order_id' => $order->id,
                'shipping_id' => $shipping->id,
                'status' => $shipping->status
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
