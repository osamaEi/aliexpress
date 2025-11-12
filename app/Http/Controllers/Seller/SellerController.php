<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    /**
     * Display the seller dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get seller statistics using assigned products relationship
        $assignedProductIds = $user->assignedProducts()->pluck('products.id');

        $stats = [
            'total_products' => $user->assignedProducts()->count(),
            'active_products' => $user->assignedProducts()->where('is_active', true)->count(),
            'total_orders' => Order::where('seller_id', $user->id)->count(),
            'pending_orders' => Order::where('seller_id', $user->id)->where('status', 'pending')->count(),
            'completed_orders' => Order::where('seller_id', $user->id)->where('status', 'delivered')->count(),
            'total_categories' => Category::count(),
            'wallet_balance' => $user->wallet ? $user->wallet->balance : 0,
            'total_revenue' => Order::where('seller_id', $user->id)
                ->where('status', 'delivered')
                ->sum('total_amount'),
        ];

        // Get recent orders
        $recentOrders = Order::where('seller_id', $user->id)
            ->with('product')
            ->latest()
            ->take(10)
            ->get();

        // Get recent products (assigned to this seller)
        $recentProducts = $user->assignedProducts()
            ->latest()
            ->take(10)
            ->get();

        return view('seller.dashboard', compact('stats', 'recentOrders', 'recentProducts'));
    }
}
