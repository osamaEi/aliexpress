<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'phone_code' => ['nullable', 'string', 'max:10'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:2'],
            // Withdrawal method fields
            'withdrawal_method' => ['nullable', 'string', 'in:paypal,bank_transfer,mobile_wallet'],
            // PayPal fields
            'paypal_email' => ['nullable', 'required_if:withdrawal_method,paypal', 'email', 'max:255'],
            // Bank transfer fields
            'bank_name' => ['nullable', 'required_if:withdrawal_method,bank_transfer', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'required_if:withdrawal_method,bank_transfer', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_iban' => ['nullable', 'required_if:withdrawal_method,bank_transfer', 'string', 'max:50'],
            'bank_swift_code' => ['nullable', 'string', 'max:20'],
            // Mobile wallet fields
            'wallet_provider' => ['nullable', 'required_if:withdrawal_method,mobile_wallet', 'string', 'max:50'],
            'wallet_phone_number' => ['nullable', 'required_if:withdrawal_method,mobile_wallet', 'string', 'max:20'],
            'wallet_holder_name' => ['nullable', 'required_if:withdrawal_method,mobile_wallet', 'string', 'max:255'],
            'marketing_code' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        // Handle marketing code - can only be entered once and requires approval
        if (isset($validated['marketing_code'])) {
            if ($user->marketing_code && $user->marketing_code_approved) {
                // If already approved, don't allow changes
                unset($validated['marketing_code']);
            } elseif ($user->marketing_code !== $validated['marketing_code']) {
                // New or changed code - set to pending approval
                $validated['marketing_code_approved'] = false;
            }
        }

        // Fill user data
        $user->fill($validated);

        // Reset email verification if email changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Redirect sellers to profit settings if they have payment method but haven't completed profit settings
        if ($user->user_type === 'seller' && $user->hasPaymentMethodSetup() && !$user->profit_settings_completed) {
            $isAr = app()->getLocale() == 'ar';
            return Redirect::route('seller.profit.setup')
                ->with('status', 'profile-updated')
                ->with('success', $isAr
                    ? 'تم تحديث الملف الشخصي بنجاح. يرجى الآن إكمال إعدادات نسب الربح.'
                    : 'Profile updated successfully. Please now complete profit settings.');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's logo.
     */
    public function updateLogo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'logo' => ['required', 'image', 'max:2048'], // 2MB max
        ]);

        $user = $request->user();

        // Delete old logo if exists
        if ($user->logo && \Storage::disk('public')->exists($user->logo)) {
            \Storage::disk('public')->delete($user->logo);
        }

        // Store new logo
        $logoPath = $request->file('logo')->store('logos', 'public');
        $user->logo = $logoPath;
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'logo-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
