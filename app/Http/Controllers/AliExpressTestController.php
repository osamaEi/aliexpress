<?php
// app/Http/Controllers/AliExpressTestController.php

namespace App\Http\Controllers;

use App\Services\AliExpressService;
use Illuminate\Http\Request;

class AliExpressTestController extends Controller
{
    protected $aliexpressService;

    public function __construct(AliExpressService $aliexpressService)
    {
        $this->aliexpressService = $aliexpressService;
    }

    /**
     * Test AliExpress API connection
     */
    public function testConnection(Request $request)
    {
        $productId = $request->get('product_id', '1005006340579394');

        $result = [
            'test_info' => [
                'timestamp' => now()->toDateTimeString(),
                'product_id_tested' => $productId,
            ],
            'configuration' => [
                'api_key' => config('services.aliexpress.api_key'),
                'api_secret' => config('services.aliexpress.api_secret') ? '***SET***' : '❌ NOT SET',
                'access_token' => config('services.aliexpress.access_token') ? '✅ SET' : '❌ NOT SET',
                'api_url' => config('services.aliexpress.api_url'),
            ],
            'api_response' => null,
            'error' => null,
            'status' => 'pending',
        ];

        try {
            // Test 1: Get product details
            $product = $this->aliexpressService->getProductDetails($productId);

            if ($product) {
                $result['status'] = 'success';
                $result['api_response'] = [
                    'product_id' => $product['ae_item_base_info_dto']['product_id'] ?? 'N/A',
                    'title' => $product['ae_item_base_info_dto']['subject'] ?? 'N/A',
                    'price' => $product['ae_item_base_info_dto']['target_sale_price'] ?? 'N/A',
                    'currency' => $product['ae_item_base_info_dto']['target_sale_price_currency'] ?? 'N/A',
                    'image_url' => $product['ae_multimedia_info_dto']['image_urls'][0] ?? 'N/A',
                    'full_data' => $product,
                ];
                $result['message'] = 'Successfully connected to AliExpress API';
            } else {
                $result['status'] = 'warning';
                $result['message'] = 'API returned empty response';
            }

        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['error'] = $e->getMessage();
            $result['message'] = 'Exception occurred while testing AliExpress API';

            // Add detailed error info
            $result['debug_info'] = [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        // If JSON is requested, return JSON response
        if ($request->wantsJson() || $request->has('json')) {
            return response()->json($result, 200, [], JSON_PRETTY_PRINT);
        }

        // Otherwise, return HTML view
        return view('aliexpress-test', [
            'status' => $result['status'],
            'message' => $result['message'] ?? null,
            'testInfo' => $result['test_info'],
            'configuration' => $result['configuration'],
            'apiResponse' => $result['api_response'],
            'error' => $result['error'] ?? null,
            'debugInfo' => $result['debug_info'] ?? null,
        ]);
    }

    /**
     * Test multiple endpoints
     */
    public function testAllEndpoints()
    {
        $results = [];

        // Test 1: Categories
        try {
            $categories = $this->aliexpressService->getCategories(0);
            $results['categories'] = [
                'status' => '✅ SUCCESS',
                'count' => count($categories['categories']['category'] ?? []),
                'sample' => array_slice($categories['categories']['category'] ?? [], 0, 3)
            ];
        } catch (\Exception $e) {
            $results['categories'] = [
                'status' => '❌ ERROR',
                'error' => $e->getMessage()
            ];
        }

        // Test 2: Product Details
        try {
            $product = $this->aliexpressService->getProductDetails('1005006340579394');
            $results['product_details'] = [
                'status' => '✅ SUCCESS',
                'product_name' => $product['ae_item_base_info_dto']['subject'] ?? 'N/A'
            ];
        } catch (\Exception $e) {
            $results['product_details'] = [
                'status' => '❌ ERROR',
                'error' => $e->getMessage()
            ];
        }

        // Test 3: Recommended Products
        try {
            $recommended = $this->aliexpressService->getRecommendedProducts([
                'limit' => 5
            ]);
            $results['recommended_products'] = [
                'status' => '✅ SUCCESS',
                'count' => count($recommended['products']['product'] ?? [])
            ];
        } catch (\Exception $e) {
            $results['recommended_products'] = [
                'status' => '❌ ERROR',
                'error' => $e->getMessage()
            ];
        }

        // Test 4: Shipping Calculation
        try {
            $shipping = $this->aliexpressService->calculateShipping(
                '1005006340579394',
                1,
                'EG'
            );
            $results['shipping_calculation'] = [
                'status' => '✅ SUCCESS',
                'data' => $shipping
            ];
        } catch (\Exception $e) {
            $results['shipping_calculation'] = [
                'status' => '❌ ERROR',
                'error' => $e->getMessage()
            ];
        }

        return response()->json([
            'overall_status' => $this->getOverallStatus($results),
            'timestamp' => now()->toDateTimeString(),
            'tests' => $results
        ], 200, [], JSON_PRETTY_PRINT);
    }

    private function getOverallStatus($results)
    {
        $allSuccess = true;
        foreach ($results as $result) {
            if ($result['status'] !== '✅ SUCCESS') {
                $allSuccess = false;
                break;
            }
        }

        return $allSuccess ? '✅ ALL TESTS PASSED' : '⚠️ SOME TESTS FAILED';
    }

    /**
     * Display test page
     */
    public function testPage()
    {
        return view('aliexpress-test');
    }

    /**
     * Generate OAuth authorization URL and display authorization page
     * Visit this endpoint to get the authorization URL
     */
    public function getAuthUrl(Request $request)
    {
        $appKey = config('services.aliexpress.api_key');
        $apiSecret = config('services.aliexpress.api_secret');
        $redirectUri = url('/aliexpress-oauth-callback'); // Our callback URL
        $state = bin2hex(random_bytes(16)); // Random state for security

        // Store state in session for verification
        session(['aliexpress_oauth_state' => $state]);

        $authUrl = 'https://api-sg.aliexpress.com/oauth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $appKey,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);

        // If JSON is requested, return JSON response
        if ($request->wantsJson() || $request->has('json')) {
            return response()->json([
                'status' => 'ready',
                'message' => 'Click the authorization_url below to authorize your app',
                'authorization_url' => $authUrl,
                'configuration' => [
                    'app_key' => $appKey,
                    'redirect_uri' => $redirectUri,
                    'state' => $state,
                ],
                'instructions' => [
                    'step_1' => 'Click on the authorization_url above',
                    'step_2' => 'Login to your AliExpress account',
                    'step_3' => 'Authorize the application',
                    'step_4' => 'You will be redirected back automatically',
                    'step_5' => 'The access token will be displayed',
                ],
                'alternative_method' => [
                    'description' => 'If you prefer to handle the callback manually',
                    'manual_url' => url('/test-aliexpress-token') . '?code=YOUR_CODE',
                ]
            ], 200, [], JSON_PRETTY_PRINT);
        }

        // Otherwise, return HTML view
        return view('aliexpress-auth', [
            'authUrl' => $authUrl,
            'appKey' => $appKey,
            'apiSecretSet' => !empty($apiSecret),
            'redirectUri' => $redirectUri,
            'state' => $state,
        ]);
    }

    /**
     * OAuth callback handler
     * This endpoint receives the authorization code from AliExpress
     */
    public function oauthCallback(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');
        $error = $request->get('error');
        $errorDescription = $request->get('error_description');

        // Check for errors
        if ($error) {
            $data = [
                'status' => 'error',
                'message' => 'OAuth authorization failed',
                'error' => $error,
                'debugInfo' => [
                    'error_description' => $errorDescription,
                ],
            ];

            if ($request->wantsJson() || $request->has('json')) {
                return response()->json($data, 400, [], JSON_PRETTY_PRINT);
            }

            return view('aliexpress-callback-simple', $data);
        }

        // Verify state to prevent CSRF attacks
        $sessionState = session('aliexpress_oauth_state');
        if ($state !== $sessionState) {
            $data = [
                'status' => 'error',
                'message' => 'State mismatch - possible CSRF attack',
                'error' => 'Invalid state parameter',
                'debugInfo' => [
                    'received_state' => $state,
                    'expected_state' => $sessionState,
                ],
            ];

            if ($request->wantsJson() || $request->has('json')) {
                return response()->json($data, 400, [], JSON_PRETTY_PRINT);
            }

            return view('aliexpress-callback-simple', $data);
        }

        // Clear the state from session
        session()->forget('aliexpress_oauth_state');

        // If code is present, exchange it for access token
        if ($code) {
            try {
                $tokenData = $this->aliexpressService->createToken($code);

                if ($tokenData) {
                    $data = [
                        'status' => 'success',
                        'message' => 'Access token created successfully!',
                        'tokenData' => $tokenData,
                    ];

                    if ($request->wantsJson() || $request->has('json')) {
                        return response()->json([
                            'status' => '✅ SUCCESS',
                            'message' => '🎉 Access token created successfully!',
                            'token_data' => $tokenData,
                            'next_steps' => [
                                'step_1' => 'Copy the access_token from token_data above',
                                'step_2' => 'Open your .env file',
                                'step_3' => 'Set: ALIEXPRESS_ACCESS_TOKEN=your_token_here',
                                'step_4' => 'Run: php artisan config:clear',
                                'step_5' => 'Test connection at: ' . url('/test-aliexpress'),
                            ],
                        ], 200, [], JSON_PRETTY_PRINT);
                    }

                    return view('aliexpress-callback-simple', $data);
                }
            } catch (\Exception $e) {
                $data = [
                    'status' => 'error',
                    'message' => 'Failed to create access token',
                    'error' => $e->getMessage(),
                    'debugInfo' => [
                        'exception_class' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                ];

                if ($request->wantsJson() || $request->has('json')) {
                    return response()->json($data, 500, [], JSON_PRETTY_PRINT);
                }

                return view('aliexpress-callback-simple', $data);
            }
        }

        $data = [
            'status' => 'error',
            'message' => 'The OAuth callback did not include an authorization code',
            'error' => 'No authorization code received',
        ];

        if ($request->wantsJson() || $request->has('json')) {
            return response()->json($data, 400, [], JSON_PRETTY_PRINT);
        }

        return view('aliexpress-callback', $data);
    }

    /**
     * Test OAuth token creation
     * This endpoint should be called after you receive the authorization code from OAuth flow
     *
     * Example URL: /test-aliexpress-token?code=YOUR_AUTH_CODE
     */
    public function testTokenCreation(Request $request)
    {
        $authCode = $request->get('code');

        $result = [
            'test_info' => [
                'timestamp' => now()->toDateTimeString(),
                'endpoint' => 'OAuth Token Creation',
            ],
            'configuration' => [
                'api_key' => config('services.aliexpress.api_key'),
                'api_secret' => config('services.aliexpress.api_secret') ? '***SET***' : '❌ NOT SET',
                'token_url' => 'https://api-sg.aliexpress.com/rest',
            ],
            'request_data' => [
                'code_provided' => $authCode ? '✅ YES' : '❌ NO',
                'code_length' => $authCode ? strlen($authCode) : 0,
            ],
            'api_response' => null,
            'error' => null,
            'status' => 'pending',
        ];

        if (!$authCode) {
            $result['status'] = 'pending';
            $result['error'] = 'Authorization code is required';
            $result['message'] = 'Please provide authorization code via ?code=YOUR_CODE parameter';
            $result['oauth_flow_instructions'] = [
                'step_1' => 'Visit AliExpress OAuth authorization URL',
                'step_2' => 'Authorize your app',
                'step_3' => 'Copy the authorization code from redirect URL',
                'step_4' => 'Call this endpoint with ?code=YOUR_CODE',
            ];
            $result['authorization_url_example'] = 'https://api-sg.aliexpress.com/oauth/authorize?response_type=code&client_id=YOUR_APP_KEY&redirect_uri=YOUR_REDIRECT_URI&state=random_state';

            if ($request->wantsJson() || $request->has('json')) {
                return response()->json($result, 400, [], JSON_PRETTY_PRINT);
            }

            return view('aliexpress-token-test', [
                'status' => $result['status'],
                'message' => $result['message'],
                'testInfo' => $result['test_info'],
                'configuration' => $result['configuration'],
                'requestData' => $result['request_data'],
                'oauthFlowInstructions' => $result['oauth_flow_instructions'],
                'authorizationUrlExample' => $result['authorization_url_example'],
            ]);
        }

        try {
            // Test token creation
            $tokenData = $this->aliexpressService->createToken($authCode);

            if ($tokenData) {
                $result['status'] = 'success';
                $result['api_response'] = $tokenData;
                $result['message'] = 'Token created successfully! Add the access_token to your .env file';

                // Provide instructions for next steps
                $result['next_steps'] = [
                    'step_1' => 'Copy the access_token from the response above',
                    'step_2' => 'Add it to your .env file: ALIEXPRESS_ACCESS_TOKEN=your_token',
                    'step_3' => 'Run: php artisan config:clear',
                    'step_4' => 'Test the connection with: /test-aliexpress',
                ];
            } else {
                $result['status'] = 'warning';
                $result['message'] = 'API returned empty response';
            }

        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['error'] = $e->getMessage();
            $result['message'] = 'Exception occurred while creating token';

            // Add detailed error info
            $result['debug_info'] = [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];

            if ($request->wantsJson() || $request->has('json')) {
                return response()->json($result, 500, [], JSON_PRETTY_PRINT);
            }

            return view('aliexpress-token-test', [
                'status' => $result['status'],
                'message' => $result['message'],
                'testInfo' => $result['test_info'],
                'configuration' => $result['configuration'],
                'requestData' => $result['request_data'],
                'error' => $result['error'],
                'debugInfo' => $result['debug_info'],
                'apiResponse' => null,
            ]);
        }

        if ($request->wantsJson() || $request->has('json')) {
            return response()->json($result, 200, [], JSON_PRETTY_PRINT);
        }

        return view('aliexpress-token-test', [
            'status' => $result['status'],
            'message' => $result['message'],
            'testInfo' => $result['test_info'],
            'configuration' => $result['configuration'],
            'requestData' => $result['request_data'],
            'apiResponse' => $result['api_response'],
            'error' => $result['error'] ?? null,
            'debugInfo' => $result['debug_info'] ?? null,
        ]);
    }

    /**
     * Test OAuth token refresh
     *
     * Example URL: /test-aliexpress-refresh?refresh_token=YOUR_REFRESH_TOKEN
     */
    public function testTokenRefresh(Request $request)
    {
        $refreshToken = $request->get('refresh_token');

        $result = [
            'test_info' => [
                'timestamp' => now()->toDateTimeString(),
                'endpoint' => 'OAuth Token Refresh',
            ],
            'request_data' => [
                'refresh_token_provided' => $refreshToken ? '✅ YES' : '❌ NO',
            ],
            'api_response' => null,
            'error' => null,
            'status' => 'pending',
        ];

        if (!$refreshToken) {
            $result['status'] = '❌ ERROR';
            $result['error'] = 'Refresh token is required';
            $result['message'] = 'Please provide refresh token via ?refresh_token=YOUR_TOKEN parameter';

            return response()->json($result, 400, [], JSON_PRETTY_PRINT);
        }

        try {
            $tokenData = $this->aliexpressService->refreshToken($refreshToken);

            if ($tokenData) {
                $result['status'] = '✅ SUCCESS';
                $result['api_response'] = $tokenData;
                $result['message'] = '✅ Token refreshed successfully!';
            } else {
                $result['status'] = '⚠️ WARNING';
                $result['message'] = 'API returned empty response';
            }

        } catch (\Exception $e) {
            $result['status'] = '❌ ERROR';
            $result['error'] = $e->getMessage();
            $result['message'] = 'Exception occurred while refreshing token';

            $result['debug_info'] = [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];

            return response()->json($result, 500, [], JSON_PRETTY_PRINT);
        }

        return response()->json($result, 200, [], JSON_PRETTY_PRINT);
    }
}
