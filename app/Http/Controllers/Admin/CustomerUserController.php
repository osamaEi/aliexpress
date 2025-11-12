<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerUserController extends Controller
{
    /**
     * Display a listing of customer users.
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'customer');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by verification status
        if ($request->filled('email_verified')) {
            if ($request->email_verified === '1') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $customers = $query->latest()
            ->paginate(20);

        return view('admin.users.customers.index', compact('customers'));
    }

    /**
     * Display the specified customer.
     */
    public function show(User $customer)
    {
        // Load customer orders and other related data
        // $customer->load(['orders']);

        return view('admin.users.customers.show', compact('customer'));
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(User $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', __('messages.customer_deleted_successfully'));
    }
}
