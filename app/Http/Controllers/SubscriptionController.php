<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
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

        // Check if user already has an active subscription
        if ($user->hasActiveSubscription()) {
            return redirect()->route('subscriptions.index')
                ->with('error', __('messages.already_have_active_subscription'));
        }

        // Show payment page with options
        return view('subscriptions.payment', compact('subscription'));
    }

    /**
     * Pay with wallet
     */
    public function payWithWallet(Request $request, Subscription $subscription)
    {
        $user = Auth::user();

        // Check if user already has an active subscription
        if ($user->hasActiveSubscription()) {
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
            \DB::transaction(function () use ($user, $subscription, $wallet) {
                // Deduct from wallet
                $wallet->debit(
                    $subscription->price,
                    'subscription_payment',
                    'Subscription payment: ' . $subscription->localized_name
                );

                // Create user subscription
                UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addDays($subscription->duration_days)->toDateString(),
                    'status' => 'active',
                    'amount_paid' => $subscription->price,
                    'payment_method' => 'wallet',
                ]);
            });

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
}
