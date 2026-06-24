<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\AliExpressDropshippingService;
use App\Services\AliexpressTextService;
use App\Services\AliExpressService;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $aliexpressService;
    protected $aliexpressTextService;
    protected $aliexpressCategoryService;
    protected $translationService;

    public function __construct(
        AliExpressDropshippingService $aliexpressService,
        AliexpressTextService $aliexpressTextService,
        AliExpressService $aliexpressCategoryService,
        TranslationService $translationService
    )
    {
        $this->aliexpressService = $aliexpressService;
        $this->aliexpressTextService = $aliexpressTextService;
        $this->aliexpressCategoryService = $aliexpressCategoryService;
        $this->translationService = $translationService;
    }

    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'assignedUsers']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
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
            } elseif ($request->source === 'local') {
                $query->whereNull('aliexpress_id');
            }
        }

        // Filter by seller / distributor
        if ($request->filled('seller')) {
            $sellerId = $request->seller;
            $query->whereHas('assignedUsers', function ($q) use ($sellerId) {
                $q->where('users.id', $sellerId);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        $categories = $this->getFilteredCategoriesForUser();

        // All sellers/distributors who have assigned products (for filter dropdown)
        $sellers = \App\Models\User::whereIn('user_type', ['seller', 'distributor'])
            ->whereHas('assignedProducts')
            ->orderBy('name')
            ->get(['id', 'name', 'store_name', 'user_type', 'logo']);

        // AED exchange rate for price conversion
        $aedCurrency = \App\Models\Currency::where('code', 'AED')->first();
        $usdToAed = $aedCurrency ? (float) $aedCurrency->exchange_rate : 3.67;

        return view('products.index', compact('products', 'categories', 'sellers', 'usdToAed'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        // Filter categories for sellers based on their selected categories
        $categories = $this->getFilteredCategoriesForUser();
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
        // Filter categories for sellers based on their selected categories
        $categories = $this->getFilteredCategoriesForUser();

        // Fetch AliExpress details if it's an AliExpress product
        $aliexpressData = null;
        if ($product->isAliexpressProduct()) {
            try {
                $result = $this->aliexpressService->getProductDetails(
                    $product->aliexpress_id,
                    [
                        'country' => 'AE',
                        'currency' => 'AED',
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
     * Show enhanced product detail view with shipping calculator.
     */
    public function detail(Product $product, Request $request)
    {
        // Handle language switching
        if ($request->has('lang')) {
            $lang = $request->get('lang');
            if (in_array($lang, ['en', 'ar'])) {
                app()->setLocale($lang);
                session()->put('locale', $lang);
            }
        } elseif (session()->has('locale')) {
            app()->setLocale(session()->get('locale'));
        }

        $product->load('category');

        // Determine locale for AliExpress API
        $apiLocale = app()->getLocale() == 'ar' ? 'ar_MA' : 'en_US';
        $apiLanguage = app()->getLocale() == 'ar' ? 'AR' : 'EN';

        // Fetch AliExpress details if it's an AliExpress product
        $aliexpressData = null;
        if ($product->isAliexpressProduct()) {
            try {
                // Get country from session or use default based on locale
                $defaultCountry = app()->getLocale() == 'ar' ? 'AE' : 'AE';
                $country = session('shipping_country', $defaultCountry);

                $result = $this->aliexpressService->getProductDetails(
                    $product->aliexpress_id,
                    [
                        'country' => $country,
                        'currency' => 'AED',
                        'language' => $apiLanguage,
                        'locale' => $apiLocale,
                    ]
                );

                if ($result['success']) {
                    $aliexpressData = $result['product'];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch AliExpress details for product detail view', [
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

        // Get user's wallet balance
        $walletBalance = 0;
        if (auth()->check() && auth()->user()->wallet) {
            $walletBalance = auth()->user()->wallet->balance;
        }

        // Get seller's profit data from pivot table if logged in as seller
        $sellerPivotData = null;
        if (auth()->check() && auth()->user()->user_type === 'seller') {
            $pivotRecord = \DB::table('product_user')
                ->where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->first();

            if ($pivotRecord) {
                $sellerPivotData = [
                    'seller_amount' => $pivotRecord->seller_amount ?? 0,
                    'admin_amount' => $pivotRecord->admin_amount ?? 0,
                    'price' => $pivotRecord->price ?? $product->price,
                ];
            }
        }

        // Check if seller has active subscription or trial to create orders
        $canCreateOrder = true; // Default for non-sellers
        if (auth()->check() && auth()->user()->user_type === 'seller') {
            $canCreateOrder = auth()->user()->canAccessFullSystem();
        }

        return view('products.detail', compact('product', 'aliexpressData', 'walletBalance', 'sellerPivotData', 'canCreateOrder'));
    }

    /**
     * Show product detail for distributor products (local products).
     */
    public function detailDistributor(Product $product, Request $request)
    {
        // Handle language switching
        if ($request->has('lang')) {
            $lang = $request->get('lang');
            if (in_array($lang, ['en', 'ar'])) {
                app()->setLocale($lang);
                session()->put('locale', $lang);
            }
        } elseif (session()->has('locale')) {
            app()->setLocale(session()->get('locale'));
        }

        $product->load(['category', 'variations', 'assignedUsers']);

        // Get the distributor (first assigned user who is a distributor)
        $distributor = $product->assignedUsers()
            ->where('user_type', 'distributor')
            ->first();

        return view('products.detail-distributor', compact('product', 'distributor'));
    }

    /**
     * Debug SKUs to find numeric SKU IDs.
     */
    public function debugSkus(Product $product)
    {
        if (!$product->isAliexpressProduct()) {
            return response()->json([
                'error' => 'This product is not from AliExpress'
            ], 400);
        }

        try {
            // Fetch fresh product details from AliExpress
            $result = $this->aliexpressService->getProductDetails(
                $product->aliexpress_id,
                [
                    'country' => 'AE',
                    'currency' => 'AED',
                    'language' => 'EN',
                ]
            );

            if (!$result['success']) {
                return response()->json([
                    'error' => 'Failed to fetch product details from AliExpress'
                ], 500);
            }

            $aliexpressData = $result['product'];
            $skus = [];

            // Extract SKUs from ae_item_sku_info_dtos
            if (isset($aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'])) {
                $skuList = $aliexpressData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'];

                if (!isset($skuList[0])) {
                    $skuList = [$skuList];
                }

                foreach ($skuList as $index => $sku) {
                    $skuAnalysis = [
                        'index' => $index,
                        'all_fields' => array_keys($sku),
                        'id' => $sku['id'] ?? null,
                        'sku_id' => $sku['sku_id'] ?? null,
                        'sku_code' => $sku['sku_code'] ?? null,
                        'sku_attr' => $sku['sku_attr'] ?? null,
                        'sku_price' => $sku['sku_price'] ?? null,
                        'sku_stock' => $sku['sku_stock'] ?? $sku['sku_available_stock'] ?? null,
                        'is_id_numeric' => isset($sku['id']) && !str_contains((string)$sku['id'], '#'),
                        'properties' => $sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'] ?? null,
                        'raw' => $sku,
                    ];

                    $skus[] = $skuAnalysis;
                }
            }

            return response()->json([
                'product_id' => $product->id,
                'aliexpress_id' => $product->aliexpress_id,
                'sku_count' => count($skus),
                'skus' => $skus,
                'has_numeric_skus' => collect($skus)->contains('is_id_numeric', true),
                'numeric_skus' => collect($skus)->filter(fn($s) => $s['is_id_numeric'])->values(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Filter categories for sellers based on their selected categories
        $categories = $this->getFilteredCategoriesForUser();
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
        // Filter categories for sellers based on their selected categories
        $categories = $this->getFilteredCategoriesForUser();
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
                    'country' => $request->get('country', 'AE'),
                    'currency' => $request->get('currency', 'AED'),
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
                    'country' => $request->get('country', 'AE'),
                    'currency' => $request->get('currency', 'AED'),
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
                    'country' => 'AE',
                    'currency' => 'AED',
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
                        'country' => 'AE',
                        'currency' => 'AED',
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
        $query = Category::where('aliexpress_category_id', '!=', null)
            ->where('is_active', true);

        // Filter categories for sellers based on their selected categories
        // Admins can see all categories
        $user = auth()->user();
        if ($user && $user->user_type === 'seller') {
            // Decode the seller's selected categories
            $mainActivities = json_decode($user->main_activity, true) ?? [];
            $subActivities = json_decode($user->sub_activity, true) ?? [];

            // Combine both main and sub category IDs
            $allowedCategoryIds = array_merge($mainActivities, $subActivities);

            // Filter query to only show allowed categories
            if (!empty($allowedCategoryIds)) {
                $query->whereIn('id', $allowedCategoryIds);
            } else {
                // If no categories selected, show nothing
                $query->whereRaw('1 = 0');
            }
        }
        // Admin can see all categories - no filtering needed

        $allCategories = $query->orderBy('order')->get();

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

        // Get distributor countries dynamically from registered distributors
        $distributorCountries = \App\Models\User::where('user_type', 'distributor')
            ->whereNotNull('country')
            ->select('country')
            ->selectRaw('COUNT(*) as distributor_count')
            ->groupBy('country')
            ->get()
            ->map(function($item) {
                return [
                    'code' => $item->country,
                    'count' => $item->distributor_count,
                ];
            });

        // Get distributors grouped by country for the modal
        $distributorsByCountry = \App\Models\User::where('user_type', 'distributor')
            ->whereNotNull('country')
            ->select('id', 'name', 'store_name', 'avatar', 'country')
            ->get()
            ->groupBy('country');

        // Check if seller has active subscription or trial
        $canAssignProducts = false;
        if (auth()->check() && auth()->user()->user_type === 'seller') {
            $canAssignProducts = auth()->user()->canAccessFullSystem();
        }

        return view('products.search', [
            'categories' => $categoriesWithChildren,
            'allCategories' => $allCategories,
            'assignedProductIds' => $assignedProductIds,
            'distributorCountries' => $distributorCountries,
            'distributorsByCountry' => $distributorsByCountry,
            'canAssignProducts' => $canAssignProducts,
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
            $requestedCategoryId = $request->get('category_id');
            $sortFilter = $request->get('sort_filter', 'orders');

            // AED is the system base currency — always request AED from the API and store AED.
            // Conversion to the user's display currency happens in the view.
            $baseCurrency = 'AED';

            // Expand Arabic keyword synonyms so users can find products by alternative names
            $arabicSynonyms = [
                'تلفون'    => 'هاتف',
                'موبايل'   => 'هاتف',
                'جوال'     => 'هاتف',
                'ايفون'    => 'iPhone',
                'سامسونج'  => 'Samsung',
                'لابتوب'   => 'laptop',
                'كمبيوتر'  => 'computer',
                'تابلت'    => 'tablet',
                'ساعة ذكية'=> 'smart watch',
                'سماعة'    => 'headphone',
                'شاحن'     => 'charger',
                'كاميرا'   => 'camera',
                'طابعة'    => 'printer',
                'شاشة'     => 'monitor',
                'مكيف'     => 'air conditioner',
                'غسالة'    => 'washing machine',
                'ثلاجة'    => 'refrigerator',
                'مروحة'    => 'fan',
                'مكنسة'    => 'vacuum cleaner',
                'خاتم'     => 'ring',
                'عطر'      => 'perfume',
                'حذاء'     => 'shoes',
                'حقيبة'    => 'bag',
                'ملابس'    => 'clothes',
                'فستان'    => 'dress',
                'عباية'    => 'abaya',
                'لعبة'     => 'toy',
                'كتاب'     => 'book',
                'قلم'      => 'pen',
            ];
            if (!empty($keyword) && isset($arabicSynonyms[trim($keyword)])) {
                $keyword = $arabicSynonyms[trim($keyword)];
            }

            // The category_id from the request is the AliExpress category ID (from the subcategory dropdown)
            // We use it directly for the API call
            $aliexpressCategoryId = null;
            if (!empty($requestedCategoryId)) {
                // The frontend sends the AliExpress category ID directly
                $aliexpressCategoryId = $requestedCategoryId;

                // Find the local category for logging purposes
                $category = \App\Models\Category::where('aliexpress_category_id', $requestedCategoryId)->first();

                Log::info('Category search requested', [
                    'aliexpress_category_id' => $aliexpressCategoryId,
                    'local_category_found' => $category ? true : false,
                    'local_category_name' => $category ? $category->name : 'N/A'
                ]);
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
                    'requested_category_id' => $requestedCategoryId,
                    'aliexpress_category_id' => $aliexpressCategoryId,
                    'sort_filter' => $sortFilter,
                    'has_keyword' => !empty($keyword)
                ]);

                // Find the category to get its name
                $category = \App\Models\Category::where('aliexpress_category_id', $requestedCategoryId)->first();

                // Strategy: Use category name as keyword if available and no keyword provided
                // This gives more relevant results than generic keywords
                $categoryKeyword = null;

                // Check if user provided a keyword
                if (!empty($keyword) && trim($keyword) !== '') {
                    // User provided a keyword, use it with category filter
                    $categoryKeyword = $keyword;
                    Log::info('Using user keyword with category filter', [
                        'user_keyword' => $keyword,
                        'category_id' => $aliexpressCategoryId,
                        'category_found' => $category ? true : false,
                        'category_name' => $category ? $category->name : 'N/A'
                    ]);
                } elseif ($category && !empty($category->name)) {
                    // No keyword provided, use the category/subcategory name as the search keyword
                    // This is brilliant - it finds products that match the category context
                    $categoryKeyword = $category->name;
                    Log::info('Using category/subcategory name as search keyword', [
                        'category_name' => $categoryKeyword,
                        'category_id' => $aliexpressCategoryId,
                        'is_subcategory' => $category->parent_id ? true : false,
                        'parent_id' => $category->parent_id ?? 'none'
                    ]);
                } else {
                    // No category name and no keyword, use strategic keywords based on sort filter
                    if ($sortFilter === 'newest') {
                        $categoryKeyword = 'new';
                    } elseif (in_array($sortFilter, ['price_low', 'price_high'])) {
                        $categoryKeyword = 'sale';
                    } else {
                        $categoryKeyword = 'best'; // For orders and ratings
                    }
                    Log::info('Using fallback keyword based on sort filter', [
                        'keyword' => $categoryKeyword,
                        'sort_filter' => $sortFilter,
                        'reason' => $category ? 'category_name_empty' : 'category_not_found'
                    ]);
                }

                // Make a single API call with the optimized keyword
                // Determine locale based on app language
                $appLocale = app()->getLocale();
                $aliexpressLocale = $appLocale === 'ar' ? 'ar_MA' : 'en_US';

                // Prepare API options ($baseCurrency = AED, defined at method top)
                $apiOptions = [
                    'page' => $request->get('page', 1),
                    'limit' => $request->get('per_page', 50), // Get 50 products per page
                    'category_id' => $aliexpressCategoryId,
                    'sort_by' => $sortBy,
                    'country' => $request->get('country', 'AE'),
                    'currency' => $baseCurrency,
                    'locale' => $request->get('locale', $aliexpressLocale),
                ];

                // Add Choice filter if requested
                if ($request->get('choice_only')) {
                    $apiOptions['item_tag'] = 'choice';
                }

                // Add price range filters
                if ($request->filled('min_price')) {
                    $apiOptions['min_price'] = $request->get('min_price');
                }
                if ($request->filled('max_price')) {
                    $apiOptions['max_price'] = $request->get('max_price');
                }

                // Add free shipping filter
                if ($request->get('free_shipping')) {
                    $apiOptions['free_ship_to'] = $request->get('country', 'AE');
                }

                // Add minimum orders filter
                if ($request->get('min_orders')) {
                    $apiOptions['min_orders'] = $request->get('min_orders');
                }

                // Add ship from filter
                if ($request->filled('ship_from')) {
                    $apiOptions['ship_from'] = $request->get('ship_from');
                }

                // Create cache key based on search parameters
                $cacheKey = 'aliexpress_search_' . md5(json_encode([
                    'keyword' => $categoryKeyword,
                    'options' => $apiOptions
                ]));

                // Cache for 30 minutes to speed up repeated searches
                $result = \Cache::remember($cacheKey, 1800, function() use ($categoryKeyword, $apiOptions) {
                    return $this->aliexpressTextService->searchProductsByText(
                        $categoryKeyword,
                        $apiOptions
                    );
                });

                Log::info('Category search result', [
                    'requested_category_id' => $requestedCategoryId,
                    'aliexpress_category_id' => $aliexpressCategoryId,
                    'keyword_used' => $categoryKeyword,
                    'choice_filter' => $request->get('choice_only') ? 'enabled' : 'disabled',
                    'products_returned' => count($result['products'] ?? []),
                    'total_count' => $result['total_count'] ?? 0
                ]);
            } elseif (!empty($keyword)) {
                // Keyword search - search products by keyword only
                Log::info('Searching products by keyword', [
                    'keyword' => $keyword,
                    'sort_filter' => $sortFilter,
                    'choice_filter' => $request->get('choice_only') ? 'enabled' : 'disabled'
                ]);

                // Detect keyword language: use Arabic locale if keyword contains Arabic characters
                $keywordIsArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $keyword);
                $aliexpressLocale = ($keywordIsArabic || app()->getLocale() === 'ar') ? 'ar_MA' : 'en_US';

                // Prepare API options ($baseCurrency = AED, defined at method top)
                $apiOptions = [
                    'page' => $request->get('page', 1),
                    'limit' => $request->get('per_page', 10),
                    'sort_by' => $sortBy,
                    'country' => $request->get('country', 'AE'),
                    'currency' => $baseCurrency,
                    'locale' => $request->get('locale', $aliexpressLocale),
                ];

                // Add Choice filter if requested
                if ($request->get('choice_only')) {
                    $apiOptions['item_tag'] = 'choice';
                }

                // Add price range filters
                if ($request->filled('min_price')) {
                    $apiOptions['min_price'] = $request->get('min_price');
                }
                if ($request->filled('max_price')) {
                    $apiOptions['max_price'] = $request->get('max_price');
                }

                // Add free shipping filter
                if ($request->get('free_shipping')) {
                    $apiOptions['free_ship_to'] = $request->get('country', 'AE');
                }

                // Add minimum orders filter
                if ($request->get('min_orders')) {
                    $apiOptions['min_orders'] = $request->get('min_orders');
                }

                // Add ship from filter
                if ($request->filled('ship_from')) {
                    $apiOptions['ship_from'] = $request->get('ship_from');
                }

                // Create cache key based on search parameters
                $cacheKey = 'aliexpress_search_' . md5(json_encode([
                    'keyword' => $keyword,
                    'options' => $apiOptions
                ]));

                // Cache for 30 minutes to speed up repeated searches
                $result = \Cache::remember($cacheKey, 1800, function() use ($keyword, $apiOptions) {
                    return $this->aliexpressTextService->searchProductsByText(
                        $keyword,
                        $apiOptions
                    );
                });
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
            $categoryQuery = Category::where('aliexpress_category_id', '!=', null)
                ->where('is_active', true);

            // Filter categories for sellers based on their selected categories
            $user = auth()->user();
            if ($user && $user->user_type === 'seller') {
                // Decode the seller's selected categories
                $mainActivities = json_decode($user->main_activity, true) ?? [];
                $subActivities = json_decode($user->sub_activity, true) ?? [];

                // Combine both main and sub category IDs
                $allowedCategoryIds = array_merge($mainActivities, $subActivities);

                // Filter query to only show allowed categories
                if (!empty($allowedCategoryIds)) {
                    $categoryQuery->whereIn('id', $allowedCategoryIds);
                } else {
                    // If no categories selected, show nothing
                    $categoryQuery->whereRaw('1 = 0');
                }
            }

            $allCategories = $categoryQuery->orderBy('order')->get();

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

            // Always populate both title_en (English) and title_ar (Arabic) for every product
            // regardless of app locale, so assign always saves correct bilingual data to the DB.
            $initialLocaleWasArabic = isset($apiOptions['locale']) && $apiOptions['locale'] === 'ar_MA';
            $searchKeyword = $keyword ?? $categoryKeyword ?? 'product';

            if (!empty($result['products'])) {
                try {
                    if ($initialLocaleWasArabic) {
                        // Primary results have Arabic titles → get English via a second cached API call
                        foreach ($result['products'] as &$product) {
                            $product['title_ar'] = $product['title'];
                        }
                        unset($product);

                        $englishApiOptions = $apiOptions;
                        $englishApiOptions['locale'] = 'en_US';
                        $englishCacheKey = 'aliexpress_search_en_' . md5(json_encode([
                            'keyword' => $searchKeyword,
                            'options' => $englishApiOptions
                        ]));

                        $englishResult = \Cache::remember($englishCacheKey, 1800, function() use ($searchKeyword, $englishApiOptions) {
                            return $this->aliexpressTextService->searchProductsByText($searchKeyword, $englishApiOptions);
                        });

                        $englishTitles = [];
                        foreach ($englishResult['products'] ?? [] as $enProd) {
                            $englishTitles[$enProd['item_id']] = $enProd['title'] ?? null;
                        }

                        foreach ($result['products'] as &$product) {
                            $enTitle = $englishTitles[$product['item_id']] ?? null;
                            $product['title_en'] = ($enTitle && $enTitle !== $product['title_ar'])
                                ? $enTitle
                                : $product['title_ar']; // fallback to Arabic if English unavailable
                        }
                        unset($product);

                    } else {
                        // Primary results have English titles → get Arabic via a second cached API call
                        foreach ($result['products'] as &$product) {
                            $product['title_en'] = $product['title'];
                        }
                        unset($product);

                        $arabicApiOptions = [
                            'locale' => 'ar_MA',
                            'page' => 1,
                            'limit' => min(count($result['products']), 50),
                            'country' => $request->get('country', 'AE'),
                            'currency' => $baseCurrency,
                        ];
                        if (!empty($aliexpressCategoryId)) {
                            $arabicApiOptions['category_id'] = $aliexpressCategoryId;
                        }
                        if ($request->get('choice_only')) {
                            $arabicApiOptions['item_tag'] = 'choice';
                        }
                        if ($request->filled('ship_from')) {
                            $arabicApiOptions['ship_from'] = $request->get('ship_from');
                        }

                        $arabicCacheKey = 'aliexpress_search_ar_' . md5(json_encode([
                            'keyword' => $searchKeyword,
                            'options' => $arabicApiOptions
                        ]));

                        $arabicResult = \Cache::remember($arabicCacheKey, 1800, function() use ($searchKeyword, $arabicApiOptions) {
                            return $this->aliexpressTextService->searchProductsByText($searchKeyword, $arabicApiOptions);
                        });

                        $arabicTitles = [];
                        foreach ($arabicResult['products'] ?? [] as $arProd) {
                            $arabicTitles[$arProd['item_id']] = $arProd['title'] ?? null;
                        }

                        foreach ($result['products'] as &$product) {
                            $arTitle = $arabicTitles[$product['item_id']] ?? null;
                            if ($arTitle && $arTitle !== $product['title_en']) {
                                $product['title_ar'] = $arTitle;
                            } else {
                                // Fallback: translate via Google Translate (cached 7 days)
                                $translationKey = 'translation_' . md5($product['title_en']);
                                $product['title_ar'] = \Cache::remember($translationKey, 604800, function() use ($product) {
                                    return $this->translationService->translate($product['title_en'], 'ar', 'en');
                                });
                            }
                        }
                        unset($product);
                    }

                    Log::info('Bilingual titles populated', [
                        'initial_locale' => $initialLocaleWasArabic ? 'ar_MA' : 'en_US',
                        'total_products' => count($result['products'])
                    ]);

                } catch (\Exception $e) {
                    Log::warning('Failed to fetch bilingual titles', ['error' => $e->getMessage()]);
                    // Fallback: ensure at least both fields exist (same value)
                    foreach ($result['products'] as &$product) {
                        if (!isset($product['title_en'])) {
                            $product['title_en'] = $product['title'];
                        }
                        if (!isset($product['title_ar'])) {
                            $product['title_ar'] = $product['title'];
                        }
                    }
                    unset($product);
                }
            }

            // Add admin profit to each product price.
            // Everything here is in AED (base currency): the API returned AED, admin_profit()
            // returns AED, and we store AED. Conversion to the user's display currency happens
            // in the view via convert_price().
            if (!empty($result['products'])) {
                $profitType = setting('admin_profit_type', 'percentage');

                foreach ($result['products'] as &$product) {
                    $basePrice = (float)($product['sale_price'] ?? 0);

                    // Admin profit in AED (percentage of base price, or fixed AED amount)
                    $adminProfit = admin_profit($basePrice);

                    $finalPrice = $basePrice + $adminProfit;

                    $product['original_sale_price'] = $basePrice;
                    $product['admin_profit']        = $adminProfit;
                    $product['sale_price']          = $finalPrice;

                    if (isset($product['sale_price_format'])) {
                        $product['original_sale_price_format'] = $product['sale_price_format'];
                        $product['sale_price_format'] = $baseCurrency . ' ' . number_format($finalPrice, 2);
                    }

                    if (isset($product['original_price'])) {
                        $originalBasePrice = (float)$product['original_price'];
                        $product['original_aliexpress_price'] = $originalBasePrice;
                        $product['original_price'] = $originalBasePrice + $adminProfit;

                        if (isset($product['original_price_format'])) {
                            $product['original_price_format'] = $baseCurrency . ' ' . number_format($product['original_price'], 2);
                        }
                    }
                }
                unset($product);

                Log::info('Admin profit applied to search results', [
                    'currency'            => $baseCurrency,
                    'profit_type'         => $profitType,
                    'admin_profit_amount' => $adminProfit ?? 0,
                    'products_count'      => count($result['products']),
                ]);
            }

            // Get distributor countries for the country filter buttons
            $distributorCountries = \App\Models\User::where('user_type', 'distributor')
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->select('country')
                ->distinct()
                ->get()
                ->map(function ($user) {
                    return ['code' => $user->country];
                })
                ->toArray();

            // Get distributors grouped by country for the dropdown
            $distributorsByCountry = \App\Models\User::where('user_type', 'distributor')
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->get()
                ->groupBy('country')
                ->map(function ($distributors) {
                    return $distributors->map(function ($dist) {
                        return [
                            'id' => $dist->id,
                            'name' => $dist->name,
                            'store_name' => $dist->store_name,
                            'avatar' => $dist->avatar,
                        ];
                    })->toArray();
                })
                ->toArray();

            // Check if seller has active subscription or trial
            $canAssignProducts = false;
            if (auth()->check() && auth()->user()->user_type === 'seller') {
                $canAssignProducts = auth()->user()->canAccessFullSystem();
            }

            return view('products.search', [
                'products' => $result['products'],
                'total_count' => $result['total_count'] ?? 0,
                'keyword' => $keyword,
                'categories' => $categoriesWithChildren,
                'allCategories' => $allCategories,
                'assignedProductIds' => $assignedProductIds,
                'distributorCountries' => $distributorCountries,
                'distributorsByCountry' => $distributorsByCountry,
                'canAssignProducts' => $canAssignProducts,
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
            $message = app()->getLocale() == 'ar'
                ? 'فقط البائعين يمكنهم إسناد المنتجات.'
                : 'Only sellers can assign products.';
            return response()->json([
                'success' => false,
                'message' => $message
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
            'product_title_ar' => 'nullable|string',
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
            $message = app()->getLocale() == 'ar'
                ? 'هذا المنتج مسند إليك بالفعل.'
                : 'This product is already assigned to you.';
            return response()->json([
                'success' => false,
                'message' => $message
            ], 400);
        }

        // Check if product already exists in products table
        $product = Product::where('aliexpress_id', $aliexpressProductId)->first();

        $basePrice = $request->product_price ?? 0;
        $productCurrency = $request->currency ?? 'AED';
        $sellerAmount = 0;
        $adminAmount = 0;
        $finalPrice = $basePrice;

        // Apply seller's subcategory profit if category is provided
        if ($request->category_id) {
            $profitSetting = $user->getProfitForSubcategory($request->category_id);

            if ($profitSetting) {
                // Pass the product currency so fixed amounts get converted properly
                $sellerAmount = $profitSetting->calculateProfit($basePrice, $productCurrency);
                $finalPrice = $profitSetting->calculateFinalPrice($basePrice, $productCurrency);

                \Log::info('Seller Profit Applied', [
                    'seller_id' => $user->id,
                    'category_id' => $request->category_id,
                    'base_price' => $basePrice,
                    'product_currency' => $productCurrency,
                    'session_currency' => session('currency_code', 'USD'),
                    'profit_type' => $profitSetting->profit_type,
                    'profit_value' => $profitSetting->profit_value,
                    'seller_amount' => $sellerAmount,
                    'final_price' => $finalPrice,
                ]);
            }

            // Apply admin profit from global settings
            $adminAmount = admin_profit($basePrice);
            if ($adminAmount > 0) {
                $finalPrice += $adminAmount;

                \Log::info('Admin Profit Applied', [
                    'base_price' => $basePrice,
                    'seller_amount' => $sellerAmount,
                    'admin_amount' => $adminAmount,
                    'final_price' => $finalPrice,
                ]);
            }
        }

        $rawTitle   = $request->product_title;
        $rawTitleAr = $request->product_title_ar;

        // Detect whether the supplied title is Arabic (contains Arabic chars)
        $rawTitleIsArabic = (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $rawTitle);

        if ($rawTitleIsArabic) {
            // product_title came in as Arabic → it IS the Arabic title
            $arabicTitle  = $rawTitle;
            // Translate Arabic → English to get the proper name field
            $transKeyEn = 'translate_en_' . md5($arabicTitle);
            try {
                $englishTitle = \Cache::remember($transKeyEn, 604800, function() use ($arabicTitle) {
                    return $this->translationService->translate($arabicTitle, 'en', 'ar');
                });
            } catch (\Exception $e) {
                $englishTitle = $arabicTitle; // fallback – better than losing the title
            }
        } else {
            // product_title is English
            $englishTitle = $rawTitle;
            $arabicTitle  = $rawTitleAr;
            // If Arabic is missing or identical to English, translate
            if (empty($arabicTitle) || $arabicTitle === $englishTitle) {
                $transKeyAr = 'translation_' . md5($englishTitle);
                try {
                    $arabicTitle = \Cache::remember($transKeyAr, 604800, function() use ($englishTitle) {
                        return $this->translationService->translate($englishTitle, 'ar', 'en');
                    });
                } catch (\Exception $e) {
                    $arabicTitle = $englishTitle;
                }
            }
        }

        if (!$product) {
            // Create the product in products table (with base price only, no seller-specific profit)
            $product = Product::create([
                'name' => $englishTitle,
                'name_ar' => $arabicTitle,
                'slug' => \Str::slug($englishTitle) . '-' . $aliexpressProductId,
                'description' => $englishTitle,
                'description_ar' => $arabicTitle,
                'short_description' => $englishTitle,
                'short_description_ar' => $arabicTitle,
                'price' => $basePrice, // Store base price only
                'currency' => $request->currency ?? 'AED',
                'original_price' => $basePrice,
                'images' => $request->product_image ? [$request->product_image] : [],
                'aliexpress_id' => $aliexpressProductId,
                'aliexpress_price' => $basePrice,
                'category_id' => $request->category_id,
                'stock_quantity' => 0,
                'is_active' => false, // Set as inactive until seller publishes
            ]);
        } else {
            // Update existing product with Arabic title and category if needed (no seller-specific profit)
            $product->update([
                'category_id' => $request->category_id ?? $product->category_id,
                'name_ar' => $arabicTitle ?? $product->name_ar,
            ]);
        }

        // Determine if this is a Choice product based on the current search filter
        $isChoice = $request->get('is_choice', false) || request()->get('choice_only', false);

        // Assign product to user via pivot table with seller-specific profit
        $user->assignedProducts()->attach($product->id, [
            'aliexpress_product_id' => $aliexpressProductId,
            'status' => 'assigned',
            'is_choice' => $isChoice,
            'seller_amount' => $sellerAmount,
            'admin_amount' => $adminAmount,
            'price' => $finalPrice,
        ]);

        $message = app()->getLocale() == 'ar'
            ? 'تم إسناد المنتج بنجاح! يمكنك مشاهدته في "منتجاتي المسندة".'
            : 'Product assigned successfully! You can now view it in "My Assigned Products".';

        return response()->json([
            'success' => true,
            'message' => $message,
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
            'products.*.product_title_ar' => 'nullable|string',
            'products.*.product_image' => 'nullable|string',
            'products.*.product_price' => 'nullable|numeric',
            'products.*.currency' => 'nullable|string|max:3',
            'products.*.category_id' => 'nullable|exists:categories,id',
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
                $productCurrency = $productData['currency'] ?? 'AED';
                $sellerAmount = 0;
                $adminAmount = 0;
                $finalPrice = $basePrice;
                $categoryId = $productData['category_id'] ?? null;

                // Apply seller's subcategory profit if category is provided
                if ($categoryId) {
                    $profitSetting = $user->getProfitForSubcategory($categoryId);

                    if ($profitSetting) {
                        // Pass the product currency so fixed amounts get converted properly
                        $sellerAmount = $profitSetting->calculateProfit($basePrice, $productCurrency);
                        $finalPrice = $profitSetting->calculateFinalPrice($basePrice, $productCurrency);

                        \Log::info('Seller Profit Applied (Bulk)', [
                            'seller_id' => $user->id,
                            'category_id' => $categoryId,
                            'base_price' => $basePrice,
                            'product_currency' => $productCurrency,
                            'session_currency' => session('currency_code', 'USD'),
                            'profit_type' => $profitSetting->profit_type,
                            'profit_value' => $profitSetting->profit_value,
                            'seller_amount' => $sellerAmount,
                            'final_price' => $finalPrice,
                        ]);
                    }

                    // Apply admin profit from global settings
                    $adminAmount = admin_profit($basePrice);
                    if ($adminAmount > 0) {
                        $finalPrice += $adminAmount;

                        \Log::info('Admin Profit Applied (Bulk)', [
                            'base_price' => $basePrice,
                            'seller_amount' => $sellerAmount,
                            'admin_amount' => $adminAmount,
                            'final_price' => $finalPrice,
                        ]);
                    }
                }

                // Detect language and ensure both English and Arabic titles are correct
                $bulkRawTitle   = $productData['product_title'];
                $bulkRawTitleAr = $productData['product_title_ar'] ?? null;
                $bulkTitleIsAr  = (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $bulkRawTitle);

                if ($bulkTitleIsAr) {
                    // product_title is Arabic → translate to English
                    $bulkArTitle = $bulkRawTitle;
                    $transKeyEn  = 'translate_en_' . md5($bulkArTitle);
                    try {
                        $bulkEnTitle = \Cache::remember($transKeyEn, 604800, function() use ($bulkArTitle) {
                            return $this->translationService->translate($bulkArTitle, 'en', 'ar');
                        });
                    } catch (\Exception $e) {
                        $bulkEnTitle = $bulkArTitle;
                    }
                } else {
                    $bulkEnTitle = $bulkRawTitle;
                    $bulkArTitle = $bulkRawTitleAr;
                    if (empty($bulkArTitle) || $bulkArTitle === $bulkEnTitle) {
                        $transKeyAr = 'translation_' . md5($bulkEnTitle);
                        try {
                            $bulkArTitle = \Cache::remember($transKeyAr, 604800, function() use ($bulkEnTitle) {
                                return $this->translationService->translate($bulkEnTitle, 'ar', 'en');
                            });
                        } catch (\Exception $e) {
                            $bulkArTitle = $bulkEnTitle;
                        }
                    }
                }

                if (!$product) {
                    // Create new product (with base price only, no seller-specific profit)
                    $product = Product::create([
                        'name' => $bulkEnTitle,
                        'name_ar' => $bulkArTitle,
                        'slug' => \Str::slug($bulkEnTitle) . '-' . $aliexpressProductId,
                        'description' => $bulkEnTitle,
                        'description_ar' => $bulkArTitle,
                        'short_description' => $bulkEnTitle,
                        'short_description_ar' => $bulkArTitle,
                        'price' => $basePrice, // Store base price only
                        'currency' => $productData['currency'] ?? 'AED',
                        'original_price' => $basePrice,
                        'images' => isset($productData['product_image']) ? [$productData['product_image']] : [],
                        'aliexpress_id' => $aliexpressProductId,
                        'aliexpress_price' => $basePrice,
                        'category_id' => $categoryId,
                        'stock_quantity' => 0,
                        'is_active' => false,
                    ]);
                } else {
                    // Update existing product with category if needed (no seller-specific profit)
                    $product->update([
                        'category_id' => $categoryId ?? $product->category_id,
                        'name_ar' => $bulkArTitle ?? $product->name_ar,
                        'name' => $product->name ?? $bulkEnTitle,
                    ]);
                }

                // Determine if this is a Choice product
                $isChoice = $request->get('is_choice', false) || request()->get('choice_only', false);

                // Assign to user with seller-specific profit in pivot table
                $user->assignedProducts()->attach($product->id, [
                    'aliexpress_product_id' => $aliexpressProductId,
                    'status' => 'assigned',
                    'is_choice' => $isChoice,
                    'seller_amount' => $sellerAmount,
                    'admin_amount' => $adminAmount,
                    'price' => $finalPrice,
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

        $isArabic = app()->getLocale() == 'ar';

        if ($isArabic) {
            $message = "تم إسناد $assignedCount منتج بنجاح";
            if ($skippedCount > 0) {
                $message .= " ($skippedCount مسند مسبقاً)";
            }
            if (count($errors) > 0) {
                $message .= ". " . count($errors) . " فشل";
            }
        } else {
            $message = "Successfully assigned $assignedCount product(s)";
            if ($skippedCount > 0) {
                $message .= " ($skippedCount already assigned)";
            }
            if (count($errors) > 0) {
                $message .= ". " . count($errors) . " failed";
            }
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
    public function myAssignedProducts(Request $request)
    {
        $user = auth()->user();

        if ($user->user_type !== 'seller') {
            return redirect()->back()->with('error', 'Only sellers can view assigned products.');
        }

        // Get all assigned products for counting
        $allAssignedProducts = $user->assignedProducts()
            ->withPivot('aliexpress_product_id', 'status', 'seller_amount', 'admin_amount', 'price')
            ->get();

        // China = products sourced from AliExpress (long numeric aliexpress_id)
        $isChina = function ($product) {
            return !empty($product->aliexpress_id) && strlen((string) $product->aliexpress_id) >= 10;
        };

        $chinaCount = $allAssignedProducts->filter($isChina)->count();

        // Local products (from distributors) grouped dynamically by their country_code.
        // Missing country_code defaults to AE for backward compatibility.
        $localCountsByCode = $allAssignedProducts
            ->reject($isChina)
            ->groupBy(fn($product) => $product->country_code ?: 'AE')
            ->map->count();

        // Resolve display info (name + flag) for each local country from the countries table.
        $countryMeta = \App\Models\Country::whereIn('code', $localCountsByCode->keys())
            ->get()
            ->keyBy('code');

        $localTabs = $localCountsByCode->map(function ($count, $code) use ($countryMeta) {
            $country = $countryMeta->get($code);
            return [
                'code'    => $code,
                'tab'     => 'local_' . $code,
                'count'   => $count,
                'name'    => $country->name ?? $code,
                'name_ar' => $country->name_ar ?? ($country->name ?? $code),
                'flag'    => $country->flag ?? null,
            ];
        })->values();

        // Build the ordered list of available tab keys (china first, then each local country).
        $availableTabs = collect();
        if ($chinaCount > 0) {
            $availableTabs->push('china');
        }
        $availableTabs = $availableTabs->merge($localTabs->pluck('tab'));

        // Get current tab filter
        $tab = $request->get('tab', 'china');

        // Backward compatibility: map the old hard-coded tab names to the new scheme.
        $legacyMap = ['uae' => 'local_AE', 'saudi' => 'local_SA'];
        if (isset($legacyMap[$tab])) {
            $tab = $legacyMap[$tab];
        }

        // If the requested tab has no products, fall back to the first available tab.
        if (!$availableTabs->contains($tab) && $availableTabs->isNotEmpty()) {
            return redirect()->route('products.my-assigned', ['tab' => $availableTabs->first()]);
        }

        // Build query based on tab
        $query = $user->assignedProducts()
            ->withPivot('aliexpress_product_id', 'status', 'seller_amount', 'admin_amount', 'price');

        if ($tab === 'china') {
            $query->where(function($q) {
                $q->whereNotNull('aliexpress_id')
                  ->whereRaw('LENGTH(aliexpress_id) >= 10');
            });
        } elseif (str_starts_with($tab, 'local_')) {
            $countryCode = substr($tab, strlen('local_'));
            $query->where(function($q) use ($countryCode) {
                // Local (non-AliExpress) products
                $q->where(function($inner) {
                    $inner->whereNull('aliexpress_id')
                          ->orWhereRaw('LENGTH(aliexpress_id) < 10');
                });
                // Match the country; AE also captures legacy rows with null country_code
                if ($countryCode === 'AE') {
                    $q->where(function($inner) {
                        $inner->where('country_code', 'AE')
                              ->orWhereNull('country_code');
                    });
                } else {
                    $q->where('country_code', $countryCode);
                }
            });
        }

        $assignedProducts = $query->orderBy('product_user.created_at', 'desc')
            ->paginate(20)
            ->appends(['tab' => $tab]);

        return view('products.assigned', compact(
            'assignedProducts',
            'chinaCount',
            'localTabs',
            'tab'
        ));
    }

    /**
     * Search products from distributors by country (UAE/Saudi)
     */
    public function searchDistributorProducts(Request $request)
    {
        $countryCode = $request->get('country_code');
        $distributorId = $request->get('distributor_id');

        // Validate that we have at least a country code
        if (empty($countryCode)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Country code is required'
                ], 400);
            }
            return back()->with('error', 'Country code is required');
        }

        // Query products from distributors
        $query = Product::with('category')
            ->where('is_active', true);

        // Filter by specific distributor if provided
        if (!empty($distributorId)) {
            $query->whereHas('assignedUsers', function ($q) use ($distributorId, $countryCode) {
                $q->where('users.id', $distributorId)
                  ->where('country', $countryCode)
                  ->where('user_type', 'distributor');
            });
        } else {
            // Filter by country
            $query->fromCountry($countryCode);
        }

        // Search by keyword if provided
        $keyword = $request->get('keyword');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('name_ar', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            // Find local category by AliExpress ID
            $category = Category::where('aliexpress_category_id', $request->category_id)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at,desc');
        $sortParts = explode(',', $sortBy);
        $sortField = $sortParts[0] ?? 'created_at';
        $sortDirection = $sortParts[1] ?? 'desc';

        // Map sort fields
        $allowedSortFields = ['created_at', 'price', 'name'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 50);
        $page = $request->get('page', 1);

        $products = $query->paginate($perPage);

        // Transform products to match AliExpress format for frontend compatibility
        $transformedProducts = $products->map(function ($product) use ($request) {
            $currency = $request->get('currency', 'AED');
            return [
                'item_id' => $product->id,
                'aliexpress_id' => $product->aliexpress_id,
                'title' => $product->name,
                'title_en' => $product->name,
                'title_ar' => $product->name_ar ?? $product->name,
                'item_main_pic' => $product->images[0] ?? ($product->photo ? asset('storage/' . $product->photo) : null),
                'sale_price' => $product->price,
                'sale_price_format' => $currency . ' ' . number_format($product->price, 2),
                'original_price' => $product->original_price ?? $product->price,
                'original_price_format' => $currency . ' ' . number_format($product->original_price ?? $product->price, 2),
                'original_sale_price' => $product->original_price ?? $product->price,
                'discount' => $product->original_price && $product->original_price > $product->price
                    ? round((($product->original_price - $product->price) / $product->original_price) * 100) . '%'
                    : null,
                'evaluate_rate' => null,
                'orders' => null,
                'item_url' => $product->aliexpress_url,
                'admin_profit' => $product->admin_amount ?? 0,
                'is_distributor_product' => true,
            ];
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'products' => $transformedProducts,
                'total_count' => $products->total(),
                'current_page' => $products->currentPage(),
                'page_size' => $products->perPage(),
            ]);
        }

        // Get categories for the view
        $categoryQuery = Category::where('aliexpress_category_id', '!=', null)
            ->where('is_active', true);

        $user = auth()->user();
        if ($user && $user->user_type === 'seller') {
            $mainActivities = json_decode($user->main_activity, true) ?? [];
            $subActivities = json_decode($user->sub_activity, true) ?? [];
            $allowedCategoryIds = array_merge($mainActivities, $subActivities);

            if (!empty($allowedCategoryIds)) {
                $categoryQuery->whereIn('id', $allowedCategoryIds);
            } else {
                $categoryQuery->whereRaw('1 = 0');
            }
        }

        $allCategories = $categoryQuery->orderBy('order')->get();
        $mainCategories = $allCategories->whereNull('parent_id');
        $categoriesWithChildren = $mainCategories->map(function($parent) use ($allCategories) {
            $parent->children = $allCategories->where('parent_id', $parent->id)->values();
            return $parent;
        });

        // Get assigned products for current user
        $assignedProductIds = [];
        if (auth()->check() && auth()->user()->user_type === 'seller') {
            $assignedProductIds = \DB::table('product_user')
                ->where('user_id', auth()->id())
                ->pluck('aliexpress_product_id')
                ->toArray();
        }

        // Get distributor countries for the country filter buttons
        $distributorCountries = \App\Models\User::where('user_type', 'distributor')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->select('country')
            ->distinct()
            ->get()
            ->map(function ($user) {
                return ['code' => $user->country];
            })
            ->toArray();

        // Get distributors grouped by country for the dropdown
        $distributorsByCountry = \App\Models\User::where('user_type', 'distributor')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->get()
            ->groupBy('country')
            ->map(function ($distributors) {
                return $distributors->map(function ($dist) {
                    return [
                        'id' => $dist->id,
                        'name' => $dist->name,
                        'store_name' => $dist->store_name,
                        'avatar' => $dist->avatar,
                    ];
                })->toArray();
            })
            ->toArray();

        $canAssignProducts = auth()->check() && auth()->user()->user_type === 'seller'
            ? auth()->user()->canAccessFullSystem()
            : false;

        return view('products.search', [
            'products' => $transformedProducts->toArray(),
            'total_count' => $products->total(),
            'keyword' => $keyword,
            'categories' => $categoriesWithChildren,
            'allCategories' => $allCategories,
            'assignedProductIds' => $assignedProductIds,
            'source_country' => $countryCode,
            'distributorCountries' => $distributorCountries,
            'distributorsByCountry' => $distributorsByCountry,
            'canAssignProducts' => $canAssignProducts,
        ]);
    }

    /**
     * Get filtered categories for the current user (sellers see only their selected categories)
     */
    protected function getFilteredCategoriesForUser()
    {
        $query = Category::active();

        // Filter categories for sellers based on their selected categories
        $user = auth()->user();
        if ($user && $user->user_type === 'seller') {
            // Decode the seller's selected categories
            $mainActivities = json_decode($user->main_activity, true) ?? [];
            $subActivities = json_decode($user->sub_activity, true) ?? [];

            // Combine both main and sub category IDs
            $allowedCategoryIds = array_merge($mainActivities, $subActivities);

            // Filter query to only show allowed categories
            if (!empty($allowedCategoryIds)) {
                $query->whereIn('id', $allowedCategoryIds);
            } else {
                // If no categories selected, show nothing
                $query->whereRaw('1 = 0');
            }
        }

        return $query->get();
    }

    /**
     * Get featured China stores (feeds/categories from AliExpress)
     */
    public function getChinaStores(Request $request)
    {
        try {
            $stores = $this->aliexpressCategoryService->getFeaturedChinaStores();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'stores' => $stores,
                ]);
            }

            return $stores;

        } catch (\Exception $e) {
            Log::error('Failed to get China stores', ['error' => $e->getMessage()]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to load stores',
                    'stores' => [],
                ], 500);
            }

            return [];
        }
    }

    /**
     * Search products from a specific China store (feed or category)
     */
    public function searchChinaStoreProducts(Request $request)
    {
        $storeId = $request->get('store_id');
        $keyword = $request->get('keyword');

        if (empty($storeId) && empty($keyword)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Store ID or keyword is required'
                ], 400);
            }
            return back()->with('error', 'Store ID or keyword is required');
        }

        try {
            $options = [
                'page' => $request->get('page', 1),
                'limit' => $request->get('per_page', 20),
                'country' => $request->get('country', 'AE'),
                'currency' => $request->get('currency', 'AED'),
                'locale' => app()->getLocale() == 'ar' ? 'ar_SA' : 'en_US',
            ];

            // If keyword is provided, use text search
            if (!empty($keyword)) {
                $result = $this->aliexpressTextService->searchProductsByText($keyword, $options);
                $products = $result['products'] ?? [];
                $totalCount = $result['total_count'] ?? count($products);
            }
            // Otherwise, get products from the store (feed/category)
            else {
                $result = $this->aliexpressCategoryService->getChinaStoreProducts($storeId, $options);
                $products = $result['products'] ?? [];
                $totalCount = $result['total_count'] ?? count($products);
            }

            // Get store info for display
            $stores = $this->aliexpressCategoryService->getFeaturedChinaStores();
            $currentStore = collect($stores)->firstWhere('id', $storeId);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'products' => $products,
                    'total_count' => $totalCount,
                    'current_page' => $options['page'],
                    'page_size' => $options['limit'],
                    'store' => $currentStore,
                ]);
            }

            // Get categories for the view
            $categoryQuery = Category::where('aliexpress_category_id', '!=', null)
                ->where('is_active', true);

            $user = auth()->user();
            if ($user && $user->user_type === 'seller') {
                $mainActivities = json_decode($user->main_activity, true) ?? [];
                $subActivities = json_decode($user->sub_activity, true) ?? [];
                $allowedCategoryIds = array_merge($mainActivities, $subActivities);

                if (!empty($allowedCategoryIds)) {
                    $categoryQuery->whereIn('id', $allowedCategoryIds);
                } else {
                    $categoryQuery->whereRaw('1 = 0');
                }
            }

            $allCategories = $categoryQuery->orderBy('order')->get();
            $mainCategories = $allCategories->whereNull('parent_id');
            $categoriesWithChildren = $mainCategories->map(function($parent) use ($allCategories) {
                $parent->children = $allCategories->where('parent_id', $parent->id)->values();
                return $parent;
            });

            // Get assigned products for current user
            $assignedProductIds = [];
            if (auth()->check() && auth()->user()->user_type === 'seller') {
                $assignedProductIds = \DB::table('product_user')
                    ->where('user_id', auth()->id())
                    ->pluck('aliexpress_product_id')
                    ->toArray();
            }

            // Get distributor countries for the view
            $distributorCountries = \App\Models\User::where('user_type', 'distributor')
                ->whereNotNull('country')
                ->select('country')
                ->selectRaw('COUNT(*) as distributor_count')
                ->groupBy('country')
                ->get()
                ->map(function($item) {
                    return [
                        'code' => $item->country,
                        'count' => $item->distributor_count,
                    ];
                });

            $distributorsByCountry = \App\Models\User::where('user_type', 'distributor')
                ->whereNotNull('country')
                ->select('id', 'name', 'store_name', 'avatar', 'country')
                ->get()
                ->groupBy('country');

            $canAssignProducts = auth()->check() && auth()->user()->user_type === 'seller'
                ? auth()->user()->canAccessFullSystem()
                : false;

            return view('products.search', [
                'products' => $products,
                'total_count' => $totalCount,
                'keyword' => $keyword,
                'categories' => $categoriesWithChildren,
                'allCategories' => $allCategories,
                'assignedProductIds' => $assignedProductIds,
                'chinaStores' => $stores,
                'currentChinaStore' => $currentStore,
                'distributorCountries' => $distributorCountries,
                'distributorsByCountry' => $distributorsByCountry,
                'source_type' => 'china_store',
                'canAssignProducts' => $canAssignProducts,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to search China store products', [
                'store_id' => $storeId,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to search products: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to search products');
        }
    }
}
