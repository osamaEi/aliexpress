<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderProfitController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'product', 'shipping'])
            ->whereNotNull('payment_status')
            ->where('payment_status', '!=', 'pending');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('aliexpress_order_id', 'like', "%{$search}%");
            });
        }

        // Totals before pagination
        $totals = (clone $query)->selectRaw('
            SUM(total_amount)           as total_revenue,
            SUM(freight_amount)         as total_shipping,
            SUM(aliexpress_profit)      as total_aliexpress_profit,
            SUM(seller_profit)          as total_seller_profit,
            SUM(COALESCE(aliexpress_profit,0) + COALESCE(seller_profit,0)) as total_profit
        ')->first();

        $orders = (clone $query)->latest()->paginate(20)->withQueryString();

        return view('admin.order-profits.index', compact('orders', 'totals'));
    }
}
