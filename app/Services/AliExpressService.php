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
            // Remove simplify to get full response structure
            // 'simplify' => 'true',
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
            'has_session' => isset($params['session']),
            'pageSize' => $params['pageSize'] ?? 'not set',
            'all_params' => $params
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
            'http_code' => 200,
            'result_keys' => array_keys($result ?? [])
        ];

        // Log the full structure for debugging
        Log::debug('Search API Result Structure', [
            'has_wrapped_response' => isset($result['aliexpress_ds_text_search_response']),
            'result_keys' => array_keys($result ?? []),
            'first_100_chars' => substr(json_encode($result), 0, 100)
        ]);

        // Check for direct response format (simplified=true)
        if (isset($result['code']) && isset($result['data'])) {
            $debugData['code'] = $result['code'];
            $debugData['request_id'] = $result['request_id'] ?? null;

            $data = $result['data'];
            $debugData['total_count'] = $data['totalCount'] ?? 0;
            $debugData['page_index'] = $data['pageIndex'] ?? 0;
            $debugData['page_size'] = $data['pageSize'] ?? 0;

            // Products can be direct array or in selection_search_product
            if (isset($data['products']['selection_search_product']) && is_array($data['products']['selection_search_product'])) {
                $products = $data['products']['selection_search_product'];
                $debugData['products_location'] = 'selection_search_product';
                Log::debug('Found products in selection_search_product', ['count' => count($products)]);
            } elseif (isset($data['products']) && is_array($data['products']) && !empty($data['products'])) {
                $products = $data['products'];
                $debugData['products_location'] = 'direct_products_array';
                Log::debug('Found products as direct array', ['count' => count($products)]);
            } else {
                Log::warning('Products array is empty or not found', [
                    'has_products' => isset($data['products']),
                    'products_type' => isset($data['products']) ? gettype($data['products']) : 'not set',
                    'products_value' => $data['products'] ?? null
                ]);
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
                $debugData['data_keys'] = array_keys($data);

                // Products are in selection_search_product array
                if (isset($data['products']['selection_search_product']) && is_array($data['products']['selection_search_product'])) {
                    $products = $data['products']['selection_search_product'];
                    $debugData['products_location'] = 'selection_search_product';
                    Log::debug('Found products in selection_search_product', ['count' => count($products)]);
                } elseif (isset($data['products']) && is_array($data['products'])) {
                    $products = $data['products'];
                    $debugData['products_location'] = 'direct_products';
                    Log::debug('Found products directly', ['count' => count($products)]);
                }
            }
        } elseif (isset($result['error_response'])) {
            $debugData['error'] = $result['error_response'];
            Log::error('API returned error response', ['error' => $result['error_response']]);
        } else {
            Log::warning('Unexpected response format', ['result' => $result]);
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
     * Get category tree with subcategories
     * Uses aliexpress.ds.category.get with category_id=0 to get root categories
     */
    public function getCategoryTree(?int $parentCategoryId = null): ?array
    {
        $params = [
            'category_id' => $parentCategoryId ?? 0,
            'app_signature' => 'dropshipping',
        ];

        try {
            // Use dropshipping category API
            $data = $this->makeRequest('aliexpress.ds.category.get', $params, true);

            Log::debug('Category Tree API Response', [
                'params' => $params,
                'response_keys' => array_keys($data ?? []),
                'raw_response' => json_encode($data)
            ]);

            // Parse response structure (with resp_result wrapper)
            if (isset($data['aliexpress_ds_category_get_response']['resp_result']['result'])) {
                $result = $data['aliexpress_ds_category_get_response']['resp_result']['result'];

                // The result contains category information in categories.category array
                if (isset($result['categories']['category'])) {
                    return is_array($result['categories']['category'])
                        ? $result['categories']['category']
                        : [$result['categories']['category']];
                }

                if (isset($result['child_category_list']['category'])) {
                    return is_array($result['child_category_list']['category'])
                        ? $result['child_category_list']['category']
                        : [$result['child_category_list']['category']];
                }

                if (isset($result['children'])) {
                    return $result['children'];
                }

                Log::warning('Unexpected category tree structure', ['result_keys' => array_keys($result)]);
                return [];
            }

            // Try without resp_result wrapper (older API format)
            if (isset($data['aliexpress_ds_category_get_response']['result'])) {
                $result = $data['aliexpress_ds_category_get_response']['result'];

                if (isset($result['categories']['category'])) {
                    return is_array($result['categories']['category'])
                        ? $result['categories']['category']
                        : [$result['categories']['category']];
                }
            }

            Log::warning('Could not parse category tree response', ['data_keys' => array_keys($data)]);
            return [];

        } catch (\Exception $e) {
            Log::error('Category Tree API Error', [
                'error' => $e->getMessage(),
                'parent_category_id' => $parentCategoryId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get child categories by parent category ID
     * Uses aliexpress.ds.category.get API (available for dropshipping apps)
     */
    public function getChildCategories(string $categoryId): ?array
    {
        $params = [
            'category_id' => $categoryId,
            'app_signature' => 'dropshipping',
        ];

        try {
            // Use dropshipping category API which is more widely available
            $data = $this->makeRequest('aliexpress.ds.category.get', $params, true);

            Log::debug('Child Categories API Response', [
                'category_id' => $categoryId,
                'response_keys' => array_keys($data ?? []),
                'raw_response' => json_encode($data)
            ]);

            // Parse response structure for ds.category.get
            if (isset($data['aliexpress_ds_category_get_response']['resp_result']['result'])) {
                $result = $data['aliexpress_ds_category_get_response']['resp_result']['result'];

                // The result contains category information in categories.category array
                if (isset($result['categories']['category'])) {
                    return is_array($result['categories']['category'])
                        ? $result['categories']['category']
                        : [$result['categories']['category']];
                }

                if (isset($result['child_category_list']['category'])) {
                    return is_array($result['child_category_list']['category'])
                        ? $result['child_category_list']['category']
                        : [$result['child_category_list']['category']];
                }

                if (isset($result['children'])) {
                    return $result['children'];
                }

                Log::warning('Unexpected category structure', ['result_keys' => array_keys($result)]);
                return [];
            }

            // Try without resp_result wrapper (older API format)
            if (isset($data['aliexpress_ds_category_get_response']['result'])) {
                $result = $data['aliexpress_ds_category_get_response']['result'];

                if (isset($result['categories']['category'])) {
                    return is_array($result['categories']['category'])
                        ? $result['categories']['category']
                        : [$result['categories']['category']];
                }
            }

            // If no standard structure, return empty array
            Log::warning('Could not parse categories response', ['data_keys' => array_keys($data)]);
            return [];

        } catch (\Exception $e) {
            Log::error('Child Categories API Error', [
                'error' => $e->getMessage(),
                'category_id' => $categoryId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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
     * Calculate freight for product and shipping address
     * API: aliexpress.ds.freight.query
     * Documentation: Dropshipping Freight Query API
     */
    public function calculateFreight(array $params): array
    {
        // Validate required parameters
        if (empty($params['product_id'])) {
            throw new \InvalidArgumentException('product_id is required');
        }
        if (empty($params['country'])) {
            throw new \InvalidArgumentException('country is required');
        }
        if (empty($params['product_num'])) {
            throw new \InvalidArgumentException('product_num is required');
        }

        // Validate SKU ID is required
        if (empty($params['sku_id'])) {
            throw new \InvalidArgumentException('sku_id is required for freight calculation');
        }

        // Build freight query request according to aliexpress.ds.freight.query API
        $queryDeliveryReq = [
            'quantity' => (int)$params['product_num'],
            'shipToCountry' => $params['country'], // Country code (e.g., AE, SA, US)
            'productId' => (string)$params['product_id'], // AliExpress product ID
            'language' => $params['language'] ?? 'en_US',
            'locale' => $params['locale'] ?? 'en_US',
            'currency' => $params['currency'] ?? 'USD',
            'selectedSkuId' => (string)$params['sku_id'], // SKU ID - REQUIRED
        ];

        // Add optional parameters
        if (!empty($params['province'])) {
            $queryDeliveryReq['provinceCode'] = $params['province'];
        }
        if (!empty($params['city'])) {
            $queryDeliveryReq['cityCode'] = $params['city'];
        }

        Log::info('Calculating freight for product', [
            'product_id' => $params['product_id'],
            'sku_id' => $params['sku_id'],
            'country' => $params['country'],
            'quantity' => $params['product_num'],
            'city' => $params['city'] ?? 'not specified',
            'province' => $params['province'] ?? 'not specified',
            'query_delivery_req' => $queryDeliveryReq
        ]);

        try {
            // Make API request using aliexpress.ds.freight.query
            $data = $this->makeRequest(
                'aliexpress.ds.freight.query',
                ['queryDeliveryReq' => json_encode($queryDeliveryReq)],
                true // Requires authentication
            );

            Log::debug('Freight calculation response', [
                'response_keys' => array_keys($data ?? []),
                'data' => $data
            ]);

            // Parse response from aliexpress.ds.freight.query
            if (isset($data['aliexpress_ds_freight_query_response'])) {
                $response = $data['aliexpress_ds_freight_query_response'];

                // Check for error in response
                if (isset($response['error_code'])) {
                    throw new \Exception(
                        'Freight calculation error: ' . ($response['error_msg'] ?? 'Unknown error') .
                        ' (Code: ' . $response['error_code'] . ')'
                    );
                }

                // Extract freight information from result
                if (isset($response['result'])) {
                    $result = $response['result'];

                    // Get first delivery option if available
                    $deliveryOptions = $result['aeop_freight_result_d_t_o_list'] ?? [];

                    if (!empty($deliveryOptions) && is_array($deliveryOptions)) {
                        $firstOption = is_array($deliveryOptions) ? $deliveryOptions[0] : $deliveryOptions;

                        return [
                            'success' => true,
                            'error_code' => null,
                            'error_desc' => null,
                            'freight_amount' => $firstOption['freight_amount'] ?? $firstOption['freight']['amount'] ?? null,
                            'freight_currency' => $firstOption['freight_currency'] ?? $firstOption['freight']['currency_code'] ?? $params['currency'] ?? 'USD',
                            'estimated_delivery_time' => $firstOption['estimated_delivery_time'] ?? $firstOption['delivery_time'] ?? null,
                            'service_name' => $firstOption['service_name'] ?? $firstOption['company'] ?? null,
                            'raw_response' => $result,
                            'all_options' => $deliveryOptions
                        ];
                    }

                    // No delivery options available
                    Log::warning('No delivery options returned from AliExpress', [
                        'product_id' => $params['product_id'],
                        'sku_id' => $params['sku_id'],
                        'country' => $params['country'],
                        'city' => $params['city'] ?? null,
                        'province' => $params['province'] ?? null,
                        'full_response' => $result,
                        'delivery_options' => $deliveryOptions
                    ]);

                    // Provide more specific error message based on error code
                    $errorMsg = 'No delivery options available for this destination';
                    if (isset($result['code'])) {
                        if ($result['code'] == 501) {
                            $errorMsg = 'Shipping information not available. This product may not ship to the selected location.';
                        } elseif ($result['code'] == 507) {
                            $errorMsg = 'Invalid SKU selected. Please try again or contact support.';
                        }
                    }
                    if (isset($result['msg'])) {
                        $errorMsg .= ' (' . $result['msg'] . ')';
                    }

                    return [
                        'success' => false,
                        'error_code' => 'NO_DELIVERY_OPTIONS',
                        'error_desc' => $errorMsg,
                        'raw_response' => $result
                    ];
                }
            }

            // If no standard response format found
            Log::error('Unexpected response format from AliExpress freight API', [
                'product_id' => $params['product_id'],
                'country' => $params['country'],
                'full_raw_response' => $data
            ]);

            return [
                'success' => false,
                'error_code' => 'PARSE_ERROR',
                'error_desc' => 'Unable to parse freight calculation response',
                'raw_response' => $data
            ];

        } catch (\Exception $e) {
            Log::error('Freight calculation failed', [
                'error' => $e->getMessage(),
                'product_id' => $params['product_id'],
                'country' => $params['country']
            ]);

            throw $e;
        }
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

    /**
     * Fetch and store product SKU data for ordering
     * This method specifically fetches SKU information needed for placing orders
     */
    public function fetchProductSkuData(string $productId)
    {
        try {
            $productDetails = $this->getProductDetails($productId);

            if (!$productDetails) {
                throw new \Exception("Product {$productId} not found on AliExpress");
            }

            // Extract SKU information from the response
            $skuData = null;
            $skuCount = 0;

            // Check for SKU data in multiple possible locations
            // Modern API format: ae_item_sku_info_dtos
            if (isset($productDetails['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'])) {
                $skuData = $productDetails['ae_item_sku_info_dtos'];
                $skuCount = count($productDetails['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']);
            }
            // Legacy API format: aeop_ae_product_s_k_us
            elseif (isset($productDetails['aeop_ae_product_s_k_us'])) {
                $skuData = $productDetails['aeop_ae_product_s_k_us'];
                $skuCount = isset($productDetails['aeop_ae_product_s_k_us']['aeop_ae_product_sku'])
                    ? count($productDetails['aeop_ae_product_s_k_us']['aeop_ae_product_sku'])
                    : 0;
            }

            Log::info('Fetched product SKU data', [
                'product_id' => $productId,
                'has_sku_data' => !empty($skuData),
                'sku_count' => $skuCount,
                'response_keys' => array_keys($productDetails)
            ]);

            return [
                'sku_data' => $skuData,
                'full_data' => $productDetails,  // Return full product details
                'product_details' => $productDetails
            ];

        } catch (\Exception $e) {
            Log::error('Failed to fetch product SKU data', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get the first available SKU for a product
     * Useful when customer hasn't selected a specific variant
     */
    public function getFirstAvailableSku(array $productData): ?string
    {
        Log::debug('Getting first available SKU', [
            'has_ae_item_sku_info_dtos' => isset($productData['ae_item_sku_info_dtos']),
            'has_aeop_ae_product_s_k_us' => isset($productData['aeop_ae_product_s_k_us']),
            'top_level_keys' => array_keys($productData)
        ]);

        // Check in ae_item_sku_info_dtos structure (from product.get API response)
        if (isset($productData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'])) {
            $skus = $productData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'];

            Log::debug('Found SKUs in ae_item_sku_info_dtos', [
                'sku_count' => count($skus),
                'first_sku_keys' => isset($skus[0]) ? array_keys($skus[0]) : []
            ]);

            foreach ($skus as $sku) {
                // Check if SKU has stock
                if (isset($sku['sku_available_stock']) && $sku['sku_available_stock'] > 0) {
                    $skuAttr = $sku['sku_attr'] ?? $sku['id'] ?? null;
                    Log::info('Selected SKU with stock', [
                        'sku_attr' => $skuAttr,
                        'stock' => $sku['sku_available_stock']
                    ]);
                    return $skuAttr;
                }
            }

            // If no SKU with stock, return first SKU anyway
            if (!empty($skus[0])) {
                $skuAttr = $skus[0]['sku_attr'] ?? $skus[0]['id'] ?? null;
                Log::warning('No SKU with stock found, using first SKU', [
                    'sku_attr' => $skuAttr
                ]);
                return $skuAttr;
            }
        }

        // Check in aeop_ae_product_s_k_us structure (from aliexpress_variants)
        if (isset($productData['aeop_ae_product_s_k_us']['aeop_ae_product_sku'])) {
            $skus = $productData['aeop_ae_product_s_k_us']['aeop_ae_product_sku'];

            if (!is_array($skus)) {
                $skus = [$skus];
            }

            Log::debug('Found SKUs in aeop_ae_product_s_k_us', [
                'sku_count' => count($skus)
            ]);

            foreach ($skus as $sku) {
                if (isset($sku['s_k_u_available_stock']) && $sku['s_k_u_available_stock'] > 0) {
                    $skuAttr = $sku['id'] ?? null;
                    Log::info('Selected SKU with stock (old structure)', [
                        'sku_attr' => $skuAttr,
                        'stock' => $sku['s_k_u_available_stock']
                    ]);
                    return $skuAttr;
                }
            }

            if (!empty($skus[0])) {
                $skuAttr = $skus[0]['id'] ?? null;
                Log::warning('No SKU with stock found, using first SKU (old structure)', [
                    'sku_attr' => $skuAttr
                ]);
                return $skuAttr;
            }
        }

        Log::warning('No SKU data found in product data');
        return null;
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

        // Build product items - clean up empty sku_attr
        $productItems = [];
        foreach ($orderData['product_items'] as $item) {
            $productItem = [
                'product_id' => $item['product_id'],
                'product_count' => $item['product_count'],
            ];

            // Only include sku_attr if it's not empty
            if (!empty($item['sku_attr'])) {
                $productItem['sku_attr'] = $item['sku_attr'];
            }

            $productItems[] = $productItem;
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
            'product_items' => $productItems
        ];

        $params = [
            'access_token' => $accessToken,
            'param_place_order_request4_open_api_d_t_o' => json_encode($orderRequest)
        ];

        Log::info('Creating AliExpress order', [
            'address' => $orderRequest['logistics_address'],
            'product_items' => $orderRequest['product_items']
        ]);

        $data = $this->makeRequest('aliexpress.ds.order.create', $params);

        if (isset($data['aliexpress_ds_order_create_response']['result'])) {
            $result = $data['aliexpress_ds_order_create_response']['result'];

            // Check if order creation failed
            if (isset($result['is_success']) && $result['is_success'] === false) {
                $errorCode = $result['error_code'] ?? 'UNKNOWN_ERROR';
                $errorMsg = $this->getOrderErrorMessage($errorCode);

                throw new \Exception("Order creation failed: {$errorMsg} (Code: {$errorCode})");
            }

            // Check if order creation was successful
            if (isset($result['is_success']) && $result['is_success'] === true) {
                // Extract order ID from response
                $orderId = null;

                // Check multiple possible locations for order ID
                if (isset($result['order_list']['number'][0])) {
                    $orderId = $result['order_list']['number'][0];
                } elseif (isset($result['order_id'])) {
                    $orderId = $result['order_id'];
                }

                Log::info('AliExpress order created successfully', [
                    'order_id' => $orderId,
                    'full_result' => $result
                ]);

                return [
                    'order_id' => $orderId,
                    'is_success' => true,
                    'full_response' => $result
                ];
            }

            return $result;
        }

        return null;
    }

    /**
     * Get user-friendly error message for order errors
     */
    private function getOrderErrorMessage(string $errorCode): string
    {
        return match($errorCode) {
            'SKU_NOT_EXIST' => 'The product SKU does not exist or the variant is not available. Please ensure the product has no variants or select a valid variant.',
            'PRODUCT_NOT_EXIST' => 'The product does not exist on AliExpress.',
            'PRODUCT_OFFLINE' => 'The product is currently offline or unavailable.',
            'INSUFFICIENT_INVENTORY' => 'Insufficient product inventory.',
            'INVALID_ADDRESS' => 'The shipping address is invalid or incomplete.',
            'INVALID_PHONE' => 'The phone number is invalid.',
            'B_DROPSHIPPER_DELIVERY_ADDRESS_VALIDATE_FAIL' => 'Shipping address validation failed. Please check: phone number format (must match country code), complete address with city/country, and ensure the address is valid for the selected country.',
            'SHIPPING_NOT_SUPPORT' => 'Shipping to this country is not supported.',
            'PRICE_CHANGED' => 'Product price has changed. Please refresh and try again.',
            default => 'An error occurred while creating the order.'
        };
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

    /**
     * Get available feed names
     * API: aliexpress.ds.feedname.get
     */
    public function getAvailableFeedNames()
    {
        $accessToken = $this->getAccessToken();

        $params = [
            'app_signature' => 'dropshipping',
        ];

        $data = $this->makeRequest('aliexpress.ds.feedname.get', $params, true);

        Log::debug('AliExpress Feed Names Response', [
            'has_response' => isset($data['aliexpress_ds_feedname_get_response']),
            'data' => $data,
        ]);

        if (isset($data['aliexpress_ds_feedname_get_response']['resp_result']['result'])) {
            return $data['aliexpress_ds_feedname_get_response']['resp_result']['result'];
        }

        return [];
    }

    /**
     * Get products by category ID using recommended feed
     * API: aliexpress.ds.recommend.feed.get
     */
    public function getProductsByCategory(int $categoryId, array $options = [])
    {
        $accessToken = $this->getAccessToken();

        // First, get available feed names to use a valid one
        $feedNames = $this->getAvailableFeedNames();
        $defaultFeedName = 'DS Best Seller';  // Default feed name

        // If we have feed names from API, use the first one or match the requested type
        if (!empty($feedNames['promos'])) {
            $requestedType = $options['feed_name'] ?? 'bestselling';

            // Try to find a matching feed
            foreach ($feedNames['promos'] as $promo) {
                $promoName = strtolower($promo['promo_name'] ?? '');
                if (strpos($promoName, 'best') !== false && strpos($requestedType, 'best') !== false) {
                    $defaultFeedName = $promo['promo_name'];
                    break;
                } elseif (strpos($promoName, 'new') !== false && strpos($requestedType, 'new') !== false) {
                    $defaultFeedName = $promo['promo_name'];
                    break;
                } elseif (strpos($promoName, 'featured') !== false && strpos($requestedType, 'featured') !== false) {
                    $defaultFeedName = $promo['promo_name'];
                    break;
                }
            }

            // If no match, use first available feed
            if (isset($feedNames['promos'][0]['promo_name'])) {
                $defaultFeedName = $feedNames['promos'][0]['promo_name'];
            }
        }

        $params = [
            'app_signature' => 'dropshipping',
            'category_id' => (string)$categoryId,
            'country' => $options['country'] ?? 'AE',
            'feed_name' => $defaultFeedName,
            'page_no' => $options['page'] ?? 1,
            'page_size' => $options['limit'] ?? 10,
            'sort' => $options['sort'] ?? 'priceAsc',
            'target_currency' => $options['currency'] ?? 'AED',
            'target_language' => $options['locale'] ?? 'EN',
        ];

        Log::debug('AliExpress Category Products Request', [
            'method' => 'aliexpress.ds.recommend.feed.get',
            'category_id' => $categoryId,
            'feed_name' => $params['feed_name'],
            'params' => $params,
        ]);

        $data = $this->makeRequest('aliexpress.ds.recommend.feed.get', $params, true);

        Log::debug('AliExpress Category Products Response', [
            'has_response' => isset($data['aliexpress_ds_recommend_feed_get_response']),
            'response_keys' => isset($data['aliexpress_ds_recommend_feed_get_response']) ? array_keys($data['aliexpress_ds_recommend_feed_get_response']) : [],
        ]);

        if (isset($data['aliexpress_ds_recommend_feed_get_response']['resp_result'])) {
            $result = $data['aliexpress_ds_recommend_feed_get_response']['resp_result'];

            if (isset($result['result']['products']['product'])) {
                $products = $result['result']['products']['product'];

                // Ensure it's an array
                if (!is_array($products)) {
                    $products = [$products];
                }

                return [
                    'products' => $this->formatCategoryProducts($products),
                    'total_count' => $result['result']['total_record_count'] ?? count($products),
                    'current_page' => $params['page_no'],
                    'page_size' => $params['page_size'],
                ];
            }
        }

        return [
            'products' => [],
            'total_count' => 0,
            'current_page' => 1,
            'page_size' => 0,
        ];
    }

    /**
     * Format category products for consistent output
     */
    private function formatCategoryProducts(array $products): array
    {
        return array_map(function($product) {
            return [
                'item_id' => $product['product_id'] ?? $product['productId'] ?? '',
                'title' => $product['product_title'] ?? $product['subject'] ?? '',
                'item_main_pic' => $product['product_main_image_url'] ?? $product['productMainImageUrl'] ?? '',
                'sale_price' => $product['target_sale_price'] ?? $product['targetSalePrice'] ?? 0,
                'original_price' => $product['target_original_price'] ?? $product['targetOriginalPrice'] ?? 0,
                'discount' => $product['discount'] ?? '',
                'sale_price_format' => $product['target_sale_price_format'] ?? '',
                'original_price_format' => $product['target_original_price_format'] ?? '',
                'evaluate_rate' => $product['evaluate_rate'] ?? $product['evaluateRate'] ?? '',
                'score' => '',
                'orders' => $product['lastest_volume'] ?? $product['volume'] ?? 0,
                'item_url' => $product['product_detail_url'] ?? $product['productUrl'] ?? '',
                'product_video_url' => $product['product_video_url'] ?? '',
                'cate_id' => $product['second_level_category_id'] ?? '',
            ];
        }, $products);
    }

    /**
     * Get detailed logistics tracking information
     * API: aliexpress.logistics.buyer.freight.get
     */
    public function getDetailedTrackingInfo(string $aliexpressOrderId)
    {
        $params = [
            'ae_order_id' => $aliexpressOrderId,
        ];

        $data = $this->makeRequest('aliexpress.logistics.buyer.freight.get', $params, true);

        Log::info('AliExpress Detailed Tracking Response', [
            'order_id' => $aliexpressOrderId,
            'response' => $data
        ]);

        if (isset($data['aliexpress_logistics_buyer_freight_get_response'])) {
            return $data['aliexpress_logistics_buyer_freight_get_response'];
        }

        return null;
    }

    /**
     * Get logistics tracking details
     * API: aliexpress.logistics.ds.trackinginfo.query
     */
    public function queryLogisticsTracking(string $aliexpressOrderId, string $logisticsNo = '', string $serviceName = '')
    {
        $params = [
            'logistics_no' => $logisticsNo,
            'origin' => 'ESCROW',
            'out_ref' => $aliexpressOrderId,
            'service_name' => $serviceName,
            'to_area' => '',
        ];

        $data = $this->makeRequest('aliexpress.logistics.ds.trackinginfo.query', $params, true);

        Log::info('AliExpress Logistics Tracking Query', [
            'order_id' => $aliexpressOrderId,
            'tracking_no' => $logisticsNo,
            'response' => $data
        ]);

        if (isset($data['aliexpress_logistics_ds_trackinginfo_query_response']['result'])) {
            return $data['aliexpress_logistics_ds_trackinginfo_query_response']['result'];
        }

        return null;
    }

    /**
     * Parse tracking status from AliExpress response
     */
    public function parseTrackingStatus(string $aliexpressStatus): string
    {
        $statusMap = [
            'PLACE_ORDER_SUCCESS' => 'pending',
            'IN_CANCEL' => 'pending',
            'WAIT_SELLER_SEND_GOODS' => 'pending',
            'SELLER_PART_SEND_GOODS' => 'in_transit',
            'WAIT_BUYER_ACCEPT_GOODS' => 'in_transit',
            'FUND_PROCESSING' => 'in_transit',
            'IN_ISSUE' => 'exception',
            'IN_FROZEN' => 'exception',
            'WAIT_SELLER_EXAMINE_MONEY' => 'in_transit',
            'RISK_CONTROL' => 'exception',
            'FINISH' => 'delivered',
        ];

        return $statusMap[strtoupper($aliexpressStatus)] ?? 'pending';
    }

    /**
     * Get shipping information for an order by AliExpress order ID
     */
    public function getOrderShippingInfo(string $aliexpressOrderId): ?array
    {
        if (!$aliexpressOrderId) {
            return null;
        }

        try {
            // Try to get tracking info
            $trackingData = $this->getTrackingInfo($aliexpressOrderId);

            if (!$trackingData) {
                // Try alternative tracking method
                $trackingData = $this->queryLogisticsTracking($aliexpressOrderId);
            }

            if ($trackingData) {
                return [
                    'tracking_number' => $trackingData['tracking_number'] ?? $trackingData['logistics_no'] ?? null,
                    'shipping_method' => $trackingData['logistics_name'] ?? $trackingData['service_name'] ?? null,
                    'carrier_code' => $trackingData['logistics_service'] ?? null,
                    'status' => $this->parseTrackingStatus($trackingData['order_status'] ?? 'WAIT_SELLER_SEND_GOODS'),
                    'tracking_events' => $trackingData['details'] ?? [],
                    'raw_response' => $trackingData,
                ];
            }

        } catch (\Exception $e) {
            Log::error('Failed to get order shipping info', [
                'aliexpress_order_id' => $aliexpressOrderId,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Sync shipping information for an order
     */
    public function syncOrderShipping(Order $order): ?array
    {
        if (!$order->aliexpress_order_id) {
            Log::warning('Cannot sync shipping: No AliExpress order ID', [
                'order_id' => $order->id
            ]);
            return null;
        }

        return $this->getOrderShippingInfo($order->aliexpress_order_id);
    }
}
