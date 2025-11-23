<?php

namespace App\Http\Controllers;

use App\Services\AliExpressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingTestController extends Controller
{
    private $aliexpressService;

    public function __construct(AliExpressService $aliexpressService)
    {
        $this->aliexpressService = $aliexpressService;
    }

    /**
     * Show the shipping test form
     */
    public function index()
    {
        return view('shipping.test');
    }

    /**
     * Get product details including available SKUs
     */
    public function getProductDetails(Request $request)
    {
        try {
            $productId = $request->input('product_id');

            if (empty($productId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ID is required',
                ]);
            }

            Log::info('Shipping Test - Fetching Product Details', ['product_id' => $productId]);

            // Fetch product details from AliExpress
            $productDetails = $this->aliexpressService->getProductDetails($productId);

            if (!$productDetails) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or API error',
                ]);
            }

            // Extract SKUs
            $skus = [];

            // Method 1: Check ae_item_sku_info_dtos (preferred - from product details API)
            if (isset($productDetails['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'])) {
                $skuList = $productDetails['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'];

                // Ensure it's an array
                if (!isset($skuList[0])) {
                    $skuList = [$skuList];
                }

                foreach ($skuList as $sku) {
                    // For AliExpress freight API, use ONLY numeric SKU ID (per official docs)
                    // Example from docs: selectedSkuId = "12000023999200390"
                    $skuId = $sku['id'] ?? $sku['sku_id'] ?? $sku['sku_code'] ?? null;

                    // IMPORTANT: Only use numeric SKU IDs (not property combinations)
                    // Property combinations contain '#' (e.g., "14:29#Pro 5") and won't work with freight API
                    $isNumeric = $skuId && !str_contains((string)$skuId, '#');

                    if ($skuId) {
                        $skus[] = [
                            'id' => $skuId,  // SKU ID
                            'sku_attr' => $sku['sku_attr'] ?? null,  // Property combo (for reference only)
                            'is_numeric' => $isNumeric,  // Flag to indicate if this is a valid numeric SKU
                            'price' => $sku['sku_price'] ?? null,
                            'stock' => $sku['sku_stock'] ?? $sku['sku_available_stock'] ?? null,
                            'available' => ($sku['sku_available_stock'] ?? 0) > 0,
                            'raw_sku' => $sku,  // Include raw SKU data for debugging
                        ];
                    }
                }
            }

            // Method 2: Check aeop_ae_product_s_k_us (fallback)
            if (empty($skus) && isset($productDetails['aeop_ae_product_s_k_us']['aeop_ae_product_sku'])) {
                $skuList = $productDetails['aeop_ae_product_s_k_us']['aeop_ae_product_sku'];

                // Ensure it's an array
                if (!isset($skuList[0])) {
                    $skuList = [$skuList];
                }

                foreach ($skuList as $sku) {
                    if (isset($sku['id'])) {
                        $skus[] = [
                            'id' => $sku['id'],
                            'sku_attr' => null,
                            'price' => $sku['sku_price'] ?? null,
                            'stock' => $sku['sku_stock'] ?? $sku['s_k_u_available_stock'] ?? null,
                            'available' => ($sku['s_k_u_available_stock'] ?? 0) > 0,
                        ];
                    }
                }
            }

            // Get first available SKU
            $firstSku = $this->aliexpressService->getFirstAvailableSku($productDetails);

            return response()->json([
                'success' => true,
                'product_id' => $productId,
                'product_title' => $productDetails['subject'] ?? 'N/A',
                'first_available_sku' => $firstSku,
                'total_skus' => count($skus),
                'skus' => $skus,
                'raw_response' => $productDetails,
            ]);

        } catch (\Exception $e) {
            Log::error('Shipping Test - Failed to get product details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching product details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate freight using the provided parameters
     */
    public function calculate(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'product_id' => 'required|string',
                'sku_id' => 'nullable|string',
                'quantity' => 'required|integer|min:1',
                'country' => 'required|string|size:2',
                'currency' => 'required|string|size:3',
                'city' => 'nullable|string',
                'province' => 'nullable|string',
                'language' => 'nullable|string',
                'locale' => 'nullable|string',
            ]);

            Log::info('Shipping Test - Request Received', $validated);

            // If SKU is not provided, try to auto-detect it
            if (empty($validated['sku_id'])) {
                try {
                    // Fetch product details from AliExpress
                    $productDetails = $this->aliexpressService->getProductDetails($validated['product_id']);

                    Log::info('Shipping Test - Product Details Fetched', [
                        'product_id' => $validated['product_id'],
                        'has_data' => !empty($productDetails),
                    ]);

                    // Try to extract SKU from product details
                    if ($productDetails) {
                        $skuId = $this->aliexpressService->getFirstAvailableSku($productDetails);
                        if ($skuId) {
                            $validated['sku_id'] = $skuId;
                            Log::info('Shipping Test - SKU Auto-Detected', ['sku_id' => $skuId]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Shipping Test - Failed to auto-detect SKU', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // If still no SKU, return error
            if (empty($validated['sku_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'SKU ID is required. Please provide SKU ID or ensure the product has valid SKUs.',
                    'raw_response' => [
                        'error' => 'MISSING_SKU',
                        'msg' => 'Could not auto-detect SKU for this product',
                    ],
                ]);
            }

            // Prepare freight calculation parameters
            $freightParams = [
                'product_id' => $validated['product_id'],
                'sku_id' => $validated['sku_id'],
                'product_num' => $validated['quantity'],
                'country' => $validated['country'],
                'currency' => $validated['currency'],
                'language' => $validated['language'] ?? 'en_US',
                'locale' => $validated['locale'] ?? 'en_US',
            ];

            // Add optional city and province if provided
            if (!empty($validated['city'])) {
                $freightParams['city'] = $validated['city'];
            }
            if (!empty($validated['province'])) {
                $freightParams['province'] = $validated['province'];
            }

            Log::info('Shipping Test - Calling AliExpress Freight API', $freightParams);

            // Call AliExpress freight calculation
            $result = $this->aliexpressService->calculateFreight($freightParams);

            Log::info('Shipping Test - API Response', [
                'success' => $result['success'],
                'has_data' => isset($result['data']),
            ]);

            // If error 501 (DELIVERY_INFO_EMPTY), retry without city/province
            if (!$result['success'] && isset($result['raw_response']['code']) && $result['raw_response']['code'] == 501) {
                if (isset($freightParams['city']) || isset($freightParams['province'])) {
                    Log::info('Shipping Test - Retrying without city/province');

                    unset($freightParams['city']);
                    unset($freightParams['province']);

                    $result = $this->aliexpressService->calculateFreight($freightParams);

                    Log::info('Shipping Test - Retry Response', [
                        'success' => $result['success'],
                    ]);
                }
            }

            // Format response
            if ($result['success'] && isset($result['data'])) {
                $freight = $result['data'];

                return response()->json([
                    'success' => true,
                    'message' => 'Freight calculated successfully',
                    'freight_amount' => $freight['freight_amount'] ?? null,
                    'freight_currency' => $freight['freight_currency'] ?? null,
                    'shipping_method' => $freight['shipping_method'] ?? null,
                    'delivery_time' => $freight['delivery_time'] ?? null,
                    'service_name' => $freight['service_name'] ?? null,
                    'company_name' => $freight['company_name'] ?? null,
                    'raw_response' => $result['raw_response'] ?? [],
                    'sku_used' => $validated['sku_id'],
                    'auto_detected' => !$request->filled('sku_id'),
                ]);
            } else {
                // Error response
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to calculate freight',
                    'raw_response' => $result['raw_response'] ?? [],
                    'sku_used' => $validated['sku_id'],
                    'auto_detected' => !$request->filled('sku_id'),
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Shipping Test - Validation Error', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'raw_response' => [
                    'error' => 'VALIDATION_ERROR',
                    'details' => $e->errors(),
                ],
            ], 422);

        } catch (\Exception $e) {
            Log::error('Shipping Test - Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'raw_response' => [
                    'error' => 'EXCEPTION',
                    'msg' => $e->getMessage(),
                ],
            ], 500);
        }
    }
}
