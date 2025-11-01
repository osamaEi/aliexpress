<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class SubscriptionManagementController extends Controller
{
    /**
     * Display subscriptions
     */
    public function index()
    {
        $subscriptions = Subscription::withCount(['userSubscriptions', 'activeSubscriptions'])
            ->orderBy('sort_order')
            ->get();

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Edit subscription
     */
    public function edit(Subscription $subscription)
    {
        return view('admin.subscriptions.edit', compact('subscription'));
    }

    /**
     * Update subscription
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'color' => 'required|string',
            'max_products' => 'required|integer|min:1',
            'max_orders_per_month' => 'required|integer|min:1',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'priority_support' => 'boolean',
            'analytics_access' => 'boolean',
            'bulk_import' => 'boolean',
            'api_access' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $subscription->update($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', __('messages.subscription_updated_successfully'));
    }

    /**
     * View user subscriptions
     */
    public function userSubscriptions()
    {
        $userSubscriptions = UserSubscription::with(['user', 'subscription'])
            ->latest()
            ->paginate(20);

        return view('admin.subscriptions.users', compact('userSubscriptions'));
    }
}
