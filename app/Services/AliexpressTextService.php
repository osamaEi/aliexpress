<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AliexpressTextService
{
    private $appKey;
    private $appSecret;
    private $accessToken;
    private $apiUrl;

    public function __construct()
    {
        $this->appKey = config('services.aliexpress.api_key');
        $this->appSecret = config('services.aliexpress.api_secret');
        $this->accessToken = config('services.aliexpress.access_token');
        $this->apiUrl = config('services.aliexpress.api_url', 'https://api-sg.aliexpress.com/sync');
    }

    /**
     * Generate HMAC-SHA256 signature
     */
    private function generateSignature(array $params): string
    {
        // Remove sign if exists
        unset($params['sign']);

        // Sort parameters alphabetically
        ksort($params);

        // Build signature string: key1value1key2value2...
        $signString = '';
        foreach ($params as $key => $value) {
            if ($value !== '' && $value !== null) {
                $signString .= $key . $value;
            }
        }

        Log::debug('Signature String', [
            'string' => substr($signString, 0, 100) . '...',
            'length' => strlen($signString)
        ]);

        // Generate HMAC-SHA256
        return strtoupper(hash_hmac('sha256', $signString, $this->appSecret));
    }

    /**
     * Search products by text using aliexpress.ds.text.search API
     */
    public function searchProductsByText(string $keyword, array $options = []): array
    {
        $timestamp = round(microtime(true) * 1000);

        // If only category is provided without keyword, use generic search terms
        // AliExpress API may not support category-only filtering effectively
        // We'll try with generic keywords that are likely to match products in any category
        if (empty($keyword) && !empty($options['category_id'])) {
            // Try common generic terms - empty string doesn't work
            // Using very short common words that appear in many products
            $keyword = 'new'; // Generic term that works across categories
        }

        // Build API parameters - IMPORTANT: Use exact parameter names from API docs
        $params = [
            'app_key' => $this->appKey,
            'countryCode' => $options['country'] ?? 'AE',
            'currency' => $options['currency'] ?? 'AED',
            'format' => 'json',
            'keyWord' => $keyword,
            'local' => $options['locale'] ?? 'en_US',  // Note: API uses 'local' not 'locale'
            'method' => 'aliexpress.ds.text.search',
            'pageIndex' => $options['page'] ?? 1,
            'pageSize' => $options['limit'] ?? 10,
            'session' => $this->accessToken,
            'sign_method' => 'sha256',
            'timestamp' => (string)$timestamp,
            'v' => '2.0',
        ];

        // Add optional parameters
        if (!empty($options['category_id'])) {
            // Convert to integer to ensure proper API format
            $params['categoryId'] = (int)$options['category_id'];
        }

        if (!empty($options['sort_by'])) {
            $params['sortBy'] = $options['sort_by'];
        }

        // Generate signature
        $params['sign'] = $this->generateSignature($params);

        Log::debug('AliExpress API Request', [
            'method' => 'aliexpress.ds.text.search',
            'keyword' => $keyword,
            'category_id' => $options['category_id'] ?? 'none',
            'params' => array_merge($params, ['sign' => substr($params['sign'], 0, 20) . '...']),
        ]);

        try {
            // Make GET request with query parameters
            $response = Http::timeout(30)
                ->get($this->apiUrl, $params);

            $statusCode = $response->status();
            $body = $response->json();

            Log::debug('AliExpress API Response', [
                'status' => $statusCode,
                'has_data' => isset($body['aliexpress_ds_text_search_response']),
                'body_preview' => json_encode($body)
            ]);

            if ($statusCode !== 200) {
                throw new \Exception("API returned status code: {$statusCode}");
            }

            return $this->parseResponse($body);

        } catch (\Exception $e) {
            Log::error('AliExpress API Request Failed', [
                'error' => $e->getMessage(),
                'keyword' => $keyword,
            ]);
            throw $e;
        }
    }

    /**
     * Parse API response
     */
    private function parseResponse(array $body): array
    {
        // Check for wrapped response
        if (isset($body['aliexpress_ds_text_search_response'])) {
            $response = $body['aliexpress_ds_text_search_response'];

            Log::debug('Response Structure', [
                'code' => $response['code'] ?? 'N/A',
                'has_data' => isset($response['data']),
                'totalCount' => $response['data']['totalCount'] ?? 'N/A',
                'data_keys' => isset($response['data']) ? array_keys($response['data']) : [],
            ]);

            if (isset($response['data'])) {
                $data = $response['data'];

                $products = [];

                // Log what we have in products
                Log::debug('Products data structure', [
                    'has_products_key' => isset($data['products']),
                    'products_type' => isset($data['products']) ? gettype($data['products']) : 'N/A',
                    'products_is_array' => isset($data['products']) && is_array($data['products']),
                    'products_keys' => isset($data['products']) && is_array($data['products']) ? array_keys($data['products']) : 'N/A',
                ]);

                // CRITICAL: Products are in selection_search_product array!
                if (isset($data['products']['selection_search_product'])) {
                    $products = $data['products']['selection_search_product'];
                    Log::debug('Found products in selection_search_product', ['count' => count($products)]);
                } elseif (isset($data['products']) && is_array($data['products'])) {
                    $products = $data['products'];
                    Log::debug('Found products directly', ['count' => count($products)]);
                } else {
                    Log::warning('No products found in response', [
                        'totalCount' => $data['totalCount'] ?? 0,
                        'has_products' => isset($data['products']),
                    ]);
                }

                return [
                    'products' => $this->formatProducts($products),
                    'total_count' => $data['totalCount'] ?? 0,
                    'current_page' => $data['pageIndex'] ?? 1,
                    'page_size' => $data['pageSize'] ?? 50,
                    'debug' => [
                        'code' => $response['code'] ?? null,
                        'total_count' => $data['totalCount'] ?? 0,
                        'products_found' => count($products),
                    ],
                ];
            }
        }

        // Check for error
        if (isset($body['error_response'])) {
            throw new \Exception(
                "AliExpress API Error: " .
                ($body['error_response']['msg'] ?? 'Unknown error')
            );
        }

        return [
            'products' => [],
            'total_count' => 0,
            'current_page' => 1,
            'page_size' => 0,
            'debug' => ['error' => 'Unexpected response structure'],
        ];
    }

    /**
     * Format products for consistent output
     */
    private function formatProducts(array $products): array
    {
        return array_map(function($product) {
            return [
                'item_id' => $product['itemId'] ?? '',
                'title' => $product['title'] ?? '',
                'item_main_pic' => $product['itemMainPic'] ?? '',
                'sale_price' => $product['targetSalePrice'] ?? $product['salePrice'] ?? 0,
                'original_price' => $product['targetOriginalPrice'] ?? $product['originalPrice'] ?? 0,
                'discount' => $product['discount'] ?? '',
                'sale_price_format' => $product['salePriceFormat'] ?? '',
                'original_price_format' => $product['originalPriceFormat'] ?? '',
                'evaluate_rate' => $product['evaluateRate'] ?? '',
                'score' => $product['score'] ?? '',
                'orders' => $product['orders'] ?? 0,
                'item_url' => $product['itemUrl'] ?? '',
                'product_video_url' => $product['productVideoUrl'] ?? '',
                'cate_id' => $product['cateId'] ?? '',
            ];
        }, $products);
    }
}
