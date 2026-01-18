<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerAccessControl
{
    /**
     * Routes that are always accessible for sellers (even after trial expires)
     */
    protected array $alwaysAccessibleRoutes = [
        'subscriptions.index',
        'subscriptions.subscribe',
        'subscriptions.history',
        'profile.edit',
        'profile.update',
        'profile.logo.update',
        'seller.tickets.index',
        'seller.tickets.create',
        'seller.tickets.store',
        'seller.tickets.show',
        'seller.tickets.reply',
        'logout',
    ];

    /**
     * Routes for initial setup
     */
    protected array $setupRoutes = [
        'seller.profit-settings.index',
        'seller.profit-settings.store',
        'seller.profit-settings.bulk-update',
        'profile.edit',
        'profile.update',
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

        $currentRoute = $request->route()->getName();

        // Always allow certain routes
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
