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

        $order->update(['status' => $request->status]);

        return redirect()->back()
            ->with('success', __('messages.order_status_updated'));
    }
}
