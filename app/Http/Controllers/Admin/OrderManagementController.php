<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderManagementController extends Controller
{
    /**
     * Display all orders
     */
    public function index(Request $request)
    {
        $query = Order::with([
            'user',
            'product',
            'product.assignedUsers' => fn($q) => $q->where('user_type', 'distributor'),
            'shippingCity',
            'shippingDistrict',
        ]);

        // Filter by country
        if ($request->filled('country')) {
            $query->where('shipping_country', $request->country);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by sync status (synced with AliExpress or not)
        if ($request->filled('sync')) {
            if ($request->sync === 'synced') {
                $query->whereNotNull('aliexpress_order_id');
            } elseif ($request->sync === 'not_synced') {
                $query->whereNull('aliexpress_order_id');
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search (order number, customer name/email, supplier order id)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('aliexpress_order_id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'oldest'        => $query->oldest(),
            'amount_high'   => $query->orderByDesc('total_amount'),
            'amount_low'    => $query->orderBy('total_amount'),
            default         => $query->latest(),
        };

        $orders = $query->paginate(20)->withQueryString();

        // Status summary cards (respecting nothing — global counts)
        $stats = [
            'total'     => Order::count(),
            'pending'   => Order::where('status', 'pending')->count(),
            'shipped'   => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        $statuses = ['pending', 'processing', 'placed', 'paid', 'shipped', 'delivered', 'cancelled'];

        // Distinct shipping countries present in orders, for the country filter
        $countries = Order::query()
            ->whereNotNull('shipping_country')
            ->where('shipping_country', '!=', '')
            ->distinct()
            ->pluck('shipping_country')
            ->sort()
            ->values();

        return view('admin.orders.index', compact('orders', 'stats', 'statuses', 'countries'));
    }

    /**
     * Sync order with AliExpress
     */
    public function sync(Order $order)
    {
        // Call the existing placeOnAliexpress method from OrderController
        app(\App\Http\Controllers\OrderController::class)->placeOnAliexpress($order);

        return redirect()->back()
            ->with('success', __('messages.order_synced_successfully'));
    }

    /**
     * Bulk sync orders
     */
    public function bulkSync(Request $request)
    {
        $orderIds = $request->input('order_ids', []);

        foreach ($orderIds as $orderId) {
            $order = Order::find($orderId);
            if ($order && $order->status === 'pending') {
                app(\App\Http\Controllers\OrderController::class)->placeOnAliexpress($order);
            }
        }

        return redirect()->back()
            ->with('success', __('messages.orders_synced_successfully'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,placed,paid,shipped,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Update status
        $order->status = $newStatus;

        // Update timestamps based on new status
        switch ($newStatus) {
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
                // Ensure shipped_at is set if not already
                if (empty($order->shipped_at)) {
                    $order->shipped_at = now()->subDays(3);
                }
                break;
        }

        $order->save();

        // Dispatch event for notifications if status changed
        if ($oldStatus !== $newStatus) {
            event(new \App\Events\OrderStatusUpdated($order, $oldStatus, $newStatus));
        }

        return redirect()->back()
            ->with('success', __('messages.order_status_updated'));
    }
}
