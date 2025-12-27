<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistributorProductController extends Controller
{
    /**
     * Display all distributor products for moderation
     */
    public function index(Request $request)
    {
        // Get products assigned to distributors with pivot status
        $query = DB::table('product_user')
            ->join('products', 'product_user.product_id', '=', 'products.id')
            ->join('users', 'product_user.user_id', '=', 'users.id')
            ->where('users.user_type', 'distributor')
            ->select(
                'product_user.*',
                'products.id as product_id',
                'products.name as product_name',
                'products.name_ar as product_name_ar',
                'products.aliexpress_id',
                'products.price',
                'products.commission_rate',
                'products.main_image',
                'users.id as distributor_id',
                'users.name as distributor_name',
                'users.email as distributor_email',
                'users.country as distributor_country'
            );

        // Filter by status
        if ($request->filled('status')) {
            $query->where('product_user.status', $request->status);
        } else {
            // Default to pending
            $query->where('product_user.status', 'pending');
        }

        // Filter by distributor
        if ($request->filled('distributor_id')) {
            $query->where('users.id', $request->distributor_id);
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('users.country', $request->country);
        }

        // Search by product name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.name_ar', 'like', "%{$search}%")
                  ->orWhere('products.aliexpress_id', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('product_user.created_at', 'desc')->paginate(20);

        // Get distributors for filter
        $distributors = User::where('user_type', 'distributor')->orderBy('name')->get(['id', 'name', 'country']);

        // Stats
        $stats = [
            'pending' => DB::table('product_user')
                ->join('users', 'product_user.user_id', '=', 'users.id')
                ->where('users.user_type', 'distributor')
                ->where('product_user.status', 'pending')
                ->count(),
            'approved' => DB::table('product_user')
                ->join('users', 'product_user.user_id', '=', 'users.id')
                ->where('users.user_type', 'distributor')
                ->where('product_user.status', 'approved')
                ->count(),
            'rejected' => DB::table('product_user')
                ->join('users', 'product_user.user_id', '=', 'users.id')
                ->where('users.user_type', 'distributor')
                ->where('product_user.status', 'rejected')
                ->count(),
        ];

        return view('admin.distributor-products.index', compact('products', 'distributors', 'stats'));
    }

    /**
     * Approve a distributor product
     */
    public function approve(Request $request, $productId, $userId)
    {
        DB::table('product_user')
            ->where('product_id', $productId)
            ->where('user_id', $userId)
            ->update([
                'status' => 'approved',
                'updated_at' => now(),
            ]);

        return back()->with('success', app()->getLocale() == 'ar' ? 'تمت الموافقة على المنتج' : 'Product approved');
    }

    /**
     * Reject a distributor product with reason
     */
    public function reject(Request $request, $productId, $userId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        DB::table('product_user')
            ->where('product_id', $productId)
            ->where('user_id', $userId)
            ->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'updated_at' => now(),
            ]);

        return back()->with('success', app()->getLocale() == 'ar' ? 'تم رفض المنتج' : 'Product rejected');
    }

    /**
     * Bulk approve products
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.user_id' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            DB::table('product_user')
                ->where('product_id', $item['product_id'])
                ->where('user_id', $item['user_id'])
                ->update([
                    'status' => 'approved',
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() == 'ar'
                ? 'تمت الموافقة على ' . count($request->items) . ' منتج'
                : count($request->items) . ' products approved',
        ]);
    }

    /**
     * Show product details
     */
    public function show($productId, $userId)
    {
        $assignment = DB::table('product_user')
            ->join('products', 'product_user.product_id', '=', 'products.id')
            ->join('users', 'product_user.user_id', '=', 'users.id')
            ->where('product_user.product_id', $productId)
            ->where('product_user.user_id', $userId)
            ->select(
                'product_user.*',
                'products.*',
                'users.id as distributor_id',
                'users.name as distributor_name',
                'users.email as distributor_email',
                'users.country as distributor_country',
                'users.phone as distributor_phone'
            )
            ->first();

        if (!$assignment) {
            return redirect()->route('admin.distributor-products.index')
                ->with('error', app()->getLocale() == 'ar' ? 'المنتج غير موجود' : 'Product not found');
        }

        return view('admin.distributor-products.show', compact('assignment'));
    }
}
