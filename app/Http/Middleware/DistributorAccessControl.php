<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DistributorAccessControl
{
    /**
     * Routes always accessible for distributors (even after trial/subscription expires).
     * Subscriptions, profile, tickets, payments, auth.
     */
    protected array $alwaysAccessibleRoutes = [
        // Subscription routes
        'subscriptions.index',
        'subscriptions.show',
        'subscriptions.subscribe',
        'subscriptions.subscribe.show',
        'subscriptions.pay-with-wallet',
        'subscriptions.pay-with-ziina',
        'subscriptions.pay-with-paymob',
        'subscriptions.initialize-paymob',
        'subscriptions.payment.success',
        'subscriptions.payment.cancel',
        'subscriptions.history',
        'subscriptions.cancel',
        'subscriptions.invoice',
        // Profile/Settings
        'profile.edit',
        'profile.update',
        'profile.logo.update',
        'profile.destroy',
        // Support tickets
        'distributor.tickets.index',
        'distributor.tickets.create',
        'distributor.tickets.store',
        'distributor.tickets.show',
        'distributor.tickets.reply',
        // Payment callbacks
        'payment.subscription',
        'payment.success',
        'payment.error',
        'payment.callback',
        'paymob.callback',
        'paymob.initiate-subscription',
        // Auth
        'logout',
        'password.request',
        'password.email',
        'password.reset',
        'password.update',
        'password.confirm',
        'verification.notice',
        'verification.verify',
        'verification.send',
        // Locale / currency
        'lang.switch',
        'currency.switch',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only apply to distributors
        if (!$user || $user->user_type !== 'distributor') {
            return $next($request);
        }

        $currentRoute = $request->route()?->getName();

        // Always allow subscription & profile routes
        if ($currentRoute && in_array($currentRoute, $this->alwaysAccessibleRoutes)) {
            return $next($request);
        }

        // Only enforce if the admin has enabled subscription requirement for distributors
        if (!Setting::get('require_subscription_distributor', '0')) {
            return $next($request);
        }

        // Check if distributor has an active subscription
        $hasActive = $user->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->exists();

        if (!$hasActive) {
            $isAr = app()->getLocale() == 'ar';
            return redirect()->route('subscriptions.index')
                ->with('error', $isAr
                    ? 'انتهت فترة التجربة أو الاشتراك. يرجى الاشتراك في إحدى الباقات للمتابعة.'
                    : 'Your trial or subscription has expired. Please subscribe to a plan to continue.');
        }

        return $next($request);
    }
}
