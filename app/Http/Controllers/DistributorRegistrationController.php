<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Currency;
use App\Models\Subscription;
use App\Models\UserSubscription;
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
        $countries = \App\Models\Country::where('is_active', true)
            ->orderBy('name')
            ->get(['code', 'name', 'name_ar']);

        return view('distributor.register.step1', compact('countries'));
    }

    /**
     * Process Step 1 and go to Step 2
     */
    public function processStep1(Request $request)
    {
        $isAr = app()->getLocale() == 'ar';

        $validated = $request->validate([
            'full_name'            => 'required|string|max:255',
            'email'                => 'required|email|unique:users,email',
            'phone_code'           => 'required|string|max:5',
            'phone'                => 'required|string|max:20',
            'store_name'           => 'required|string|max:255',
            'store_slug'           => 'required|string|max:255|unique:users,store_slug|regex:/^[a-z0-9-]+$/',
            'password'             => 'required|string|min:8|confirmed',
            'store_type'           => 'required|in:online,physical,both',
            'country'              => 'required_if:store_type,physical|required_if:store_type,both|nullable|string|max:100',
            'store_region'         => 'required_if:store_type,physical|required_if:store_type,both|nullable|string|max:255',
            'store_address'        => 'required_if:store_type,physical|required_if:store_type,both|nullable|string|max:500',
            'store_map_url'        => 'required_if:store_type,physical|required_if:store_type,both|nullable|url|max:1000',
            'support_phone'        => 'required|string|max:30',
            'support_email'        => 'required|email|max:255',
            'working_hours'        => 'nullable|array',
            'working_hours.*.day'  => 'nullable|string',
            'working_hours.*.open' => 'nullable|string',
            'working_hours.*.close'=> 'nullable|string',
            'g-recaptcha-response' => config('services.recaptcha.enabled') ? 'required' : 'nullable',
        ], [
            'g-recaptcha-response.required' => $isAr ? 'يرجى التحقق من أنك لست روبوت' : 'Please verify that you are not a robot',
            'store_slug.regex'     => $isAr ? 'رابط المتجر يجب أن يحتوي على أحرف إنجليزية صغيرة وأرقام وشرطات فقط' : 'Store URL must contain only lowercase letters, numbers and dashes',
            'store_slug.unique'    => $isAr ? 'رابط المتجر مستخدم بالفعل' : 'Store URL is already taken',
            'store_type.required'  => $isAr ? 'نوع المتجر مطلوب' : 'Store type is required',
            'store_type.in'        => $isAr ? 'نوع المتجر غير صالح' : 'Invalid store type',
            'country.required_if'  => $isAr ? 'الدولة مطلوبة للمتجر الفعلي' : 'Country is required for physical store',
            'store_region.required_if' => $isAr ? 'المنطقة مطلوبة للمتجر الفعلي' : 'Region is required for physical store',
            'store_address.required_if'=> $isAr ? 'العنوان مطلوب للمتجر الفعلي' : 'Address is required for physical store',
            'store_map_url.required_if'=> $isAr ? 'رابط خرائط قوقل مطلوب للمتجر الفعلي' : 'Google Maps URL is required for physical store',
            'store_map_url.url'    => $isAr ? 'رابط خرائط قوقل غير صالح' : 'Invalid Google Maps URL',
            'support_phone.required'=> $isAr ? 'رقم هاتف الدعم مطلوب' : 'Support phone is required',
            'support_email.required'=> $isAr ? 'بريد الدعم الإلكتروني مطلوب' : 'Support email is required',
            'phone_code.required'  => $isAr ? 'رمز الدولة مطلوب' : 'Country code is required',
            'phone.required'       => $isAr ? 'رقم الهاتف مطلوب' : 'Phone number is required',
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

        // Combine phone_code + phone for DB storage (e.g. 971501234567)
        $validated['phone'] = $validated['phone_code'] . $validated['phone'];
        $validated['phone_code'] = '+' . $validated['phone_code'];

        // Build working_hours: only include days that are enabled
        $workingHours = [];
        if (!empty($request->working_hours)) {
            foreach ($request->working_hours as $entry) {
                if (!empty($entry['enabled'])) {
                    $workingHours[] = [
                        'day'   => $entry['day'],
                        'open'  => $entry['open'] ?? '09:00',
                        'close' => $entry['close'] ?? '18:00',
                    ];
                }
            }
        }
        $validated['working_hours'] = $workingHours;

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
     * Verify Email OTP and proceed to WhatsApp verification
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

        // Mark email as verified in session
        Session::put('distributor_email_verified', true);
        Session::forget('otp_' . $email);
        Session::forget('otp_expiry_' . $email);

        return redirect()->route('distributor.register.step5');
    }

    /**
     * Show Step 5: WhatsApp Verification
     */
    public function showStep5()
    {
        if (!Session::has('distributor_registration') || !Session::get('distributor_email_verified')) {
            return redirect()->route('distributor.register.step1');
        }

        $phone = Session::get('distributor_registration.phone');

        // Generate and send WhatsApp OTP
        $this->sendWhatsAppOTP($phone);

        return view('distributor.register.step5', compact('phone'));
    }

    /**
     * Verify WhatsApp OTP and complete registration
     */
    public function verifyWhatsAppOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $data = Session::get('distributor_registration');
        $phone = $data['phone'];

        $storedOTP = Session::get('whatsapp_otp_' . $phone);
        $otpExpiry = Session::get('whatsapp_otp_expiry_' . $phone);

        if (!$storedOTP || !$otpExpiry) {
            return back()->withErrors(['otp' => app()->getLocale() == 'ar'
                ? 'انتهت صلاحية رمز التحقق. يرجى طلب رمز جديد.'
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
            'name'                  => $data['full_name'],
            'full_name'             => $data['full_name'],
            'email'                 => $data['email'],
            'phone'                 => $data['phone'],
            'phone_code'            => $data['phone_code'] ?? '+971',
            'country'               => $data['country'] ?? null,
            'password'              => Hash::make($data['password'] ?? Str::random(16)),
            'user_type'             => 'distributor',
            'store_name'            => $data['store_name'],
            'store_slug'            => $data['store_slug'],
            'store_type'            => $data['store_type'] ?? 'online',
            'store_region'          => $data['store_region'] ?? null,
            'store_address'         => $data['store_address'] ?? null,
            'store_map_url'         => $data['store_map_url'] ?? null,
            'support_phone'         => $data['support_phone'] ?? null,
            'support_email'         => $data['support_email'] ?? null,
            'working_hours'         => $data['working_hours'] ?? [],
            'logo'                  => $data['logo_path'] ?? null,
            'commercial_register'   => $data['commercial_register_path'] ?? null,
            'freelance_document'    => $data['freelance_document_path'] ?? null,
            'default_currency'      => $data['default_currency'],
            'social_media_accounts' => $data['social_media_accounts'] ?? [],
            'is_verified'           => false,
            'email_verified_at'     => now(),
        ]);

        // Create 24-hour free trial subscription
        $this->createTrialSubscription($user);

        // Clear session data
        Session::forget('distributor_registration');
        Session::forget('distributor_email_verified');
        Session::forget('whatsapp_otp_' . $phone);
        Session::forget('whatsapp_otp_expiry_' . $phone);

        // Log the user in
        Auth::login($user);

        return redirect()->route('distributor.dashboard')->with('success', app()->getLocale() == 'ar'
            ? 'تم التسجيل بنجاح! حسابك قيد المراجعة من قبل الإدارة.'
            : 'Registration completed successfully! Your account is under review by admin.');
    }

    /**
     * Resend Email OTP
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
            ? 'تم إعادة إرسال رمز التحقق إلى بريدك الإلكتروني.'
            : 'OTP has been resent to your email.');
    }

    /**
     * Resend WhatsApp OTP
     */
    public function resendWhatsAppOTP()
    {
        $phone = Session::get('distributor_registration.phone');

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
     * Create a 24-hour free trial subscription for new distributor
     */
    private function createTrialSubscription(User $user): void
    {
        $subscription = Subscription::where('is_active', true)->orderBy('sort_order')->first();

        UserSubscription::create([
            'user_id'         => $user->id,
            'subscription_id' => $subscription?->id,
            'start_date'      => now()->toDateString(),
            'end_date'        => now()->addDay()->toDateString(),
            'status'          => 'active',
            'amount_paid'     => 0.00,
            'payment_method'  => 'trial',
            'transaction_id'  => 'TRIAL-' . strtoupper(Str::random(10)),
        ]);
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
