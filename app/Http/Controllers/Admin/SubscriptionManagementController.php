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
     * Show create form
     */
    public function create()
    {
        return view('admin.subscriptions.create');
    }

    /**
     * Store new subscription
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
            'color' => 'required|string',
            'role' => 'required|in:seller,distributor,both',
            'max_products' => 'required|integer|min:1',
            'max_orders_per_month' => 'required|integer|min:1',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'priority_support' => 'boolean',
            'analytics_access' => 'boolean',
            'bulk_import' => 'boolean',
            'api_access' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['priority_support'] = $request->boolean('priority_support');
        $validated['analytics_access'] = $request->boolean('analytics_access');
        $validated['bulk_import'] = $request->boolean('bulk_import');
        $validated['api_access'] = $request->boolean('api_access');
        $validated['is_active'] = $request->boolean('is_active');

        Subscription::create($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', __('messages.subscription_created_successfully'));
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
            'role' => 'required|in:seller,distributor,both',
            'max_products' => 'required|integer|min:1',
            'max_orders_per_month' => 'required|integer|min:1',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'priority_support' => 'boolean',
            'analytics_access' => 'boolean',
            'bulk_import' => 'boolean',
            'api_access' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['priority_support'] = $request->boolean('priority_support');
        $validated['analytics_access'] = $request->boolean('analytics_access');
        $validated['bulk_import'] = $request->boolean('bulk_import');
        $validated['api_access'] = $request->boolean('api_access');
        $validated['is_active'] = $request->boolean('is_active');

        $subscription->update($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', __('messages.subscription_updated_successfully'));
    }

    /**
     * View seller subscriptions
     */
    public function sellerSubscriptions(Request $request)
    {
        $query = UserSubscription::with(['user', 'subscription'])
            ->whereHas('user', fn($q) => $q->where('user_type', 'seller'));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%'));
        }

        $userSubscriptions = $query->latest()->paginate(20)->withQueryString();
        $role = 'seller';

        return view('admin.subscriptions.users', compact('userSubscriptions', 'role'));
    }

    /**
     * View distributor subscriptions
     */
    public function distributorSubscriptions(Request $request)
    {
        $query = UserSubscription::with(['user', 'subscription'])
            ->whereHas('user', fn($q) => $q->where('user_type', 'distributor'));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%'));
        }

        $userSubscriptions = $query->latest()->paginate(20)->withQueryString();
        $role = 'distributor';

        return view('admin.subscriptions.users', compact('userSubscriptions', 'role'));
    }

    /**
     * View all user subscriptions (kept for backwards compat)
     */
    public function userSubscriptions(Request $request)
    {
        $query = UserSubscription::with(['user', 'subscription']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%'));
        }

        $userSubscriptions = $query->latest()->paginate(20)->withQueryString();
        $role = 'all';

        return view('admin.subscriptions.users', compact('userSubscriptions', 'role'));
    }

    /**
     * Close a user subscription
     */
    public function closeSubscription(UserSubscription $userSubscription)
    {
        // Only allow closing active subscriptions
        if ($userSubscription->status !== 'active') {
            return redirect()->route('admin.subscriptions.users')
                ->with('error', __('messages.subscription_already_closed'));
        }

        $userSubscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Closed by admin'
        ]);

        return redirect()->route('admin.subscriptions.users')
            ->with('success', __('messages.subscription_closed_successfully'));
    }
}
