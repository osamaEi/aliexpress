<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\AliExpressToken;

class AliExpressService
{
    protected $gatewayUrl;
    protected $authGatewayUrl = 'https://eco.aliexpress.com/token/authorize'; // Separate OAuth gateway
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->apiKey = config('services.aliexpress.api_key');
        $this->apiSecret = config('services.aliexpress.api_secret');
        $this->gatewayUrl = config('services.aliexpress.api_url', 'https://api-sg.aliexpress.com/sync');

        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new \Exception('AliExpress API credentials not configured in .env');
        }
    }

    // ===================================================================
    // SIGNATURE & REQUEST HANDLING
    // ===================================================================

    /**
     * Generate HMAC-SHA256 signature for regular API calls
     * AliExpress API signature: HMAC-SHA256(sorted key+value pairs, app_secret)
     */
    private function generateSign(array $params): string
    {
        // Remove sign if exists
        unset($params['sign']);

        // Sort by key alphabetically
        ksort($params);

        // Build signature string - AliExpress format: key1value1key2value2...
        // NO path prefix needed for /sync endpoint
        $stringToBeSigned = '';

        foreach ($params as $key => $value) {
            // Skip empty values (but keep 0 and '0')
            if ($value !== '' && $value !== null && $value !== []) {
                // Handle complex types
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                // Convert value to string if needed
                $stringToBeSigned .= $key . strval($value);
            }
        }

        Log::debug('AliExpress Signature', [
            'method' => $params['method'] ?? 'unknown',
            'string_to_sign' => substr($stringToBeSigned, 0, 100) . '...',
            'string_length' => strlen($stringToBeSigned),
            'signature_preview' => substr(strtoupper(hash_hmac('sha256', $stringToBeSigned, $this->apiSecret)), 0, 20) . '...'
        ]);

        // Use HMAC-SHA256 with app secret as the key
        return strtoupper(hash_hmac('sha256', $stringToBeSigned, $this->apiSecret));
    }

    /**
     * Generate HMAC-SHA256 signature for OAuth endpoints
     * OAuth endpoints use HMAC instead of simple hash
     */
    private function generateOAuthSign(string $apiPath, array $params): string
    {
        // Remove sign if exists
        unset($params['sign']);

        // Sort by key
        ksort($params);

        // Build signature string: API_PATH + sorted params
        $stringToBeSigned = $apiPath;

        foreach ($params as $key => $value) {
            // Skip empty values
            if ($value !== '' && $value !== null && $value !== []) {
                // Handle complex types
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $stringToBeSigned .= $key . $value;
            }
        }

        Log::debug('AliExpress OAuth Signature', [
            'api_path' => $apiPath,
            'string_to_sign' => $stringToBeSigned,
            'string_length' => strlen($stringToBeSigned)
        ]);

        // Use HMAC-SHA256 with app secret
        return strtoupper(hash_hmac('sha256', $stringToBeSigned, $this->apiSecret));
    }

    /**
     * Make API request (following official IopClient pattern)
     */
    private function makeRequest(string $method, array $additionalParams = [], bool $requiresAuth = false)
    {
        $params = array_merge([
            'app_key' => $this->apiKey,
            'method' => $method,
            'timestamp' => (string)(now()->timestamp * 1000),
            'format' => 'json',
            'sign_method' => 'sha256',
            'partner_id' => 'laravel-sdk-1.0',
            'simplify' => 'true',
        ], $additionalParams);

        // Add session (access token) BEFORE signature if required
        // Official SDK includes session in signature!
        if ($requiresAuth) {
            $params['session'] = $this->getAccessToken();
        }

        // Generate signature (includes session if present)
        $params['sign'] = $this->generateSign($params);

        Log::debug('AliExpress API Request', [
            'method' => $method,
            'params_count' => count($params),
            'has_session' => isset($params['session'])
        ]);

        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post($this->gatewayUrl, $params);

            Log::debug('AliExpress API Response', [
                'method' => $method,
                'status' => $response->status(),
                'success' => $response->successful(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Check for API errors
                if (isset($data['error_response'])) {
                    throw new \Exception(
                        "API Error [{$data['error_response']['code']}]: " .
                        $data['error_response']['msg']
                    );
                }

                return $data;
            }

            throw new \Exception(
                "HTTP Error [{$response->status()}]: " . $response->body()
            );

        } catch (\Exception $e) {
            Log::error('AliExpress API Error', [
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // ===================================================================
    // OAUTH TOKEN MANAGEMENT
    // ===================================================================

    /**
     * Get authorization URL (Step 1)
     */
    public function getAuthorizationUrl(string $redirectUri, ?string $state = null): string
    {
        $authUrl = 'https://oauth.aliexpress.com/authorize';

        $params = [
            'response_type' => 'code',
            'client_id' => $this->apiKey,
            'redirect_uri' => $redirectUri,
            'state' => $state ?? bin2hex(random_bytes(16)),
            'sp' => 'ae',
        ];

        return $authUrl . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token (Step 2)
     * Uses direct OAuth endpoint: /auth/token/create
     */
    public function createToken(string $authCode): array
    {
        $apiPath = '/auth/token/create';

        $params = [
            'app_key' => $this->apiKey,
            'timestamp' => (string)(now()->timestamp * 1000),
            'sign_method' => 'sha256',
            'code' => $authCode,
        ];

        // Generate OAuth signature (uses HMAC-SHA256)
        $params['sign'] = $this->generateOAuthSign($apiPath, $params);

        Log::debug('AliExpress Token Creation Request', [
            'params_count' => count($params),
            'api_path' => $apiPath
        ]);

        try {
            // OAuth endpoints use direct path, not method parameter
            $response = Http::timeout(30)
                ->asForm()
                ->post($this->gatewayUrl . $apiPath, $params);

            Log::debug('AliExpress Token Creation Response', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // OAuth endpoints return data directly, not wrapped in response object
                if (isset($data['access_token'])) {
                    // Save tokens
                    $this->saveTokens($data);

                    return $data;
                }

                // Check for errors
                if (isset($data['error_response'])) {
                    throw new \Exception(
                        "API Error [{$data['error_response']['code']}]: " .
                        $data['error_response']['msg']
                    );
                }
            }

            throw new \Exception(
                "HTTP Error [{$response->status()}]: " . $response->body()
            );

        } catch (\Exception $e) {
            Log::error('AliExpress Token Creation Error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to create token: ' . $e->getMessage());
        }
    }

    /**
     * Refresh access token
     * Uses direct OAuth endpoint: /auth/token/refresh
     */
    public function refreshToken(?string $refreshToken = null): array
    {
        // If no refresh token provided, get from storage
        if (!$refreshToken) {
            $tokenModel = AliExpressToken::where('account', 'default')->first();
            if (!$tokenModel) {
                throw new \Exception('No refresh token found');
            }
            $refreshToken = decrypt($tokenModel->refresh_token);
        }

        $apiPath = '/auth/token/refresh';

        $params = [
            'app_key' => $this->apiKey,
            'timestamp' => (string)(now()->timestamp * 1000),
            'sign_method' => 'sha256',
            'refresh_token' => $refreshToken,
        ];

        // Generate OAuth signature (uses HMAC-SHA256)
        $params['sign'] = $this->generateOAuthSign($apiPath, $params);

        Log::debug('AliExpress Token Refresh Request', [
            'params_count' => count($params),
            'api_path' => $apiPath
        ]);

        try {
            // OAuth endpoints use direct path, not method parameter
            $response = Http::timeout(30)
                ->asForm()
                ->post($this->gatewayUrl . $apiPath, $params);

            Log::debug('AliExpress Token Refresh Response', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // OAuth endpoints return data directly, not wrapped in response object
                if (isset($data['access_token'])) {
                    // Update tokens
                    $this->saveTokens($data);

                    return $data;
                }

                // Check for errors
                if (isset($data['error_response'])) {
                    throw new \Exception(
                        "API Error [{$data['error_response']['code']}]: " .
                        $data['error_response']['msg']
                    );
                }
            }

            throw new \Exception(
                "HTTP Error [{$response->status()}]: " . $response->body()
            );

        } catch (\Exception $e) {
            Log::error('AliExpress Token Refresh Error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Save tokens to database and cache
     */
    private function saveTokens(array $tokenData): void
    {
        // Log raw token data to debug expiry time format
        Log::debug('AliExpress Token Data', [
            'expire_time_raw' => $tokenData['expire_time'] ?? 'missing',
            'refresh_token_valid_time_raw' => $tokenData['refresh_token_valid_time'] ?? 'missing',
            'account' => $tokenData['account'] ?? 'default',
            'all_keys' => array_keys($tokenData)
        ]);

        // Calculate expiry times
        // AliExpress can return expire_time in different formats:
        // 1. Absolute timestamp in milliseconds (e.g., 1729276781000)
        // 2. Duration in seconds (e.g., 2592000)
        // We detect which format by checking the size of the number

        $expireTime = $tokenData['expire_time'] ?? 2592000;
        $refreshExpireTime = $tokenData['refresh_token_valid_time'] ?? 7776000;

        // If the value is very large (> 10 years in seconds), it's a timestamp in milliseconds
        if ($expireTime > 315360000) { // 10 years in seconds
            // It's a Unix timestamp in milliseconds, convert to Carbon instance
            $accessTokenExpiry = \Carbon\Carbon::createFromTimestampMs($expireTime);
        } else {
            // It's a duration in seconds
            $accessTokenExpiry = now()->addSeconds($expireTime);
        }

        if ($refreshExpireTime > 315360000) { // 10 years in seconds
            // It's a Unix timestamp in milliseconds
            $refreshTokenExpiry = \Carbon\Carbon::createFromTimestampMs($refreshExpireTime);
        } else {
            // It's a duration in seconds
            $refreshTokenExpiry = now()->addSeconds($refreshExpireTime);
        }

        // Save to database
        AliExpressToken::updateOrCreate(
            ['account' => $tokenData['account'] ?? 'default'],
            [
                'access_token' => encrypt($tokenData['access_token']),
                'refresh_token' => encrypt($tokenData['refresh_token']),
                'expires_at' => $accessTokenExpiry,
                'refresh_expires_at' => $refreshTokenExpiry,
                'account_platform' => $tokenData['account_platform'] ?? 'AE',
            ]
        );

        // Save to cache for fast access
        Cache::put(
            'aliexpress_access_token',
            $tokenData['access_token'],
            $accessTokenExpiry->subMinutes(5) // Refresh 5 min before expiry
        );

        Log::info('AliExpress tokens saved', [
            'expires_at' => $accessTokenExpiry,
            'refresh_expires_at' => $refreshTokenExpiry
        ]);
    }

    /**
     * Get valid access token (auto-refresh if needed)
     */
    public function getAccessToken(): string
    {
        // Check cache first
        $cachedToken = Cache::get('aliexpress_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        // Check config for static token (from .env)
        $configToken = config('services.aliexpress.access_token');
        if ($configToken) {
            // Cache it for 1 hour (static tokens don't expire quickly)
            Cache::put('aliexpress_access_token', $configToken, now()->addHour());
            return $configToken;
        }

        // Load from database
        $tokenModel = AliExpressToken::where('account', 'default')->first();

        if (!$tokenModel) {
            throw new \Exception(
                'No access token found. Please set ALIEXPRESS_ACCESS_TOKEN in .env or authorize via OAuth'
            );
        }

        // Check if expired
        if ($tokenModel->isExpired()) {
            Log::info('Access token expired, refreshing...');

            // Refresh token
            $result = $this->refreshToken(decrypt($tokenModel->refresh_token));
            return $result['access_token'];
        }

        // Token still valid
        $accessToken = decrypt($tokenModel->access_token);

        // Re-cache
        Cache::put(
            'aliexpress_access_token',
            $accessToken,
            $tokenModel->expires_at->subMinutes(5)
        );

        return $accessToken;
    }

    /**
     * Check if user is authorized
     */
    public function isAuthorized(): bool
    {
        try {
            $this->getAccessToken();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get token status
     */
    public function getTokenStatus(): ?array
    {
        $tokenModel = AliExpressToken::where('account', 'default')->first();

        if (!$tokenModel) {
            return null;
        }

        return [
            'authorized' => !$tokenModel->isExpired(),
            'expires_at' => $tokenModel->expires_at,
            'expires_in' => $tokenModel->expires_at->diffForHumans(),
            'can_refresh' => $tokenModel->canRefresh(),
            'refresh_expires_at' => $tokenModel->refresh_expires_at,
        ];
    }

    // ===================================================================
    // PRODUCT APIs (No OAuth Required - System Level)
    // ===================================================================

    /**
     * Get product details
     * API: aliexpress.ds.product.get
     * Note: This API requires OAuth access token
     */
    public function getProductDetails(string $productId, array $options = [])
    {
        $params = [
            'product_id' => $productId,
            'target_currency' => $options['currency'] ?? 'USD',
            'target_language' => $options['language'] ?? 'EN',
            'ship_to_country' => $options['country'] ?? 'EG',
        ];

        // This API requires OAuth access token
        $data = $this->makeRequest('aliexpress.ds.product.get', $params, true);

        if (isset($data['aliexpress_ds_product_get_response']['result'])) {
            return $data['aliexpress_ds_product_get_response']['result'];
        }

        return null;
    }

    /**
     * Get recommended products
     * API: aliexpress.ds.recommend.feed.get
     */
    public function getRecommendedProducts(array $options = [])
    {
        $params = [
            'feed_name' => 'DS_bestselling',  // Required parameter: DS_bestselling, DS_newarrival, etc.
            'page_no' => $options['page'] ?? 1,
            'page_size' => min($options['limit'] ?? 20, 50),
            'target_currency' => $options['currency'] ?? 'USD',
            'target_language' => $options['language'] ?? 'EN',
            'ship_to_country' => $options['country'] ?? 'EG',
            'sort' => $options['sort'] ?? 'SALE_PRICE_ASC',
        ];

        // Optional parameters
        if (!empty($options['category_id'])) {
            $params['category_id'] = $options['category_id'];
        }
        if (!empty($options['keywords'])) {
            $params['keywords'] = $options['keywords'];
        }

        // Make request (requires OAuth session)
        $data = $this->makeRequest('aliexpress.ds.recommend.feed.get', $params, true);

        if (isset($data['aliexpress_ds_recommend_feed_get_response']['result'])) {
            return $data['aliexpress_ds_recommend_feed_get_response']['result'];
        }

        return null;
    }

    /**
     * Search products using Text Search API (aliexpress.ds.text.search)
     * This is the main dropshipping search API
     */
    public function searchProductsByText(string $keyword, array $options = []): array
    {
        $params = [
            'keyWord' => $keyword,
            'local' => $options['locale'] ?? 'en_US',
            'countryCode' => $options['country'] ?? 'AE',
            'currency' => $options['currency'] ?? 'AED',
            'pageIndex' => $options['page'] ?? 1,
            'pageSize' => min($options['limit'] ?? 50, 50)
        ];

        if (!empty($options['category_id'])) {
            $params['categoryId'] = $options['category_id'];
        }

        if (!empty($options['sort_by'])) {
            $params['sortBy'] = $options['sort_by'];
        }

        $result = $this->makeRequest('aliexpress.ds.text.search', $params, true);

        $products = [];
        $debugData = [
            'method' => 'aliexpress.ds.text.search',
            'keyword' => $keyword,
            'http_code' => 200
        ];

        // Check if response has the simplified format (code, data, request_id)
        if (isset($result['code']) && isset($result['data'])) {
            $debugData['code'] = $result['code'];
            $debugData['request_id'] = $result['request_id'] ?? null;

            $data = $result['data'];
            $debugData['total_count'] = $data['totalCount'] ?? 0;
            $debugData['page_index'] = $data['pageIndex'] ?? 0;
            $debugData['page_size'] = $data['pageSize'] ?? 0;

            // Products can be in different locations
            if (isset($data['products']) && is_array($data['products']) && !empty($data['products'])) {
                $products = $data['products'];
                $debugData['products_location'] = 'direct_products_array';
            }
        }
        // Check for wrapped response format
        elseif (isset($result['aliexpress_ds_text_search_response'])) {
            $response = $result['aliexpress_ds_text_search_response'];

            $debugData['code'] = $response['code'] ?? null;
            $debugData['msg'] = $response['msg'] ?? null;

            if (isset($response['data'])) {
                $data = $response['data'];

                $debugData['total_count'] = $data['totalCount'] ?? 0;
                $debugData['page_index'] = $data['pageIndex'] ?? 0;
                $debugData['page_size'] = $data['pageSize'] ?? 0;

                // Products are in selection_search_product array
                if (isset($data['products']['selection_search_product']) && is_array($data['products']['selection_search_product'])) {
                    $products = $data['products']['selection_search_product'];
                    $debugData['products_location'] = 'selection_search_product';
                } elseif (isset($data['products']) && is_array($data['products'])) {
                    $products = $data['products'];
                    $debugData['products_location'] = 'direct_products';
                }
            }
        } elseif (isset($result['error_response'])) {
            $debugData['error'] = $result['error_response'];
        }

        $debugData['products_found'] = count($products);

        return [
            'products' => $this->formatTextSearchProducts($products),
            'debug' => $debugData,
            'raw' => $result,
            'total_count' => $debugData['total_count'] ?? 0,
            'current_page' => $options['page'] ?? 1,
            'page_size' => min($options['limit'] ?? 50, 50)
        ];
    }

    /**
     * Format text search products for consistent display
     */
    private function formatTextSearchProducts(array $products): array
    {
        return array_map(function ($product) {
            return [
                'item_id' => $product['itemId'] ?? '',
                'title' => $product['title'] ?? 'No Title',
                'item_main_pic' => $product['itemMainPic'] ?? '',
                'sale_price' => $product['salePrice'] ?? $product['targetSalePrice'] ?? 0,
                'original_price' => $product['originalPrice'] ?? $product['targetOriginalPrice'] ?? 0,
                'sale_price_format' => $product['salePriceFormat'] ?? '',
                'original_price_format' => $product['originalPriceFormat'] ?? '',
                'discount' => $product['discount'] ?? '',
                'evaluate_rate' => $product['evaluateRate'] ?? '',
                'score' => $product['score'] ?? '',
                'orders' => $product['orders'] ?? 0,
                'item_url' => $product['itemUrl'] ?? '',
                'product_video_url' => $product['productVideoUrl'] ?? '',
                'cate_id' => $product['cateId'] ?? ''
            ];
        }, $products);
    }

    /**
     * Search products using Affiliate API (works without dropshipping enrollment)
     */
    public function searchProducts(string $keywords, array $options = [])
    {
        // Use Affiliate Hot Products API - works immediately without dropshipping enrollment
        $params = [
            'keywords' => $keywords,
            'page_no' => $options['page'] ?? 1,
            'page_size' => min($options['limit'] ?? 20, 50),
            'target_currency' => $options['currency'] ?? 'USD',
            'target_language' => $options['language'] ?? 'EN',
            'ship_to_country' => $options['country'] ?? 'US',
            'sort' => 'SALE_PRICE_ASC',
            'tracking_id' => config('services.aliexpress.tracking_id') ?: 'default',
        ];

        // Optional category filter
        if (!empty($options['category_id'])) {
            $params['category_ids'] = $options['category_id'];
        }

        try {
            // Try Affiliate Hot Products Query first
            $data = $this->makeRequest('aliexpress.affiliate.hotproduct.query', $params, false);

            if (isset($data['aliexpress_affiliate_hotproduct_query_response']['result']['products']['product'])) {
                $products = $data['aliexpress_affiliate_hotproduct_query_response']['result']['products']['product'];

                // Normalize format to match dropshipping API
                $normalizedProducts = array_map(function($p) {
                    return [
                        'product_id' => $p['product_id'] ?? '',
                        'product_title' => $p['product_title'] ?? '',
                        'product_main_image_url' => $p['product_main_image_url'] ?? '',
                        'target_sale_price' => $p['target_sale_price'] ?? $p['sale_price'] ?? 0,
                        'target_original_price' => $p['target_original_price'] ?? $p['original_price'] ?? 0,
                        'promotion_link' => $p['promotion_link'] ?? '',
                    ];
                }, is_array($products) ? $products : [$products]);

                Log::info("Found " . count($normalizedProducts) . " products using Affiliate API");

                return [
                    'products' => $normalizedProducts,
                    'total_count' => $data['aliexpress_affiliate_hotproduct_query_response']['result']['total_record_count'] ?? count($normalizedProducts),
                    'current_count' => count($normalizedProducts),
                ];
            }
        } catch (\Exception $e) {
            Log::error("Affiliate API error: " . $e->getMessage());
        }

        // If affiliate fails, provide helpful message
        return [
            'products' => [],
            'total_count' => 0,
            'current_count' => 0,
            'message' => 'No products found. Please check your API credentials and tracking ID.',
        ];
    }

    /**
     * Get categories
     * API: aliexpress.ds.category.get
     */
    public function getCategories(int $categoryId = 0)
    {
        $params = [
            'category_id' => $categoryId,
            'app_signature' => 'dropshipping',
        ];

        $data = $this->makeRequest('aliexpress.ds.category.get', $params);

        if (isset($data['aliexpress_ds_category_get_response']['result'])) {
            return $data['aliexpress_ds_category_get_response']['result'];
        }

        return null;
    }

    /**
     * Calculate shipping cost
     * API: aliexpress.ds.order.freight.calculate
     */
    public function calculateShipping(
        string $productId,
        int $quantity,
        string $countryCode,
        array $options = []
    ) {
        $freightRequest = [
            'country_code' => $countryCode,
            'send_goods_country_code' => 'CN', // China
            'product_id' => $productId,
            'product_num' => $quantity,
        ];

        if (!empty($options['province'])) {
            $freightRequest['province_code'] = $options['province'];
        }
        if (!empty($options['city'])) {
            $freightRequest['city_code'] = $options['city'];
        }
        if (!empty($options['price'])) {
            $freightRequest['price'] = $options['price'];
        }

        $params = [
            'param_aeop_freight_calculate_request' => json_encode($freightRequest)
        ];

        $data = $this->makeRequest('aliexpress.ds.order.freight.calculate', $params);

        if (isset($data['aliexpress_ds_order_freight_calculate_response']['result'])) {
            return $data['aliexpress_ds_order_freight_calculate_response']['result'];
        }

        return null;
    }

    /**
     * Import product from AliExpress
     * This method retrieves product details and formats them for local storage
     */
    public function importProduct(string $productId, ?int $categoryId = null, float $profitMargin = 30.0)
    {
        // Get product details
        $product = $this->getProductDetails($productId);

        if (!$product) {
            throw new \Exception("Product not found: {$productId}");
        }

        // Calculate pricing
        $aliexpressPrice = $product['target_sale_price'] ?? $product['target_original_price'] ?? 0;
        $shippingCost = 0;

        // Try to calculate shipping
        try {
            $shipping = $this->calculateShipping($productId, 1, 'EG');
            if ($shipping && isset($shipping['freight']['amount'])) {
                $shippingCost = $shipping['freight']['amount'];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to calculate shipping', ['error' => $e->getMessage()]);
        }

        // Calculate final price with profit margin
        $cost = $aliexpressPrice + $shippingCost;
        $price = $cost * (1 + ($profitMargin / 100));

        return [
            'name' => $product['subject'] ?? '',
            'description' => $product['detail'] ?? '',
            'short_description' => substr($product['subject'] ?? '', 0, 255),
            'price' => round($price, 2),
            'compare_price' => round($price * 1.2, 2), // 20% higher as compare price
            'cost' => round($cost, 2),
            'aliexpress_id' => $productId,
            'aliexpress_url' => $product['product_detail_url'] ?? "https://www.aliexpress.com/item/{$productId}.html",
            'aliexpress_price' => $aliexpressPrice,
            'shipping_cost' => $shippingCost,
            'supplier_profit_margin' => $profitMargin,
            'category_id' => $categoryId,
            'images' => $product['product_main_image_url'] ? [$product['product_main_image_url']] : [],
            'aliexpress_variants' => $product['aeop_ae_product_s_k_us'] ?? null,
            'specifications' => $product['aeop_ae_product_propertys'] ?? null,
            'stock_quantity' => $product['aeop_ae_product_s_k_us'][0]['s_k_u_available_stock'] ?? 100,
            'track_inventory' => true,
            'is_active' => false, // Inactive by default, review before activating
        ];
    }

    /**
     * Sync product with AliExpress
     * Updates local product with latest data from AliExpress
     */
    public function syncProduct($product)
    {
        if (!$product->aliexpress_id) {
            throw new \Exception('Product is not linked to AliExpress');
        }

        // Get latest product details
        $latestData = $this->getProductDetails($product->aliexpress_id);

        if (!$latestData) {
            throw new \Exception('Product not found on AliExpress');
        }

        // Update product
        $aliexpressPrice = $latestData['target_sale_price'] ?? $latestData['target_original_price'] ?? 0;

        // Recalculate pricing with existing margin
        $margin = $product->supplier_profit_margin ?? 30.0;
        $cost = $aliexpressPrice + ($product->shipping_cost ?? 0);
        $price = $cost * (1 + ($margin / 100));

        $product->update([
            'name' => $latestData['subject'] ?? $product->name,
            'aliexpress_price' => $aliexpressPrice,
            'price' => round($price, 2),
            'cost' => round($cost, 2),
            'aliexpress_variants' => $latestData['aeop_ae_product_s_k_us'] ?? $product->aliexpress_variants,
            'specifications' => $latestData['aeop_ae_product_propertys'] ?? $product->specifications,
            'last_synced_at' => now(),
        ]);

        return $product;
    }

    // ===================================================================
    // ORDER APIs (Requires OAuth - User Level)
    // ===================================================================

    /**
     * Create dropshipping order
     * API: aliexpress.ds.order.create
     */
    public function createOrder(array $orderData, ?string $accessToken = null)
    {
        // Get access token
        if (!$accessToken) {
            $accessToken = $this->getAccessToken();
        }

        // Build order request
        $orderRequest = [
            'logistics_address' => [
                'contact_person' => $orderData['contact_person'],
                'mobile_no' => $orderData['mobile_no'],
                'phone_country' => $orderData['phone_country'],
                'address' => $orderData['address'],
                'address2' => $orderData['address2'] ?? '',
                'city' => $orderData['city'],
                'country' => $orderData['country'],
                'province' => $orderData['province'],
                'zip' => $orderData['zip'],
                'full_name' => $orderData['full_name'] ?? $orderData['contact_person'],
            ],
            'product_items' => $orderData['product_items']
        ];

        $params = [
            'access_token' => $accessToken,
            'param_aeop_order_create_request' => json_encode($orderRequest)
        ];

        $data = $this->makeRequest('aliexpress.ds.order.create', $params);

        if (isset($data['aliexpress_ds_order_create_response']['result'])) {
            return $data['aliexpress_ds_order_create_response']['result'];
        }

        return null;
    }

    /**
     * Get order info
     * API: aliexpress.ds.order.get
     */
    public function getOrderInfo(string $orderId, ?string $accessToken = null)
    {
        if (!$accessToken) {
            $accessToken = $this->getAccessToken();
        }

        $params = [
            'access_token' => $accessToken,
            'order_id' => $orderId
        ];

        $data = $this->makeRequest('aliexpress.ds.order.get', $params);

        if (isset($data['aliexpress_ds_order_get_response']['result'])) {
            return $data['aliexpress_ds_order_get_response']['result'];
        }

        return null;
    }

    /**
     * Get tracking info
     * API: aliexpress.ds.tracking.info.query
     */
    public function getTrackingInfo(string $orderId, ?string $accessToken = null)
    {
        if (!$accessToken) {
            $accessToken = $this->getAccessToken();
        }

        $params = [
            'access_token' => $accessToken,
            'order_id' => $orderId,
            'logistics_type' => 'TRACKING_NO'
        ];

        $data = $this->makeRequest('aliexpress.ds.tracking.info.query', $params);

        if (isset($data['aliexpress_ds_tracking_info_query_response']['result'])) {
            return $data['aliexpress_ds_tracking_info_query_response']['result'];
        }

        return null;
    }
}
