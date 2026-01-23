<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Currency;
use App\Services\WhatsAppOTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class DistributorRegistrationController extends Controller
{
    /**
     * Show Step 1: Basic Information (Name, Email, Phone, Store Name, Store Slug)
     */
    public function showStep1()
    {
        return view('distributor.register.step1');
    }

    /**
     * Process Step 1 and go to Step 2
     */
    public function processStep1(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'store_name' => 'required|string|max:255',
            'store_slug' => 'required|string|max:255|unique:users,store_slug|regex:/^[a-z0-9-]+$/',
            'country' => 'required|string|max:100',
            'g-recaptcha-response' => config('services.recaptcha.enabled') ? 'required' : 'nullable',
        ], [
            'g-recaptcha-response.required' => app()->getLocale() == 'ar'
                ? 'يرجى التحقق من أنك لست روبوت'
                : 'Please verify that you are not a robot',
            'store_slug.regex' => app()->getLocale() == 'ar'
                ? 'رابط المتجر يجب أن يحتوي على أحرف إنجليزية صغيرة وأرقام وشرطات فقط'
                : 'Store URL must contain only lowercase letters, numbers and dashes',
            'store_slug.unique' => app()->getLocale() == 'ar'
                ? 'رابط المتجر مستخدم بالفعل'
                : 'Store URL is already taken',
            'phone.required' => app()->getLocale() == 'ar'
                ? 'رقم الهاتف مطلوب'
                : 'Phone number is required',
        ]);

        // Verify reCAPTCHA v3
        if (config('services.recaptcha.enabled')) {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            $recaptchaSecret = config('services.recaptcha.secret_key');

            $response = file_get_contents(
                "https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}"
            );

            $responseData = json_decode($response);

            if (!$responseData->success || (isset($responseData->score) && $responseData->score < 0.5)) {
                return back()->withErrors([
                    'g-recaptcha-response' => app()->getLocale() == 'ar'
                        ? 'فشل التحقق من reCAPTCHA. يرجى المحاولة مرة أخرى.'
                        : 'reCAPTCHA verification failed. Please try again.'
                ])->withInput();
            }
        }

        // Remove reCAPTCHA from validated data
        unset($validated['g-recaptcha-response']);

        // Store data in session
        Session::put('distributor_registration', $validated);

        return redirect()->route('distributor.register.step2');
    }

    /**
     * Show Step 2: Business Documents & Store Logo
     */
    public function showStep2()
    {
        if (!Session::has('distributor_registration')) {
            return redirect()->route('distributor.register.step1');
        }

        return view('distributor.register.step2');
    }

    /**
     * Process Step 2 and go to Step 3
     */
    public function processStep2(Request $request)
    {
        $validated = $request->validate([
            'logo' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'commercial_register' => 'required|file|mimes:pdf,jpeg,jpg,png|max:5120',
            'freelance_document' => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:5120',
        ], [
            'logo.required' => app()->getLocale() == 'ar'
                ? 'شعار المتجر مطلوب'
                : 'Store logo is required',
            'logo.image' => app()->getLocale() == 'ar'
                ? 'يجب أن يكون الشعار صورة'
                : 'Logo must be an image',
            'logo.mimes' => app()->getLocale() == 'ar'
                ? 'يجب أن يكون الشعار بصيغة: jpeg, jpg, png'
                : 'Logo must be a file of type: jpeg, jpg, png',
            'logo.max' => app()->getLocale() == 'ar'
                ? 'يجب أن يكون حجم الشعار أقل من 2 ميجابايت'
                : 'Logo size must be less than 2MB',
            'commercial_register.required' => app()->getLocale() == 'ar'
                ? 'السجل التجاري مطلوب'
                : 'Commercial register is required',
            'commercial_register.mimes' => app()->getLocale() == 'ar'
                ? 'يجب أن يكون السجل التجاري بصيغة: pdf, jpeg, jpg, png'
                : 'Commercial register must be a file of type: pdf, jpeg, jpg, png',
            'commercial_register.max' => app()->getLocale() == 'ar'
                ? 'يجب أن يكون حجم السجل التجاري أقل من 5 ميجابايت'
                : 'Commercial register size must be less than 5MB',
            'freelance_document.mimes' => app()->getLocale() == 'ar'
                ? 'يجب أن تكون وثيقة العمل الحر بصيغة: pdf, jpeg, jpg, png'
                : 'Freelance document must be a file of type: pdf, jpeg, jpg, png',
            'freelance_document.max' => app()->getLocale() == 'ar'
                ? 'يجب أن يكون حجم وثيقة العمل الحر أقل من 5 ميجابايت'
                : 'Freelance document size must be less than 5MB',
        ]);

        $data = Session::get('distributor_registration');

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($data['store_name']) . '.' . $logo->getClientOriginalExtension();
            $data['logo_path'] = $logo->storeAs('logos/distributors', $logoName, 'public');
        }

        // Handle commercial register upload
        if ($request->hasFile('commercial_register')) {
            $doc = $request->file('commercial_register');
            $docName = time() . '_commercial_' . Str::slug($data['store_name']) . '.' . $doc->getClientOriginalExtension();
            $data['commercial_register_path'] = $doc->storeAs('documents/distributors', $docName, 'public');
        }

        // Handle freelance document upload (optional)
        if ($request->hasFile('freelance_document')) {
            $doc = $request->file('freelance_document');
            $docName = time() . '_freelance_' . Str::slug($data['store_name']) . '.' . $doc->getClientOriginalExtension();
            $data['freelance_document_path'] = $doc->storeAs('documents/distributors', $docName, 'public');
        }

        Session::put('distributor_registration', $data);

        return redirect()->route('distributor.register.step3');
    }

    /**
     * Show Step 3: Currency & Social Media
     */
    public function showStep3()
    {
        if (!Session::has('distributor_registration')) {
            return redirect()->route('distributor.register.step1');
        }

        $currencies = Currency::active();

        return view('distributor.register.step3', compact('currencies'));
    }

    /**
     * Process Step 3 and go to Step 4
     */
    public function processStep3(Request $request)
    {
        $validated = $request->validate([
            'default_currency' => 'required|string|exists:currencies,code',
            'social_media' => 'nullable|array',
            'social_media.*.type' => 'nullable|string|in:facebook,instagram,twitter,tiktok,snapchat,youtube,whatsapp,telegram',
            'social_media.*.url' => 'nullable|url',
        ], [
            'default_currency.required' => app()->getLocale() == 'ar'
                ? 'العملة الافتراضية مطلوبة'
                : 'Default currency is required',
            'default_currency.exists' => app()->getLocale() == 'ar'
                ? 'العملة المختارة غير صالحة'
                : 'Selected currency is invalid',
            'social_media.*.url.url' => app()->getLocale() == 'ar'
                ? 'رابط حساب التواصل الاجتماعي غير صالح'
                : 'Social media URL is invalid',
        ]);

        $data = Session::get('distributor_registration');
        $data['default_currency'] = $validated['default_currency'];

        // Filter out empty social media entries - only include if both type and url are provided
        $socialMedia = [];
        if (!empty($request->social_media)) {
            foreach ($request->social_media as $social) {
                if (!empty($social['type']) && !empty($social['url'])) {
                    $socialMedia[] = [
                        'type' => $social['type'],
                        'url' => $social['url'],
                    ];
                }
            }
        }
        $data['social_media_accounts'] = $socialMedia;

        Session::put('distributor_registration', $data);

        return redirect()->route('distributor.register.step4');
    }

    /**
     * Show Step 4: Email Verification
     */
    public function showStep4()
    {
        if (!Session::has('distributor_registration')) {
            return redirect()->route('distributor.register.step1');
        }

        $email = Session::get('distributor_registration.email');

        // Generate and send OTP
        $this->sendOTP($email);

        return view('distributor.register.step4', compact('email'));
    }

    /**
     * Verify OTP and complete registration
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $data = Session::get('distributor_registration');
        $email = $data['email'];

        // Check OTP from session
        $storedOTP = Session::get('otp_' . $email);
        $otpExpiry = Session::get('otp_expiry_' . $email);

        if (!$storedOTP || !$otpExpiry) {
            return back()->withErrors(['otp' => app()->getLocale() == 'ar'
                ? 'انتهت صلاحية رمز التحقق أو غير موجود. يرجى طلب رمز جديد.'
                : 'OTP expired or not found. Please request a new one.']);
        }

        if (now()->gt($otpExpiry)) {
            return back()->withErrors(['otp' => app()->getLocale() == 'ar'
                ? 'انتهت صلاحية رمز التحقق. يرجى طلب رمز جديد.'
                : 'OTP has expired. Please request a new one.']);
        }

        if ($request->otp !== $storedOTP) {
            return back()->withErrors(['otp' => app()->getLocale() == 'ar'
                ? 'رمز التحقق غير صحيح.'
                : 'Invalid OTP code.']);
        }

        // Create user
        $user = User::create([
            'name' => $data['full_name'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'country' => $data['country'],
            'password' => Hash::make(Str::random(16)),
            'user_type' => 'distributor',
            'store_name' => $data['store_name'],
            'store_slug' => $data['store_slug'],
            'logo' => $data['logo_path'] ?? null,
            'commercial_register' => $data['commercial_register_path'] ?? null,
            'freelance_document' => $data['freelance_document_path'] ?? null,
            'default_currency' => $data['default_currency'],
            'social_media_accounts' => $data['social_media_accounts'] ?? [],
            'is_verified' => false, // Admin needs to verify distributor
            'email_verified_at' => now(),
        ]);

        // Clear session data
        Session::forget('distributor_registration');
        Session::forget('otp_' . $email);
        Session::forget('otp_expiry_' . $email);

        // Log the user in
        Auth::login($user);

        return redirect()->route('distributor.dashboard')->with('success', app()->getLocale() == 'ar'
            ? 'تم التسجيل بنجاح! حسابك قيد المراجعة من قبل الإدارة.'
            : 'Registration completed successfully! Your account is under review by admin.');
    }

    /**
     * Resend OTP
     */
    public function resendOTP()
    {
        $email = Session::get('distributor_registration.email');

        if (!$email) {
            return back()->withErrors(['email' => app()->getLocale() == 'ar'
                ? 'انتهت صلاحية الجلسة. يرجى بدء التسجيل من جديد.'
                : 'Session expired. Please start registration again.']);
        }

        $this->sendOTP($email);

        return back()->with('success', app()->getLocale() == 'ar'
            ? 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.'
            : 'OTP has been resent to your email.');
    }

    /**
     * Send OTP to email
     */
    private function sendOTP($email)
    {
        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in session (expires in 10 minutes)
        Session::put('otp_' . $email, $otp);
        Session::put('otp_expiry_' . $email, now()->addMinutes(10));

        // Send email with template
        Mail::send('emails.otp-verification', ['otp' => $otp], function($message) use ($email) {
            $message->to($email)
                    ->subject(app()->getLocale() == 'ar'
                        ? 'رمز التحقق - تسجيل المتجر'
                        : 'Email Verification Code - Distributor Registration');
        });

        // Send OTP via WhatsApp
        $phone = Session::get('distributor_registration.phone');
        if ($phone) {
            $isEnglish = app()->getLocale() !== 'ar';
            $whatsappService = new WhatsAppOTPService();
            $whatsappService->sendOTP($phone, $otp, $isEnglish);
        }
    }

    /**
     * Check if store slug is available (AJAX)
     */
    public function checkSlug(Request $request)
    {
        $slug = $request->get('slug');

        if (empty($slug)) {
            return response()->json(['available' => false]);
        }

        $exists = User::where('store_slug', $slug)->exists();

        return response()->json(['available' => !$exists]);
    }
}
