<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\Order;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        // Get coupon statistics for this distributor
        $coupons = Coupon::where('store_id', $user->id)->get();
        $stats['total_coupons'] = $coupons->count();
        $stats['active_coupons'] = $coupons->filter(fn($c) => $c->isValid())->count();
        $stats['expired_coupons'] = $coupons->filter(fn($c) => $c->isExpired())->count();
        $stats['total_commission_earned'] = $coupons->sum('total_commission_earned');
        $stats['pending_commission'] = CouponUsage::whereHas('coupon', function ($q) use ($user) {
            $q->where('store_id', $user->id);
        })->pending()->sum('commission_amount');

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

        // Get recent coupons for this distributor
        $recentCoupons = Coupon::where('store_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('distributor.dashboard', compact('stats', 'recentOrders', 'recentProducts', 'recentCoupons'));
    }

    /**
     * Display list of products assigned to distributor.
     */
    public function products()
    {
        $user = Auth::user();
        $products = $user->assignedProducts()->with('category')->paginate(20);

        [$productCount, $maxProducts, $subscriptionPlan] = $this->getSubscriptionLimits($user);

        return view('distributor.products.index', compact('products', 'productCount', 'maxProducts', 'subscriptionPlan'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function createProduct()
    {
        $user = Auth::user();

        [$productCount, $maxProducts, $subscriptionPlan] = $this->getSubscriptionLimits($user);

        // Block access if product limit reached
        if ($maxProducts !== null && $productCount >= $maxProducts) {
            $isAr = app()->getLocale() == 'ar';
            return redirect()->route('distributor.products')
                ->with('error', $isAr
                    ? "لقد وصلت إلى الحد الأقصى ({$maxProducts}) منتجات لباقتك الحالية. يرجى الترقية إلى باقة أعلى."
                    : "You have reached the maximum ({$maxProducts}) products for your current plan. Please upgrade to a higher plan.");
        }

        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        $currencies = \App\Models\Currency::active();

        return view('distributor.products.create', compact('categories', 'currencies', 'productCount', 'maxProducts', 'subscriptionPlan'));
    }

    /**
     * Get subcategories for a parent category (AJAX endpoint).
     */
    public function getSubcategories($parentId)
    {
        $subcategories = Category::where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'name_ar']);

        return response()->json($subcategories);
    }

    /**
     * Store a newly created product.
     */
    public function storeProduct(Request $request)
    {
        $user = Auth::user();

        // Enforce subscription product limit
        [$productCount, $maxProducts] = $this->getSubscriptionLimits($user);
        if ($maxProducts !== null && $productCount >= $maxProducts) {
            $isAr = app()->getLocale() == 'ar';
            return redirect()->route('distributor.products')
                ->with('error', $isAr
                    ? "لقد وصلت إلى الحد الأقصى ({$maxProducts}) منتجات لباقتك الحالية."
                    : "You have reached the maximum ({$maxProducts}) products for your current plan.");
        }

        // Base validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'description_ar' => ['required', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'short_description_ar' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:3'],
            'sku' => ['nullable', 'string', 'max:100'],
            'track_inventory' => ['boolean'],
            'has_variations' => ['boolean'],
            'parent_category_id' => ['required', 'exists:categories,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'additional_images' => ['nullable', 'array', 'max:5'],
            'additional_images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'video' => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:51200'], // 50MB
        ];

        // Conditional validation based on has_variations
        if ($request->has('has_variations') && $request->has_variations) {
            $rules['variations'] = ['required', 'array', 'min:1'];
            $rules['variations.*.name'] = ['required', 'string', 'max:255'];
            $rules['variations.*.price'] = ['required', 'numeric', 'min:0'];
            $rules['variations.*.quantity'] = ['required', 'integer', 'min:0'];
        } else {
            $rules['price'] = ['required', 'numeric', 'min:0'];
            $rules['stock_quantity'] = ['required', 'integer', 'min:0'];
        }

        $validated = $request->validate($rules);

        // Handle main photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('products', 'public');
            $validated['photo'] = $photoPath;
        }

        // Handle video upload
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('products/videos', 'public');
            $validated['video'] = $videoPath;
        }

        // Remove fields not in product table
        unset($validated['parent_category_id']);
        unset($validated['additional_images']);
        unset($validated['variations']);

        // Set defaults for variation products
        if ($request->has('has_variations') && $request->has_variations) {
            $validated['has_variations'] = true;
            $validated['price'] = 0; // Will be calculated from variations
            $validated['stock_quantity'] = 0; // Will be calculated from variations
        } else {
            $validated['has_variations'] = false;
        }

        // Generate unique random slug from name
        $baseSlug = Str::slug($validated['name']);
        $validated['slug'] = $baseSlug . '-' . Str::random(8);
        $validated['is_active'] = true;

        // Generate unique SKU if provided
        if (!empty($validated['sku'])) {
            $baseSku = $validated['sku'];
            $validated['sku'] = $baseSku . '-' . Str::random(6);
        }

        // Calculate seller and admin amounts
        $price = $validated['price'] ?? 0;
        $validated['seller_amount'] = $price * 0.7;
        $validated['admin_amount'] = $price * 0.3;

        // Create the product
        $product = Product::create($validated);

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            $sortOrder = 0;
            foreach ($request->file('additional_images') as $image) {
                $imagePath = $image->store('products/additional', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        // Handle variations
        if ($request->has('has_variations') && $request->has_variations && !empty($request->variations)) {
            $minPrice = PHP_FLOAT_MAX;
            $totalStock = 0;

            foreach ($request->variations as $variation) {
                if (!empty($variation['name']) && isset($variation['price'])) {
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'name' => $variation['name'],
                        'price' => $variation['price'],
                        'quantity' => $variation['quantity'] ?? 0,
                    ]);

                    if ($variation['price'] < $minPrice) {
                        $minPrice = $variation['price'];
                    }
                    $totalStock += $variation['quantity'] ?? 0;
                }
            }

            // Update product with min price and total stock
            $product->update([
                'price' => $minPrice != PHP_FLOAT_MAX ? $minPrice : 0,
                'stock_quantity' => $totalStock,
                'seller_amount' => ($minPrice != PHP_FLOAT_MAX ? $minPrice : 0) * 0.7,
                'admin_amount' => ($minPrice != PHP_FLOAT_MAX ? $minPrice : 0) * 0.3,
            ]);
        }

        // Assign product to the distributor
        $user->assignedProducts()->attach($product->id, [
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
            // Show subcategories of the parent (only active)
            $categories = Category::where('parent_id', $parentId)
                ->where('is_active', true)
                ->withCount('children')
                ->orderBy('name', 'asc')
                ->paginate(20);
        } else {
            // Show only root categories (no parent, only active)
            $categories = Category::whereNull('parent_id')
                ->where('is_active', true)
                ->withCount('children')
                ->orderBy('name', 'asc')
                ->paginate(20);
        }

        return view('distributor.categories.index', compact('categories', 'parentCategory'));
    }

    /**
     * Display the specified product.
     */
    public function showProduct($id)
    {
        $user = Auth::user();
        $product = $user->assignedProducts()
            ->with(['category', 'additionalImages', 'variations'])
            ->findOrFail($id);

        return view('distributor.products.show', compact('product'));
    }

    /**
     * Show the form for editing a product.
     */
    public function editProduct($id)
    {
        $user = Auth::user();
        $product = $user->assignedProducts()->findOrFail($id);

        // Get only parent categories (main categories)
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        // Get the parent category if product has a category
        $parentCategory = null;
        if ($product->category) {
            $parentCategory = $product->category->parent_id
                ? Category::find($product->category->parent_id)
                : $product->category;
        }

        $currencies = \App\Models\Currency::active();

        return view('distributor.products.edit', compact('product', 'categories', 'parentCategory', 'currencies'));
    }

    /**
     * Update the specified product.
     */
    public function updateProduct(Request $request, $id)
    {
        $user = Auth::user();
        $product = $user->assignedProducts()->findOrFail($id);

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
            'parent_category_id' => ['required', 'exists:categories,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
            'specifications' => ['nullable', 'array'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'processing_time_days' => ['nullable', 'integer', 'min:0'],
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($product->photo && \Storage::disk('public')->exists($product->photo)) {
                \Storage::disk('public')->delete($product->photo);
            }
            $photoPath = $request->file('photo')->store('products', 'public');
            $validated['photo'] = $photoPath;
        }

        // Remove parent_category_id from validated data (not a product field)
        unset($validated['parent_category_id']);

        // Update slug if name changed
        if ($validated['name'] !== $product->name) {
            $baseSlug = Str::slug($validated['name']);
            $validated['slug'] = $baseSlug . '-' . Str::random(8);
        }

        // Generate unique SKU if provided and different from current
        if (!empty($validated['sku']) && $validated['sku'] !== $product->sku) {
            $baseSku = $validated['sku'];
            $validated['sku'] = $baseSku . '-' . Str::random(6);
        }

        // Calculate seller and admin amounts
        $validated['seller_amount'] = $validated['price'] * 0.7; // 70% to seller
        $validated['admin_amount'] = $validated['price'] * 0.3; // 30% to admin

        // Update the product
        $product->update($validated);

        return redirect()->route('distributor.products')
            ->with('success', __('messages.product_updated_successfully'));
    }

    /**
     * Display orders assigned to distributor's products.
     */
    public function orders(Request $request)
    {
        $user = Auth::user();
        $assignedProductIds = $user->assignedProducts()->pluck('products.id');

        $query = Order::whereIn('product_id', $assignedProductIds)
            ->with(['product', 'user']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by order number or customer name
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(20);

        return view('distributor.orders.index', compact('orders'));
    }

    /**
     * Update order status (for distributor).
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        $user = Auth::user();
        $assignedProductIds = $user->assignedProducts()->pluck('products.id');

        // Find order and verify it belongs to distributor's products
        $order = Order::whereIn('product_id', $assignedProductIds)
            ->findOrFail($orderId);

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $oldStatus = $order->status;
        $order->status = $validated['status'];
        $order->save();

        \Log::info('Distributor updated order status', [
            'distributor_id' => $user->id,
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status']
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'ar' ? 'تم تحديث حالة الطلب بنجاح' : 'Order status updated successfully',
                'status' => $order->status
            ]);
        }

        return redirect()->back()
            ->with('success', app()->getLocale() == 'ar' ? 'تم تحديث حالة الطلب بنجاح' : 'Order status updated successfully');
    }

    /**
     * Get subscription limits for the given user.
     * Returns [currentProductCount, maxProducts|null, subscriptionPlan|null]
     * maxProducts = null means unlimited.
     */
    private function getSubscriptionLimits($user): array
    {
        $productCount = $user->assignedProducts()->count();

        $activeUserSub = $user->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->with('subscription')
            ->latest()
            ->first();

        if (!$activeUserSub || !$activeUserSub->subscription) {
            return [$productCount, null, null];
        }

        $plan = $activeUserSub->subscription;
        // max_products = 0 or null means unlimited
        $maxProducts = ($plan->max_products && $plan->max_products > 0) ? $plan->max_products : null;

        return [$productCount, $maxProducts, $plan];
    }
}
