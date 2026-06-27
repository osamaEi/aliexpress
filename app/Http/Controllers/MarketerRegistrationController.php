<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;
use App\Services\WhatsAppOTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Marketer (مسوق) registration — mirrors the seller flow (email + WhatsApp OTP),
 * but without the category-selection step (not relevant for marketers).
 *
 * Steps: 1) Account + details  2) Email OTP  3) WhatsApp OTP -> create account.
 */
class MarketerRegistrationController extends Controller
{
    /* ───────────────────────── Step 1: Account + details ───────────────────────── */

    public function showStep1()
    {
        $countries = Country::active()->orderBy('sort_order')->orderBy('name')->get();

        return view('marketer.register.step1', compact('countries'));
    }

    public function processStep1(Request $request)
    {
        $isAr = app()->getLocale() == 'ar';

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'phone_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            // Marketing details
            'bio' => 'nullable|string|max:1000',
            'website_url' => 'nullable|url|max:255',
            'experience_years' => 'nullable|integer|min:0|max:80',
            'audience_size' => 'nullable|string|max:50',
            'marketing_channels' => 'nullable|array',
            'marketing_channels.*' => 'nullable|string|max:50',
            'social_facebook' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
            'social_tiktok' => 'nullable|string|max:255',
            'social_youtube' => 'nullable|string|max:255',
            'social_snapchat' => 'nullable|string|max:255',
            'social_x' => 'nullable|string|max:255',
        ], [
            'full_name.required' => $isAr ? 'الاسم الكامل مطلوب' : 'Full name is required',
            'country.required' => $isAr ? 'يرجى اختيار الدولة' : 'Please select a country',
            'phone_code.required' => $isAr ? 'رمز الدولة مطلوب' : 'Country code is required',
            'phone.required' => $isAr ? 'رقم الهاتف مطلوب' : 'Phone number is required',
            'email.required' => $isAr ? 'البريد الإلكتروني مطلوب' : 'Email address is required',
            'email.email' => $isAr ? 'يرجى إدخال بريد إلكتروني صحيح' : 'Please enter a valid email address',
            'email.unique' => $isAr ? 'البريد الإلكتروني مسجل بالفعل' : 'This email is already registered',
            'password.required' => $isAr ? 'كلمة المرور مطلوبة' : 'Password is required',
            'password.min' => $isAr ? 'كلمة المرور يجب أن تكون 8 أحرف على الأقل' : 'Password must be at least 8 characters',
            'password.confirmed' => $isAr ? 'تأكيد كلمة المرور غير متطابق' : 'Password confirmation does not match',
        ]);

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($validated['full_name']) . '.' . $logo->getClientOriginalExtension();
            $logoPath = $logo->storeAs('logos/marketers', $logoName, 'public');
        }

        // Combine phone_code + phone for DB storage
        $validated['phone'] = $validated['phone_code'] . $validated['phone'];
        $validated['phone_code'] = '+' . $validated['phone_code'];

        // Build social media accounts array
        $social = array_filter([
            'facebook' => $request->input('social_facebook'),
            'instagram' => $request->input('social_instagram'),
            'tiktok' => $request->input('social_tiktok'),
            'youtube' => $request->input('social_youtube'),
            'snapchat' => $request->input('social_snapchat'),
            'x' => $request->input('social_x'),
        ]);
        $validated['social_media'] = $social;

        unset($validated['logo'], $validated['password_confirmation']);
        $validated['social_facebook'] = $validated['social_instagram'] = $validated['social_tiktok'] = null;
        $validated['social_youtube'] = $validated['social_snapchat'] = $validated['social_x'] = null;

        if ($logoPath) {
            $validated['logo_path'] = $logoPath;
        }

        Session::put('marketer_registration', $validated);

        return redirect()->route('marketer.register.step2');
    }

    /* ───────────────────────── Step 2: Email OTP ───────────────────────── */

    public function showStep2()
    {
        if (!Session::has('marketer_registration')) {
            return redirect()->route('marketer.register.step1');
        }

        $email = Session::get('marketer_registration.email');
        $this->sendEmailOTP($email);

        return view('marketer.register.step2', compact('email'));
    }

    public function verifyOTP(Request $request)
    {
        $isAr = app()->getLocale() == 'ar';

        $request->validate(['otp' => 'required|string|size:6'], [
            'otp.required' => $isAr ? 'رمز التحقق مطلوب' : 'Verification code is required',
            'otp.size' => $isAr ? 'رمز التحقق يجب أن يكون 6 أرقام' : 'Verification code must be 6 digits',
        ]);

        $email = Session::get('marketer_registration.email');
        $storedOTP = Session::get('otp_' . $email);
        $otpExpiry = Session::get('otp_expiry_' . $email);

        if (!$storedOTP || !$otpExpiry || now()->gt($otpExpiry)) {
            return back()->withErrors(['otp' => $isAr ? 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.' : 'OTP expired. Please request a new one.']);
        }
        if ($request->otp !== $storedOTP) {
            return back()->withErrors(['otp' => $isAr ? 'رمز التحقق غير صحيح.' : 'Invalid OTP code.']);
        }

        Session::put('marketer_email_verified', true);
        Session::forget('otp_' . $email);
        Session::forget('otp_expiry_' . $email);

        return redirect()->route('marketer.register.step3');
    }

    public function resendOTP()
    {
        $email = Session::get('marketer_registration.email');
        if (!$email) {
            return back()->withErrors(['email' => app()->getLocale() == 'ar' ? 'انتهت صلاحية الجلسة.' : 'Session expired.']);
        }
        $this->sendEmailOTP($email);

        return back()->with('success', app()->getLocale() == 'ar' ? 'تم إعادة إرسال الرمز إلى بريدك.' : 'OTP resent to your email.');
    }

    /* ───────────────────────── Step 3: WhatsApp OTP -> create ───────────────────────── */

    public function showStep3()
    {
        if (!Session::has('marketer_registration') || !Session::get('marketer_email_verified')) {
            return redirect()->route('marketer.register.step1');
        }

        $phone = Session::get('marketer_registration.phone');
        $this->sendWhatsAppOTP($phone);

        return view('marketer.register.step3', compact('phone'));
    }

    public function verifyWhatsAppOTP(Request $request)
    {
        $isAr = app()->getLocale() == 'ar';

        $request->validate(['otp' => 'required|string|size:6'], [
            'otp.required' => $isAr ? 'رمز التحقق مطلوب' : 'Verification code is required',
            'otp.size' => $isAr ? 'رمز التحقق يجب أن يكون 6 أرقام' : 'Verification code must be 6 digits',
        ]);

        $data = Session::get('marketer_registration');
        $phone = $data['phone'];

        $storedOTP = Session::get('whatsapp_otp_' . $phone);
        $otpExpiry = Session::get('whatsapp_otp_expiry_' . $phone);

        if (!$storedOTP || !$otpExpiry || now()->gt($otpExpiry)) {
            return back()->withErrors(['otp' => $isAr ? 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.' : 'OTP expired. Please request a new one.']);
        }
        if ($request->otp !== $storedOTP) {
            return back()->withErrors(['otp' => $isAr ? 'رمز التحقق غير صحيح.' : 'Invalid OTP code.']);
        }

        // Create the marketer account
        $user = User::create([
            'name' => $data['full_name'],
            'full_name' => $data['full_name'],
            'country' => $data['country'],
            'phone' => $data['phone'] ?? null,
            'phone_code' => $data['phone_code'] ?? '+971',
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'user_type' => 'marketer',
            'bio' => $data['bio'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'social_media_accounts' => $data['social_media'] ?? [],
            'logo' => $data['logo_path'] ?? null,
            'is_verified' => true,
            'verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        // Give the marketer a wallet upfront
        if (method_exists($user, 'getOrCreateWallet')) {
            $user->getOrCreateWallet();
        }

        // Clear session
        Session::forget('marketer_registration');
        Session::forget('marketer_email_verified');
        Session::forget('whatsapp_otp_' . $phone);
        Session::forget('whatsapp_otp_expiry_' . $phone);

        Auth::login($user);

        return redirect()->route('marketer.dashboard')->with('success', $isAr
            ? 'تم إنشاء حساب المسوّق بنجاح! ابدأ بتصفّح الكوبونات وطلب تفعيلها.'
            : 'Marketer account created! Start browsing coupons and request activation.');
    }

    public function resendWhatsAppOTP()
    {
        $phone = Session::get('marketer_registration.phone');
        if (!$phone) {
            return back()->withErrors(['phone' => app()->getLocale() == 'ar' ? 'انتهت صلاحية الجلسة.' : 'Session expired.']);
        }
        $this->sendWhatsAppOTP($phone);

        return back()->with('success', app()->getLocale() == 'ar' ? 'تم إعادة إرسال الرمز عبر واتساب.' : 'OTP resent via WhatsApp.');
    }

    /* ───────────────────────── Helpers ───────────────────────── */

    private function sendEmailOTP($email)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Session::put('otp_' . $email, $otp);
        Session::put('otp_expiry_' . $email, now()->addMinutes(10));

        Mail::send('emails.otp-verification', ['otp' => $otp], function ($message) use ($email) {
            $message->to($email)->subject(app()->getLocale() == 'ar'
                ? 'رمز التحقق - تسجيل المسوّق'
                : 'Email Verification Code - Marketer Registration');
        });
    }

    private function sendWhatsAppOTP($phone)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Session::put('whatsapp_otp_' . $phone, $otp);
        Session::put('whatsapp_otp_expiry_' . $phone, now()->addMinutes(10));

        $isEnglish = app()->getLocale() !== 'ar';
        (new WhatsAppOTPService())->sendOTP($phone, $otp, $isEnglish);
    }
}
