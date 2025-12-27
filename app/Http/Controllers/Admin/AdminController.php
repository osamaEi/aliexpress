<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\UserSubscription;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        // Basic stats
        $stats = [
            'total_users' => User::count(),
            'total_sellers' => User::where('user_type', 'seller')->count(),
            'total_merchants' => User::where('user_type', 'buyer')->count(),
            'total_distributors' => User::where('user_type', 'distributor')->count(),
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'active_subscriptions' => UserSubscription::where('status', 'active')->count(),
            'total_revenue' => UserSubscription::where('status', 'active')->sum('amount_paid'),
        ];

        // Products by country (based on distributor's country via product_user pivot)
        $productsByCountry = DB::table('products')
            ->join('product_user', 'products.id', '=', 'product_user.product_id')
            ->join('users', 'product_user.user_id', '=', 'users.id')
            ->where('users.user_type', 'distributor')
            ->whereNotNull('users.country')
            ->select('users.country', DB::raw('count(DISTINCT products.id) as count'))
            ->groupBy('users.country')
            ->get()
            ->keyBy('country');

        // Products with commission (from affiliate coupons)
        $affiliateStats = [
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::active()->count(),
            'expired_coupons' => Coupon::expired()->count(),
            'total_commission_earned' => Coupon::sum('total_commission_earned'),
            'pending_commission' => CouponUsage::pending()->sum('commission_amount'),
        ];

        // Products from AliExpress (have aliexpress_id with 10+ digits)
        $aliexpressProducts = Product::whereNotNull('aliexpress_id')
            ->whereRaw('LENGTH(aliexpress_id) >= 10')
            ->count();

        // Local distributor products (via product_user pivot)
        $distributorProducts = Product::whereHas('assignedUsers', function($q) {
            $q->where('user_type', 'distributor');
        })->count();

        // Wallet/PayPal stats - unpaid store balances
        $walletStats = [
            'total_wallet_balance' => Wallet::sum('balance'),
            'total_pending_balance' => Wallet::sum('pending_balance'),
            'distributor_balance' => Wallet::whereHas('user', function($q) {
                $q->where('user_type', 'distributor');
            })->sum('balance'),
            'seller_balance' => Wallet::whereHas('user', function($q) {
                $q->where('user_type', 'seller');
            })->sum('balance'),
        ];

        // Orders with coupon (orders that used affiliate coupons)
        $productsWithCoupon = CouponUsage::distinct('order_id')->count('order_id');

        $recentOrders = Order::with(['user', 'product'])
            ->latest()
            ->take(10)
            ->get();

        $recentSubscriptions = UserSubscription::with(['user', 'subscription'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'productsByCountry',
            'affiliateStats',
            'aliexpressProducts',
            'distributorProducts',
            'walletStats',
            'productsWithCoupon',
            'recentOrders',
            'recentSubscriptions'
        ));
    }

    /**
     * Token Management Page
     */
    public function tokens()
    {
        $tokens = [
            'app_key' => config('services.aliexpress.api_key', env('ALIEXPRESS_APP_KEY', '')),
            'app_secret' => config('services.aliexpress.api_secret', env('ALIEXPRESS_APP_SECRET', '')),
        ];

        // Get token status from database
        $tokenStatus = null;
        try {
            $authService = new \App\Services\AliExpressAuthService();
            $tokenStatus = $authService->getTokenStatus();
        } catch (\Exception $e) {
            // No tokens yet
        }

        return view('admin.tokens', compact('tokens', 'tokenStatus'));
    }

    /**
     * Update Tokens
     */
    public function updateTokens(Request $request)
    {
        $request->validate([
            'app_key' => 'required|string',
            'app_secret' => 'required|string',
        ]);

        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            return redirect()->route('admin.tokens')
                ->with('error', '.env file not found');
        }

        $envContent = file_get_contents($envFile);

        // Update or add APP_KEY
        if (preg_match('/ALIEXPRESS_APP_KEY=/', $envContent)) {
            $envContent = preg_replace(
                '/ALIEXPRESS_APP_KEY=.*/',
                'ALIEXPRESS_APP_KEY=' . $request->app_key,
                $envContent
            );
        } else {
            $envContent .= "\nALIEXPRESS_APP_KEY=" . $request->app_key;
        }

        // Update or add APP_SECRET
        if (preg_match('/ALIEXPRESS_APP_SECRET=/', $envContent)) {
            $envContent = preg_replace(
                '/ALIEXPRESS_APP_SECRET=.*/',
                'ALIEXPRESS_APP_SECRET=' . $request->app_secret,
                $envContent
            );
        } else {
            $envContent .= "\nALIEXPRESS_APP_SECRET=" . $request->app_secret;
        }

        file_put_contents($envFile, $envContent);

        // Clear config cache
        \Artisan::call('config:clear');

        return redirect()->route('admin.tokens')
            ->with('success', __('messages.tokens_updated_successfully'));
    }

    /**
     * Generate new access token using authorization URL
     */
    public function generateToken()
    {
        // Check if credentials are configured
        $appKey = config('services.aliexpress.api_key');
        $appSecret = config('services.aliexpress.api_secret');

        if (empty($appKey) || empty($appSecret)) {
            return redirect()->route('admin.tokens')
                ->with('error', 'Please configure your App Key and App Secret first before generating a token.');
        }

        try {
            $authService = new \App\Services\AliExpressAuthService();
            $redirectUri = route('admin.tokens.callback');
            $authUrl = $authService->getAuthorizationUrl($redirectUri);

            return redirect($authUrl);
        } catch (\Exception $e) {
            return redirect()->route('admin.tokens')
                ->with('error', 'Failed to generate authorization URL: ' . $e->getMessage());
        }
    }

    /**
     * Handle OAuth callback and exchange code for token
     */
    public function tokenCallback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect()->route('admin.tokens')
                ->with('error', 'Authorization code not received');
        }

        try {
            $authService = new \App\Services\AliExpressAuthService();
            $tokenData = $authService->createToken($request->code);

            // Save access token to .env file
            $this->saveAccessTokenToEnv($tokenData['access_token']);

            return redirect()->route('admin.tokens')
                ->with('success', 'Access token generated successfully and saved to .env! Expires: ' .
                       \Carbon\Carbon::createFromTimestampMs($tokenData['expire_time'])->diffForHumans());
        } catch (\Exception $e) {
            return redirect()->route('admin.tokens')
                ->with('error', 'Failed to generate access token: ' . $e->getMessage());
        }
    }

    /**
     * Save access token to .env file
     */
    private function saveAccessTokenToEnv(string $accessToken)
    {
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            throw new \Exception('.env file not found');
        }

        $envContent = file_get_contents($envFile);

        // Update or add ALIEXPRESS_ACCESS_TOKEN
        if (preg_match('/ALIEXPRESS_ACCESS_TOKEN=/', $envContent)) {
            $envContent = preg_replace(
                '/ALIEXPRESS_ACCESS_TOKEN=.*/',
                'ALIEXPRESS_ACCESS_TOKEN=' . $accessToken,
                $envContent
            );
        } else {
            $envContent .= "\nALIEXPRESS_ACCESS_TOKEN=" . $accessToken;
        }

        file_put_contents($envFile, $envContent);

        // Clear config cache to reload the new value
        \Artisan::call('config:clear');
    }
}
