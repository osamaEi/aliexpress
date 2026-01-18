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
        'subscriptions.history',
        'subscriptions.cancel',
        // Profile/Settings routes
        'profile.edit',
        'profile.update',
        'profile.logo.update',
        'profile.destroy',
        // Profit settings (part of setup and settings)
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
        'payment.success',
        'payment.error',
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
        'locale.switch',
        'currency.switch',
    ];

    /**
     * Routes for initial setup (profile + profit settings)
     */
    protected array $setupRoutes = [
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

            // Redirect to appropriate setup page
            if (!$user->setup_completed_at) {
                return redirect()->route('profile.edit')
                    ->with('warning', __('messages.please_complete_profile_settings'));
            }

            if (!$user->profit_settings_completed) {
                return redirect()->route('seller.profit-settings.index')
                    ->with('warning', __('messages.please_complete_profit_settings'));
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
