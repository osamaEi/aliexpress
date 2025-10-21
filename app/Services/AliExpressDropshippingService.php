<?php

namespace App\Services;

use AliExpress\Sdk\IopClient;
use AliExpress\Sdk\IopRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AliExpressDropshippingService
{
    protected $client;
    protected $accessToken;

    public function __construct()
    {
        $apiKey = config('services.aliexpress.api_key');
        $apiSecret = config('services.aliexpress.api_secret');
        $gatewayUrl = config('services.aliexpress.api_url');
        $this->accessToken = config('services.aliexpress.access_token');

        if (empty($apiKey) || empty($apiSecret)) {
            throw new \Exception('AliExpress API credentials not configured');
        }

        if (empty($this->accessToken)) {
            throw new \Exception('AliExpress access token not configured. Please set ALIEXPRESS_ACCESS_TOKEN in .env file');
        }

        $this->client = new IopClient($gatewayUrl, $apiKey, $apiSecret);
    }

    /**
     * Search products using feed item IDs
     */
    public function searchProducts(string $keywords = '', array $options = [])
    {
        try {
            // Step 1: Get product IDs from feed
            $request = new IopRequest('aliexpress.ds.feed.itemids.get');

            // Required parameters
            $feedName = !empty($keywords) ? 'DS bestseller' : 'DS bestseller';
            $request->addApiParam('feed_name', $feedName);
            $request->addApiParam('page_size', min($options['limit'] ?? 20, 200));

            // Optional parameters
            if (!empty($options['category_id'])) {
                $request->addApiParam('category_id', $options['category_id']);
            }

            Log::info('AliExpress Feed Item IDs Request', [
                'feed_name' => $feedName,
                'page_size' => min($options['limit'] ?? 20, 200),
                'category_id' => $options['category_id'] ?? null,
            ]);

            $response = $this->client->execute($request, $this->accessToken);
            $data = json_decode($response, true);

            Log::info('AliExpress Feed Item IDs Response', ['response' => $data]);

            // Check for errors
            if (isset($data['error_response'])) {
                throw new \Exception(
                    "API Error: {$data['error_response']['msg']} (Code: {$data['error_response']['code']})"
                );
            }

            // Extract product IDs
            $productIds = [];
            if (isset($data['result']['products']) && is_array($data['result']['products'])) {
                $productIds = $data['result']['products'];
            }

            if (empty($productIds)) {
                return [
                    'success' => false,
                    'products' => [],
                    'message' => 'No products found. Your account may need to be enrolled in the AliExpress Dropshipping Program at https://ds.aliexpress.com/',
                ];
            }

            // Step 2: Get product details for each ID
            $products = [];
            $country = $options['country'] ?? 'US';
            $currency = $options['currency'] ?? 'USD';
            $language = $options['language'] ?? 'EN';

            foreach ($productIds as $productId) {
                $productDetails = $this->getProductDetails($productId, [
                    'country' => $country,
                    'currency' => $currency,
                    'language' => $language,
                ]);

                if ($productDetails['success']) {
                    $products[] = $productDetails['product'];
                }
            }

            return [
                'success' => true,
                'products' => $products,
                'total_count' => $data['result']['total'] ?? count($products),
                'current_count' => count($products),
                'search_id' => $data['result']['search_id'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('AliExpress Search Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'products' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get product details by ID
     */
    public function getProductDetails(string $productId, array $options = [])
    {
        try {
            $request = new IopRequest('aliexpress.ds.product.get');

            $request->addApiParam('product_id', $productId);
            $request->addApiParam('ship_to_country', $options['country'] ?? 'US');
            $request->addApiParam('target_currency', $options['currency'] ?? 'USD');
            $request->addApiParam('target_language', $options['language'] ?? 'EN');

            $response = $this->client->execute($request, $this->accessToken);
            $data = json_decode($response, true);

            if (isset($data['error_response'])) {
                throw new \Exception(
                    "API Error: {$data['error_response']['msg']}"
                );
            }

            if (isset($data['aliexpress_ds_product_get_response']['result'])) {
                return [
                    'success' => true,
                    'product' => $data['aliexpress_ds_product_get_response']['result'],
                ];
            }

            return [
                'success' => false,
                'error' => 'Product not found',
            ];

        } catch (\Exception $e) {
            Log::error('AliExpress Product Details Error', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if account is enrolled in dropshipping program
     */
    public function checkDropshippingAccess()
    {
        try {
            // Try to fetch bestselling feed with minimal parameters
            $request = new IopRequest('aliexpress.ds.feed.itemids.get');
            $request->addApiParam('feed_name', 'DS bestseller');
            $request->addApiParam('page_size', 1);

            $response = $this->client->execute($request, $this->accessToken);
            $data = json_decode($response, true);

            if (isset($data['error_response'])) {
                return [
                    'enrolled' => false,
                    'message' => $data['error_response']['msg'],
                    'action_required' => 'Please enroll at https://ds.aliexpress.com/',
                ];
            }

            if (isset($data['result'])) {
                return [
                    'enrolled' => true,
                    'message' => 'Account is enrolled in dropshipping program',
                    'product_count' => $data['result']['total'] ?? 0,
                ];
            }

            return [
                'enrolled' => false,
                'message' => 'Unable to verify enrollment status',
            ];

        } catch (\Exception $e) {
            return [
                'enrolled' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
