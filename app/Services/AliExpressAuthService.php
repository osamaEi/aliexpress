<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\AliExpressToken;

class AliExpressAuthService
{
    protected $gatewayUrl = 'https://api-sg.aliexpress.com/rest';
    protected $authGatewayUrl = 'https://eco.aliexpress.com/token/authorize';
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->apiKey = config('services.aliexpress.api_key');
        $this->apiSecret = config('services.aliexpress.api_secret');

        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new \Exception('AliExpress API credentials not configured in .env');
        }
    }

    // ===================================================================
    // SIGNATURE GENERATION
    // ===================================================================

    /**
     * Generate HMAC-SHA256 signature for OAuth endpoints
     * OAuth endpoints use HMAC instead of simple hash
     */
    public function generateOAuthSign(string $apiPath, array $params): string
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

        Log::debug('AliExpress Authorization URL Generated', [
            'auth_url' => $authUrl,
            'client_id' => $this->apiKey,
            'redirect_uri' => $redirectUri,
            'full_url' => $authUrl . '?' . http_build_query($params)
        ]);

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

        // Load from database
        $tokenModel = AliExpressToken::where('account', 'default')->first();

        if (!$tokenModel) {
            throw new \Exception(
                'No access token found. Please authorize first at: ' .
                route('aliexpress.authorize')
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
}
