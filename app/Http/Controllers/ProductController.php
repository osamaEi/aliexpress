<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\AliExpressDropshippingService;
use App\Services\AliexpressTextService;
use App\Services\AliExpressService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $aliexpressService;
    protected $aliexpressTextService;
    protected $aliexpressCategoryService;

    public function __construct(
        AliExpressDropshippingService $aliexpressService,
        AliexpressTextService $aliexpressTextService,
        AliExpressService $aliexpressCategoryService
    )
    {
        $this->aliexpressService = $aliexpressService;
        $this->aliexpressTextService = $aliexpressTextService;
        $this->aliexpressCategoryService = $aliexpressCategoryService;
    }

    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('aliexpress_id', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by source
        if ($request->filled('source')) {
            if ($request->source === 'aliexpress') {
                $query->whereNotNull('aliexpress_id');
            } else {
                $query->whereNull('aliexpress_id');
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->latest()->paginate(20);
        $categories = Category::active()->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::active()->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'stock_quantity' => 'required|integer|min:0',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product = Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load('category');
        $categories = Category::active()->get();
        return view('products.show', compact('product', 'categories'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'original_price' => 'nullable|numeric|min:0',
            'markup_amount' => 'nullable|numeric|min:0',
            'markup_percentage' => 'nullable|numeric|min:0|max:1000',
            'compare_price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'stock_quantity' => 'required|integer|min:0',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_profit_margin' => 'nullable|numeric|min:0|max:100',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Show the import from AliExpress form.
     */
    public function import()
    {
        $categories = Category::active()->get();
        return view('products.import', compact('categories'));
    }

    /**
     * Search products on AliExpress.
     */
    public function searchAliexpress(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|min:2',
        ]);

        try {
            $result = $this->aliexpressService->searchProducts(
                $request->keyword,
                [
                    'limit' => $request->get('per_page', 20),
                    'category_id' => $request->get('category_id'),
                    'country' => $request->get('country', 'US'),
                    'currency' => $request->get('currency', 'USD'),
                    'language' => $request->get('language', 'EN'),
                ]
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'products' => $result['products'],
                    'total_count' => $result['total_count'] ?? 0,
                    'current_count' => $result['current_count'] ?? 0,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? $result['message'] ?? 'Failed to search products',
                'message' => $result['message'] ?? null,
            ], 400);

        } catch (\Exception $e) {
            Log::error('AliExpress Search Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'details' => 'Check storage/logs/laravel.log for more details'
            ], 500);
        }
    }

    /**
     * Import a product from AliExpress.
     */
    public function importFromAliexpress(Request $request)
    {
        $request->validate([
            'aliexpress_id' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'profit_margin' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $result = $this->aliexpressService->getProductDetails(
                $request->aliexpress_id,
                [
                    'country' => $request->get('country', 'US'),
                    'currency' => $request->get('currency', 'USD'),
                    'language' => $request->get('language', 'EN'),
                ]
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to get product details.',
                ], 400);
            }

            $productData = $result['product'];
            $profitMargin = $request->get('profit_margin', 30.0);

            // Calculate pricing
            $aliexpressPrice = $productData['target_sale_price'] ?? $productData['target_original_price'] ?? 0;
            $cost = $aliexpressPrice;
            $price = $cost * (1 + ($profitMargin / 100));

            // Create product
            $product = Product::create([
                'name' => $productData['subject'] ?? 'Imported Product',
                'slug' => Str::slug($productData['subject'] ?? 'imported-product-' . $request->aliexpress_id),
                'description' => $productData['detail'] ?? '',
                'short_description' => isset($productData['subject']) ? substr($productData['subject'], 0, 500) : '',
                'price' => round($price, 2),
                'compare_price' => round($price * 1.2, 2),
                'cost' => round($cost, 2),
                'sku' => 'AE-' . $request->aliexpress_id,
                'stock_quantity' => 100,
                'track_inventory' => true,
                'is_active' => false,
                'category_id' => $request->category_id,
                'aliexpress_id' => $request->aliexpress_id,
                'aliexpress_url' => $productData['product_detail_url'] ?? "https://www.aliexpress.com/item/{$request->aliexpress_id}.html",
                'aliexpress_price' => $aliexpressPrice,
                'supplier_profit_margin' => $profitMargin,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product imported successfully.',
                'product' => $product,
            ]);

        } catch (\Exception $e) {
            Log::error('AliExpress Import Error', [
                'message' => $e->getMessage(),
                'product_id' => $request->aliexpress_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'details' => 'Check storage/logs/laravel.log for more details'
            ], 500);
        }
    }

    /**
     * Sync product with AliExpress.
     */
    public function sync(Product $product)
    {
        if (!$product->isAliexpressProduct()) {
            return redirect()->back()
                ->with('error', 'This product is not from AliExpress.');
        }

        try {
            $result = $this->aliexpressService->getProductDetails(
                $product->aliexpress_id,
                [
                    'country' => 'US',
                    'currency' => 'USD',
                    'language' => 'EN',
                ]
            );

            if (!$result['success']) {
                return redirect()->back()
                    ->with('error', 'Failed to sync product: ' . ($result['error'] ?? 'Unknown error'));
            }

            $productData = $result['product'];
            $profitMargin = $product->supplier_profit_margin ?? 30.0;

            // Calculate pricing
            $aliexpressPrice = $productData['target_sale_price'] ?? $productData['target_original_price'] ?? 0;
            $cost = $aliexpressPrice;
            $price = $cost * (1 + ($profitMargin / 100));

            // Update product
            $product->update([
                'name' => $productData['subject'] ?? $product->name,
                'description' => $productData['detail'] ?? $product->description,
                'price' => round($price, 2),
                'cost' => round($cost, 2),
                'aliexpress_price' => $aliexpressPrice,
                'last_synced_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', 'Product synced successfully.');

        } catch (\Exception $e) {
            Log::error('AliExpress Sync Error', [
                'message' => $e->getMessage(),
                'product_id' => $product->id,
                'aliexpress_id' => $product->aliexpress_id,
            ]);

            return redirect()->back()
                ->with('error', 'Failed to sync product. Please try again later.');
        }
    }

    /**
     * Bulk sync all AliExpress products.
     */
    public function syncAll()
    {
        $products = Product::fromAliexpress()->get();
        $synced = 0;
        $failed = 0;

        foreach ($products as $product) {
            try {
                $result = $this->aliexpressService->getProductDetails(
                    $product->aliexpress_id,
                    [
                        'country' => 'US',
                        'currency' => 'USD',
                        'language' => 'EN',
                    ]
                );

                if ($result['success']) {
                    $productData = $result['product'];
                    $profitMargin = $product->supplier_profit_margin ?? 30.0;

                    // Calculate pricing
                    $aliexpressPrice = $productData['target_sale_price'] ?? $productData['target_original_price'] ?? 0;
                    $cost = $aliexpressPrice;
                    $price = $cost * (1 + ($profitMargin / 100));

                    // Update product
                    $product->update([
                        'name' => $productData['subject'] ?? $product->name,
                        'description' => $productData['detail'] ?? $product->description,
                        'price' => round($price, 2),
                        'cost' => round($cost, 2),
                        'aliexpress_price' => $aliexpressPrice,
                        'last_synced_at' => now(),
                    ]);

                    $synced++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                Log::error('AliExpress Bulk Sync Error', [
                    'message' => $e->getMessage(),
                    'product_id' => $product->id,
                    'aliexpress_id' => $product->aliexpress_id,
                ]);
                $failed++;
            }
        }

        return redirect()->back()
            ->with('success', "Synced {$synced} products. {$failed} failed.");
    }

    /**
     * Show AliExpress product search page (text search).
     */
    public function searchPage()
    {
        // Get only active categories with AliExpress IDs
        $allCategories = Category::where('aliexpress_category_id', '!=', null)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Separate main categories (no parent) and subcategories
        $mainCategories = $allCategories->whereNull('parent_id');

        // Organize subcategories by parent
        $categoriesWithChildren = $mainCategories->map(function($parent) use ($allCategories) {
            $parent->children = $allCategories->where('parent_id', $parent->id)->values();
            return $parent;
        });

        // Get assigned products for current user (if seller)
        $assignedProductIds = [];
        if (auth()->check() && auth()->user()->user_type === 'seller') {
            $assignedProductIds = \DB::table('product_user')
                ->where('user_id', auth()->id())
                ->pluck('aliexpress_product_id')
                ->toArray();
        }

        return view('products.search', [
            'categories' => $categoriesWithChildren,
            'allCategories' => $allCategories,
            'assignedProductIds' => $assignedProductIds,
        ]);
    }

    /**
     * Search products using AliExpress Text Search API.
     */
    public function searchByText(Request $request)
    {
        // Validate: keyword is required unless category_id is provided
        $request->validate([
            'keyword' => 'required_without:category_id|nullable|string|min:2',
            'category_id' => 'nullable|string',
        ]);

        try {
            $keyword = $request->keyword ?? '';
            $categoryId = $request->get('category_id');
            $sortFilter = $request->get('sort_filter', 'orders');

            // Map sort filter to API sort_by parameter
            $sortByMap = [
                'orders' => 'orders,desc',        // Best Seller
                'newest' => null,                 // Will use generic keyword 'new'
                'price_low' => 'min_price,asc',   // Price: Low to High
                'price_high' => 'min_price,desc', // Price: High to Low
                'rating' => 'comments,desc',      // Top Rated
            ];

            $sortBy = $sortByMap[$sortFilter] ?? null;

            // Separate: Category selection vs Keyword search
            if (!empty($categoryId)) {
                // Category selected - get products from category immediately
                Log::info('Getting products by category', [
                    'category_id' => $categoryId,
                    'sort_filter' => $sortFilter
                ]);

                // Adjust keywords based on sort filter
                $genericKeywords = ['new', 'best', 'top', 'hot', 'sale', 'fashion', 'quality', 'style'];

                // For 'newest' filter, prioritize 'new' keyword
                if ($sortFilter === 'newest') {
                    $genericKeywords = ['new', '2025', 'latest', 'fresh', 'recent', 'best', 'top', 'hot'];
                }
                // Add single letters for broader search
                $genericKeywords = array_merge($genericKeywords, ['a', 's', 't', 'e', 'i', 'o', 'n', 'r']);

                $result = null;
                $bestResult = null;
                $maxProducts = 0;

                foreach ($genericKeywords as $testKeyword) {
                    $tempResult = $this->aliexpressTextService->searchProductsByText(
                        $testKeyword,
                        [
                            'page' => $request->get('page', 1),
                            'limit' => $request->get('per_page', 10),
                            'category_id' => $categoryId,
                            'sort_by' => $sortBy, // Use mapped sort parameter
                            'country' => $request->get('country', 'AE'),
                            'currency' => $request->get('currency', 'AED'),
                            'locale' => $request->get('locale', 'en_US'),
                        ]
                    );

                    $productCount = $tempResult['total_count'] ?? 0;

                    // Keep the result with most products
                    if ($productCount > $maxProducts) {
                        $maxProducts = $productCount;
                        $bestResult = $tempResult;

                        Log::info('Better result found', [
                            'keyword' => $testKeyword,
                            'count' => count($tempResult['products'] ?? []),
                            'total' => $productCount
                        ]);
                    }

                    // If we found 100+ products, that's excellent - stop searching
                    if ($productCount >= 100) {
                        break;
                    }
                }

                // Use the best result we found
                $result = $bestResult ?? [
                    'products' => [],
                    'total_count' => 0,
                    'current_page' => 1,
                    'page_size' => 0,
                ];

                if (empty($result['products'])) {
                    Log::warning('No products found for category', ['category_id' => $categoryId]);
                }
            } elseif (!empty($keyword)) {
                // Keyword search - search products by keyword only
                Log::info('Searching products by keyword', [
                    'keyword' => $keyword,
                    'sort_filter' => $sortFilter
                ]);

                $result = $this->aliexpressTextService->searchProductsByText(
                    $keyword,
                    [
                        'page' => $request->get('page', 1),
                        'limit' => $request->get('per_page', 10),
                        'sort_by' => $sortBy, // Use mapped sort parameter
                        'country' => $request->get('country', 'AE'),
                        'currency' => $request->get('currency', 'AED'),
                        'locale' => $request->get('locale', 'en_US'),
                    ]
                );
            } else {
                // No category and no keyword selected
                $result = [
                    'products' => [],
                    'total_count' => 0,
                    'current_page' => 1,
                    'page_size' => 0,
                ];
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'products' => $result['products'],
                    'total_count' => $result['total_count'] ?? 0,
                    'current_page' => $result['current_page'] ?? 1,
                    'page_size' => $result['page_size'] ?? 50,
                    'debug' => $request->get('debug') ? $result['debug'] : null,
                ]);
            }

            // Get only active categories with AliExpress IDs
            $allCategories = Category::where('aliexpress_category_id', '!=', null)
                ->where('is_active', true)
                ->orderBy('order')
                ->get();

            // Separate main categories (no parent) and subcategories
            $mainCategories = $allCategories->whereNull('parent_id');

            // Organize subcategories by parent
            $categoriesWithChildren = $mainCategories->map(function($parent) use ($allCategories) {
                $parent->children = $allCategories->where('parent_id', $parent->id)->values();
                return $parent;
            });

            // Get assigned products for current user (if seller)
            $assignedProductIds = [];
            if (auth()->check() && auth()->user()->user_type === 'seller') {
                $assignedProductIds = \DB::table('product_user')
                    ->where('user_id', auth()->id())
                    ->pluck('aliexpress_product_id')
                    ->toArray();
            }

            return view('products.search', [
                'products' => $result['products'],
                'total_count' => $result['total_count'] ?? 0,
                'keyword' => $keyword,
                'categories' => $categoriesWithChildren,
                'allCategories' => $allCategories,
                'assignedProductIds' => $assignedProductIds,
                'debug' => $request->get('debug') ? $result : null,
            ]);

        } catch (\Exception $e) {
            Log::error('AliExpress Text Search Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'details' => 'Check storage/logs/laravel.log for more details'
                ], 500);
            }

            return back()->with('error', 'Failed to search products: ' . $e->getMessage());
        }
    }

    /**
     * Assign AliExpress product to current seller
     */
    public function assignProduct(Request $request)
    {
        $request->validate([
            'aliexpress_product_id' => 'required|string',
            'product_title' => 'required|string',
            'product_image' => 'nullable|string',
            'product_price' => 'nullable|numeric',
            'currency' => 'nullable|string|max:3',
        ]);

        $user = auth()->user();

        // Check if user is a seller
        if ($user->user_type !== 'seller') {
            return response()->json([
                'success' => false,
                'message' => 'Only sellers can assign products.'
            ], 403);
        }

        $aliexpressProductId = $request->aliexpress_product_id;

        // Check if already assigned to this user (check both through relationship and direct DB)
        $alreadyAssigned = $user->assignedProducts()
            ->wherePivot('aliexpress_product_id', $aliexpressProductId)
            ->exists();

        // Also check direct assignment without product_id
        if (!$alreadyAssigned) {
            $alreadyAssigned = \DB::table('product_user')
                ->where('user_id', $user->id)
                ->where('aliexpress_product_id', $aliexpressProductId)
                ->exists();
        }

        if ($alreadyAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'This product is already assigned to you.'
            ], 400);
        }

        // Check if product already exists in products table
        $product = Product::where('aliexpress_id', $aliexpressProductId)->first();

        if (!$product) {
            // Create the product in products table
            $product = Product::create([
                'name' => $request->product_title,
                'slug' => \Str::slug($request->product_title) . '-' . $aliexpressProductId,
                'description' => 'Product imported from AliExpress',
                'price' => $request->product_price ?? 0,
                'currency' => $request->currency ?? 'AED',
                'original_price' => $request->product_price ?? 0,
                'images' => $request->product_image ? [$request->product_image] : [],
                'aliexpress_id' => $aliexpressProductId,
                'aliexpress_price' => $request->product_price ?? 0,
                'stock_quantity' => 0,
                'is_active' => false, // Set as inactive until seller publishes
            ]);
        }

        // Assign product to user via pivot table
        $user->assignedProducts()->attach($product->id, [
            'aliexpress_product_id' => $aliexpressProductId,
            'status' => 'assigned'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product assigned successfully! You can now view it in "My Assigned Products".'
        ]);
    }

    /**
     * Get assigned products for current seller
     */
    public function myAssignedProducts()
    {
        $user = auth()->user();

        if ($user->user_type !== 'seller') {
            return redirect()->back()->with('error', 'Only sellers can view assigned products.');
        }

        $assignedProducts = $user->assignedProducts()
            ->withPivot('aliexpress_product_id', 'status')
            ->orderBy('product_user.created_at', 'desc')
            ->paginate(20);

        return view('products.assigned', compact('assignedProducts'));
    }
}
