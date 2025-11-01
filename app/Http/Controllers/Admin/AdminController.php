<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_sellers' => User::where('user_type', 'seller')->count(),
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'active_subscriptions' => UserSubscription::where('status', 'active')->count(),
            'total_revenue' => UserSubscription::where('status', 'active')->sum('amount_paid'),
        ];

        $recentOrders = Order::with(['user', 'product'])
            ->latest()
            ->take(10)
            ->get();

        $recentSubscriptions = UserSubscription::with(['user', 'subscription'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentSubscriptions'));
    }

    /**
     * Token Management Page
     */
    public function tokens()
    {
        $envFile = base_path('.env');
        $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';

        // Extract current tokens
        preg_match('/ALIEXPRESS_APP_KEY=(.*)/', $envContent, $appKey);
        preg_match('/ALIEXPRESS_APP_SECRET=(.*)/', $envContent, $appSecret);
        preg_match('/ALIEXPRESS_ACCESS_TOKEN=(.*)/', $envContent, $accessToken);

        $tokens = [
            'app_key' => $appKey[1] ?? '',
            'app_secret' => $appSecret[1] ?? '',
            'access_token' => $accessToken[1] ?? '',
        ];

        return view('admin.tokens', compact('tokens'));
    }

    /**
     * Update Tokens
     */
    public function updateTokens(Request $request)
    {
        $request->validate([
            'app_key' => 'required|string',
            'app_secret' => 'required|string',
            'access_token' => 'nullable|string',
        ]);

        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        // Update tokens
        $envContent = preg_replace(
            '/ALIEXPRESS_APP_KEY=.*/',
            'ALIEXPRESS_APP_KEY=' . $request->app_key,
            $envContent
        );

        $envContent = preg_replace(
            '/ALIEXPRESS_APP_SECRET=.*/',
            'ALIEXPRESS_APP_SECRET=' . $request->app_secret,
            $envContent
        );

        if ($request->filled('access_token')) {
            $envContent = preg_replace(
                '/ALIEXPRESS_ACCESS_TOKEN=.*/',
                'ALIEXPRESS_ACCESS_TOKEN=' . $request->access_token,
                $envContent
            );
        }

        file_put_contents($envFile, $envContent);

        // Clear config cache
        \Artisan::call('config:clear');

        return redirect()->route('admin.tokens')
            ->with('success', __('messages.tokens_updated_successfully'));
    }
}
