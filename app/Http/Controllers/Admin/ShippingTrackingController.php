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
        $query = Shipping::with(['order.user', 'order.product']);

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

        $shippings = $query->latest('updated_at')->paginate(20);

        // Get statistics
        $stats = [
            'total' => Shipping::count(),
            'pending' => Shipping::where('status', 'pending')->count(),
            'in_transit' => Shipping::where('status', 'in_transit')->count(),
            'delivered' => Shipping::where('status', 'delivered')->count(),
            'exception' => Shipping::where('status', 'exception')->count(),
        ];

        return view('admin.shipping.index', compact('shippings', 'stats'));
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
            $trackingData = $this->aliexpressService->syncOrderShipping($order);

            if (!$trackingData) {
                return redirect()->back()->with('warning', 'No tracking information available yet.');
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

            Log::info('Shipping tracking synced successfully', [
                'order_id' => $order->id,
                'shipping_id' => $shipping->id,
                'status' => $shipping->status
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
                ->whereNotIn('status', ['delivered', 'cancelled', 'failed'])
                ->get();

            $synced = 0;
            $failed = 0;

            foreach ($orders as $order) {
                try {
                    $trackingData = $this->aliexpressService->syncOrderShipping($order);

                    if ($trackingData) {
                        Shipping::updateOrCreate(
                            ['order_id' => $order->id],
                            array_merge($trackingData, [
                                'last_synced_at' => now(),
                            ])
                        );
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

            $message = "Synced {$synced} shipments successfully.";
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
