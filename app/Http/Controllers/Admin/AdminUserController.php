<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Display a listing of admin users.
     */
    public function index(Request $request)
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('slug', 'admin');
        });

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->with('roles')
            ->latest()
            ->paginate(20);

        return view('admin.users.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin user.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.admins.create', compact('roles'));
    }

    /**
     * Store a newly created admin user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->id);
        }

        // Assign additional roles if provided
        if (!empty($validated['roles'])) {
            $user->roles()->syncWithoutDetaching($validated['roles']);
        }

        return redirect()->route('admin.admins.index')
            ->with('success', __('messages.admin_created_successfully'));
    }

    /**
     * Show the form for editing an admin user.
     */
    public function edit(User $admin)
    {
        $roles = Role::all();
        $adminRoles = $admin->roles->pluck('id')->toArray();
        return view('admin.users.admins.edit', compact('admin', 'roles', 'adminRoles'));
    }

    /**
     * Update the specified admin user.
     */
    public function update(Request $request, User $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $admin->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $admin->update(['password' => Hash::make($validated['password'])]);
        }

        // Sync roles
        $adminRole = Role::where('slug', 'admin')->first();
        $rolesToSync = [$adminRole->id];

        if (!empty($validated['roles'])) {
            $rolesToSync = array_unique(array_merge($rolesToSync, $validated['roles']));
        }

        $admin->roles()->sync($rolesToSync);

        return redirect()->route('admin.admins.index')
            ->with('success', __('messages.admin_updated_successfully'));
    }

    /**
     * Remove the specified admin user.
     */
    public function destroy(User $admin)
    {
        // Prevent deleting yourself
        if ($admin->id === auth()->id()) {
            return back()->with('error', __('messages.cannot_delete_yourself'));
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', __('messages.admin_deleted_successfully'));
    }
}
