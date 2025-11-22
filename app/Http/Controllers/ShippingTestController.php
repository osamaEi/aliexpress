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
