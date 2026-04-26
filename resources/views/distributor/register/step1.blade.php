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

        * { margin: 0; padding: 0; box-sizing: border-box; }

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
            max-width: 1150px;
            width: 100%;
            display: grid;
            grid-template-columns: 360px 1fr;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(86, 28, 4, 0.12);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Sidebar ── */
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
            width: 250px; height: 250px;
            background: rgba(255,255,255,.05);
            border-radius: 50%;
            top: -80px; left: -80px;
        }
        .sidebar::after {
            content: '';
            position: absolute;
            width: 180px; height: 180px;
            background: rgba(255,255,255,.05);
            border-radius: 50%;
            bottom: -60px; right: -60px;
        }
        .logo-wrapper { text-align: center; margin-bottom: 40px; position: relative; z-index: 2; }
        .logo-wrapper img { max-width: 150px; height: auto; margin-bottom: 16px; }
        .logo-wrapper h3 { font-size: 20px; font-weight: 700; margin: 0; }

        .distributor-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,.15);
            padding: 7px 14px; border-radius: 50px;
            margin-top: 12px; font-size: 13px;
        }

        .progress-sidebar { position: relative; z-index: 2; }
        .progress-step {
            display: flex; align-items: flex-start;
            margin-bottom: 28px; position: relative;
        }
        .progress-step::before {
            content: '';
            position: absolute;
            inset-inline-start: 22px;
            top: 48px; width: 2px;
            height: calc(100% + 28px);
            background: rgba(255,255,255,.2);
        }
        .progress-step:last-child::before { display: none; }
        .step-number {
            width: 45px; height: 45px; border-radius: 50%;
            background: rgba(255,255,255,.15);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 17px; flex-shrink: 0;
            margin-inline-end: 14px;
            transition: all .3s;
        }
        .progress-step.active .step-number { background: white; color: var(--distributor-color); box-shadow: 0 4px 15px rgba(255,255,255,.3); }
        .progress-step.completed .step-number { background: #10b981; color: white; }
        .step-content h4 { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
        .step-content p  { font-size: 12px; opacity: .8; margin: 0; }
        .progress-step.active .step-content { opacity: 1; }
        .progress-step:not(.active) .step-content { opacity: .6; }

        /* ── Content area ── */
        .content-area { padding: 40px 50px; overflow-y: auto; max-height: 100vh; }
        .content-header { margin-bottom: 30px; }
        .content-header h2 { font-size: 26px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
        .content-header p  { color: #666; font-size: 14px; }

        /* ── Section headers ── */
        .section-title {
            font-size: 15px; font-weight: 700; color: var(--distributor-color);
            border-bottom: 2px solid #f0e8e4; padding-bottom: 10px;
            margin-bottom: 20px; margin-top: 30px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-title:first-of-type { margin-top: 0; }

        /* ── Form ── */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px; }
        .form-label i { margin-inline-end: 5px; color: var(--distributor-color); }
        .form-label .required { color: #ef4444; margin-inline-start: 3px; }

        .form-control, .form-select {
            width: 100%; padding: 12px 16px;
            border: 2px solid #e5e7eb; border-radius: 10px;
            font-size: 14px; transition: all .3s; background: white;
            font-family: 'Cairo', sans-serif;
        }
        .form-control:focus, .form-select:focus {
            outline: none; border-color: var(--distributor-color);
            box-shadow: 0 0 0 4px rgba(86,28,4,.08);
        }
        .form-control.is-invalid, .form-select.is-invalid { border-color: #ef4444; }
        .form-control[readonly] { background: #f9fafb; color: #555; cursor: not-allowed; }
        .text-danger { color: #ef4444; font-size: 12px; margin-top: 5px; display: block; }

        /* Store type cards */
        .store-type-group { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; }
        .store-type-card { position: relative; cursor: pointer; }
        .store-type-card input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
        .store-type-label {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 18px 10px; border: 2px solid #e5e7eb; border-radius: 12px;
            text-align: center; transition: all .25s; font-size: 13px; font-weight: 600; color: #555;
            gap: 8px;
        }
        .store-type-label i { font-size: 24px; }
        .store-type-card input:checked + .store-type-label {
            border-color: var(--distributor-color);
            background: rgba(86,28,4,.06);
            color: var(--distributor-color);
        }
        .store-type-label:hover { border-color: var(--distributor-secondary); background: #fdf5f3; }

        /* Physical store section */
        #physicalFields {
            background: #fdf5f3; border: 1px solid #f0e0d8;
            border-radius: 14px; padding: 20px;
            margin-top: 6px; display: none;
        }

        /* Input group for slug */
        .slug-wrapper { position: relative; }
        .slug-suffix {
            position: absolute;
            inset-inline-end: 12px;
            top: 50%; transform: translateY(-50%);
            background: #f3f4f6; color: #666; font-size: 13px;
            padding: 5px 10px; border-radius: 6px; pointer-events: none;
        }
        .slug-input { padding-inline-end: 120px; }
        .slug-status { font-size: 12px; margin-top: 5px; }
        .slug-status.available { color: #10b981; }
        .slug-status.taken     { color: #ef4444; }

        /* Phone input */
        .phone-input-wrapper {
            display: flex; align-items: center;
            border: 2px solid #e5e7eb; border-radius: 10px;
            overflow: hidden; transition: all .3s; direction: ltr;
        }
        .phone-input-wrapper:focus-within {
            border-color: var(--distributor-color);
            box-shadow: 0 0 0 4px rgba(86,28,4,.08);
        }
        .phone-code-trigger {
            display: flex; align-items: center; gap: 6px;
            padding: 12px 10px; cursor: pointer;
            background: #f9fafb;
            border-inline-end: 2px solid #e5e7eb;
            flex-shrink: 0;
        }
        .phone-input-wrapper input {
            border: none; outline: none; padding: 12px 14px;
            flex: 1; font-size: 14px; direction: ltr; text-align: left;
            font-family: 'Cairo', sans-serif;
        }
        .phone-code-dropdown {
            position: absolute; top: 100%;
            inset-inline-start: 0;
            width: 200px; background: white;
            border: 2px solid #e5e7eb; border-radius: 10px;
            margin-top: 4px; max-height: 240px;
            overflow-y: auto; z-index: 200;
            box-shadow: 0 10px 25px rgba(0,0,0,.15);
        }
        .phone-code-option { display: flex; align-items: center; gap: 8px; padding: 9px 12px; cursor: pointer; transition: background .2s; font-size: 13px; }
        .phone-code-option:hover { background: #f3f4f6; }

        /* Custom country dropdown */
        .country-trigger {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; border: 2px solid #e5e7eb;
            border-radius: 10px; cursor: pointer;
            background: white; transition: border-color .25s, box-shadow .25s;
            font-size: 14px; font-family: 'Cairo', sans-serif;
            min-height: 48px; user-select: none;
        }
        .country-trigger:focus-within, .country-trigger.open {
            border-color: var(--distributor-color);
            box-shadow: 0 0 0 4px rgba(86,28,4,.08);
            outline: none;
        }
        .country-trigger.is-invalid { border-color: #ef4444; }
        .country-trigger .arrow {
            margin-inline-start: auto; color: #999; font-size: 16px;
            transition: transform .2s;
        }
        .country-trigger.open .arrow { transform: rotate(180deg); }

        .country-panel {
            position: absolute; top: calc(100% + 4px); left: 0; right: 0;
            background: white; border: 2px solid #e0d6d1;
            border-radius: 12px; z-index: 400;
            box-shadow: 0 12px 32px rgba(86,28,4,.13);
            display: flex; flex-direction: column; overflow: hidden;
        }
        .country-search-wrap { padding: 8px 10px; border-bottom: 1px solid #f0e8e4; }
        .country-search-wrap input {
            width: 100%; padding: 8px 12px;
            border: 1.5px solid #e5e7eb; border-radius: 8px;
            font-size: 13px; font-family: 'Cairo', sans-serif;
            outline: none; background: #fafafa;
        }
        .country-search-wrap input:focus { border-color: var(--distributor-color); background: white; }
        .country-list { max-height: 220px; overflow-y: auto; }
        .country-list::-webkit-scrollbar { width: 4px; }
        .country-list::-webkit-scrollbar-track { background: transparent; }
        .country-list::-webkit-scrollbar-thumb { background: #e0d6d1; border-radius: 4px; }
        .country-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 14px; cursor: pointer;
            font-size: 13px; font-family: 'Cairo', sans-serif;
            transition: background .15s;
        }
        .country-item:hover { background: #fdf5f3; }
        .country-item.active { background: #f5ede9; color: var(--distributor-color); font-weight: 600; }
        .country-item img { width: 26px; height: 19px; object-fit: cover; border-radius: 3px; flex-shrink: 0; border: 1px solid #eee; }
        .country-item .no-flag { width: 26px; height: 19px; background: #f3f4f6; border-radius: 3px; flex-shrink: 0; }

        /* Working hours table */
        .working-hours-table { width: 100%; border-collapse: collapse; }
        .working-hours-table th {
            background: #f8f5f3; padding: 10px 12px;
            font-size: 13px; font-weight: 600; color: #555;
            text-align: start;
        }
        .working-hours-table td { padding: 8px 12px; border-bottom: 1px solid #f0e8e4; vertical-align: middle; }
        .day-toggle { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .day-toggle input[type="checkbox"] {
            width: 18px; height: 18px; accent-color: var(--distributor-color); cursor: pointer;
        }
        .time-inputs { display: flex; align-items: center; gap: 8px; }
        .time-inputs input[type="time"] {
            padding: 7px 10px; border: 1.5px solid #e5e7eb;
            border-radius: 8px; font-size: 13px;
            font-family: 'Cairo', sans-serif;
        }
        .time-inputs input[type="time"]:focus { outline: none; border-color: var(--distributor-color); }
        .time-inputs span { font-size: 12px; color: #888; }
        .row-closed td { opacity: .45; }
        .row-closed .time-inputs input { pointer-events: none; }

        /* Submit button */
        .btn-continue {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, var(--distributor-color) 0%, var(--distributor-secondary) 100%);
            color: white; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            transition: all .3s; margin-top: 30px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            font-family: 'Cairo', sans-serif;
        }
        .btn-continue:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(86,28,4,.25); }

        .login-link { text-align: center; margin-top: 20px; color: #666; font-size: 13px; }
        .login-link a { color: var(--distributor-color); text-decoration: none; font-weight: 600; }
        .login-link a:hover { text-decoration: underline; }

        .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 22px; border: none; }
        .alert-danger { background: #fee; color: #c00; }

        /* Language switcher */
        .language-switcher {
            position: fixed; top: 20px;
            inset-inline-end: 20px;
            z-index: 1000;
            display: flex; gap: 8px;
            background: white; padding: 7px 10px;
            border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,.1);
        }
        .lang-btn {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 14px; border: 2px solid transparent;
            border-radius: 50px; background: transparent;
            color: #666; font-size: 13px; font-weight: 500;
            cursor: pointer; transition: all .3s; text-decoration: none;
        }
        .lang-btn:hover { background: #f8f9fa; color: var(--distributor-color); }
        .lang-btn.active {
            background: linear-gradient(135deg, var(--distributor-color) 0%, var(--distributor-secondary) 100%);
            color: white; border-color: var(--distributor-color);
        }

        @media (max-width: 992px) {
            .main-container { grid-template-columns: 1fr; }
            .sidebar { padding: 25px 20px; }
            .progress-sidebar { display: flex; gap: 16px; overflow-x: auto; }
            .progress-step { flex-direction: column; align-items: center; text-align: center; margin-bottom: 0; min-width: 90px; }
            .progress-step::before { display: none; }
            .step-number { margin: 0 0 8px; }
            .content-area { padding: 25px 20px; max-height: none; }
            .store-type-group { grid-template-columns: 1fr 1fr 1fr; }
        }
        @media (max-width: 576px) {
            .store-type-group { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="language-switcher">
        <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">
            <img src="https://flagcdn.com/w20/gb.png" alt="English" style="width:20px;height:15px;">
            <span>English</span>
        </a>
        <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">
            <img src="https://flagcdn.com/w20/ae.png" alt="العربية" style="width:20px;height:15px;">
            <span>العربية</span>
        </a>
    </div>

    <div class="main-container">
        {{-- ───── Sidebar ───── --}}
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

        {{-- ───── Content ───── --}}
        <div class="content-area">
            <div class="content-header">
                <h2>{{ app()->getLocale() == 'ar' ? 'مرحباً بك!' : 'Welcome!' }}</h2>
                <p>{{ app()->getLocale() == 'ar' ? 'ابدأ رحلتك كمتجر معنا بتعبئة المعلومات الأساسية' : 'Start your journey as a distributor by filling basic information' }}</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    <strong>{{ app()->getLocale() == 'ar' ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}</strong>
                    <ul class="mb-0 mt-2" style="padding-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('distributor.register.step1.process') }}" method="POST" id="registrationForm">
                @csrf

                {{-- ── Personal info ── --}}
                <div class="section-title">
                    <i class="ri-user-line"></i>
                    {{ app()->getLocale() == 'ar' ? 'المعلومات الشخصية' : 'Personal Information' }}
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ri-user-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'الاسم الكامل' : 'Full Name' }}
                                <span class="required">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('full_name') is-invalid @enderror"
                                   name="full_name"
                                   value="{{ old('full_name') }}"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل اسمك الكامل' : 'Enter your full name' }}"
                                   required>
                            @error('full_name')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ri-mail-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}
                                <span class="required">*</span>
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="example@email.com"
                                   required>
                            @error('email')<span class="text-danger">{{ $message }}</span>@enderror
                            <small class="text-muted d-block mt-1">{{ app()->getLocale() == 'ar' ? 'سنرسل رمز التحقق إلى هذا البريد' : 'We will send a verification code to this email' }}</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ri-phone-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'رقم الهاتف (واتساب)' : 'Phone Number (WhatsApp)' }}
                                <span class="required">*</span>
                            </label>
                            <div style="position:relative;">
                                <div class="phone-input-wrapper" id="phoneInputWrapper">
                                    <div class="phone-code-trigger" id="phoneCodeSelected">
                                        <img id="selectedFlag" src="https://flagcdn.com/w20/ae.png" style="width:20px;height:15px;border-radius:2px;">
                                        <span id="selectedCode" style="font-size:13px;font-weight:500;color:#333;">+971</span>
                                        <i class="ri-arrow-down-s-line" style="font-size:13px;color:#999;"></i>
                                    </div>
                                    <input type="text"
                                           class="@error('phone') is-invalid @enderror"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone') }}"
                                           placeholder="{{ app()->getLocale() == 'ar' ? 'رقم الهاتف' : 'Phone number' }}"
                                           required>
                                </div>
                                @php
                                    $phoneCodes = [
                                        ['code'=>'971','flag'=>'ae','name'=>'UAE'],
                                        ['code'=>'966','flag'=>'sa','name'=>'KSA'],
                                        ['code'=>'20','flag'=>'eg','name'=>'Egypt'],
                                        ['code'=>'965','flag'=>'kw','name'=>'Kuwait'],
                                        ['code'=>'974','flag'=>'qa','name'=>'Qatar'],
                                        ['code'=>'973','flag'=>'bh','name'=>'Bahrain'],
                                        ['code'=>'968','flag'=>'om','name'=>'Oman'],
                                        ['code'=>'962','flag'=>'jo','name'=>'Jordan'],
                                        ['code'=>'961','flag'=>'lb','name'=>'Lebanon'],
                                        ['code'=>'963','flag'=>'sy','name'=>'Syria'],
                                        ['code'=>'970','flag'=>'ps','name'=>'Palestine'],
                                        ['code'=>'964','flag'=>'iq','name'=>'Iraq'],
                                        ['code'=>'218','flag'=>'ly','name'=>'Libya'],
                                        ['code'=>'216','flag'=>'tn','name'=>'Tunisia'],
                                        ['code'=>'213','flag'=>'dz','name'=>'Algeria'],
                                        ['code'=>'212','flag'=>'ma','name'=>'Morocco'],
                                        ['code'=>'249','flag'=>'sd','name'=>'Sudan'],
                                        ['code'=>'967','flag'=>'ye','name'=>'Yemen'],
                                    ];
                                @endphp
                                <div class="phone-code-dropdown" id="phoneCodeDropdown" style="display:none;">
                                    @foreach($phoneCodes as $c)
                                    <div class="phone-code-option" data-code="{{ $c['code'] }}" data-flag="{{ $c['flag'] }}">
                                        <img src="https://flagcdn.com/w20/{{ $c['flag'] }}.png" style="width:20px;height:15px;border-radius:2px;">
                                        <span>+{{ $c['code'] }} {{ $c['name'] }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="phone_code" id="phone_code" value="{{ old('phone_code', '971') }}">
                            </div>
                            @error('phone')<span class="text-danger">{{ $message }}</span>@enderror
                            <small class="text-muted d-block mt-1">{{ app()->getLocale() == 'ar' ? 'سنرسل رمز التحقق عبر واتساب' : 'We will send a verification code via WhatsApp' }}</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ri-lock-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'كلمة المرور' : 'Password' }}
                                <span class="required">*</span>
                            </label>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   name="password"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل كلمة المرور (8 أحرف على الأقل)' : 'At least 8 characters' }}"
                                   minlength="8" required>
                            @error('password')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ri-lock-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'تأكيد كلمة المرور' : 'Confirm Password' }}
                                <span class="required">*</span>
                            </label>
                            <input type="password"
                                   class="form-control"
                                   name="password_confirmation"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'أعد إدخال كلمة المرور' : 'Re-enter password' }}"
                                   minlength="8" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ri-global-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }}
                                <span class="required">*</span>
                            </label>
                            <div style="position:relative;">
                                {{-- Trigger button --}}
                                <div class="country-trigger @error('country') is-invalid @enderror" id="countryTrigger" tabindex="0">
                                    <img id="ctrFlag" src="" alt="" style="width:26px;height:19px;object-fit:cover;border-radius:3px;border:1px solid #eee;display:none;flex-shrink:0;">
                                    <span id="ctrName" style="color:#aaa;font-family:'Cairo',sans-serif;">
                                        {{ app()->getLocale() == 'ar' ? '— اختر الدولة —' : '— Select Country —' }}
                                    </span>
                                    <i class="ri-arrow-down-s-line arrow"></i>
                                </div>
                                {{-- Hidden input submitted with the form --}}
                                <input type="hidden" name="country" id="countryVal" value="{{ old('country') }}">

                                {{-- Dropdown panel --}}
                                <div class="country-panel" id="countryPanel" style="display:none;">
                                    <div class="country-search-wrap">
                                        <input type="text" id="countrySearch"
                                               placeholder="{{ app()->getLocale() == 'ar' ? 'ابحث عن دولة...' : 'Search country...' }}"
                                               autocomplete="off">
                                    </div>
                                    <div class="country-list" id="countryList">
                                        @foreach($countries as $c)
                                        @php $flagCode = strtolower($c->code); $label = app()->getLocale() == 'ar' ? ($c->name_ar ?: $c->name) : $c->name; @endphp
                                        <div class="country-item"
                                             data-value="{{ $c->code }}"
                                             data-label="{{ $label }}"
                                             data-flag="{{ $flagCode }}">
                                            <img src="https://flagcdn.com/{{ $flagCode }}.svg"
                                                 alt="{{ $c->code }}"
                                                 onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'no-flag'}))">
                                            <span>{{ $label }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('country')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                {{-- ── Store info ── --}}
                <div class="section-title">
                    <i class="ri-store-2-line"></i>
                    {{ app()->getLocale() == 'ar' ? 'معلومات المتجر' : 'Store Information' }}
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
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
                            @error('store_name')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ri-link"></i>
                                {{ app()->getLocale() == 'ar' ? 'رابط المتجر (يُنشأ تلقائياً)' : 'Store URL (auto-generated)' }}
                            </label>
                            <div class="slug-wrapper">
                                <input type="text"
                                       class="form-control slug-input @error('store_slug') is-invalid @enderror"
                                       id="store_slug"
                                       name="store_slug"
                                       value="{{ old('store_slug') }}"
                                       readonly
                                       placeholder="{{ app()->getLocale() == 'ar' ? 'يُنشأ تلقائياً من اسم المتجر' : 'Auto-generated from store name' }}">
                                <span class="slug-suffix">.selaa.ae</span>
                            </div>
                            <div id="slugStatus" class="slug-status"></div>
                            @error('store_slug')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ri-store-3-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'نوع المتجر' : 'Store Type' }}
                                <span class="required">*</span>
                            </label>
                            <div class="store-type-group">
                                <label class="store-type-card">
                                    <input type="radio" name="store_type" value="online"
                                        {{ old('store_type') == 'online' ? 'checked' : '' }}>
                                    <div class="store-type-label">
                                        <i class="ri-global-line"></i>
                                        {{ app()->getLocale() == 'ar' ? 'موقع إلكتروني' : 'Online Store' }}
                                    </div>
                                </label>
                                <label class="store-type-card">
                                    <input type="radio" name="store_type" value="physical"
                                        {{ old('store_type') == 'physical' ? 'checked' : '' }}>
                                    <div class="store-type-label">
                                        <i class="ri-building-line"></i>
                                        {{ app()->getLocale() == 'ar' ? 'موقع قائم' : 'Physical Store' }}
                                    </div>
                                </label>
                                <label class="store-type-card">
                                    <input type="radio" name="store_type" value="both"
                                        {{ old('store_type') == 'both' ? 'checked' : '' }}>
                                    <div class="store-type-label">
                                        <i class="ri-layout-grid-line"></i>
                                        {{ app()->getLocale() == 'ar' ? 'كلاهما' : 'Both' }}
                                    </div>
                                </label>
                            </div>
                            @error('store_type')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Physical store details (shown conditionally) --}}
                    <div class="col-12">
                        <div id="physicalFields">
                            <p style="font-size:13px;color:var(--distributor-color);font-weight:600;margin-bottom:16px;">
                                <i class="ri-map-pin-line me-1"></i>
                                {{ app()->getLocale() == 'ar' ? 'تفاصيل الموقع الفعلي' : 'Physical Location Details' }}
                            </p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="ri-map-pin-2-line"></i>
                                            {{ app()->getLocale() == 'ar' ? 'المنطقة' : 'Region / City' }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text"
                                               class="form-control @error('store_region') is-invalid @enderror"
                                               name="store_region"
                                               value="{{ old('store_region') }}"
                                               placeholder="{{ app()->getLocale() == 'ar' ? 'مثال: دبي، أبوظبي' : 'e.g. Dubai, Abu Dhabi' }}">
                                        @error('store_region')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="ri-road-map-line"></i>
                                            {{ app()->getLocale() == 'ar' ? 'العنوان التفصيلي' : 'Detailed Address' }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text"
                                               class="form-control @error('store_address') is-invalid @enderror"
                                               name="store_address"
                                               value="{{ old('store_address') }}"
                                               placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل العنوان الكامل للمتجر' : 'Enter the full store address' }}">
                                        @error('store_address')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="ri-map-2-line"></i>
                                            {{ app()->getLocale() == 'ar' ? 'رابط خرائط قوقل' : 'Google Maps Link' }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="url"
                                               class="form-control @error('store_map_url') is-invalid @enderror"
                                               name="store_map_url"
                                               value="{{ old('store_map_url') }}"
                                               placeholder="https://maps.google.com/..."
                                               dir="ltr">
                                        @error('store_map_url')<span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Support ── --}}
                <div class="section-title">
                    <i class="ri-customer-service-2-line"></i>
                    {{ app()->getLocale() == 'ar' ? 'معلومات الدعم' : 'Support Information' }}
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ri-phone-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'رقم هاتف الدعم' : 'Support Phone' }}
                                <span class="required">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('support_phone') is-invalid @enderror"
                                   name="support_phone"
                                   value="{{ old('support_phone') }}"
                                   placeholder="{{ app()->getLocale() == 'ar' ? '+971XXXXXXXXX' : '+971XXXXXXXXX' }}"
                                   dir="ltr">
                            @error('support_phone')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ri-mail-line"></i>
                                {{ app()->getLocale() == 'ar' ? 'البريد الإلكتروني للدعم' : 'Support Email' }}
                                <span class="required">*</span>
                            </label>
                            <input type="email"
                                   class="form-control @error('support_email') is-invalid @enderror"
                                   name="support_email"
                                   value="{{ old('support_email') }}"
                                   placeholder="support@store.com"
                                   dir="ltr">
                            @error('support_email')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                {{-- ── Working hours ── --}}
                <div class="section-title">
                    <i class="ri-time-line"></i>
                    {{ app()->getLocale() == 'ar' ? 'أوقات العمل' : 'Working Hours' }}
                </div>

                @php
                    $days = app()->getLocale() == 'ar'
                        ? [
                            ['key'=>'saturday',  'label'=>'السبت'],
                            ['key'=>'sunday',    'label'=>'الأحد'],
                            ['key'=>'monday',    'label'=>'الاثنين'],
                            ['key'=>'tuesday',   'label'=>'الثلاثاء'],
                            ['key'=>'wednesday', 'label'=>'الأربعاء'],
                            ['key'=>'thursday',  'label'=>'الخميس'],
                            ['key'=>'friday',    'label'=>'الجمعة'],
                          ]
                        : [
                            ['key'=>'monday',    'label'=>'Monday'],
                            ['key'=>'tuesday',   'label'=>'Tuesday'],
                            ['key'=>'wednesday', 'label'=>'Wednesday'],
                            ['key'=>'thursday',  'label'=>'Thursday'],
                            ['key'=>'friday',    'label'=>'Friday'],
                            ['key'=>'saturday',  'label'=>'Saturday'],
                            ['key'=>'sunday',    'label'=>'Sunday'],
                          ];
                    $oldHours = old('working_hours', []);
                    $oldHoursMap = [];
                    foreach ($oldHours as $i => $h) {
                        if (!empty($h['day'])) $oldHoursMap[$h['day']] = $h;
                    }
                @endphp

                <div style="overflow-x:auto;">
                    <table class="working-hours-table" id="workingHoursTable">
                        <thead>
                            <tr>
                                <th>{{ app()->getLocale() == 'ar' ? 'اليوم' : 'Day' }}</th>
                                <th>{{ app()->getLocale() == 'ar' ? 'وقت الفتح' : 'Open' }}</th>
                                <th style="text-align:center;">–</th>
                                <th>{{ app()->getLocale() == 'ar' ? 'وقت الإغلاق' : 'Close' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $i => $day)
                            @php
                                $dayData = $oldHoursMap[$day['key']] ?? [];
                                $isEnabled = !empty($dayData['enabled']) || (!empty($oldHoursMap) && !empty($dayData));
                            @endphp
                            <tr id="row_{{ $day['key'] }}" class="{{ $isEnabled ? '' : 'row-closed' }}">
                                <td>
                                    <label class="day-toggle">
                                        <input type="checkbox"
                                               name="working_hours[{{ $i }}][enabled]"
                                               value="1"
                                               id="check_{{ $day['key'] }}"
                                               {{ $isEnabled ? 'checked' : '' }}
                                               onchange="toggleDay('{{ $day['key'] }}')">
                                        <input type="hidden" name="working_hours[{{ $i }}][day]" value="{{ $day['key'] }}">
                                        <span style="font-size:14px;font-weight:600;">{{ $day['label'] }}</span>
                                    </label>
                                </td>
                                <td>
                                    <div class="time-inputs">
                                        <input type="time"
                                               name="working_hours[{{ $i }}][open]"
                                               value="{{ $dayData['open'] ?? '09:00' }}"
                                               id="open_{{ $day['key'] }}">
                                    </div>
                                </td>
                                <td style="text-align:center;color:#aaa;">–</td>
                                <td>
                                    <div class="time-inputs">
                                        <input type="time"
                                               name="working_hours[{{ $i }}][close]"
                                               value="{{ $dayData['close'] ?? '18:00' }}"
                                               id="close_{{ $day['key'] }}">
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(config('services.recaptcha.enabled'))
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                @error('g-recaptcha-response')
                    <span class="text-danger text-center d-block mt-2">{{ $message }}</span>
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

    <script>
    // ── Store slug: auto-generate from store name, readonly ──
    const storeNameInput = document.getElementById('store_name');
    const storeSlugInput = document.getElementById('store_slug');
    const slugStatus     = document.getElementById('slugStatus');

    storeNameInput.addEventListener('input', function () {
        const slug = this.value
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        storeSlugInput.value = slug;
        checkSlug(slug);
    });

    let slugTimer;
    function checkSlug(slug) {
        clearTimeout(slugTimer);
        if (slug.length < 3) { slugStatus.textContent = ''; return; }
        slugTimer = setTimeout(() => {
            fetch('{{ route("distributor.register.check-slug") }}?slug=' + encodeURIComponent(slug))
                .then(r => r.json())
                .then(data => {
                    if (data.available) {
                        slugStatus.className = 'slug-status available';
                        slugStatus.innerHTML = '<i class="ri-check-line"></i> {{ app()->getLocale() == "ar" ? "متاح" : "Available" }}';
                    } else {
                        slugStatus.className = 'slug-status taken';
                        slugStatus.innerHTML = '<i class="ri-close-line"></i> {{ app()->getLocale() == "ar" ? "غير متاح" : "Not available" }}';
                    }
                }).catch(() => { slugStatus.textContent = ''; });
        }, 500);
    }

    // ── Store type toggle ──
    const physicalFields = document.getElementById('physicalFields');
    document.querySelectorAll('input[name="store_type"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const showPhysical = this.value === 'physical' || this.value === 'both';
            physicalFields.style.display = showPhysical ? 'block' : 'none';
            physicalFields.querySelectorAll('input, select').forEach(el => {
                if (showPhysical) {
                    el.removeAttribute('disabled');
                } else {
                    el.setAttribute('disabled', 'disabled');
                }
            });
        });
    });
    // Restore on page load (for validation errors)
    (function () {
        const checked = document.querySelector('input[name="store_type"]:checked');
        if (checked && (checked.value === 'physical' || checked.value === 'both')) {
            physicalFields.style.display = 'block';
        } else {
            physicalFields.querySelectorAll('input, select').forEach(el => el.setAttribute('disabled', 'disabled'));
        }
    })();

    // ── Phone code dropdown ──
    const phoneCodeSelected  = document.getElementById('phoneCodeSelected');
    const phoneCodeDropdown  = document.getElementById('phoneCodeDropdown');
    const selectedFlag       = document.getElementById('selectedFlag');
    const selectedCode       = document.getElementById('selectedCode');
    const phoneCodeInput     = document.getElementById('phone_code');

    phoneCodeSelected.addEventListener('click', e => {
        e.stopPropagation();
        phoneCodeDropdown.style.display = phoneCodeDropdown.style.display === 'none' ? 'block' : 'none';
        if (typeof closeCountryPanel === 'function') closeCountryPanel();
    });
    document.querySelectorAll('.phone-code-option').forEach(opt => {
        opt.addEventListener('click', function () {
            selectedFlag.src = `https://flagcdn.com/w20/${this.dataset.flag}.png`;
            selectedCode.textContent = `+${this.dataset.code}`;
            phoneCodeInput.value = this.dataset.code;
            phoneCodeDropdown.style.display = 'none';
        });
    });

    // ── Country custom dropdown ──
    const countryTrigger = document.getElementById('countryTrigger');
    const countryPanel   = document.getElementById('countryPanel');
    const countryVal     = document.getElementById('countryVal');
    const countrySearch  = document.getElementById('countrySearch');
    const ctrFlag        = document.getElementById('ctrFlag');
    const ctrName        = document.getElementById('ctrName');
    const countryItems   = document.querySelectorAll('.country-item');

    function openCountryPanel() {
        countryPanel.style.display = 'flex';
        countryTrigger.classList.add('open');
        phoneCodeDropdown.style.display = 'none';
        countrySearch.value = '';
        filterCountry('');
        setTimeout(() => countrySearch.focus(), 30);
    }
    function closeCountryPanel() {
        countryPanel.style.display = 'none';
        countryTrigger.classList.remove('open');
    }
    function filterCountry(q) {
        countryItems.forEach(item => {
            item.style.display = item.dataset.label.toLowerCase().includes(q) ? 'flex' : 'none';
        });
    }
    function selectCountry(item) {
        const val   = item.dataset.value;
        const label = item.dataset.label;
        const flag  = item.dataset.flag;
        countryVal.value   = val;
        ctrName.textContent = label;
        ctrName.style.color = '#222';
        ctrFlag.src         = `https://flagcdn.com/${flag}.svg`;
        ctrFlag.style.display = 'block';
        countryTrigger.classList.remove('is-invalid');
        countryItems.forEach(el => el.classList.remove('active'));
        item.classList.add('active');
        closeCountryPanel();
    }

    countryTrigger.addEventListener('click', e => {
        e.stopPropagation();
        countryPanel.style.display === 'none' ? openCountryPanel() : closeCountryPanel();
    });
    countrySearch.addEventListener('input', function() { filterCountry(this.value.toLowerCase()); });
    countryPanel.addEventListener('click', e => e.stopPropagation());
    countryItems.forEach(item => item.addEventListener('click', () => selectCountry(item)));

    // Restore selection on page load (validation redisplay)
    (function() {
        const oldVal = '{{ old("country") }}';
        if (oldVal) {
            const match = document.querySelector(`.country-item[data-value="${oldVal}"]`);
            if (match) selectCountry(match);
        }
    })();

    document.addEventListener('click', () => {
        phoneCodeDropdown.style.display = 'none';
        closeCountryPanel();
    });

    // ── Working hours row toggle ──
    function toggleDay(dayKey) {
        const row = document.getElementById('row_' + dayKey);
        const cb  = document.getElementById('check_' + dayKey);
        if (cb.checked) {
            row.classList.remove('row-closed');
            row.querySelectorAll('input[type="time"]').forEach(el => el.removeAttribute('disabled'));
        } else {
            row.classList.add('row-closed');
            row.querySelectorAll('input[type="time"]').forEach(el => el.setAttribute('disabled', 'disabled'));
        }
    }
    // Disable time inputs for unchecked rows on load
    document.querySelectorAll('.working-hours-table tbody tr').forEach(row => {
        if (row.classList.contains('row-closed')) {
            row.querySelectorAll('input[type="time"]').forEach(el => el.setAttribute('disabled', 'disabled'));
        }
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
