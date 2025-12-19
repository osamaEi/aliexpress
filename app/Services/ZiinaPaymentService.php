<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ZiinaPaymentService
{
    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var bool
     */
    protected $testMode;

    /**
     * Initialize the Ziina service with configuration
     */
    public function __construct()
    {
        $this->apiUrl = config('ziina.api_url', 'https://api-v2.ziina.com/api');
        $this->apiKey = config('ziina.api_key');
        $this->testMode = config('ziina.test_mode', true);
    }

    /**
     * Create a payment intent for subscription
     *
     * @param float $amount Amount in AED
     * @param string $description
     * @param array $metadata
     * @return array Payment intent data
     * @throws \Exception If API request fails
     */
    public function createSubscriptionPayment(float $amount, string $description, array $metadata = [])
    {
        // Convert amount from AED to fils (1 AED = 100 fils)
        $amountInFils = (int)($amount * 100);

        // Ensure minimum amount (2 AED = 200 fils)
        if ($amountInFils < 200) {
            throw new Exception('The minimum payment amount is 2 AED (200 fils)');
        }

        // Prepare success and cancel URLs
        $successUrl = route('subscriptions.payment.success', [
            'payment_intent_id' => '{PAYMENT_INTENT_ID}'
        ]);

        $cancelUrl = route('subscriptions.payment.cancel');

        // Convert all metadata values to strings
        $stringMetadata = [];
        foreach ($metadata as $key => $value) {
            $stringMetadata[$key] = (string)$value;
        }

        // Build request payload
        $payload = [
            'amount' => $amountInFils,
            'currency_code' => 'AED',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'description' => $description,
            'test' => $this->testMode,
            'metadata' => $stringMetadata,
        ];

        Log::debug('Ziina subscription payment intent payload', ['payload' => $payload]);

        try {
            // Make API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/payment_intent', $payload);

            Log::debug('Ziina subscription payment intent response', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                $errorData = $response->json();
                Log::error('Ziina subscription payment intent creation failed', [
                    'error' => $errorData,
                    'status' => $response->status(),
                ]);

                throw new Exception('Failed to create payment: ' .
                    ($errorData['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            Log::error('Exception creating Ziina subscription payment intent', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get payment intent details by ID
     *
     * @param string $paymentIntentId
     * @return array Payment intent data
     * @throws \Exception If API request fails
     */
    public function getPaymentIntent(string $paymentIntentId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->apiUrl . '/payment_intent/' . $paymentIntentId);

            Log::debug('Ziina get payment intent response', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                $errorData = $response->json();
                Log::error('Failed to retrieve Ziina payment intent', [
                    'payment_intent_id' => $paymentIntentId,
                    'status' => $response->status(),
                    'error' => $errorData
                ]);

                throw new Exception('Failed to retrieve payment details: ' .
                    ($errorData['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            Log::error('Exception retrieving Ziina payment intent', [
                'payment_intent_id' => $paymentIntentId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a payment intent for wallet deposit
     *
     * @param float $amount Amount in AED
     * @param int $userId User ID
     * @return array Payment intent data
     * @throws \Exception If API request fails
     */
    public function createWalletDepositPayment(float $amount, int $userId)
    {
        // Convert amount from AED to fils (1 AED = 100 fils)
        $amountInFils = (int)($amount * 100);

        // Ensure minimum amount (2 AED = 200 fils)
        if ($amountInFils < 200) {
            throw new Exception('The minimum payment amount is 2 AED (200 fils)');
        }

        // Prepare success and cancel URLs
        $successUrl = route('wallet.deposit.success', [
            'payment_intent_id' => '{PAYMENT_INTENT_ID}'
        ]);

        $cancelUrl = route('wallet.deposit.cancel');

        // Build request payload
        $payload = [
            'amount' => $amountInFils,
            'currency_code' => 'AED',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'description' => 'Wallet Deposit - Balance Top-up',
            'test' => $this->testMode,
            'metadata' => [
                'user_id' => (string)$userId,
                'payment_type' => 'wallet_deposit',
            ],
        ];

        Log::debug('Ziina wallet deposit payment intent payload', ['payload' => $payload]);

        try {
            // Make API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/payment_intent', $payload);

            Log::debug('Ziina wallet deposit payment intent response', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                $errorData = $response->json();
                Log::error('Ziina wallet deposit payment intent creation failed', [
                    'error' => $errorData,
                    'status' => $response->status(),
                ]);

                throw new Exception('Failed to create payment: ' .
                    ($errorData['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            Log::error('Exception creating Ziina wallet deposit payment intent', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate payment gateway fees
     * Fee structure: 2 AED + 7.9% of the amount
     *
     * @param float $amount Amount in AED
     * @return array ['gross_amount' => float, 'fee' => float, 'net_amount' => float, 'fixed_fee' => float, 'percentage_fee' => float]
     */
    public function calculateFees(float $amount): array
    {
        // Payment gateway fee structure
        // Fixed fee: 2 AED + Percentage fee: 7.9% of the amount
        $fixedFee = 2.00; // 2 AED
        $percentageRate = 0.079; // 7.9%

        $percentageFee = $amount * $percentageRate;
        $totalFee = $fixedFee + $percentageFee;
        $grossAmount = $amount + $totalFee;

        return [
            'net_amount' => round($amount, 2),
            'fixed_fee' => round($fixedFee, 2),
            'percentage_fee' => round($percentageFee, 2),
            'fee' => round($totalFee, 2),
            'gross_amount' => round($grossAmount, 2),
        ];
    }
}
