<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscription plans
     */
    public function index()
    {
        $subscriptions = Subscription::active()->get();
        $currentSubscription = Auth::user()->activeSubscription;

        return view('subscriptions.index', compact('subscriptions', 'currentSubscription'));
    }

    /**
     * Show the specified subscription plan
     */
    public function show(Subscription $subscription)
    {
        $currentSubscription = Auth::user()->activeSubscription;

        return view('subscriptions.show', compact('subscription', 'currentSubscription'));
    }

    /**
     * Subscribe to a plan - Show payment page
     */
    public function subscribe(Request $request, Subscription $subscription)
    {
        $user = Auth::user();
        $currentSubscription = $user->activeSubscription;
        $isUpgrade = false;
        $remainingDays = 0;
        $totalDays = $subscription->duration_days;

        // Check if user already has an active subscription to the same plan
        if ($currentSubscription && $currentSubscription->subscription_id == $subscription->id) {
            return redirect()->route('subscriptions.index')
                ->with('error', __('messages.already_have_active_subscription'));
        }

        // If upgrading, calculate remaining days
        if ($currentSubscription) {
            $isUpgrade = true;
            $remainingDays = $currentSubscription->days_remaining;
            $totalDays = $subscription->duration_days + $remainingDays;
        }

        // Calculate payment gateway fees
        $ziinaService = app(\App\Services\ZiinaPaymentService::class);
        $feeBreakdown = $ziinaService->calculateFees($subscription->price);

        // Show payment page with options
        return view('subscriptions.payment', compact(
            'subscription',
            'isUpgrade',
            'remainingDays',
            'totalDays',
            'currentSubscription',
            'feeBreakdown'
        ));
    }

    /**
     * Pay with wallet
     */
    public function payWithWallet(Request $request, Subscription $subscription)
    {
        $user = Auth::user();
        $currentSubscription = $user->activeSubscription;

        // Check if user already has an active subscription to the same plan
        if ($currentSubscription && $currentSubscription->subscription_id == $subscription->id) {
            return redirect()->route('subscriptions.index')
                ->with('error', __('messages.already_have_active_subscription'));
        }

        // Check wallet balance
        $wallet = $user->getOrCreateWallet();

        if ($wallet->balance < $subscription->price) {
            return redirect()->back()
                ->with('error', __('messages.insufficient_wallet_balance'));
        }

        try {
            $userSubscription = null;
            \DB::transaction(function () use ($user, $subscription, $wallet, $currentSubscription, &$userSubscription) {
                // Deduct from wallet
                $wallet->debit(
                    $subscription->price,
                    'subscription_payment',
                    'Subscription payment: ' . $subscription->localized_name
                );

                // Calculate end date (add remaining days if upgrading)
                $startDate = now()->toDateString();
                $remainingDays = $currentSubscription ? $currentSubscription->days_remaining : 0;
                $totalDays = $subscription->duration_days + $remainingDays;
                $endDate = now()->addDays($totalDays)->toDateString();

                // Cancel current subscription if exists
                if ($currentSubscription) {
                    $currentSubscription->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Upgraded to ' . $subscription->localized_name,
                    ]);
                }

                // Create user subscription
                $userSubscription = UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'amount_paid' => $subscription->price,
                    'payment_method' => 'wallet',
                ]);
            });

            // Send email notification to admin
            if ($userSubscription) {
                $userSubscription->load(['user', 'subscription']);
                $adminEmail = env('ADMIN_EMAIL', env('MAIL_FROM_ADDRESS'));
                if ($adminEmail) {
                    \Mail::to($adminEmail)->send(new \App\Mail\NewSubscriptionNotification($userSubscription));
                }
            }

            return redirect()->route('subscriptions.index')
                ->with('success', __('messages.subscription_successful'));

        } catch (\Exception $e) {
            \Log::error('Wallet subscription payment failed', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', __('messages.payment_failed'));
        }
    }

    /**
     * Pay with Ziina - Initiate payment
     */
    public function payWithZiina(Request $request, Subscription $subscription)
    {
        $user = Auth::user();
        $currentSubscription = $user->activeSubscription;

        // Check if user already has an active subscription to the same plan
        if ($currentSubscription && $currentSubscription->subscription_id == $subscription->id) {
            return redirect()->route('subscriptions.index')
                ->with('error', __('messages.already_have_active_subscription'));
        }

        try {
            $ziinaService = app(\App\Services\ZiinaPaymentService::class);

            // Calculate remaining days if upgrading
            $remainingDays = $currentSubscription ? $currentSubscription->days_remaining : 0;
            $totalDays = $subscription->duration_days + $remainingDays;

            // Calculate payment gateway fees
            $feeBreakdown = $ziinaService->calculateFees($subscription->price);

            // Prepare metadata
            $metadata = [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'payment_type' => 'subscription',
                'subscription_price' => $subscription->price,
                'gateway_fee' => $feeBreakdown['fee'],
                'total_amount' => $feeBreakdown['gross_amount'],
                'remaining_days' => $remainingDays,
                'total_days' => $totalDays,
                'current_subscription_id' => $currentSubscription ? $currentSubscription->id : '',
            ];

            // Create payment intent with total amount (subscription price + gateway fees)
            $description = __('messages.subscription_payment') . ': ' . $subscription->localized_name;
            $paymentIntent = $ziinaService->createSubscriptionPayment(
                $feeBreakdown['gross_amount'],
                $description,
                $metadata
            );

            // Store payment intent ID in session
            session([
                'ziina_subscription_payment' => [
                    'payment_intent_id' => $paymentIntent['id'],
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id,
                    'subscription_price' => $subscription->price,
                    'gateway_fee' => $feeBreakdown['fee'],
                    'total_amount' => $feeBreakdown['gross_amount'],
                    'remaining_days' => $remainingDays,
                    'total_days' => $totalDays,
                    'current_subscription_id' => $currentSubscription ? $currentSubscription->id : null,
                ]
            ]);

            // Redirect to Ziina payment page
            return redirect($paymentIntent['redirect_url']);

        } catch (\Exception $e) {
            \Log::error('Ziina subscription payment initiation failed', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', __('messages.payment_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Handle successful Ziina payment
     */
    public function ziinaPaymentSuccess(Request $request)
    {
        $paymentIntentId = $request->query('payment_intent_id');

        if (!$paymentIntentId) {
            return redirect()->route('subscriptions.index')
                ->with('error', __('messages.invalid_payment_data'));
        }

        try {
            // Get payment session data
            $sessionData = session('ziina_subscription_payment');

            if (!$sessionData || $sessionData['payment_intent_id'] !== $paymentIntentId) {
                throw new \Exception('Invalid payment session');
            }

            // Verify payment with Ziina
            $ziinaService = app(\App\Services\ZiinaPaymentService::class);
            $paymentIntent = $ziinaService->getPaymentIntent($paymentIntentId);

            // Check payment status
            if (!in_array($paymentIntent['status'], ['completed', 'succeeded'])) {
                throw new \Exception('Payment not completed');
            }

            // Get subscription
            $subscription = Subscription::findOrFail($sessionData['subscription_id']);
            $user = User::findOrFail($sessionData['user_id']);
            $currentSubscription = $sessionData['current_subscription_id']
                ? UserSubscription::find($sessionData['current_subscription_id'])
                : null;

            // Calculate end date
            $startDate = now()->toDateString();
            $endDate = now()->addDays($sessionData['total_days'])->toDateString();

            // Create user subscription
            $userSubscription = null;
            \DB::transaction(function () use ($user, $subscription, $paymentIntentId, $currentSubscription, $startDate, $endDate, $sessionData, &$userSubscription) {
                // Cancel current subscription if exists
                if ($currentSubscription) {
                    $currentSubscription->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Upgraded to ' . $subscription->localized_name,
                    ]);
                }

                $userSubscription = UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'amount_paid' => $sessionData['total_amount'], // Total amount including gateway fees
                    'payment_method' => 'ziina',
                    'transaction_id' => $paymentIntentId,
                ]);
            });

            // Send email notification to admin
            if ($userSubscription) {
                $userSubscription->load(['user', 'subscription']);
                $adminEmail = env('ADMIN_EMAIL', env('MAIL_FROM_ADDRESS'));
                if ($adminEmail) {
                    \Mail::to($adminEmail)->send(new \App\Mail\NewSubscriptionNotification($userSubscription));
                }
            }

            // Clear session data
            session()->forget('ziina_subscription_payment');

            \Log::info('Ziina subscription payment successful', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'subscription_price' => $sessionData['subscription_price'],
                'gateway_fee' => $sessionData['gateway_fee'],
                'total_amount_paid' => $sessionData['total_amount'],
                'payment_intent_id' => $paymentIntentId
            ]);

            return redirect()->route('subscriptions.index')
                ->with('success', __('messages.subscription_successful'));

        } catch (\Exception $e) {
            \Log::error('Ziina subscription payment verification failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('subscriptions.index')
                ->with('error', __('messages.payment_verification_failed'));
        }
    }

    /**
     * Handle cancelled Ziina payment
     */
    public function ziinaPaymentCancel(Request $request)
    {
        // Clear session data
        session()->forget('ziina_subscription_payment');

        return redirect()->route('subscriptions.index')
            ->with('info', __('messages.payment_cancelled'));
    }

    /**
     * Pay with Paymob - Show iframe page
     */
    public function payWithPaymob(Request $request, Subscription $subscription)
    {
        $user = Auth::user();
        $currentSubscription = $user->activeSubscription;

        // Check if user already has an active subscription to the same plan
        if ($currentSubscription && $currentSubscription->subscription_id == $subscription->id) {
            return redirect()->route('subscriptions.index')
                ->with('error', __('messages.already_have_active_subscription'));
        }

        // Calculate remaining days if upgrading
        $remainingDays = $currentSubscription ? $currentSubscription->days_remaining : 0;
        $totalDays = $subscription->duration_days + $remainingDays;

        // Create payment intent with Paymob
        $merchantOrderId = 'SUB-' . $subscription->id . '-' . $user->id . '-' . time();

        // Store payment intent in session for callback
        session([
            'paymob_subscription' => [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'merchant_order_id' => $merchantOrderId,
                'amount' => $subscription->price,
                'remaining_days' => $remainingDays,
                'total_days' => $totalDays,
                'current_subscription_id' => $currentSubscription ? $currentSubscription->id : null,
            ]
        ]);

        // Show payment iframe page
        return view('subscriptions.paymob-iframe', compact('subscription'));
    }

    /**
     * Initialize Paymob payment (AJAX)
     */
    public function initializePaymobPayment(Request $request, Subscription $subscription)
    {
        try {
            $paymobController = app(\App\Http\Controllers\PaymobController::class);
            return $paymobController->initiateSubscriptionPayment($request, $subscription);

        } catch (\Exception $e) {
            \Log::error('Paymob subscription payment initiation failed', [
                'user_id' => Auth::id(),
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.payment_failed')
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return redirect()->route('subscriptions.index')
                ->with('error', __('messages.no_active_subscription'));
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('reason'),
        ]);

        return redirect()->route('subscriptions.index')
            ->with('success', __('messages.subscription_cancelled'));
    }

    /**
     * View user's subscription history
     */
    public function history()
    {
        $subscriptions = Auth::user()->subscriptions()
            ->with('subscription')
            ->latest()
            ->paginate(10);

        return view('subscriptions.history', compact('subscriptions'));
    }

    /**
     * Display invoice for a subscription
     */
    public function invoice(UserSubscription $userSubscription)
    {
        // Ensure user can only view their own invoices
        if ($userSubscription->user_id !== Auth::id()) {
            abort(403, __('messages.unauthorized_access'));
        }

        // Load subscription relationship
        $userSubscription->load('subscription');

        return view('subscriptions.invoice', compact('userSubscription'));
    }
}
