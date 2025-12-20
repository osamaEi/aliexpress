<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DistributorController extends Controller
{
    /**
     * Display the distributor dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get distributor statistics using assigned products relationship
        $assignedProductIds = $user->assignedProducts()->pluck('products.id');

        $stats = [
            'total_products' => $user->assignedProducts()->count(),
            'active_products' => $user->assignedProducts()->where('is_active', true)->count(),
            'total_orders' => Order::whereIn('product_id', $assignedProductIds)->count(),
            'pending_orders' => Order::whereIn('product_id', $assignedProductIds)->where('status', 'pending')->count(),
            'completed_orders' => Order::whereIn('product_id', $assignedProductIds)->where('status', 'delivered')->count(),
            'total_categories' => Category::where('is_active', true)->count(),
            'wallet_balance' => $user->wallet ? $user->wallet->balance : 0,
            'total_revenue' => Order::whereIn('product_id', $assignedProductIds)
                ->where('status', 'delivered')
                ->sum('total_price'),
        ];

        // Get recent orders for distributor's products
        $recentOrders = Order::whereIn('product_id', $assignedProductIds)
            ->with('product')
            ->latest()
            ->take(10)
            ->get();

        // Get recent products (assigned to this distributor)
        $recentProducts = $user->assignedProducts()
            ->latest()
            ->take(10)
            ->get();

        return view('distributor.dashboard', compact('stats', 'recentOrders', 'recentProducts'));
    }

    /**
     * Display list of products assigned to distributor.
     */
    public function products()
    {
        $user = Auth::user();
        $products = $user->assignedProducts()->with('category')->paginate(20);

        return view('distributor.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function createProduct()
    {
        $categories = Category::where('is_active', true)->get();
        return view('distributor.products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'description_ar' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'short_description_ar' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'track_inventory' => ['boolean'],
            'category_id' => ['required', 'exists:categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
            'specifications' => ['nullable', 'array'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'processing_time_days' => ['nullable', 'integer', 'min:0'],
        ]);

        // Generate unique random slug from name
        $baseSlug = Str::slug($validated['name']);
        $validated['slug'] = $baseSlug . '-' . Str::random(8);
        $validated['is_active'] = true;

        // Generate unique SKU if provided
        if (!empty($validated['sku'])) {
            $baseSku = $validated['sku'];
            $validated['sku'] = $baseSku . '-' . Str::random(6);
        }

        // Calculate seller and admin amounts (you can adjust this logic)
        $validated['seller_amount'] = $validated['price'] * 0.7; // 70% to seller
        $validated['admin_amount'] = $validated['price'] * 0.3; // 30% to admin

        // Create the product
        $product = Product::create($validated);

        // Assign product to the distributor
        Auth::user()->assignedProducts()->attach($product->id, [
            'status' => 'published'
        ]);

        return redirect()->route('distributor.products')
            ->with('success', __('messages.product_created_successfully'));
    }

    /**
     * Display all categories (read-only for distributors).
     */
    public function categories(Request $request)
    {
        // Get parent category if specified
        $parentId = $request->get('parent_id');
        $parentCategory = null;

        if ($parentId) {
            $parentCategory = Category::find($parentId);
            // Show subcategories of the parent
            $categories = Category::where('parent_id', $parentId)
                ->withCount('children')
                ->orderBy('name', 'asc')
                ->paginate(20);
        } else {
            // Show only root categories (no parent)
            $categories = Category::whereNull('parent_id')
                ->withCount('children')
                ->orderBy('name', 'asc')
                ->paginate(20);
        }

        return view('distributor.categories.index', compact('categories', 'parentCategory'));
    }

    /**
     * Display orders assigned to distributor's products.
     */
    public function orders()
    {
        $user = Auth::user();
        $assignedProductIds = $user->assignedProducts()->pluck('products.id');

        $orders = Order::whereIn('product_id', $assignedProductIds)
            ->with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('distributor.orders.index', compact('orders'));
    }
}
