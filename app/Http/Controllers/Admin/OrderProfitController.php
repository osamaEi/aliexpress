<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profit;
use Illuminate\Http\Request;

class OrderProfitController extends Controller
{
    /**
     * Display the order profit page
     */
    public function index(Request $request)
    {
        $query = Profit::with(['order.user', 'product']);

        // Filter by order status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->whereHas('order', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Filter by payment status if provided
        if ($request->has('payment_status') && $request->payment_status !== 'all') {
            $query->whereHas('order', function($q) use ($request) {
                $q->where('payment_status', $request->payment_status);
            });
        }

        // Filter by date range if provided
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search) {
            $query->whereHas('order', function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_email', 'like', '%' . $request->search . '%');
            });
        }

        // Calculate totals BEFORE pagination
        $totalAdminProfit = $query->sum('admin_profit');
        $totalSellerProfit = $query->sum('seller_profit');
        $totalShippingCost = $query->sum('shipping_price');
        $totalProfit = $query->sum('total_profit');
        $totalRevenue = $query->sum('final_price');
        $totalCost = $query->sum('total_cost');

        // Get paginated profits (clone query to avoid affecting totals)
        $profits = (clone $query)->latest('created_at')->paginate(20);

        return view('admin.order-profits.index', compact(
            'profits',
            'totalAdminProfit',
            'totalSellerProfit',
            'totalShippingCost',
            'totalProfit',
            'totalRevenue',
            'totalCost'
        ));
    }
}
