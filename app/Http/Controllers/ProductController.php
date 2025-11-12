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

        // Fetch AliExpress details if it's an AliExpress product
        $aliexpressData = null;
        if ($product->isAliexpressProduct()) {
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
                    $aliexpressData = $result['product'];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch AliExpress details for product view', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                ]);

                // Fallback to stored data if API call fails
                if ($product->aliexpress_data) {
                    $aliexpressData = $product->aliexpress_data;
                    Log::info('Using stored AliExpress data as fallback');
                }
            }
        }

        return view('products.show', compact('product', 'categories', 'aliexpressData'));
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
            // original_price is NOT validated - it should never be updated
            'seller_amount' => 'nullable|numeric|min:0',
            'admin_amount' => 'nullable|numeric|min:0',
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

        // Remove original_price from update if it was sent in the request
        // This ensures it can never be changed via the edit form
        unset($validated['original_price']);

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

            // ========== VALIDATE SKU DATA (REQUIRED FOR ORDERING) ==========
            $hasSKUData = isset($productData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'])
                || isset($productData['aeop_ae_product_s_k_us']);

            if (!$hasSKUData) {
                Log::warning('Product imported without SKU data - orders may fail', [
                    'product_id' => $request->aliexpress_id,
                    'data_keys' => array_keys($productData)
                ]);
            }

            // Calculate pricing
            $aliexpressPrice = $productData['target_sale_price'] ?? $productData['target_original_price'] ?? 0;
            $currency = $request->get('currency', 'USD');
            $cost = $aliexpressPrice;

            // Calculate price with profit margin
            $profitAmount = $cost * ($profitMargin / 100);
            $price = $cost + $profitAmount;

            // ========== GET PRODUCT IMAGES ==========
            $images = [];
            if (isset($productData['images']) && is_array($productData['images'])) {
                $images = $productData['images'];
            } elseif (isset($productData['image_url'])) {
                $images = [$productData['image_url']];
            } elseif (isset($productData['ae_multimedia_info_dto']['image_urls'])) {
                // Extract from multimedia info (common in ds.product.get)
                $imageUrls = $productData['ae_multimedia_info_dto']['image_urls'];
                if (is_string($imageUrls)) {
                    $images = array_filter(explode(';', $imageUrls));
                }
            }

            // ========== EXTRACT SKU VARIANTS FOR QUICK ACCESS ==========
            $skuVariants = null;
            if (isset($productData['ae_item_sku_info_dtos'])) {
                $skuVariants = $productData['ae_item_sku_info_dtos'];
            } elseif (isset($productData['aeop_ae_product_s_k_us'])) {
                $skuVariants = $productData['aeop_ae_product_s_k_us'];
            }

            // ========== GET PRODUCT NAME ==========
            $productName = $productData['subject']
                ?? $productData['ae_item_base_info_dto']['subject']
                ?? 'Imported Product';

            // ========== GET PRODUCT DESCRIPTION ==========
            $description = $productData['detail']
                ?? $productData['ae_item_base_info_dto']['detail']
                ?? 'Product imported from AliExpress';

            // Create product with complete data
            $product = Product::create([
                'name' => $productName,
                'slug' => Str::slug($productName . '-' . $request->aliexpress_id),
                'description' => $description,
                'short_description' => substr($productName, 0, 500),
                'price' => round($price, 2),
                'currency' => $currency,
                'original_price' => round($aliexpressPrice, 2),
                'seller_amount' => null,
                'admin_amount' => round($profitAmount, 2),
                'compare_price' => round($price * 1.2, 2),
                'cost' => round($cost, 2),
                'sku' => 'AE-' . $request->aliexpress_id,
                'stock_quantity' => 100,
                'track_inventory' => false, // Dropshipping - no inventory tracking
                'is_active' => false, // Set to false until reviewed
                'category_id' => $request->category_id,
                'aliexpress_id' => $request->aliexpress_id,
                'aliexpress_url' => $productData['product_detail_url'] ?? "https://www.aliexpress.com/item/{$request->aliexpress_id}.html",
                'aliexpress_price' => $aliexpressPrice,
                'supplier_profit_margin' => $profitMargin,
                'aliexpress_data' => $productData, // ✅ CRITICAL: Complete API response with SKU data
                'aliexpress_variants' => $skuVariants, // ✅ Extracted SKU variants for quick access
                'images' => $images,
                'last_synced_at' => now(), // Mark as synced
            ]);

            Log::info('Product imported successfully', [
                'product_id' => $product->id,
                'aliexpress_id' => $request->aliexpress_id,
                'has_sku_data' => $hasSKUData,
                'sku_count' => isset($skuVariants['ae_item_sku_info_d_t_o']) ? count($skuVariants['ae_item_sku_info_d_t_o']) : 0,
                'image_count' => count($images)
            ]);

            // Build success message with order readiness info
            $message = 'Product imported successfully.';
            if ($hasSKUData) {
                $skuCount = isset($skuVariants['ae_item_sku_info_d_t_o']) ? count($skuVariants['ae_item_sku_info_d_t_o']) : 0;
                $message .= " ✅ Ready for ordering ({$skuCount} variants available).";
            } else {
                $message .= " ⚠️ Warning: Product may not be ready for ordering (missing SKU data). Run sync command to fix.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'product' => $product,
                'order_ready' => $hasSKUData,
                'sku_count' => isset($skuVariants['ae_item_sku_info_d_t_o']) ? count($skuVariants['ae_item_sku_info_d_t_o']) : 0,
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
            Log::info('Starting product sync', [
                'product_id' => $product->id,
                'aliexpress_id' => $product->aliexpress_id,
                'current_price' => $product->price,
                'current_original_price' => $product->original_price,
                'current_seller_amount' => $product->seller_amount,
                'current_admin_amount' => $product->admin_amount,
            ]);

            $result = $this->aliexpressService->getProductDetails(
                $product->aliexpress_id,
                [
                    'country' => 'US',
                    'currency' => 'USD',
                    'language' => 'EN',
                ]
            );

            if (!$result['success']) {
                Log::error('Sync failed - API error', [
                    'product_id' => $product->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
                return redirect()->back()
                    ->with('error', 'Failed to sync product: ' . ($result['error'] ?? 'Unknown error'));
            }

            $productData = $result['product'];

            Log::info('Product data received from AliExpress', [
                'product_id' => $product->id,
                'raw_data' => json_encode($productData, JSON_PRETTY_PRINT),
                'target_sale_price' => $productData['target_sale_price'] ?? 'NOT SET',
                'target_original_price' => $productData['target_original_price'] ?? 'NOT SET',
            ]);

            $profitMargin = $product->supplier_profit_margin ?? 30.0;

            // Calculate pricing - extract from SKU data if target prices not available
            $aliexpressPrice = $productData['target_sale_price'] ?? $productData['target_original_price'] ?? 0;

            // If no target price, try to get from first SKU
            if ($aliexpressPrice == 0 && isset($productData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'][0])) {
                $firstSku = $productData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'][0];
                $aliexpressPrice = $firstSku['offer_sale_price'] ?? $firstSku['sku_price'] ?? 0;

                Log::info('Extracted price from SKU data', [
                    'product_id' => $product->id,
                    'sku_offer_sale_price' => $firstSku['offer_sale_price'] ?? 'not set',
                    'sku_price' => $firstSku['sku_price'] ?? 'not set',
                    'extracted_price' => $aliexpressPrice,
                ]);
            }

            $cost = $aliexpressPrice;
            $price = $cost * (1 + ($profitMargin / 100));

            Log::info('Price calculation details', [
                'product_id' => $product->id,
                'aliexpress_price' => $aliexpressPrice,
                'profit_margin' => $profitMargin,
                'calculated_cost' => $cost,
                'calculated_price' => $price,
                'rounded_price' => round($price, 2),
            ]);

            // Update product - DO NOT update original_price, seller_amount, or admin_amount during sync
            $updateData = [
                'name' => $productData['subject'] ?? $product->name,
                'description' => $productData['detail'] ?? $product->description,
                'price' => round($price, 2),
                'cost' => round($cost, 2),
                'aliexpress_price' => $aliexpressPrice,
                'aliexpress_data' => $productData, // Store complete API response
                'last_synced_at' => now(),
            ];

            Log::info('Updating product with data', [
                'product_id' => $product->id,
                'update_data' => $updateData,
            ]);

            $product->update($updateData);

            Log::info('Product synced successfully', [
                'product_id' => $product->id,
                'new_price' => $product->fresh()->price,
                'new_aliexpress_price' => $product->fresh()->aliexpress_price,
            ]);

            return redirect()->back()
                ->with('success', 'Product synced successfully.');

        } catch (\Exception $e) {
            Log::error('AliExpress Sync Error', [
                'message' => $e->getMessage(),
                'product_id' => $product->id,
                'aliexpress_id' => $product->aliexpress_id,
                'trace' => $e->getTraceAsString(),
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
            $localCategoryId = $request->get('category_id');
            $sortFilter = $request->get('sort_filter', 'orders');

            // Get AliExpress category ID from our local category
            $aliexpressCategoryId = null;
            if (!empty($localCategoryId)) {
                $category = \App\Models\Category::find($localCategoryId);
                if ($category && $category->aliexpress_category_id) {
                    $aliexpressCategoryId = $category->aliexpress_category_id;
                    Log::info('Mapped local category to AliExpress category', [
                        'local_category_id' => $localCategoryId,
                        'aliexpress_category_id' => $aliexpressCategoryId
                    ]);
                } else {
                    Log::warning('Category not found or missing AliExpress ID', [
                        'local_category_id' => $localCategoryId
                    ]);
                }
            }

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
            if (!empty($aliexpressCategoryId)) {
                // Category selected - get products from category immediately
                Log::info('Getting products by category', [
                    'local_category_id' => $localCategoryId,
                    'aliexpress_category_id' => $aliexpressCategoryId,
                    'sort_filter' => $sortFilter
                ]);

                // Use more aggressive keywords and broader search terms for categories
                $genericKeywords = [
                    // Common generic terms
                    'a', 'e', 'i', 'o', 'new', 'best', 'top', 'hot', 'sale',
                    'fashion', 'quality', 'style', 'popular', 'latest', 'trending',
                    // Single letters for maximum coverage
                    's', 't', 'n', 'r', 'l', 'c', 'd', 'm', 'p', 'h',
                    // Numbers and years
                    '2025', '2024', 'pro', 'plus', 'max'
                ];

                // For 'newest' filter, prioritize 'new' keyword
                if ($sortFilter === 'newest') {
                    $genericKeywords = array_merge(
                        ['new', '2025', 'latest', 'fresh', 'recent'],
                        $genericKeywords
                    );
                }

                $result = null;
                $bestResult = null;
                $maxProducts = 0;
                $maxAttempts = 15; // Increase attempts to find products
                $attemptCount = 0;

                foreach ($genericKeywords as $testKeyword) {
                    if ($attemptCount >= $maxAttempts) {
                        break;
                    }
                    $attemptCount++;

                    $tempResult = $this->aliexpressTextService->searchProductsByText(
                        $testKeyword,
                        [
                            'page' => $request->get('page', 1),
                            'limit' => $request->get('per_page', 50), // Increased from 10 to 50
                            'category_id' => $aliexpressCategoryId,
                            'sort_by' => $sortBy, // Use mapped sort parameter
                            'country' => $request->get('country', 'AE'),
                            'currency' => $request->get('currency', 'AED'),
                            'locale' => $request->get('locale', 'en_US'),
                        ]
                    );

                    $productCount = $tempResult['total_count'] ?? 0;
                    $returnedProducts = count($tempResult['products'] ?? []);

                    Log::info('Category search attempt', [
                        'keyword' => $testKeyword,
                        'attempt' => $attemptCount,
                        'returned_products' => $returnedProducts,
                        'total_count' => $productCount
                    ]);

                    // Keep the result with most products
                    if ($productCount > $maxProducts) {
                        $maxProducts = $productCount;
                        $bestResult = $tempResult;

                        Log::info('Better result found', [
                            'keyword' => $testKeyword,
                            'count' => $returnedProducts,
                            'total' => $productCount
                        ]);
                    }

                    // If we found 500+ products total, that's excellent - stop searching
                    if ($productCount >= 500) {
                        Log::info('Found excellent category result', [
                            'keyword' => $testKeyword,
                            'total_count' => $productCount
                        ]);
                        break;
                    }

                    // If we got 30+ actual products returned, that's good enough
                    if ($returnedProducts >= 30 && $productCount >= 100) {
                        Log::info('Found good category result', [
                            'keyword' => $testKeyword,
                            'returned' => $returnedProducts,
                            'total' => $productCount
                        ]);
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
                    Log::warning('No products found for category', [
                        'local_category_id' => $localCategoryId,
                        'aliexpress_category_id' => $aliexpressCategoryId,
                        'attempts' => $attemptCount
                    ]);
                } else {
                    Log::info('Final category search result', [
                        'local_category_id' => $localCategoryId,
                        'aliexpress_category_id' => $aliexpressCategoryId,
                        'products_returned' => count($result['products']),
                        'total_count' => $result['total_count'] ?? 0,
                        'attempts_made' => $attemptCount
                    ]);
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

            // Add admin profit to each product price
            if (!empty($result['products'])) {
                foreach ($result['products'] as &$product) {
                    // Get the base price (sale price)
                    $basePrice = (float)($product['sale_price'] ?? 0);

                    // Calculate admin profit
                    $adminProfit = admin_profit($basePrice);

                    // Calculate final price (base price + admin profit)
                    $finalPrice = $basePrice + $adminProfit;

                    // Store original price for reference
                    $product['original_sale_price'] = $basePrice;
                    $product['admin_profit'] = $adminProfit;

                    // Update sale price to include admin profit
                    $product['sale_price'] = $finalPrice;

                    // Update formatted price if it exists
                    if (isset($product['sale_price_format'])) {
                        $currency = $request->get('currency', 'AED');
                        $product['original_sale_price_format'] = $product['sale_price_format'];
                        $product['sale_price_format'] = $currency . ' ' . number_format($finalPrice, 2);
                    }

                    // Also update original price if needed
                    if (isset($product['original_price'])) {
                        $originalBasePrice = (float)$product['original_price'];
                        $originalAdminProfit = admin_profit($originalBasePrice);
                        $product['original_aliexpress_price'] = $originalBasePrice;
                        $product['original_price'] = $originalBasePrice + $originalAdminProfit;

                        if (isset($product['original_price_format'])) {
                            $currency = $request->get('currency', 'AED');
                            $product['original_price_format'] = $currency . ' ' . number_format($product['original_price'], 2);
                        }
                    }
                }
                unset($product); // Break reference
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
     * Assign AliExpress product(s) to current seller
     * Supports both single and bulk assignments
     */
    public function assignProduct(Request $request)
    {
        $user = auth()->user();

        // Check if user is a seller
        if ($user->user_type !== 'seller') {
            return response()->json([
                'success' => false,
                'message' => 'Only sellers can assign products.'
            ], 403);
        }

        // Check if this is a bulk assignment
        if ($request->has('products') && is_array($request->products)) {
            return $this->bulkAssignProducts($request, $user);
        }

        // Single product assignment
        $request->validate([
            'aliexpress_product_id' => 'required|string',
            'product_title' => 'required|string',
            'product_image' => 'nullable|string',
            'product_price' => 'nullable|numeric',
            'currency' => 'nullable|string|max:3',
            'category_id' => 'nullable|exists:categories,id',
        ]);

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

        $basePrice = $request->product_price ?? 0;
        $sellerAmount = 0;
        $finalPrice = $basePrice;

        // Apply seller's subcategory profit if category is provided
        if ($request->category_id) {
            $profitSetting = $user->getProfitForSubcategory($request->category_id);

            if ($profitSetting) {
                $sellerAmount = $profitSetting->calculateProfit($basePrice);
                $finalPrice = $profitSetting->calculateFinalPrice($basePrice);

                \Log::info('Seller Profit Applied', [
                    'seller_id' => $user->id,
                    'category_id' => $request->category_id,
                    'base_price' => $basePrice,
                    'profit_type' => $profitSetting->profit_type,
                    'profit_value' => $profitSetting->profit_value,
                    'seller_amount' => $sellerAmount,
                    'final_price' => $finalPrice,
                ]);
            }
        }

        if (!$product) {
            // Create the product in products table
            $product = Product::create([
                'name' => $request->product_title,
                'slug' => \Str::slug($request->product_title) . '-' . $aliexpressProductId,
                'description' => 'Product imported from AliExpress',
                'price' => $finalPrice,
                'currency' => $request->currency ?? 'AED',
                'original_price' => $basePrice,
                'seller_amount' => $sellerAmount,
                'images' => $request->product_image ? [$request->product_image] : [],
                'aliexpress_id' => $aliexpressProductId,
                'aliexpress_price' => $basePrice,
                'category_id' => $request->category_id,
                'stock_quantity' => 0,
                'is_active' => false, // Set as inactive until seller publishes
            ]);
        } else {
            // Update existing product with seller's profit
            $product->update([
                'price' => $finalPrice,
                'seller_amount' => $sellerAmount,
                'category_id' => $request->category_id ?? $product->category_id,
            ]);
        }

        // Assign product to user via pivot table
        $user->assignedProducts()->attach($product->id, [
            'aliexpress_product_id' => $aliexpressProductId,
            'status' => 'assigned'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product assigned successfully! You can now view it in "My Assigned Products".',
            'applied_profit' => $sellerAmount > 0,
            'profit_amount' => $sellerAmount,
            'final_price' => $finalPrice,
        ]);
    }

    /**
     * Bulk assign multiple products to seller
     */
    protected function bulkAssignProducts(Request $request, $user)
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.aliexpress_product_id' => 'required|string',
            'products.*.product_title' => 'required|string',
            'products.*.product_image' => 'nullable|string',
            'products.*.product_price' => 'nullable|numeric',
            'products.*.currency' => 'nullable|string|max:3',
        ]);

        $products = $request->products;
        $assignedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($products as $productData) {
            try {
                $aliexpressProductId = $productData['aliexpress_product_id'];

                // Check if already assigned
                $alreadyAssigned = $user->assignedProducts()
                    ->wherePivot('aliexpress_product_id', $aliexpressProductId)
                    ->exists();

                if (!$alreadyAssigned) {
                    $alreadyAssigned = \DB::table('product_user')
                        ->where('user_id', $user->id)
                        ->where('aliexpress_product_id', $aliexpressProductId)
                        ->exists();
                }

                if ($alreadyAssigned) {
                    $skippedCount++;
                    continue;
                }

                // Check if product exists
                $product = Product::where('aliexpress_id', $aliexpressProductId)->first();

                $basePrice = $productData['product_price'] ?? 0;
                $sellerAmount = 0;
                $finalPrice = $basePrice;

                if (!$product) {
                    // Create new product
                    $product = Product::create([
                        'name' => $productData['product_title'],
                        'slug' => \Str::slug($productData['product_title']) . '-' . $aliexpressProductId,
                        'description' => 'Product imported from AliExpress',
                        'price' => $finalPrice,
                        'currency' => $productData['currency'] ?? 'AED',
                        'original_price' => $basePrice,
                        'seller_amount' => $sellerAmount,
                        'images' => isset($productData['product_image']) ? [$productData['product_image']] : [],
                        'aliexpress_id' => $aliexpressProductId,
                        'aliexpress_price' => $basePrice,
                        'stock_quantity' => 0,
                        'is_active' => false,
                    ]);
                }

                // Assign to user
                $user->assignedProducts()->attach($product->id, [
                    'aliexpress_product_id' => $aliexpressProductId,
                    'status' => 'assigned'
                ]);

                $assignedCount++;

            } catch (\Exception $e) {
                \Log::error('Bulk assign error', [
                    'product_id' => $productData['aliexpress_product_id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $errors[] = $e->getMessage();
            }
        }

        $message = "Successfully assigned $assignedCount product(s)";
        if ($skippedCount > 0) {
            $message .= " ($skippedCount already assigned)";
        }
        if (count($errors) > 0) {
            $message .= ". " . count($errors) . " failed";
        }

        return response()->json([
            'success' => $assignedCount > 0,
            'message' => $message,
            'assigned_count' => $assignedCount,
            'skipped_count' => $skippedCount,
            'error_count' => count($errors),
            'errors' => $errors
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
