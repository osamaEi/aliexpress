<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
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
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => app()->getLocale() == 'ar'
                ? 'يرجى التحقق من أنك لست روبوت'
                : 'Please verify that you are not a robot',
            'logo.image' => app()->getLocale() == 'ar'
                ? 'يجب أن يكون الملف صورة'
                : 'The file must be an image',
            'logo.mimes' => app()->getLocale() == 'ar'
                ? 'يجب أن تكون الصورة بصيغة: jpeg, jpg, png'
                : 'The image must be a file of type: jpeg, jpg, png',
            'logo.max' => app()->getLocale() == 'ar'
                ? 'يجب أن يكون حجم الصورة أقل من 2 ميجابايت'
                : 'The image size must be less than 2MB',
        ]);

        // Verify reCAPTCHA v3 (skip in local development if needed)
        if (env('APP_ENV') !== 'local' || env('RECAPTCHA_ENABLED', true)) {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            $recaptchaSecret = env('RECAPTCHA_SECRET_KEY');

            $response = file_get_contents(
                "https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}"
            );

            $responseData = json_decode($response);

            // For reCAPTCHA v3, check success and score (0.0 to 1.0, higher is better)
            // Score of 0.5 or above is typically considered human
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

        // Remove reCAPTCHA and logo file from validated data (can't be serialized in session)
        unset($validated['g-recaptcha-response']);
        unset($validated['logo']);

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
        $validated = $request->validate([
            'main_categories' => 'required|array|min:1',
            'main_categories.*' => 'exists:categories,id',
            'sub_categories' => 'required|array|min:1',
            'sub_categories.*' => 'exists:categories,id',
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

        // Generate and send OTP
        $this->sendOTP($email);

        return view('seller.register.step3', compact('email'));
    }

    /**
     * Verify OTP and complete registration
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $data = Session::get('seller_registration');
        $email = $data['email'];

        // Find temporary OTP record or check session
        $storedOTP = Session::get('otp_' . $email);
        $otpExpiry = Session::get('otp_expiry_' . $email);

        if (!$storedOTP || !$otpExpiry) {
            return back()->withErrors(['otp' => 'OTP expired or not found. Please request a new one.']);
        }

        if (now()->gt($otpExpiry)) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        if ($request->otp !== $storedOTP) {
            return back()->withErrors(['otp' => 'Invalid OTP code.']);
        }

        // Create user
        $user = User::create([
            'name' => $data['full_name'],
            'full_name' => $data['full_name'],
            'company_name' => $data['company_name'],
            'country' => $data['country'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(16)), // Random password, will use OTP for login
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
        Session::forget('otp_' . $email);
        Session::forget('otp_expiry_' . $email);

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registration completed successfully!');
    }

    /**
     * Resend OTP
     */
    public function resendOTP()
    {
        $email = Session::get('seller_registration.email');

        if (!$email) {
            return back()->withErrors(['email' => 'Session expired. Please start registration again.']);
        }

        $this->sendOTP($email);

        return back()->with('success', 'OTP has been resent to your email.');
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

        // Send email with beautiful template
        Mail::send('emails.otp-verification', ['otp' => $otp], function($message) use ($email) {
            $message->to($email)
                    ->subject(app()->getLocale() == 'ar'
                        ? 'رمز التحقق - تسجيل البائع'
                        : 'Email Verification Code - Seller Registration');
        });
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
