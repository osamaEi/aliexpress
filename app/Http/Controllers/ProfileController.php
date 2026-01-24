<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Currency;
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
        $user = $request->user();
        $isSeller = $user->user_type === 'seller';
        $isAr = app()->getLocale() == 'ar';

        // Base validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => [$isSeller ? 'required' : 'nullable', 'string', 'max:20'],
            'phone_code' => [$isSeller ? 'required' : 'nullable', 'string', 'max:10'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            // Withdrawal method fields
            'withdrawal_method' => [$isSeller ? 'required' : 'nullable', 'string', 'in:paypal,bank_transfer,mobile_wallet'],
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
        ];

        // Add seller-specific required fields
        if ($isSeller) {
            $rules['store_name'] = ['required', 'string', 'max:255'];
            $rules['store_slug'] = ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,store_slug,' . $user->id];
            $rules['default_currency'] = ['required', 'string', 'max:10'];
            // Commercial register is required if not already uploaded
            $commercialRegisterRules = ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
            if (empty($user->commercial_register)) {
                array_unshift($commercialRegisterRules, 'required');
            } else {
                array_unshift($commercialRegisterRules, 'nullable');
            }
            $rules['commercial_register'] = $commercialRegisterRules;
            $rules['freelance_document'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']; // Optional
        }

        // Custom validation messages
        $messages = [
            'phone.required' => $isAr ? 'رقم الهاتف مطلوب' : 'Phone number is required',
            'store_name.required' => $isAr ? 'اسم المتجر مطلوب' : 'Store name is required',
            'store_slug.required' => $isAr ? 'رابط المتجر مطلوب' : 'Store link is required',
            'store_slug.alpha_dash' => $isAr ? 'رابط المتجر يجب أن يحتوي على حروف وأرقام وشرطات فقط' : 'Store link may only contain letters, numbers, dashes and underscores',
            'store_slug.unique' => $isAr ? 'رابط المتجر مستخدم بالفعل' : 'This store link is already taken',
            'default_currency.required' => $isAr ? 'العملة الافتراضية مطلوبة' : 'Default currency is required',
            'withdrawal_method.required' => $isAr ? 'طريقة السحب مطلوبة' : 'Withdrawal method is required',
            'commercial_register.required' => $isAr ? 'السجل التجاري مطلوب' : 'Commercial register is required',
            'commercial_register.mimes' => $isAr ? 'السجل التجاري يجب أن يكون ملف PDF أو صورة' : 'Commercial register must be a PDF or image file',
            'commercial_register.max' => $isAr ? 'حجم الملف يجب أن يكون أقل من 5 ميجابايت' : 'File size must be less than 5MB',
            'freelance_document.mimes' => $isAr ? 'وثيقة العمل الحر يجب أن تكون ملف PDF أو صورة' : 'Freelance document must be a PDF or image file',
            'freelance_document.max' => $isAr ? 'حجم الملف يجب أن يكون أقل من 5 ميجابايت' : 'File size must be less than 5MB',
        ];

        $validated = $request->validate($rules, $messages);

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

        // Handle commercial register file upload
        if ($request->hasFile('commercial_register')) {
            // Delete old file if exists
            if ($user->commercial_register && \Storage::disk('public')->exists($user->commercial_register)) {
                \Storage::disk('public')->delete($user->commercial_register);
            }
            $validated['commercial_register'] = $request->file('commercial_register')->store('documents/commercial_registers', 'public');
        } else {
            unset($validated['commercial_register']);
        }

        // Handle freelance document file upload
        if ($request->hasFile('freelance_document')) {
            // Delete old file if exists
            if ($user->freelance_document && \Storage::disk('public')->exists($user->freelance_document)) {
                \Storage::disk('public')->delete($user->freelance_document);
            }
            $validated['freelance_document'] = $request->file('freelance_document')->store('documents/freelance_documents', 'public');
        } else {
            unset($validated['freelance_document']);
        }

        // Fill user data
        $user->fill($validated);

        // Reset email verification if email changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Update session currency if default_currency was changed
        if (isset($validated['default_currency'])) {
            $currency = Currency::where('code', $validated['default_currency'])->first();
            if ($currency) {
                session([
                    'currency_code' => $currency->code,
                    'currency_symbol' => $currency->symbol,
                    'currency_rate' => $currency->exchange_rate,
                ]);
            }
        }

        // Redirect sellers to profit settings if they have completed profile but haven't completed profit settings
        if ($isSeller && $user->hasCompletedSellerProfile() && !$user->profit_settings_completed) {
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
