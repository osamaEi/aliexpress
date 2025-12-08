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
        $query = Order::with(['user', 'product']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $orders = $query->latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
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
