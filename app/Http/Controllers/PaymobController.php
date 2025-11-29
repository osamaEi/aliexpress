<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use App\Models\UserSubscription;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

class PaymobController extends Controller
{
    /**
     * Initiate payment with Paymob (generic method)
     */
    public function initiatePayment(Request $request, string $merchantOrderId, float $amount, string $type = 'subscription')
    {
        try {
            $user = Auth::user();

            // Convert price to AED cents
            $amountCents = (int) ($amount * 100);

            // 1) Auth: get token
            $authResponse = Http::timeout(30)
                ->connectTimeout(10)
                ->post(config('paymob.base_url') . '/api/auth/tokens', [
                    'api_key' => config('paymob.api_key'),
                ]);

            if (!$authResponse->successful()) {
                throw new \Exception('Authentication with Paymob failed');
            }

            $auth = $authResponse->json();
            if (!isset($auth['token'])) {
                throw new \Exception('Token not found in authentication response');
            }

            // 2) Register order
            $orderResponse = Http::withToken($auth['token'])
                ->timeout(30)
                ->connectTimeout(10)
                ->post(config('paymob.base_url') . '/api/ecommerce/orders', [
                    'amount_cents' => $amountCents,
                    'currency' => config('paymob.currency'),
                    'merchant_order_id' => $merchantOrderId,
                    'delivery_needed' => false,
                    'items' => [],
                ]);

            if (!$orderResponse->successful()) {
                throw new \Exception('Failed to create order with Paymob');
            }

            $order = $orderResponse->json();
            if (!isset($order['id'])) {
                throw new \Exception('Order ID not found in response');
            }

            // 3) Payment key
            $billing = [
                "apartment" => "NA",
                "email" => $user->email,
                "floor" => "NA",
                "first_name" => explode(' ', $user->name)[0] ?? 'User',
                "street" => "Address",
                "building" => "NA",
                "phone_number" => $user->phone ?? "0501234567",
                "shipping_method" => "PKG",
                "postal_code" => "00000",
                "city" => "Dubai",
                "country" => "AE",
                "last_name" => explode(' ', $user->name)[1] ?? 'Name',
                "state" => "Dubai"
            ];

            $paymentKeyResponse = Http::withToken($auth['token'])
                ->timeout(30)
                ->connectTimeout(10)
                ->post(config('paymob.base_url') . '/api/acceptance/payment_keys', [
                    'amount_cents' => $amountCents,
                    'currency' => config('paymob.currency'),
                    'order_id' => $order['id'],
                    'billing_data' => $billing,
                    'expiration' => 3600,
                    'integration_id' => config('paymob.card_integration_id'),
                ]);

            if (!$paymentKeyResponse->successful()) {
                throw new \Exception('Failed to generate payment key');
            }

            $paymentKey = $paymentKeyResponse->json();
            if (!isset($paymentKey['token'])) {
                throw new \Exception('Payment token not found in response');
            }

            // Redirect to Paymob iframe
            $iframeUrl = 'https://accept.paymob.com/api/acceptance/iframes/' . config('paymob.iframe_id') . '?payment_token=' . $paymentKey['token'];
            return redirect($iframeUrl);

        } catch (\Exception $e) {
            Log::error('Paymob payment initialization failed', [
                'error' => $e->getMessage(),
                'merchant_order_id' => $merchantOrderId,
                'type' => $type,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', __('messages.payment_failed'));
        }
    }

    /**
     * Initiate subscription payment with Paymob
     */
    public function initiateSubscriptionPayment(Request $request, Subscription $subscription)
    {
        try {
            $user = Auth::user();

            // Check if user already has an active subscription
            if ($user->hasActiveSubscription()) {
                return response()->json([
                    'error' => 'Failed to initialize payment',
                    'message' => __('messages.already_have_active_subscription')
                ], 400);
            }

            // Convert price to AED cents (price is already in AED)
            $amountCents = (int) ($subscription->price * 100);

            $merchantOrderId = 'SUB-' . $subscription->id . '-' . $user->id . '-' . now()->timestamp;

            // 1) Auth: get token
            $authResponse = Http::timeout(30)
                ->connectTimeout(10)
                ->post(config('paymob.base_url') . '/api/auth/tokens', [
                    'api_key' => config('paymob.api_key'),
                ]);

            if (!$authResponse->successful()) {
                return response()->json([
                    'error' => 'Failed to initialize Paymob payment',
                    'message' => 'Authentication with Paymob failed',
                    'details' => $authResponse->json()
                ], 502);
            }

            $auth = $authResponse->json();
            if (!isset($auth['token'])) {
                return response()->json([
                    'error' => 'Failed to initialize Paymob payment',
                    'message' => 'Token not found in authentication response',
                    'details' => $auth
                ], 502);
            }

            // 2) Register order
            $orderResponse = Http::withToken($auth['token'])
                ->timeout(30)
                ->connectTimeout(10)
                ->post(config('paymob.base_url') . '/api/ecommerce/orders', [
                    'amount_cents' => $amountCents,
                    'currency' => config('paymob.currency'),
                    'merchant_order_id' => $merchantOrderId,
                    'delivery_needed' => false,
                    'items' => [],
                ]);

            if (!$orderResponse->successful()) {
                return response()->json([
                    'error' => 'Failed to initialize Paymob payment',
                    'message' => 'Failed to create order with Paymob',
                    'details' => $orderResponse->json()
                ], 502);
            }

            $order = $orderResponse->json();
            if (!isset($order['id'])) {
                return response()->json([
                    'error' => 'Failed to initialize Paymob payment',
                    'message' => 'Order ID not found in response',
                    'details' => $order
                ], 502);
            }

            // 3) Payment key
            $billing = [
                "apartment" => "NA",
                "email" => $user->email,
                "floor" => "NA",
                "first_name" => explode(' ', $user->name)[0] ?? 'User',
                "street" => "Address",
                "building" => "NA",
                "phone_number" => $user->phone ?? "0501234567",
                "shipping_method" => "PKG",
                "postal_code" => "00000",
                "city" => "Dubai",
                "country" => "AE",
                "last_name" => explode(' ', $user->name)[1] ?? 'Name',
                "state" => "Dubai"
            ];

            $paymentKeyResponse = Http::withToken($auth['token'])
                ->timeout(30)
                ->connectTimeout(10)
                ->post(config('paymob.base_url') . '/api/acceptance/payment_keys', [
                    'amount_cents' => $amountCents,
                    'currency' => config('paymob.currency'),
                    'order_id' => $order['id'],
                    'billing_data' => $billing,
                    'expiration' => 3600,
                    'integration_id' => config('paymob.card_integration_id'),
                ]);

            if (!$paymentKeyResponse->successful()) {
                return response()->json([
                    'error' => 'Failed to initialize Paymob payment',
                    'message' => 'Failed to generate payment key',
                    'details' => $paymentKeyResponse->json()
                ], 502);
            }

            $paymentKey = $paymentKeyResponse->json();
            if (!isset($paymentKey['token'])) {
                return response()->json([
                    'error' => 'Failed to initialize Paymob payment',
                    'message' => 'Payment token not found in response',
                    'details' => $paymentKey
                ], 502);
            }

            // Return the payment token and iframe ID
            return response()->json([
                'success' => true,
                'paymentToken' => $paymentKey['token'],
                'iframeId' => config('paymob.iframe_id'),
                'merchantOrderId' => $merchantOrderId,
            ]);

        } catch (ConnectionException $e) {
            return response()->json([
                'error' => 'Failed to initialize Paymob payment',
                'message' => 'Connection error: ' . $e->getMessage(),
                'hint' => 'Please check your network connection and Paymob API availability.'
            ], 503);
        } catch (\Exception $e) {
            Log::error('Paymob subscription payment initialization failed', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id ?? null,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'error' => 'Failed to initialize Paymob payment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Customer browser hits this after paying (success/fail)
     */
    public function callback(Request $request)
    {
        // Show processing page; final confirmation comes from webhook
        return view('paymob.callback', ['query' => $request->all()]);
    }

    /**
     * Server-to-server notification from Paymob
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->all();

            // 1) Verify HMAC signature (optional for now)
            $hmac = config('paymob.hmac');
            if ($hmac && !$this->validHmac($payload, $hmac)) {
                Log::warning('Paymob webhook: Invalid HMAC signature', ['payload' => $payload]);
            }

            // 2) Extract merchant_order_id
            $merchantOrderId = data_get($payload, 'obj.order.merchant_order_id')
                ?? data_get($payload, 'payload.obj.order.merchant_order_id')
                ?? $request->input('obj.order.merchant_order_id')
                ?? $request->input('merchant_order_id');

            Log::info('Paymob webhook: Received', [
                'merchant_order_id' => $merchantOrderId,
            ]);

            // Check if this is a subscription payment (format: SUB-{subscription_id}-{user_id}-{timestamp})
            if ($merchantOrderId && preg_match('/SUB-(\d+)-(\d+)-(\d+)/', $merchantOrderId, $matches)) {
                $subscriptionId = $matches[1];
                $userId = $matches[2];

                // Get success status
                $success = data_get($payload, 'obj.success') === true
                    || data_get($payload, 'obj.success') === 'true';

                // Extract txn_response_code
                $txnResponseCode = data_get($payload, 'obj.data.txn_response_code')
                    ?? data_get($payload, 'obj.txn_response_code');

                // Extract order ID
                $orderId = data_get($payload, 'obj.order.id')
                    ?? data_get($payload, 'obj.id');

                Log::info('Paymob webhook: Subscription payment', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => $userId,
                    'success' => $success,
                    'txn_response_code' => $txnResponseCode,
                    'order_id' => $orderId,
                ]);

                // 3) If payment is successful, create subscription
                if ($success && $txnResponseCode === 'APPROVED') {
                    $subscription = Subscription::find($subscriptionId);

                    if ($subscription) {
                        // Get subscription data from session (if exists)
                        $sessionData = session('paymob_subscription');
                        $remainingDays = 0;
                        $totalDays = $subscription->duration_days;
                        $currentSubscriptionId = null;

                        if ($sessionData && $sessionData['merchant_order_id'] === $merchantOrderId) {
                            $remainingDays = $sessionData['remaining_days'] ?? 0;
                            $totalDays = $sessionData['total_days'] ?? $subscription->duration_days;
                            $currentSubscriptionId = $sessionData['current_subscription_id'] ?? null;
                        }

                        // Create user subscription with upgrade logic
                        \DB::transaction(function () use ($userId, $subscription, $orderId, $merchantOrderId, $totalDays, $currentSubscriptionId) {
                            // Cancel current subscription if exists
                            if ($currentSubscriptionId) {
                                $current = UserSubscription::find($currentSubscriptionId);
                                if ($current) {
                                    $current->update([
                                        'status' => 'cancelled',
                                        'cancelled_at' => now(),
                                        'cancellation_reason' => 'Upgraded to ' . $subscription->localized_name,
                                    ]);
                                }
                            }

                            UserSubscription::create([
                                'user_id' => $userId,
                                'subscription_id' => $subscription->id,
                                'start_date' => now()->toDateString(),
                                'end_date' => now()->addDays($totalDays)->toDateString(),
                                'status' => 'active',
                                'amount_paid' => $subscription->price,
                                'payment_method' => 'paymob',
                                'transaction_id' => $orderId,
                            ]);

                            // Optional: Save payment transaction record
                            \App\Models\PaymentTransaction::create([
                                'user_id' => $userId,
                                'merchant_order_id' => $merchantOrderId,
                                'transaction_id' => $orderId,
                                'type' => 'subscription',
                                'amount' => $subscription->price,
                                'currency' => 'AED',
                                'status' => 'success',
                                'payment_method' => 'paymob',
                                'callback_data' => $request->all(),
                                'paid_at' => now(),
                            ]);
                        });

                        // Clear session data
                        session()->forget('paymob_subscription');

                        Log::info('Paymob webhook: Subscription activated', [
                            'subscription_id' => $subscriptionId,
                            'user_id' => $userId,
                            'merchant_order_id' => $merchantOrderId,
                        ]);
                    } else {
                        Log::error('Paymob webhook: Subscription not found', [
                            'subscription_id' => $subscriptionId,
                        ]);
                    }
                } else {
                    Log::info('Paymob webhook: Payment not approved', [
                        'subscription_id' => $subscriptionId,
                        'user_id' => $userId,
                        'success' => $success,
                        'txn_response_code' => $txnResponseCode,
                    ]);
                }
            }

            return response()->json(['ok' => true]);

        } catch (\Exception $e) {
            Log::error('Paymob webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validate HMAC signature
     */
    private function validHmac(array $payload, string $incoming): bool
    {
        // TODO: Implement HMAC validation according to Paymob docs
        return true;
    }
}
