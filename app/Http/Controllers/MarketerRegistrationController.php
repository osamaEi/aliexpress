<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MarketerRegistrationController extends Controller
{
    /**
     * Show the single-step marketer registration form.
     */
    public function show()
    {
        $countries = Country::active()->orderBy('sort_order')->orderBy('name')->get();

        return view('marketer.register.register', compact('countries'));
    }

    /**
     * Create the marketer account and log them in.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:3',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'full_name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country' => $validated['country'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'marketer',
            'is_verified' => true,
        ]);

        // Give the marketer a wallet up front
        if (method_exists($user, 'getOrCreateWallet')) {
            $user->getOrCreateWallet();
        }

        Auth::login($user);

        return redirect()->route('marketer.dashboard')
            ->with('success', app()->getLocale() === 'ar'
                ? 'تم إنشاء حساب المسوّق بنجاح!'
                : 'Marketer account created successfully!');
    }
}
