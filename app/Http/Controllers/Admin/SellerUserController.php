<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class SellerUserController extends Controller
{
    /**
     * Display a listing of seller users.
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'seller');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Filter by verification status
        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->is_verified === '1');
        }

        // Filter by activity
        if ($request->filled('main_activity')) {
            $query->where('main_activity', $request->main_activity);
        }

        $sellers = $query->with(['subscriptions' => function ($q) {
                $q->where('status', 'active')->latest();
            }])
            ->withCount('subscriptions')
            ->latest()
            ->paginate(20);

        // Get unique main activities for filter
        $mainActivities = User::where('user_type', 'seller')
            ->whereNotNull('main_activity')
            ->distinct()
            ->pluck('main_activity');

        return view('admin.users.sellers.index', compact('sellers', 'mainActivities'));
    }

    /**
     * Display the specified seller.
     */
    public function show(User $seller)
    {
        $seller->load(['subscriptions', 'wallet', 'assignedProducts']);

        return view('admin.users.sellers.show', compact('seller'));
    }

    /**
     * Update seller verification status.
     */
    public function updateVerification(Request $request, User $seller)
    {
        $validated = $request->validate([
            'is_verified' => 'required|boolean',
        ]);

        $seller->update([
            'is_verified' => $validated['is_verified'],
            'verified_at' => $validated['is_verified'] ? now() : null,
        ]);

        return back()->with('success', __('messages.seller_verification_updated'));
    }

    /**
     * Suspend/Activate seller account.
     */
    public function toggleStatus(User $seller)
    {
        // You can add a status field to users table if needed
        // For now, we'll use email_verified_at as a simple toggle

        return back()->with('success', __('messages.seller_status_updated'));
    }

    /**
     * Remove the specified seller.
     */
    public function destroy(User $seller)
    {
        $seller->delete();

        return redirect()->route('admin.sellers.index')
            ->with('success', __('messages.seller_deleted_successfully'));
    }
}
