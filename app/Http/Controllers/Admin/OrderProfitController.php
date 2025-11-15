<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderProfitController extends Controller
{
    /**
     * Display the order profit page
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'product']);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by payment status if provided
        if ($request->has('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range if provided
        if ($request->has('date_from')) {
            $query->whereDate('placed_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('placed_at', '<=', $request->date_to);
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_email', 'like', '%' . $request->search . '%');
            });
        }

        $orders = $query->latest('placed_at')->paginate(20);

        // Calculate totals
        $totalAliexpressProfit = $query->sum('aliexpress_profit');
        $totalAdminCategoryProfit = $query->sum('admin_category_profit');
        $totalSellerProfit = $query->sum('seller_profit');
        $totalProfit = $totalAliexpressProfit + $totalAdminCategoryProfit + $totalSellerProfit;

        return view('admin.order-profits.index', compact(
            'orders',
            'totalAliexpressProfit',
            'totalAdminCategoryProfit',
            'totalSellerProfit',
            'totalProfit'
        ));
    }
}
