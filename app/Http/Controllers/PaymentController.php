<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\UserSubscription;
use App\Services\PaymobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymobService;

    public function __construct(PaymobService $paymobService)
    {
        $this->paymobService = $paymobService;
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
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'merchant_order_id' => 'SUB-' . $subscription->id . '-' . time(),
                'type' => 'subscription',
                'amount' => $subscription->price,
                'currency' => 'AED',
                'status' => 'pending',
            ]);

            // Prepare customer data
            $customerData = [
                'email' => $user->email,
                'first_name' => explode(' ', $user->name)[0],
                'last_name' => explode(' ', $user->name)[1] ?? explode(' ', $user->name)[0],
                'phone' => $user->phone ?? '0000000000',
            ];

            // Prepare items
            $items = [[
                'name' => $subscription->localized_name,
                'amount_cents' => (int)($subscription->price * 100),
                'description' => $subscription->localized_description ?? 'Subscription Plan',
                'quantity' => 1
            ]];

            // Initiate payment with Paymob
            $paymentData = $this->paymobService->initiatePayment(
                $subscription->price,
                $paymentTransaction->merchant_order_id,
                $customerData,
                $items
            );

            // Update transaction with Paymob order ID
            $paymentTransaction->update([
                'paymob_order_id' => $paymentData['paymob_order_id']
            ]);

            // Redirect to Paymob payment page
            return redirect($paymentData['payment_url']);

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
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'merchant_order_id' => 'ORD-' . $order->id . '-' . time(),
                'type' => 'order',
                'amount' => $order->total_amount,
                'currency' => 'AED',
                'status' => 'pending',
            ]);

            // Prepare customer data
            $customerData = [
                'email' => $user->email,
                'first_name' => explode(' ', $user->name)[0],
                'last_name' => explode(' ', $user->name)[1] ?? explode(' ', $user->name)[0],
                'phone' => $user->phone ?? '0000000000',
                'street' => $order->shipping_address ?? 'NA',
                'city' => $order->shipping_city ?? 'NA',
            ];

            // Prepare items from order items
            $items = $order->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'amount_cents' => (int)($item->price * 100),
                    'description' => $item->product_name,
                    'quantity' => $item->quantity
                ];
            })->toArray();

            // Initiate payment with Paymob
            $paymentData = $this->paymobService->initiatePayment(
                $order->total_amount,
                $paymentTransaction->merchant_order_id,
                $customerData,
                $items
            );

            // Update transaction with Paymob order ID
            $paymentTransaction->update([
                'paymob_order_id' => $paymentData['paymob_order_id']
            ]);

            // Redirect to Paymob payment page
            return redirect($paymentData['payment_url']);

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
     * Handle Paymob callback
     */
    public function callback(Request $request)
    {
        try {
            $data = $request->all();

            Log::info('Paymob Callback Received', ['data' => $data]);

            // Process callback
            $result = $this->paymobService->processCallback($data);

            if (!$result['success']) {
                Log::warning('Paymob Callback Failed', ['result' => $result]);
                return response()->json(['message' => 'Invalid callback'], 400);
            }

            // Find transaction
            $merchantOrderId = $result['order_id'];
            $transaction = PaymentTransaction::where('merchant_order_id', $merchantOrderId)->first();

            if (!$transaction) {
                Log::error('Transaction not found', ['merchant_order_id' => $merchantOrderId]);
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            // Update transaction
            $transaction->update([
                'transaction_id' => $result['transaction_id'],
                'status' => 'success',
                'payment_method' => $data['source_data']['type'] ?? 'card',
                'callback_data' => $data,
                'paid_at' => now(),
            ]);

            // Process based on type
            if ($transaction->type === 'subscription') {
                $this->processSubscriptionPayment($transaction);
            } elseif ($transaction->type === 'order') {
                $this->processOrderPayment($transaction);
            }

            return response()->json(['message' => 'Payment processed successfully']);

        } catch (\Exception $e) {
            Log::error('Payment Callback Error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            return response()->json(['message' => 'Callback processing failed'], 500);
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
