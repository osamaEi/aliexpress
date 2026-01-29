<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() == 'ar' ? 'تسجيل متجر جديد - الخطوة 1' : 'Distributor Registration - Step 1' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @if(config('services.recaptcha.enabled'))
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endif
    <style>
        :root {
            --primary-color: #561C04;
            --secondary-color: #7a2805;
            --success-color: #10b981;
            --distributor-color: #561C04;
            --distributor-secondary: #7a2805;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom right, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .main-container {
            max-width: 1100px;
            width: 100%;
            display: grid;
            grid-template-columns: 400px 1fr;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(86, 28, 4, 0.12);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar {
            background: linear-gradient(135deg, var(--distributor-color) 0%, var(--distributor-secondary) 100%);
            padding: 50px 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            top: -80px;
            left: -80px;
        }

        .sidebar::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -60px;
            right: -60px;
        }

        .logo-wrapper {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            z-index: 2;
        }

        .logo-wrapper img {
            max-width: 160px;
            height: auto;
            margin-bottom: 20px;
        }

        .logo-wrapper h3 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .progress-sidebar {
            position: relative;
            z-index: 2;
        }

        .progress-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            position: relative;
        }

        .progress-step::before {
            content: '';
            position: absolute;
            {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 22px;
            top: 50px;
            width: 2px;
            height: calc(100% + 30px);
            background: rgba(255, 255, 255, 0.2);
        }

        .progress-step:last-child::before {
            display: none;
        }

        .step-number {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
            {{ app()->getLocale() == 'ar' ? 'margin-left' : 'margin-right' }}: 15px;
            transition: all 0.3s;
        }

        .progress-step.active .step-number {
            background: white;
            color: var(--distributor-color);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        .progress-step.completed .step-number {
            background: #10b981;
            color: white;
        }

        .step-content h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .step-content p {
            font-size: 13px;
            opacity: 0.8;
            margin: 0;
        }

        .progress-step.active .step-content {
            opacity: 1;
        }

        .progress-step:not(.active) .step-content {
            opacity: 0.6;
        }

        .content-area {
            padding: 50px 60px;
        }

        .content-header {
            margin-bottom: 40px;
        }

        .content-header h2 {
            font-size: 30px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .content-header p {
            color: #666;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .form-label i {
            {{ app()->getLocale() == 'ar' ? 'margin-left' : 'margin-right' }}: 6px;
            color: var(--distributor-color);
        }

        .form-label .required {
            color: #ef4444;
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 4px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 13px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background: white;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--distributor-color);
            box-shadow: 0 0 0 4px rgba(86, 28, 4, 0.08);
        }

        .form-control.is-invalid {
            border-color: #ef4444;
        }

        .text-danger {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
        }

        .input-group {
            position: relative;
        }

        .input-group-text {
            position: absolute;
            {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 14px;
            background: #f3f4f6;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .store-slug-input {
            {{ app()->getLocale() == 'ar' ? 'padding-left' : 'padding-right' }}: 160px;
        }

        .slug-status {
            font-size: 12px;
            margin-top: 6px;
        }

        .slug-status.available {
            color: #10b981;
        }

        .slug-status.taken {
            color: #ef4444;
        }

        .btn-continue {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--distributor-color) 0%, var(--distributor-secondary) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(86, 28, 4, 0.25);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: var(--distributor-color);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: none;
        }

        .alert-danger {
            background: #fee;
            color: #c00;
        }

        @media (max-width: 992px) {
            .main-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                padding: 30px 25px;
            }

            .progress-sidebar {
                display: flex;
                gap: 20px;
                overflow-x: auto;
            }

            .progress-step {
                flex-direction: column;
                align-items: center;
                text-align: center;
                margin-bottom: 0;
                min-width: 100px;
            }

            .progress-step::before {
                display: none;
            }

            .step-number {
                margin-left: 0;
                margin-right: 0;
                margin-bottom: 10px;
            }

            .content-area {
                padding: 30px 25px;
            }
        }

        .language-switcher {
            position: absolute;
            top: 20px;
            {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
            background: white;
            padding: 8px 12px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .lang-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 2px solid transparent;
            border-radius: 50px;
            background: transparent;
            color: #666;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .lang-btn:hover {
            background: #f8f9fa;
            color: var(--distributor-color);
        }

        .lang-btn.active {
            background: linear-gradient(135deg, var(--distributor-color) 0%, var(--distributor-secondary) 100%);
            color: white;
            border-color: var(--distributor-color);
        }

        .lang-btn img {
            width: 20px;
            height: 20px;
        }

        .sidebar-footer {
            margin-top: 30px;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .sidebar-footer img {
            max-width: 200px;
            height: auto;
            opacity: 0.9;
        }

        .grecaptcha-badge {
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 9999 !important;
            position: fixed !important;
            bottom: 14px !important;
            right: 14px !important;
        }

        .distributor-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 50px;
            margin-top: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="language-switcher">
        <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">
            <img src="https://flagcdn.com/w20/gb.png" alt="English" style="width: 20px; height: 15px;">
            <span>English</span>
        </a>
        <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">
            <img src="https://flagcdn.com/w20/ae.png" alt="العربية" style="width: 20px; height: 15px;">
            <span>العربية</span>
        </a>
    </div>

    <div class="main-container">
        <div class="sidebar">
            <div class="logo-wrapper">
                <img src="{{ asset('logo/logo10.png') }}" alt="Logo">
              
                <h3>{{ app()->getLocale() == 'ar' ? 'تسجيل متجر جديد' : 'Distributor Registration' }}</h3>
                <div class="distributor-badge">
                    <i class="ri-truck-line"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'حساب متجر' : 'Distributor Account' }}</span>
                </div>
            </div>

            <div class="progress-sidebar">
                <div class="progress-step active">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'المعلومات الأساسية' : 'Basic Information' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'بيانات المتجر والتواصل' : 'Store and contact details' }}</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'المستندات' : 'Documents' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'السجل التجاري والشعار' : 'Commercial register & logo' }}</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'الإعدادات' : 'Settings' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'العملة والتواصل الاجتماعي' : 'Currency & social media' }}</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'التحقق' : 'Verification' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'تأكيد البريد الإلكتروني' : 'Email confirmation' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-area">
            <div class="content-header">
                <h2>{{ app()->getLocale() == 'ar' ? 'مرحباً بك!' : 'Welcome!' }}</h2>
                <p>{{ app()->getLocale() == 'ar' ? 'ابدأ رحلتك كمتجر معنا بتعبئة المعلومات الأساسية' : 'Start your journey as a distributor by filling basic information' }}</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    <strong>{{ app()->getLocale() == 'ar' ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}</strong>
                    <ul class="mb-0 mt-2" style="padding-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('distributor.register.step1.process') }}" method="POST" id="registrationForm">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="full_name" class="form-label">
                                <i class="ri-user-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'الاسم الكامل' : 'Full Name' }}
                                <span class="required">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('full_name') is-invalid @enderror"
                                   id="full_name"
                                   name="full_name"
                                   value="{{ old('full_name') }}"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل اسمك الكامل' : 'Enter your full name' }}"
                                   required>
                            @error('full_name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone" class="form-label">
                                <i class="ri-phone-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'رقم الهاتف (واتساب)' : 'Phone Number (WhatsApp)' }}
                                <span class="required">*</span>
                            </label>
                            <div style="position: relative;">
                                <div class="phone-input-wrapper" style="display: flex; align-items: center; border: 2px solid #e5e7eb; border-radius: 10px; overflow: hidden; transition: all 0.3s; direction: ltr;" id="phoneInputWrapper">
                                    <div class="phone-code-trigger" id="phoneCodeSelected" style="display: flex; align-items: center; gap: 6px; padding: 13px 12px; cursor: pointer; background: #f9fafb; border-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 2px solid #e5e7eb; flex-shrink: 0;">
                                        <img id="selectedFlag" src="https://flagcdn.com/w20/ae.png" style="width: 20px; height: 15px; border-radius: 2px;">
                                        <span id="selectedCode" style="font-size: 14px; font-weight: 500; color: #333;">+971</span>
                                        <i class="ri-arrow-down-s-line" style="font-size: 14px; color: #999;"></i>
                                    </div>
                                    <input type="text"
                                           class="@error('phone') is-invalid @enderror"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone') }}"
                                           placeholder="{{ app()->getLocale() == 'ar' ? 'رقم الهاتف' : 'Phone number' }}"
                                           style="border: none; outline: none; padding: 13px 15px; flex: 1; font-size: 15px; direction: ltr; text-align: left; width: 100%;"
                                           required>
                                </div>
                                <div class="phone-code-dropdown" id="phoneCodeDropdown" style="display: none; position: absolute; top: 100%; {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0; width: 200px; background: white; border: 2px solid #e5e7eb; border-radius: 10px; margin-top: 4px; max-height: 250px; overflow-y: auto; z-index: 100; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                                    @php
                                        $phoneCodes = [
                                            ['code' => '971', 'flag' => 'ae', 'name' => 'UAE'],
                                            ['code' => '966', 'flag' => 'sa', 'name' => 'KSA'],
                                            ['code' => '20', 'flag' => 'eg', 'name' => 'Egypt'],
                                            ['code' => '965', 'flag' => 'kw', 'name' => 'Kuwait'],
                                            ['code' => '974', 'flag' => 'qa', 'name' => 'Qatar'],
                                            ['code' => '973', 'flag' => 'bh', 'name' => 'Bahrain'],
                                            ['code' => '968', 'flag' => 'om', 'name' => 'Oman'],
                                            ['code' => '962', 'flag' => 'jo', 'name' => 'Jordan'],
                                            ['code' => '961', 'flag' => 'lb', 'name' => 'Lebanon'],
                                            ['code' => '963', 'flag' => 'sy', 'name' => 'Syria'],
                                            ['code' => '970', 'flag' => 'ps', 'name' => 'Palestine'],
                                            ['code' => '964', 'flag' => 'iq', 'name' => 'Iraq'],
                                            ['code' => '218', 'flag' => 'ly', 'name' => 'Libya'],
                                            ['code' => '216', 'flag' => 'tn', 'name' => 'Tunisia'],
                                            ['code' => '213', 'flag' => 'dz', 'name' => 'Algeria'],
                                            ['code' => '212', 'flag' => 'ma', 'name' => 'Morocco'],
                                            ['code' => '249', 'flag' => 'sd', 'name' => 'Sudan'],
                                            ['code' => '967', 'flag' => 'ye', 'name' => 'Yemen'],
                                        ];
                                    @endphp
                                    @foreach($phoneCodes as $country)
                                        <div class="phone-code-option" data-code="{{ $country['code'] }}" data-flag="{{ $country['flag'] }}" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; cursor: pointer; transition: background 0.2s;">
                                            <img src="https://flagcdn.com/w20/{{ $country['flag'] }}.png" style="width: 20px; height: 15px; border-radius: 2px;">
                                            <span>+{{ $country['code'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="phone_code" id="phone_code" value="{{ old('phone_code', '971') }}">
                            </div>
                            @error('phone')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">{{ app()->getLocale() == 'ar' ? 'سنرسل رمز التحقق عبر واتساب' : 'We will send verification code via WhatsApp' }}</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="store_name" class="form-label">
                                <i class="ri-store-2-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'اسم المتجر' : 'Store Name' }}
                                <span class="required">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('store_name') is-invalid @enderror"
                                   id="store_name"
                                   name="store_name"
                                   value="{{ old('store_name') }}"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل اسم متجرك' : 'Enter your store name' }}"
                                   required>
                            @error('store_name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="store_slug" class="form-label">
                                <i class="ri-link"></i>
                                {{ app()->getLocale() == 'ar' ? 'رابط المتجر' : 'Store URL' }}
                                <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control store-slug-input @error('store_slug') is-invalid @enderror"
                                       id="store_slug"
                                       name="store_slug"
                                       value="{{ old('store_slug') }}"
                                       placeholder="{{ app()->getLocale() == 'ar' ? 'اسم-المتجر' : 'store-name' }}"
                                       pattern="[a-z0-9-]+"
                                       required>
                                <span class="input-group-text">.selaa.com</span>
                            </div>
                            <div id="slugStatus" class="slug-status"></div>
                            @error('store_slug')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">
                                {{ app()->getLocale() == 'ar' ? 'أحرف إنجليزية صغيرة وأرقام وشرطات فقط' : 'Lowercase letters, numbers and dashes only' }}
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country" class="form-label">
                                <i class="ri-map-pin-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }}
                                <span class="required">*</span>
                            </label>
                            <div style="position: relative;">
                                <div class="country-select-trigger" id="countrySelectTrigger" style="display: flex; align-items: center; gap: 10px; padding: 13px 18px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; background: white; transition: all 0.3s; font-size: 15px;">
                                    <img id="countryFlag" src="" style="width: 22px; height: 16px; border-radius: 2px; object-fit: cover; display: none;">
                                    <span id="countryName" style="flex: 1; color: #999;">{{ app()->getLocale() == 'ar' ? 'اختر الدولة' : 'Select Country' }}</span>
                                    <i class="ri-arrow-down-s-line" style="font-size: 16px; color: #999;"></i>
                                </div>
                                <div class="country-dropdown" id="countryDropdown" style="display: none; position: absolute; top: 100%; {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0; {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 0; background: white; border: 2px solid #e5e7eb; border-radius: 10px; margin-top: 4px; max-height: 250px; overflow-y: auto; z-index: 100; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                                    @php
                                        $countries = [
                                            ['value' => 'AE', 'flag' => 'ae', 'name_ar' => 'الإمارات العربية المتحدة', 'name_en' => 'United Arab Emirates'],
                                            ['value' => 'SA', 'flag' => 'sa', 'name_ar' => 'المملكة العربية السعودية', 'name_en' => 'Saudi Arabia'],
                                            ['value' => 'KW', 'flag' => 'kw', 'name_ar' => 'الكويت', 'name_en' => 'Kuwait'],
                                            ['value' => 'QA', 'flag' => 'qa', 'name_ar' => 'قطر', 'name_en' => 'Qatar'],
                                            ['value' => 'BH', 'flag' => 'bh', 'name_ar' => 'البحرين', 'name_en' => 'Bahrain'],
                                            ['value' => 'OM', 'flag' => 'om', 'name_ar' => 'سلطنة عُمان', 'name_en' => 'Oman'],
                                            ['value' => 'JO', 'flag' => 'jo', 'name_ar' => 'الأردن', 'name_en' => 'Jordan'],
                                            ['value' => 'LB', 'flag' => 'lb', 'name_ar' => 'لبنان', 'name_en' => 'Lebanon'],
                                            ['value' => 'SY', 'flag' => 'sy', 'name_ar' => 'سوريا', 'name_en' => 'Syria'],
                                            ['value' => 'PS', 'flag' => 'ps', 'name_ar' => 'فلسطين', 'name_en' => 'Palestine'],
                                            ['value' => 'IQ', 'flag' => 'iq', 'name_ar' => 'العراق', 'name_en' => 'Iraq'],
                                            ['value' => 'EG', 'flag' => 'eg', 'name_ar' => 'مصر', 'name_en' => 'Egypt'],
                                            ['value' => 'LY', 'flag' => 'ly', 'name_ar' => 'ليبيا', 'name_en' => 'Libya'],
                                            ['value' => 'TN', 'flag' => 'tn', 'name_ar' => 'تونس', 'name_en' => 'Tunisia'],
                                            ['value' => 'DZ', 'flag' => 'dz', 'name_ar' => 'الجزائر', 'name_en' => 'Algeria'],
                                            ['value' => 'MA', 'flag' => 'ma', 'name_ar' => 'المغرب', 'name_en' => 'Morocco'],
                                            ['value' => 'MR', 'flag' => 'mr', 'name_ar' => 'موريتانيا', 'name_en' => 'Mauritania'],
                                            ['value' => 'SD', 'flag' => 'sd', 'name_ar' => 'السودان', 'name_en' => 'Sudan'],
                                            ['value' => 'YE', 'flag' => 'ye', 'name_ar' => 'اليمن', 'name_en' => 'Yemen'],
                                            ['value' => 'SO', 'flag' => 'so', 'name_ar' => 'الصومال', 'name_en' => 'Somalia'],
                                            ['value' => 'DJ', 'flag' => 'dj', 'name_ar' => 'جيبوتي', 'name_en' => 'Djibouti'],
                                            ['value' => 'KM', 'flag' => 'km', 'name_ar' => 'جزر القمر', 'name_en' => 'Comoros'],
                                        ];
                                    @endphp
                                    @foreach($countries as $c)
                                        <div class="country-option" data-value="{{ $c['value'] }}" data-flag="{{ $c['flag'] }}" data-name="{{ app()->getLocale() == 'ar' ? $c['name_ar'] : $c['name_en'] }}" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; cursor: pointer; transition: background 0.2s; font-size: 14px;">
                                            <img src="https://flagcdn.com/w20/{{ $c['flag'] }}.png" style="width: 20px; height: 15px; border-radius: 2px;">
                                            <span>{{ app()->getLocale() == 'ar' ? $c['name_ar'] : $c['name_en'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="country" id="country" value="{{ old('country') }}" required>
                            </div>
                            @error('country')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="ri-mail-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}
                                <span class="required">*</span>
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="example@email.com"
                                   required>
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">
                                {{ app()->getLocale() == 'ar' ? 'سنرسل رمز التحقق إلى هذا البريد' : 'We will send verification code to this email' }}
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="ri-lock-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'كلمة المرور' : 'Password' }}
                                <span class="required">*</span>
                            </label>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل كلمة المرور' : 'Enter password' }}"
                                   minlength="8"
                                   required>
                            @error('password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">
                                {{ app()->getLocale() == 'ar' ? '8 أحرف على الأقل' : 'At least 8 characters' }}
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">
                                <i class="ri-lock-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'تأكيد كلمة المرور' : 'Confirm Password' }}
                                <span class="required">*</span>
                            </label>
                            <input type="password"
                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'أعد إدخال كلمة المرور' : 'Re-enter password' }}"
                                   minlength="8"
                                   required>
                            @error('password_confirmation')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @if(config('services.recaptcha.enabled'))
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                @error('g-recaptcha-response')
                    <div class="text-danger text-center mb-3">{{ $message }}</div>
                @enderror
                @endif

                <button type="submit" class="btn-continue" id="submitBtn">
                    <span>{{ app()->getLocale() == 'ar' ? 'التالي' : 'Next' }}</span>
                    <i class="{{ app()->getLocale() == 'ar' ? 'ri-arrow-left-line' : 'ri-arrow-right-line' }}"></i>
                </button>

                <div class="login-link">
                    {{ app()->getLocale() == 'ar' ? 'لديك حساب بالفعل؟' : 'Already have an account?' }}
                    <a href="{{ route('login') }}">{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول' : 'Sign In' }}</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto-generate slug from store name
        const storeNameInput = document.getElementById('store_name');
        const storeSlugInput = document.getElementById('store_slug');
        const slugStatus = document.getElementById('slugStatus');

        storeNameInput.addEventListener('input', function() {
            let slug = this.value
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            storeSlugInput.value = slug;
            checkSlugAvailability(slug);
        });

        storeSlugInput.addEventListener('input', function() {
            let slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9-]/g, '')
                .replace(/-+/g, '-');
            this.value = slug;
            checkSlugAvailability(slug);
        });

        let slugCheckTimeout;
        function checkSlugAvailability(slug) {
            clearTimeout(slugCheckTimeout);
            if (slug.length < 3) {
                slugStatus.textContent = '';
                return;
            }

            slugCheckTimeout = setTimeout(() => {
                fetch('{{ route("distributor.register.check-slug") }}?slug=' + encodeURIComponent(slug))
                    .then(response => response.json())
                    .then(data => {
                        if (data.available) {
                            slugStatus.className = 'slug-status available';
                            slugStatus.innerHTML = '<i class="ri-check-line"></i> {{ app()->getLocale() == "ar" ? "متاح" : "Available" }}';
                        } else {
                            slugStatus.className = 'slug-status taken';
                            slugStatus.innerHTML = '<i class="ri-close-line"></i> {{ app()->getLocale() == "ar" ? "غير متاح" : "Not available" }}';
                        }
                    })
                    .catch(() => {
                        slugStatus.textContent = '';
                    });
            }, 500);
        }
    </script>

    <script>
        // Phone code dropdown functionality
        const phoneCodeSelected = document.getElementById('phoneCodeSelected');
        const phoneCodeDropdown = document.getElementById('phoneCodeDropdown');
        const phoneCodeOptions = document.querySelectorAll('.phone-code-option');
        const selectedFlag = document.getElementById('selectedFlag');
        const selectedCode = document.getElementById('selectedCode');
        const phoneCodeInput = document.getElementById('phone_code');
        const phoneInputWrapper = document.getElementById('phoneInputWrapper');

        phoneCodeSelected.addEventListener('click', function(e) {
            e.stopPropagation();
            phoneCodeDropdown.style.display = phoneCodeDropdown.style.display === 'none' ? 'block' : 'none';
        });

        phoneCodeOptions.forEach(option => {
            option.addEventListener('click', function() {
                const code = this.getAttribute('data-code');
                const flag = this.getAttribute('data-flag');

                selectedFlag.src = `https://flagcdn.com/w20/${flag}.png`;
                selectedCode.textContent = `+${code}`;
                phoneCodeInput.value = code;
                phoneCodeDropdown.style.display = 'none';
            });

            option.addEventListener('mouseenter', function() {
                this.style.background = '#f3f4f6';
            });

            option.addEventListener('mouseleave', function() {
                this.style.background = 'white';
            });
        });

        document.addEventListener('click', function() {
            phoneCodeDropdown.style.display = 'none';
        });

        phoneInputWrapper.addEventListener('focusin', function() {
            this.style.borderColor = 'var(--distributor-color)';
            this.style.boxShadow = '0 0 0 4px rgba(86, 28, 4, 0.08)';
        });

        phoneInputWrapper.addEventListener('focusout', function() {
            this.style.borderColor = '#e5e7eb';
            this.style.boxShadow = 'none';
        });
    </script>

    @if(config('services.recaptcha.enabled'))
    <script>
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'submit'}).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    document.getElementById('registrationForm').submit();
                });
            });
        });
    </script>
    @endif
</body>
</html>
