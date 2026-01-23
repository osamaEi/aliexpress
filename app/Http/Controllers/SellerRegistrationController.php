<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Services\WhatsAppOTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class SellerRegistrationController extends Controller
{
    /**
     * Show Step 1: Basic Information
     */
    public function showStep1()
    {
        return view('seller.register.step1');
    }

    /**
     * Process Step 1 and go to Step 2
     */
    public function processStep1(Request $request)
    {
        $isAr = app()->getLocale() == 'ar';

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'phone_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'g-recaptcha-response' => config('services.recaptcha.enabled') ? 'required' : 'nullable',
        ], [
            'full_name.required' => $isAr ? 'الاسم الكامل مطلوب' : 'Full name is required',
            'full_name.max' => $isAr ? 'الاسم الكامل يجب ألا يزيد عن 255 حرف' : 'Full name must not exceed 255 characters',
            'company_name.required' => $isAr ? 'اسم الشركة مطلوب' : 'Company name is required',
            'company_name.max' => $isAr ? 'اسم الشركة يجب ألا يزيد عن 255 حرف' : 'Company name must not exceed 255 characters',
            'country.required' => $isAr ? 'يرجى اختيار الدولة' : 'Please select a country',
            'phone_code.required' => $isAr ? 'رمز الدولة مطلوب' : 'Country code is required',
            'phone.required' => $isAr ? 'رقم الهاتف مطلوب' : 'Phone number is required',
            'phone.max' => $isAr ? 'رقم الهاتف يجب ألا يزيد عن 20 رقم' : 'Phone number must not exceed 20 digits',
            'email.required' => $isAr ? 'البريد الإلكتروني مطلوب' : 'Email address is required',
            'email.email' => $isAr ? 'يرجى إدخال بريد إلكتروني صحيح' : 'Please enter a valid email address',
            'email.unique' => $isAr ? 'البريد الإلكتروني مسجل بالفعل' : 'This email is already registered',
            'password.required' => $isAr ? 'كلمة المرور مطلوبة' : 'Password is required',
            'password.min' => $isAr ? 'كلمة المرور يجب أن تكون 8 أحرف على الأقل' : 'Password must be at least 8 characters',
            'password.confirmed' => $isAr ? 'تأكيد كلمة المرور غير متطابق' : 'Password confirmation does not match',
            'logo.image' => $isAr ? 'يجب أن يكون الملف صورة' : 'The file must be an image',
            'logo.mimes' => $isAr ? 'يجب أن تكون الصورة بصيغة: jpeg, jpg, png' : 'The image must be a file of type: jpeg, jpg, png',
            'logo.max' => $isAr ? 'يجب أن يكون حجم الصورة أقل من 2 ميجابايت' : 'The image size must be less than 2MB',
            'g-recaptcha-response.required' => $isAr ? 'يرجى التحقق من أنك لست روبوت' : 'Please verify that you are not a robot',
        ]);

        // Verify reCAPTCHA v3
        if (config('services.recaptcha.enabled')) {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            $recaptchaSecret = config('services.recaptcha.secret_key');

            $response = file_get_contents(
                "https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}"
            );

            $responseData = json_decode($response);

            // For reCAPTCHA v3, check success and score (0.5+ is human)
            if (!$responseData->success || (isset($responseData->score) && $responseData->score < 0.5)) {
                return back()->withErrors([
                    'g-recaptcha-response' => app()->getLocale() == 'ar'
                        ? 'فشل التحقق من reCAPTCHA. يرجى المحاولة مرة أخرى.'
                        : 'reCAPTCHA verification failed. Please try again.'
                ])->withInput();
            }
        }

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($validated['company_name']) . '.' . $logo->getClientOriginalExtension();
            $logoPath = $logo->storeAs('logos/sellers', $logoName, 'public');
        }

        // Combine phone_code + phone for DB storage (e.g. 971501234567)
        $validated['phone'] = $validated['phone_code'] . $validated['phone'];
        $validated['phone_code'] = '+' . $validated['phone_code'];

        // Remove reCAPTCHA, logo file, and password_confirmation from validated data
        unset($validated['g-recaptcha-response']);
        unset($validated['logo']);
        unset($validated['password_confirmation']);

        // Add logo path to validated data
        if ($logoPath) {
            $validated['logo_path'] = $logoPath;
        }

        // Store data in session
        Session::put('seller_registration', $validated);

        return redirect()->route('seller.register.step2');
    }

    /**
     * Show Step 2: Activity Selection
     */
    public function showStep2()
    {
        if (!Session::has('seller_registration')) {
            return redirect()->route('seller.register.step1');
        }

        // Get main categories (parent categories)
        $mainCategories = Category::active()
            ->root()
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // Get all subcategories grouped by parent
        $subCategories = [];
        foreach ($mainCategories as $category) {
            $subCategories[$category->id] = $category->children()
                ->active()
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        }

        return view('seller.register.step2', compact('mainCategories', 'subCategories'));
    }

    /**
     * Process Step 2 and go to Step 3
     */
    public function processStep2(Request $request)
    {
        $isAr = app()->getLocale() == 'ar';

        $validated = $request->validate([
            'main_categories' => 'required|array|min:1',
            'main_categories.*' => 'exists:categories,id',
            'sub_categories' => 'required|array|min:1',
            'sub_categories.*' => 'exists:categories,id',
        ], [
            'main_categories.required' => $isAr ? 'يرجى اختيار فئة رئيسية واحدة على الأقل' : 'Please select at least one main category',
            'main_categories.min' => $isAr ? 'يرجى اختيار فئة رئيسية واحدة على الأقل' : 'Please select at least one main category',
            'main_categories.*.exists' => $isAr ? 'الفئة المختارة غير صالحة' : 'The selected category is invalid',
            'sub_categories.required' => $isAr ? 'يرجى اختيار فئة فرعية واحدة على الأقل' : 'Please select at least one subcategory',
            'sub_categories.min' => $isAr ? 'يرجى اختيار فئة فرعية واحدة على الأقل' : 'Please select at least one subcategory',
            'sub_categories.*.exists' => $isAr ? 'الفئة الفرعية المختارة غير صالحة' : 'The selected subcategory is invalid',
        ]);

        // Merge with existing session data
        $data = Session::get('seller_registration');
        $data = array_merge($data, $validated);
        Session::put('seller_registration', $data);

        return redirect()->route('seller.register.step3');
    }

    /**
     * Show Step 3: Email Verification
     */
    public function showStep3()
    {
        if (!Session::has('seller_registration')) {
            return redirect()->route('seller.register.step1');
        }

        $email = Session::get('seller_registration.email');

        // Generate and send email OTP
        $this->sendEmailOTP($email);

        return view('seller.register.step3', compact('email'));
    }

    /**
     * Verify Email OTP and go to Step 4
     */
    public function verifyOTP(Request $request)
    {
        $isAr = app()->getLocale() == 'ar';

        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => $isAr ? 'رمز التحقق مطلوب' : 'Verification code is required',
            'otp.size' => $isAr ? 'رمز التحقق يجب أن يكون 6 أرقام' : 'Verification code must be 6 digits',
        ]);

        $data = Session::get('seller_registration');
        $email = $data['email'];

        // Find temporary OTP record or check session
        $storedOTP = Session::get('otp_' . $email);
        $otpExpiry = Session::get('otp_expiry_' . $email);

        if (!$storedOTP || !$otpExpiry) {
            return back()->withErrors(['otp' => app()->getLocale() == 'ar'
                ? 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.'
                : 'OTP expired or not found. Please request a new one.']);
        }

        if (now()->gt($otpExpiry)) {
            return back()->withErrors(['otp' => app()->getLocale() == 'ar'
                ? 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.'
                : 'OTP has expired. Please request a new one.']);
        }

        if ($request->otp !== $storedOTP) {
            return back()->withErrors(['otp' => app()->getLocale() == 'ar'
                ? 'رمز التحقق غير صحيح.'
                : 'Invalid OTP code.']);
        }

        // Mark email as verified in session
        Session::put('seller_email_verified', true);
        Session::forget('otp_' . $email);
        Session::forget('otp_expiry_' . $email);

        return redirect()->route('seller.register.step4');
    }

    /**
     * Show Step 4: WhatsApp Verification
     */
    public function showStep4()
    {
        if (!Session::has('seller_registration') || !Session::get('seller_email_verified')) {
            return redirect()->route('seller.register.step1');
        }

        $phone = Session::get('seller_registration.phone');

        // Generate and send WhatsApp OTP
        $this->sendWhatsAppOTP($phone);

        return view('seller.register.step4', compact('phone'));
    }

    /**
     * Verify WhatsApp OTP and complete registration
     */
    public function verifyWhatsAppOTP(Request $request)
    {
        $isAr = app()->getLocale() == 'ar';

        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => $isAr ? 'رمز التحقق مطلوب' : 'Verification code is required',
            'otp.size' => $isAr ? 'رمز التحقق يجب أن يكون 6 أرقام' : 'Verification code must be 6 digits',
        ]);

        $data = Session::get('seller_registration');
        $phone = $data['phone'];

        $storedOTP = Session::get('whatsapp_otp_' . $phone);
        $otpExpiry = Session::get('whatsapp_otp_expiry_' . $phone);

        if (!$storedOTP || !$otpExpiry) {
            return back()->withErrors(['otp' => app()->getLocale() == 'ar'
                ? 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.'
                : 'OTP expired or not found. Please request a new one.']);
        }

        if (now()->gt($otpExpiry)) {
            return back()->withErrors(['otp' => app()->getLocale() == 'ar'
                ? 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.'
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
            'company_name' => $data['company_name'],
            'country' => $data['country'],
            'phone' => $data['phone'] ?? null,
            'phone_code' => $data['phone_code'] ?? '+971',
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'user_type' => 'seller',
            'main_activity' => json_encode($data['main_categories']),
            'sub_activity' => json_encode($data['sub_categories']),
            'logo' => $data['logo_path'] ?? null,
            'is_verified' => true,
            'verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        // Clear session data
        Session::forget('seller_registration');
        Session::forget('seller_email_verified');
        Session::forget('whatsapp_otp_' . $phone);
        Session::forget('whatsapp_otp_expiry_' . $phone);

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', app()->getLocale() == 'ar'
            ? 'تم التسجيل بنجاح!'
            : 'Registration completed successfully!');
    }

    /**
     * Resend Email OTP
     */
    public function resendOTP()
    {
        $email = Session::get('seller_registration.email');

        if (!$email) {
            return back()->withErrors(['email' => app()->getLocale() == 'ar'
                ? 'انتهت صلاحية الجلسة. يرجى بدء التسجيل من جديد.'
                : 'Session expired. Please start registration again.']);
        }

        $this->sendEmailOTP($email);

        return back()->with('success', app()->getLocale() == 'ar'
            ? 'تم إعادة إرسال رمز التحقق إلى بريدك الإلكتروني.'
            : 'OTP has been resent to your email.');
    }

    /**
     * Resend WhatsApp OTP
     */
    public function resendWhatsAppOTP()
    {
        $phone = Session::get('seller_registration.phone');

        if (!$phone) {
            return back()->withErrors(['phone' => app()->getLocale() == 'ar'
                ? 'انتهت صلاحية الجلسة. يرجى بدء التسجيل من جديد.'
                : 'Session expired. Please start registration again.']);
        }

        $this->sendWhatsAppOTP($phone);

        return back()->with('success', app()->getLocale() == 'ar'
            ? 'تم إعادة إرسال رمز التحقق عبر واتساب.'
            : 'OTP has been resent via WhatsApp.');
    }

    /**
     * Send OTP to email
     */
    private function sendEmailOTP($email)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Session::put('otp_' . $email, $otp);
        Session::put('otp_expiry_' . $email, now()->addMinutes(10));

        Mail::send('emails.otp-verification', ['otp' => $otp], function($message) use ($email) {
            $message->to($email)
                    ->subject(app()->getLocale() == 'ar'
                        ? 'رمز التحقق - تسجيل البائع'
                        : 'Email Verification Code - Seller Registration');
        });
    }

    /**
     * Send OTP via WhatsApp
     */
    private function sendWhatsAppOTP($phone)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Session::put('whatsapp_otp_' . $phone, $otp);
        Session::put('whatsapp_otp_expiry_' . $phone, now()->addMinutes(10));

        // Phone is already the full number (code + local), send directly
        $isEnglish = app()->getLocale() !== 'ar';
        $whatsappService = new WhatsAppOTPService();
        $whatsappService->sendOTP($phone, $otp, $isEnglish);
    }

    /**
     * Get activities list in Arabic
     */
    private function getActivities()
    {
        return [
            'إلكترونيات' => [
                'هواتف محمولة',
                'أجهزة كمبيوتر',
                'كاميرات',
                'أجهزة صوتية',
                'ملحقات إلكترونية',
            ],
            'أزياء وملابس' => [
                'ملابس رجالية',
                'ملابس نسائية',
                'ملابس أطفال',
                'أحذية',
                'إكسسوارات',
            ],
            'منزل ومطبخ' => [
                'أثاث',
                'ديكور',
                'أدوات مطبخ',
                'أجهزة منزلية',
                'مفروشات',
            ],
            'رياضة ولياقة' => [
                'معدات رياضية',
                'ملابس رياضية',
                'مكملات غذائية',
                'دراجات',
            ],
            'جمال وعناية شخصية' => [
                'مستحضرات تجميل',
                'عطور',
                'عناية بالبشرة',
                'عناية بالشعر',
            ],
            'ألعاب وهوايات' => [
                'ألعاب أطفال',
                'ألعاب إلكترونية',
                'فنون وحرف',
                'كتب',
            ],
            'سيارات وإكسسوارات' => [
                'قطع غيار',
                'إكسسوارات سيارات',
                'أدوات صيانة',
            ],
            'أخرى' => [
                'منتجات متنوعة',
            ],
        ];
    }
}
