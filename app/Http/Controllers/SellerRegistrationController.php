<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class sSellerRegistrationController extends Controller
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
        ]);

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

        // Activities list in Arabic
        $activities = $this->getActivities();

        return view('seller.register.step2', compact('activities'));
    }

    /**
     * Process Step 2 and go to Step 3
     */
    public function processStep2(Request $request)
    {
        $validated = $request->validate([
            'main_activity' => 'required|string',
            'sub_activity' => 'required|string',
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
            'main_activity' => $data['main_activity'],
            'sub_activity' => $data['sub_activity'],
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

        // Send email
        Mail::raw("Your verification code is: $otp\n\nThis code will expire in 10 minutes.", function($message) use ($email) {
            $message->to($email)
                    ->subject('Email Verification Code - Seller Registration');
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
