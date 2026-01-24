<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerAccessControl
{
    /**
     * Routes that are always accessible for sellers (even after trial expires)
     * These are: Subscriptions, Settings/Profile, Support tickets
     */
    protected array $alwaysAccessibleRoutes = [
        // Subscription routes
        'subscriptions.index',
        'subscriptions.show',
        'subscriptions.create',
        'subscriptions.store',
        'subscriptions.edit',
        'subscriptions.update',
        'subscriptions.destroy',
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
        // Profile/Settings routes
        'profile.edit',
        'profile.update',
        'profile.logo.update',
        'profile.destroy',
        // Initial Profit Setup routes (must complete after registration)
        'seller.profit.setup',
        'seller.profit.store',
        // Profit settings management (part of setup and settings)
        'seller.profit-settings.index',
        'seller.profit-settings.store',
        'seller.profit-settings.bulk-update',
        'seller.profit-settings.toggle',
        'seller.profit-settings.destroy',
        'seller.profit-settings.api.get',
        // Support tickets
        'seller.tickets.index',
        'seller.tickets.create',
        'seller.tickets.store',
        'seller.tickets.show',
        'seller.tickets.reply',
        // Payment for subscription
        'payment.subscription',
        'payment.order',
        'payment.success',
        'payment.error',
        'payment.callback',
        'paymob.callback',
        'paymob.initiate-subscription',
        // Auth routes
        'logout',
        'password.request',
        'password.email',
        'password.reset',
        'password.update',
        'password.confirm',
        'verification.notice',
        'verification.verify',
        'verification.send',
        // Language and locale switching
        'lang.switch',
        'currency.switch',
    ];

    /**
     * Routes for initial setup (profit settings)
     */
    protected array $setupRoutes = [
        'seller.profit.setup',
        'seller.profit.store',
        'seller.profit-settings.index',
        'seller.profit-settings.store',
        'seller.profit-settings.bulk-update',
        'profile.edit',
        'profile.update',
        'profile.logo.update',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only apply to sellers
        if (!$user || $user->user_type !== 'seller') {
            return $next($request);
        }

        $currentRoute = $request->route()?->getName();

        // Always allow certain routes (subscriptions, settings, support)
        if ($this->isAlwaysAccessible($currentRoute)) {
            return $next($request);
        }

        // Check if seller has completed initial setup
        if (!$user->hasCompletedSetup()) {
            // Allow setup routes
            if ($this->isSetupRoute($currentRoute)) {
                return $next($request);
            }

            $isAr = app()->getLocale() == 'ar';

            // Step 1: Check if payment method is set up
            if (!$user->hasPaymentMethodSetup()) {
                return redirect()->route('profile.edit')
                    ->with('warning', $isAr
                        ? 'يرجى إكمال بيانات الملف الشخصي وطريقة السحب أولاً'
                        : 'Please complete your profile and withdrawal method first');
            }

            // Step 2: Check if profit settings are completed
            if (!$user->profit_settings_completed) {
                return redirect()->route('seller.profit.setup')
                    ->with('warning', $isAr
                        ? 'يرجى إكمال إعدادات نسب الربح للمتابعة'
                        : 'Please complete profit settings to continue');
            }
        }

        // Check if trial expired and no active subscription
        if ($user->hasTrialExpired()) {
            return redirect()->route('subscriptions.index')
                ->with('error', __('messages.trial_expired_please_subscribe'));
        }

        return $next($request);
    }

    /**
     * Check if route is always accessible
     */
    protected function isAlwaysAccessible(?string $route): bool
    {
        if (!$route) {
            return false;
        }

        return in_array($route, $this->alwaysAccessibleRoutes);
    }

    /**
     * Check if route is a setup route
     */
    protected function isSetupRoute(?string $route): bool
    {
        if (!$route) {
            return false;
        }

        return in_array($route, $this->setupRoutes);
    }
}
