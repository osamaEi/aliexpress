<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\UserSubscription;
use App\Models\Order;
use App\Models\WithdrawalRequest;
use App\Notifications\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by user type
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount('subscriptions')
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display detailed view of a specific user/seller
     */
    public function show(User $user)
    {
        // Load relationships
        $user->load(['wallet', 'activeSubscription.subscription']);

        // Get assigned products
        $products = $user->assignedProducts()
            ->with('category')
            ->paginate(10, ['*'], 'products_page');

        // Get orders
        $orders = Order::where('user_id', $user->id)
            ->with('product')
            ->latest()
            ->take(10)
            ->get();

        // Get withdrawal requests
        $withdrawalRequests = WithdrawalRequest::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        // Get active subscription
        $activeSubscription = $user->activeSubscription;

        // Get wallet
        $wallet = $user->wallet;

        // Get all subscriptions for assignment
        $subscriptions = Subscription::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Get category names for main and sub activities
        $mainCategories = [];
        $subCategories = [];

        if ($user->main_activity) {
            $mainActivityIds = json_decode($user->main_activity, true);
            if (is_array($mainActivityIds)) {
                $mainCategories = \App\Models\Category::whereIn('id', $mainActivityIds)
                    ->get()
                    ->pluck(app()->getLocale() == 'ar' ? 'name_ar' : 'name')
                    ->filter()
                    ->toArray();
            }
        }

        if ($user->sub_activity) {
            $subActivityIds = json_decode($user->sub_activity, true);
            if (is_array($subActivityIds)) {
                $subCategories = \App\Models\Category::whereIn('id', $subActivityIds)
                    ->get()
                    ->pluck(app()->getLocale() == 'ar' ? 'name_ar' : 'name')
                    ->filter()
                    ->toArray();
            }
        }

        return view('admin.users.show', compact(
            'user',
            'products',
            'orders',
            'withdrawalRequests',
            'activeSubscription',
            'wallet',
            'subscriptions',
            'mainCategories',
            'subCategories'
        ));
    }

    /**
     * Toggle user block status
     */
    public function toggleBlock(Request $request, User $user)
    {
        if ($user->is_blocked) {
            // Unblock user
            $user->update([
                'is_blocked' => false,
                'block_reason' => null,
            ]);

            return redirect()->route('admin.users.show', $user)
                ->with('success', __('messages.user_unblocked_successfully'));
        } else {
            // Block user
            $request->validate([
                'block_reason' => 'required|string|max:1000',
            ]);

            $user->update([
                'is_blocked' => true,
                'block_reason' => $request->block_reason,
            ]);

            return redirect()->route('admin.users.show', $user)
                ->with('success', __('messages.user_blocked_successfully'));
        }
    }

    /**
     * Send notification to user
     */
    public function sendNotification(Request $request, User $user)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        try {
            $user->notify(new AdminNotification(
                $request->title,
                $request->message
            ));

            return redirect()->route('admin.users.show', $user)
                ->with('success', __('messages.notification_sent_successfully'));
        } catch (\Exception $e) {
            return redirect()->route('admin.users.show', $user)
                ->with('error', __('messages.notification_send_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Assign subscription to user
     */
    public function assignSubscription(Request $request, User $user)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'duration_days' => 'nullable|integer|min:1',
        ]);

        $subscription = Subscription::findOrFail($request->subscription_id);
        $durationDays = $request->duration_days ?? $subscription->duration_days;

        try {
            DB::beginTransaction();

            // Cancel any existing active subscriptions
            UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);

            // Create new subscription
            $userSubscription = UserSubscription::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays($durationDays)->toDateString(),
                'amount_paid' => 0, // Admin assigned, no payment
                'payment_method' => 'admin_assigned',
                'status' => 'active',
            ]);

            DB::commit();

            return redirect()->route('admin.users.show', $user)
                ->with('success', __('messages.subscription_assigned_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.users.show', $user)
                ->with('error', __('messages.subscription_assignment_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Extend user's current subscription
     */
    public function extendSubscription(Request $request, User $user)
    {
        $request->validate([
            'extend_days' => 'required|integer|min:1|max:365',
        ]);

        $activeSubscription = $user->activeSubscription;

        if (!$activeSubscription) {
            return redirect()->route('admin.users.show', $user)
                ->with('error', __('messages.no_active_subscription'));
        }

        try {
            $currentEndDate = \Carbon\Carbon::parse($activeSubscription->end_date);
            $newEndDate = $currentEndDate->addDays($request->extend_days);

            $activeSubscription->update([
                'end_date' => $newEndDate->toDateString(),
            ]);

            return redirect()->route('admin.users.show', $user)
                ->with('success', __('messages.subscription_extended_successfully', ['days' => $request->extend_days]));
        } catch (\Exception $e) {
            return redirect()->route('admin.users.show', $user)
                ->with('error', __('messages.subscription_extension_failed') . ': ' . $e->getMessage());
        }
    }
}
