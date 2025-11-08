<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private $clientId;
    private $clientSecret;
    private $mode;
    private $apiUrl;

    public function __construct()
    {
        $this->mode = config('paypal.mode');
        $this->clientId = config("paypal.{$this->mode}.client_id");
        $this->clientSecret = config("paypal.{$this->mode}.client_secret");
        $this->apiUrl = config("paypal.api_url.{$this->mode}");
    }

    /**
     * Get PayPal OAuth access token
     *
     * @return string
     * @throws Exception
     */
    private function getAccessToken()
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post("{$this->apiUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            throw new Exception('Failed to get PayPal access token: ' . $response->body());
        } catch (Exception $e) {
            Log::error('PayPal Authentication Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a PayPal order
     *
     * @param float $amount
     * @param string $currency
     * @param string $description
     * @param string $referenceId
     * @return array
     * @throws Exception
     */
    public function createOrder($amount, $currency = null, $description = 'Payment', $referenceId = null)
    {
        try {
            $currency = $currency ?? config('paypal.currency');
            $accessToken = $this->getAccessToken();

            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $referenceId ?? uniqid('order_'),
                        'description' => $description,
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                    ],
                ],
                'application_context' => [
                    'return_url' => config('paypal.return_url'),
                    'cancel_url' => config('paypal.cancel_url'),
                    'brand_name' => config('app.name'),
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW',
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post("{$this->apiUrl}/v2/checkout/orders", $orderData);

            if ($response->successful()) {
                $order = $response->json();
                Log::info('PayPal Order Created', ['order_id' => $order['id']]);
                return $order;
            }

            throw new Exception('Failed to create PayPal order: ' . $response->body());
        } catch (Exception $e) {
            Log::error('PayPal Order Creation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Capture a PayPal order payment
     *
     * @param string $orderId
     * @return array
     * @throws Exception
     */
    public function captureOrder($orderId)
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->post("{$this->apiUrl}/v2/checkout/orders/{$orderId}/capture");

            if ($response->successful()) {
                $capture = $response->json();
                Log::info('PayPal Order Captured', ['order_id' => $orderId]);
                return $capture;
            }

            throw new Exception('Failed to capture PayPal order: ' . $response->body());
        } catch (Exception $e) {
            Log::error('PayPal Order Capture Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get PayPal order details
     *
     * @param string $orderId
     * @return array
     * @throws Exception
     */
    public function getOrderDetails($orderId)
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->get("{$this->apiUrl}/v2/checkout/orders/{$orderId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to get PayPal order details: ' . $response->body());
        } catch (Exception $e) {
            Log::error('PayPal Get Order Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Refund a captured payment
     *
     * @param string $captureId
     * @param float|null $amount
     * @param string|null $currency
     * @return array
     * @throws Exception
     */
    public function refundPayment($captureId, $amount = null, $currency = null)
    {
        try {
            $accessToken = $this->getAccessToken();

            $refundData = [];
            if ($amount !== null) {
                $refundData['amount'] = [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency_code' => $currency ?? config('paypal.currency'),
                ];
            }

            $response = Http::withToken($accessToken)
                ->post("{$this->apiUrl}/v2/payments/captures/{$captureId}/refund", $refundData);

            if ($response->successful()) {
                $refund = $response->json();
                Log::info('PayPal Refund Processed', ['capture_id' => $captureId]);
                return $refund;
            }

            throw new Exception('Failed to refund PayPal payment: ' . $response->body());
        } catch (Exception $e) {
            Log::error('PayPal Refund Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify PayPal webhook signature
     *
     * @param array $headers
     * @param string $payload
     * @param string $webhookId
     * @return bool
     * @throws Exception
     */
    public function verifyWebhookSignature($headers, $payload, $webhookId)
    {
        try {
            $accessToken = $this->getAccessToken();

            $verificationData = [
                'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
                'cert_url' => $headers['PAYPAL-CERT-URL'] ?? '',
                'auth_algo' => $headers['PAYPAL-AUTH-ALGO'] ?? '',
                'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($payload, true),
            ];

            $response = Http::withToken($accessToken)
                ->post("{$this->apiUrl}/v1/notifications/verify-webhook-signature", $verificationData);

            if ($response->successful()) {
                $result = $response->json();
                return $result['verification_status'] === 'SUCCESS';
            }

            return false;
        } catch (Exception $e) {
            Log::error('PayPal Webhook Verification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get approval URL for checkout
     *
     * @param array $order
     * @return string|null
     */
    public function getApprovalUrl($order)
    {
        $links = $order['links'] ?? [];
        foreach ($links as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }
        return null;
    }

    /**
     * Check if order is approved
     *
     * @param array $order
     * @return bool
     */
    public function isOrderApproved($order)
    {
        return isset($order['status']) && $order['status'] === 'APPROVED';
    }

    /**
     * Check if order is completed
     *
     * @param array $order
     * @return bool
     */
    public function isOrderCompleted($order)
    {
        return isset($order['status']) && $order['status'] === 'COMPLETED';
    }
}
