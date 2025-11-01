<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    protected $apiKey;
    protected $integrationId;
    protected $iframeId;
    protected $hmacSecret;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('paymob.api_key');
        $this->integrationId = config('paymob.integration_id');
        $this->iframeId = config('paymob.iframe_id');
        $this->hmacSecret = config('paymob.hmac_secret');
        $this->baseUrl = config('paymob.base_url', 'https://uae.paymob.com/api');
    }

    /**
     * Step 1: Authentication Request
     * Get authentication token
     */
    public function authenticate()
    {
        try {
            $response = Http::post("{$this->baseUrl}/auth/tokens", [
                'api_key' => $this->apiKey
            ]);

            if ($response->successful()) {
                return $response->json()['token'];
            }

            Log::error('Paymob Authentication Failed', ['response' => $response->body()]);
            throw new \Exception('Failed to authenticate with Paymob');
        } catch (\Exception $e) {
            Log::error('Paymob Authentication Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Step 2: Order Registration API
     * Register order with Paymob
     */
    public function createOrder($authToken, $amountCents, $orderId, $items = [])
    {
        try {
            $response = Http::post("{$this->baseUrl}/ecommerce/orders", [
                'auth_token' => $authToken,
                'delivery_needed' => 'false',
                'amount_cents' => $amountCents, // Amount in cents (multiply by 100)
                'currency' => 'AED',
                'merchant_order_id' => $orderId,
                'items' => $items
            ]);

            if ($response->successful()) {
                return $response->json()['id'];
            }

            Log::error('Paymob Order Creation Failed', ['response' => $response->body()]);
            throw new \Exception('Failed to create order with Paymob');
        } catch (\Exception $e) {
            Log::error('Paymob Order Creation Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Step 3: Payment Key Request
     * Get payment key for iframe
     */
    public function getPaymentKey($authToken, $orderId, $amountCents, $billingData)
    {
        try {
            $response = Http::post("{$this->baseUrl}/acceptance/payment_keys", [
                'auth_token' => $authToken,
                'amount_cents' => $amountCents,
                'expiration' => 3600, // 1 hour
                'order_id' => $orderId,
                'billing_data' => $billingData,
                'currency' => 'AED',
                'integration_id' => $this->integrationId,
                'lock_order_when_paid' => 'false'
            ]);

            if ($response->successful()) {
                return $response->json()['token'];
            }

            Log::error('Paymob Payment Key Failed', ['response' => $response->body()]);
            throw new \Exception('Failed to get payment key from Paymob');
        } catch (\Exception $e) {
            Log::error('Paymob Payment Key Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Complete payment flow
     * Returns payment URL or payment key for iframe
     */
    public function initiatePayment($amount, $orderId, $customerData, $items = [])
    {
        try {
            // Convert amount to cents
            $amountCents = (int) ($amount * 100);

            // Step 1: Authenticate
            $authToken = $this->authenticate();

            // Step 2: Create Order
            $paymobOrderId = $this->createOrder($authToken, $amountCents, $orderId, $items);

            // Step 3: Get Payment Key
            $billingData = [
                'apartment' => $customerData['apartment'] ?? 'NA',
                'email' => $customerData['email'],
                'floor' => $customerData['floor'] ?? 'NA',
                'first_name' => $customerData['first_name'],
                'street' => $customerData['street'] ?? 'NA',
                'building' => $customerData['building'] ?? 'NA',
                'phone_number' => $customerData['phone'],
                'shipping_method' => 'NA',
                'postal_code' => $customerData['postal_code'] ?? 'NA',
                'city' => $customerData['city'] ?? 'NA',
                'country' => $customerData['country'] ?? 'AE',
                'last_name' => $customerData['last_name'] ?? $customerData['first_name'],
                'state' => $customerData['state'] ?? 'NA'
            ];

            $paymentToken = $this->getPaymentKey($authToken, $paymobOrderId, $amountCents, $billingData);

            return [
                'payment_token' => $paymentToken,
                'iframe_id' => $this->iframeId,
                'payment_url' => "https://uae.paymob.com/api/acceptance/iframes/{$this->iframeId}?payment_token={$paymentToken}",
                'paymob_order_id' => $paymobOrderId
            ];
        } catch (\Exception $e) {
            Log::error('Paymob Payment Initiation Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Verify HMAC signature from callback
     */
    public function verifyHmac($data)
    {
        $concatenatedString =
            $data['amount_cents'] .
            $data['created_at'] .
            $data['currency'] .
            $data['error_occured'] .
            $data['has_parent_transaction'] .
            $data['id'] .
            $data['integration_id'] .
            $data['is_3d_secure'] .
            $data['is_auth'] .
            $data['is_capture'] .
            $data['is_refunded'] .
            $data['is_standalone_payment'] .
            $data['is_voided'] .
            $data['order']['id'] .
            $data['owner'] .
            $data['pending'] .
            $data['source_data']['pan'] .
            $data['source_data']['sub_type'] .
            $data['source_data']['type'] .
            $data['success'];

        $hash = hash_hmac('sha512', $concatenatedString, $this->hmacSecret);

        return hash_equals($hash, $data['hmac']);
    }

    /**
     * Process callback from Paymob
     */
    public function processCallback($data)
    {
        try {
            // Verify HMAC
            if (!$this->verifyHmac($data)) {
                Log::warning('Paymob HMAC Verification Failed', ['data' => $data]);
                return [
                    'success' => false,
                    'message' => 'Invalid HMAC signature'
                ];
            }

            // Check if payment was successful
            $isSuccess = $data['success'] === 'true' || $data['success'] === true;

            return [
                'success' => $isSuccess,
                'transaction_id' => $data['id'],
                'order_id' => $data['order']['merchant_order_id'] ?? null,
                'amount_cents' => $data['amount_cents'],
                'currency' => $data['currency'],
                'created_at' => $data['created_at'],
                'is_refunded' => $data['is_refunded'],
                'error_occured' => $data['error_occured'],
                'pending' => $data['pending'],
                'raw_data' => $data
            ];
        } catch (\Exception $e) {
            Log::error('Paymob Callback Processing Error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Refund a transaction
     */
    public function refund($transactionId, $amountCents)
    {
        try {
            $authToken = $this->authenticate();

            $response = Http::post("{$this->baseUrl}/acceptance/void_refund/refund", [
                'auth_token' => $authToken,
                'transaction_id' => $transactionId,
                'amount_cents' => $amountCents
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Paymob Refund Failed', ['response' => $response->body()]);
            throw new \Exception('Failed to process refund');
        } catch (\Exception $e) {
            Log::error('Paymob Refund Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Check transaction status
     */
    public function getTransactionStatus($transactionId)
    {
        try {
            $authToken = $this->authenticate();

            $response = Http::get("{$this->baseUrl}/acceptance/transactions/{$transactionId}", [
                'token' => $authToken
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Paymob Transaction Status Failed', ['response' => $response->body()]);
            throw new \Exception('Failed to get transaction status');
        } catch (\Exception $e) {
            Log::error('Paymob Transaction Status Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
