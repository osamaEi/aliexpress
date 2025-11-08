<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\UserSubscription;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Initiate payment for subscription
     */
    public function initiateSubscriptionPayment(Request $request, Subscription $subscription)
    {
        try {
            $user = Auth::user();

            // Check if user already has active subscription
            if ($user->activeSubscription) {
                return redirect()->route('subscriptions.index')
                    ->with('error', __('messages.already_have_active_subscription'));
            }

            // Create payment transaction record
            $merchantOrderId = 'SUB-' . $subscription->id . '-' . time();
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'merchant_order_id' => $merchantOrderId,
                'type' => 'subscription',
                'amount' => $subscription->price,
                'currency' => config('paypal.currency'),
                'status' => 'pending',
            ]);

            // Create PayPal order
            $paypalOrder = $this->paypalService->createOrder(
                $subscription->price,
                config('paypal.currency'),
                $subscription->localized_name . ' - Subscription Plan',
                $merchantOrderId
            );

            // Update transaction with PayPal order ID
            $paymentTransaction->update([
                'paypal_order_id' => $paypalOrder['id']
            ]);

            // Get approval URL and redirect user to PayPal
            $approvalUrl = $this->paypalService->getApprovalUrl($paypalOrder);

            if (!$approvalUrl) {
                throw new \Exception('Could not get PayPal approval URL');
            }

            return redirect($approvalUrl);

        } catch (\Exception $e) {
            Log::error('Subscription Payment Initiation Error', [
                'subscription_id' => $subscription->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('subscriptions.index')
                ->with('error', __('messages.payment_initiation_failed'));
        }
    }

    /**
     * Initiate payment for order
     */
    public function initiateOrderPayment(Request $request, Order $order)
    {
        try {
            $user = Auth::user();

            // Check if order belongs to user
            if ($order->user_id !== $user->id) {
                abort(403);
            }

            // Check if order is already paid
            if ($order->payment_status === 'paid') {
                return redirect()->route('orders.show', $order)
                    ->with('error', __('messages.order_already_paid'));
            }

            // Create payment transaction record
            $merchantOrderId = 'ORD-' . $order->id . '-' . time();
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'merchant_order_id' => $merchantOrderId,
                'type' => 'order',
                'amount' => $order->total_amount,
                'currency' => config('paypal.currency'),
                'status' => 'pending',
            ]);

            // Create PayPal order
            $paypalOrder = $this->paypalService->createOrder(
                $order->total_amount,
                config('paypal.currency'),
                'Order #' . $order->id,
                $merchantOrderId
            );

            // Update transaction with PayPal order ID
            $paymentTransaction->update([
                'paypal_order_id' => $paypalOrder['id']
            ]);

            // Get approval URL and redirect user to PayPal
            $approvalUrl = $this->paypalService->getApprovalUrl($paypalOrder);

            if (!$approvalUrl) {
                throw new \Exception('Could not get PayPal approval URL');
            }

            return redirect($approvalUrl);

        } catch (\Exception $e) {
            Log::error('Order Payment Initiation Error', [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', __('messages.payment_initiation_failed'));
        }
    }

    /**
     * Handle PayPal callback (Return from PayPal)
     */
    public function callback(Request $request)
    {
        try {
            $token = $request->query('token');
            $payerId = $request->query('PayerID');

            Log::info('PayPal Callback Received', ['token' => $token, 'payer_id' => $payerId]);

            if (!$token) {
                Log::warning('PayPal callback missing token');
                return redirect()->route('payment.error')
                    ->with('error', __('messages.payment_failed'));
            }

            // Find transaction by PayPal order ID
            $transaction = PaymentTransaction::where('paypal_order_id', $token)->first();

            if (!$transaction) {
                Log::error('Transaction not found', ['paypal_order_id' => $token]);
                return redirect()->route('payment.error')
                    ->with('error', __('messages.transaction_not_found'));
            }

            // Capture the payment
            $captureResult = $this->paypalService->captureOrder($token);

            // Check if payment was successful
            $captureStatus = $captureResult['status'] ?? '';

            if ($captureStatus === 'COMPLETED') {
                // Extract transaction ID from capture result
                $transactionId = $captureResult['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

                // Update transaction
                $transaction->update([
                    'transaction_id' => $transactionId,
                    'status' => 'success',
                    'payment_method' => 'paypal',
                    'callback_data' => $captureResult,
                    'paid_at' => now(),
                ]);

                // Process based on type
                if ($transaction->type === 'subscription') {
                    $this->processSubscriptionPayment($transaction);
                } elseif ($transaction->type === 'order') {
                    $this->processOrderPayment($transaction);
                }

                return redirect()->route('payment.success', [
                    'id' => $transactionId,
                    'merchant_order_id' => $transaction->merchant_order_id
                ]);
            } else {
                Log::warning('PayPal payment not completed', ['status' => $captureStatus]);
                $transaction->markAsFailed();

                return redirect()->route('payment.error')
                    ->with('error', __('messages.payment_failed'));
            }

        } catch (\Exception $e) {
            Log::error('Payment Callback Error', [
                'error' => $e->getMessage(),
                'token' => $request->query('token')
            ]);

            return redirect()->route('payment.error')
                ->with('error', __('messages.payment_processing_error'));
        }
    }

    /**
     * Process subscription payment
     */
    protected function processSubscriptionPayment(PaymentTransaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            // Extract subscription ID from merchant_order_id
            preg_match('/SUB-(\d+)-/', $transaction->merchant_order_id, $matches);
            $subscriptionId = $matches[1] ?? null;

            if (!$subscriptionId) {
                throw new \Exception('Invalid subscription ID in merchant order');
            }

            $subscription = Subscription::find($subscriptionId);

            if (!$subscription) {
                throw new \Exception('Subscription not found');
            }

            // Create user subscription
            UserSubscription::create([
                'user_id' => $transaction->user_id,
                'subscription_id' => $subscription->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays($subscription->duration_days)->toDateString(),
                'status' => 'active',
                'amount_paid' => $transaction->amount,
                'payment_method' => $transaction->payment_method ?? 'card',
            ]);

            Log::info('Subscription Activated', [
                'user_id' => $transaction->user_id,
                'subscription_id' => $subscription->id
            ]);
        });
    }

    /**
     * Process order payment
     */
    protected function processOrderPayment(PaymentTransaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            // Extract order ID from merchant_order_id
            preg_match('/ORD-(\d+)-/', $transaction->merchant_order_id, $matches);
            $orderId = $matches[1] ?? null;

            if (!$orderId) {
                throw new \Exception('Invalid order ID in merchant order');
            }

            $order = Order::find($orderId);

            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Update order payment status
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => $transaction->payment_method ?? 'card',
                'status' => 'processing',
            ]);

            Log::info('Order Payment Processed', [
                'order_id' => $order->id,
                'amount' => $transaction->amount
            ]);
        });
    }

    /**
     * Payment success page
     */
    public function success(Request $request)
    {
        $transactionId = $request->query('id');
        $merchantOrderId = $request->query('merchant_order_id');

        $transaction = PaymentTransaction::where('merchant_order_id', $merchantOrderId)->first();

        if (!$transaction) {
            return redirect()->route('dashboard')
                ->with('error', __('messages.transaction_not_found'));
        }

        if ($transaction->type === 'subscription') {
            return redirect()->route('subscriptions.index')
                ->with('success', __('messages.subscription_successful'));
        } else {
            return redirect()->route('orders.index')
                ->with('success', __('messages.payment_successful'));
        }
    }

    /**
     * Payment error page
     */
    public function error(Request $request)
    {
        $merchantOrderId = $request->query('merchant_order_id');

        $transaction = PaymentTransaction::where('merchant_order_id', $merchantOrderId)->first();

        if ($transaction) {
            $transaction->markAsFailed();
        }

        return redirect()->route('dashboard')
            ->with('error', __('messages.payment_failed'));
    }
}
