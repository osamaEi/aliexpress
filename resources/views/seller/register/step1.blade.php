<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() == 'ar' ? 'تسجيل بائع جديد - الخطوة 1' : 'Seller Registration - Step 1' }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('logo/logo.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Google reCAPTCHA v3 -->
    @if(config('services.recaptcha.enabled'))
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endif
    <style>
        :root {
            --primary-color: #561C04;
            --secondary-color: #7a2805;
            --success-color: #10b981;
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

        /* Left Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
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
            margin-bottom: 35px;
            position: relative;
        }

        .progress-step::before {
            content: '';
            position: absolute;
            left: 22px;
            top: 50px;
            width: 2px;
            height: calc(100% + 35px);
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
            margin-left: 15px;
            transition: all 0.3s;
        }

        .progress-step.active .step-number {
            background: white;
            color: var(--primary-color);
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

        /* Right Content */
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
            margin-left: 6px;
            color: var(--primary-color);
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
            border-color: var(--primary-color);
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

        .btn-continue {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
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
            color: var(--primary-color);
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
                min-width: 120px;
            }

            .progress-step::before {
                display: none;
            }

            .step-number {
                margin-left: 0;
                margin-bottom: 10px;
            }

            .content-area {
                padding: 30px 25px;
            }
        }

        /* Language Switcher */
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
            color: var(--primary-color);
        }

        .lang-btn.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-color: var(--primary-color);
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

        /* Logo Upload Styles */
        .logo-upload-wrapper {
            margin-bottom: 25px;
        }

        .logo-upload-area {
            border: 2px dashed #e5e7eb;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: #f9fafb;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }

        .logo-upload-area:hover {
            border-color: var(--primary-color);
            background: #fef3f2;
        }

        .logo-upload-area.dragover {
            border-color: var(--primary-color);
            background: #fef3f2;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .upload-text h4 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .upload-text p {
            font-size: 13px;
            color: #666;
            margin: 0;
        }

        .logo-preview {
            display: none;
            margin-top: 20px;
            text-align: center;
        }

        .logo-preview.active {
            display: block;
        }

        .logo-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 10px;
            background: white;
        }

        .remove-logo {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
            padding: 8px 16px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .remove-logo:hover {
            background: #dc2626;
        }

        #logo_file {
            display: none;
        }

        /* reCAPTCHA v3 Badge Styling */
        .grecaptcha-badge {
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 9999 !important;
            position: fixed !important;
            bottom: 14px !important;
            right: 14px !important;
        }
    </style>
</head>
<body>
    <!-- Language Switcher -->
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
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-wrapper">
                <img src="{{ asset('logo/logo10.png') }}" alt="Logo">


                <h3>{{ app()->getLocale() == 'ar' ? 'تسجيل البائع' : 'Seller Registration' }}</h3>
            </div>

            <div class="progress-sidebar">
                <div class="progress-step active">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'المعلومات الأساسية' : 'Basic Information' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'البيانات الشخصية والتواصل' : 'Personal and contact details' }}</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'النشاط التجاري' : 'Business Activity' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'حدد مجال عملك' : 'Select your business field' }}</p>
                    </div>
                </div>

                <div class="progress-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'التحقق' : 'Verification' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'تأكيد البريد الإلكتروني' : 'Email confirmation' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="content-header">
                <h2>{{ app()->getLocale() == 'ar' ? 'مرحباً بك!' : 'Welcome!' }}</h2>
                <p>{{ app()->getLocale() == 'ar' ? 'ابدأ رحلتك معنا بتعبئة المعلومات الأساسية' : 'Start your journey with us by filling basic information' }}</p>
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

            <form action="{{ route('seller.register.step1.process') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Logo Upload Section -->
                <div class="logo-upload-wrapper">
                    <label class="form-label">
                        <i class="ri-image-line"></i> {{ app()->getLocale() == 'ar' ? 'شعار الشركة (اختياري)' : 'Company Logo (Optional)' }}
                    </label>
                    <div class="logo-upload-area" id="logoUploadArea">
                        <i class="ri-upload-cloud-2-line upload-icon"></i>
                        <div class="upload-text">
                            <h4>{{ app()->getLocale() == 'ar' ? 'اضغط لرفع الشعار أو اسحبه هنا' : 'Click to upload logo or drag it here' }}</h4>
                            <p>{{ app()->getLocale() == 'ar' ? 'PNG, JPG, JPEG حتى 2MB' : 'PNG, JPG, JPEG up to 2MB' }}</p>
                        </div>
                        <input type="file"
                               id="logo_file"
                               name="logo"
                               accept="image/png,image/jpeg,image/jpg">
                    </div>
                    <div class="logo-preview" id="logoPreview">
                        <img id="logoImage" src="" alt="Logo Preview">
                        <br>
                        <button type="button" class="remove-logo" id="removeLogo">
                            <i class="ri-delete-bin-line"></i>
                            <span>{{ app()->getLocale() == 'ar' ? 'إزالة الشعار' : 'Remove Logo' }}</span>
                        </button>
                    </div>
                    @error('logo')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="full_name" class="form-label">
                                <i class="ri-user-line"></i> {{ app()->getLocale() == 'ar' ? 'الاسم الكامل' : 'Full Name' }}
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
                            <label for="company_name" class="form-label">
                                <i class="ri-building-line"></i> {{ app()->getLocale() == 'ar' ? 'اسم الشركة' : 'Company Name' }}
                            </label>
                            <input type="text"
                                   class="form-control @error('company_name') is-invalid @enderror"
                                   id="company_name"
                                   name="company_name"
                                   value="{{ old('company_name') }}"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل اسم شركتك' : 'Enter your company name' }}"
                                   required>
                            @error('company_name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country" class="form-label">
                                <i class="ri-map-pin-line"></i> {{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }}
                            </label>
                            <select class="form-select @error('country') is-invalid @enderror"
                                    id="country"
                                    name="country"
                                    required>
                                <option value="">{{ app()->getLocale() == 'ar' ? 'اختر الدولة' : 'Select Country' }}</option>
                                <!-- دول الخليج العربي -->
                                <option value="الإمارات العربية المتحدة" {{ old('country') == 'الإمارات العربية المتحدة' ? 'selected' : '' }}>الإمارات العربية المتحدة</option>
                                <option value="المملكة العربية السعودية" {{ old('country') == 'المملكة العربية السعودية' ? 'selected' : '' }}>المملكة العربية السعودية</option>
                                <option value="الكويت" {{ old('country') == 'الكويت' ? 'selected' : '' }}>الكويت</option>
                                <option value="قطر" {{ old('country') == 'قطر' ? 'selected' : '' }}>قطر</option>
                                <option value="البحرين" {{ old('country') == 'البحرين' ? 'selected' : '' }}>البحرين</option>
                                <option value="سلطنة عُمان" {{ old('country') == 'سلطنة عُمان' ? 'selected' : '' }}>سلطنة عُمان</option>
                                <!-- دول الشام -->
                                <option value="الأردن" {{ old('country') == 'الأردن' ? 'selected' : '' }}>الأردن</option>
                                <option value="لبنان" {{ old('country') == 'لبنان' ? 'selected' : '' }}>لبنان</option>
                                <option value="سوريا" {{ old('country') == 'سوريا' ? 'selected' : '' }}>سوريا</option>
                                <option value="فلسطين" {{ old('country') == 'فلسطين' ? 'selected' : '' }}>فلسطين</option>
                                <option value="العراق" {{ old('country') == 'العراق' ? 'selected' : '' }}>العراق</option>
                                <!-- دول شمال أفريقيا -->
                                <option value="مصر" {{ old('country') == 'مصر' ? 'selected' : '' }}>مصر</option>
                                <option value="ليبيا" {{ old('country') == 'ليبيا' ? 'selected' : '' }}>ليبيا</option>
                                <option value="تونس" {{ old('country') == 'تونس' ? 'selected' : '' }}>تونس</option>
                                <option value="الجزائر" {{ old('country') == 'الجزائر' ? 'selected' : '' }}>الجزائر</option>
                                <option value="المغرب" {{ old('country') == 'المغرب' ? 'selected' : '' }}>المغرب</option>
                                <option value="موريتانيا" {{ old('country') == 'موريتانيا' ? 'selected' : '' }}>موريتانيا</option>
                                <!-- دول أخرى -->
                                <option value="السودان" {{ old('country') == 'السودان' ? 'selected' : '' }}>السودان</option>
                                <option value="اليمن" {{ old('country') == 'اليمن' ? 'selected' : '' }}>اليمن</option>
                                <option value="الصومال" {{ old('country') == 'الصومال' ? 'selected' : '' }}>الصومال</option>
                                <option value="جيبوتي" {{ old('country') == 'جيبوتي' ? 'selected' : '' }}>جيبوتي</option>
                                <option value="جزر القمر" {{ old('country') == 'جزر القمر' ? 'selected' : '' }}>جزر القمر</option>
                            </select>
                            @error('country')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="ri-mail-line"></i> {{ app()->getLocale() == 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}
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
                            <small class="text-muted d-block mt-1">{{ app()->getLocale() == 'ar' ? 'سنرسل رمز التحقق إلى هذا البريد' : 'We will send verification code to this email' }}</small>
                        </div>
                    </div>
                </div>

                <!-- Hidden reCAPTCHA v3 token field -->
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

    <!-- Logo Upload Script -->
    <script>
        const logoUploadArea = document.getElementById('logoUploadArea');
        const logoFile = document.getElementById('logo_file');
        const logoPreview = document.getElementById('logoPreview');
        const logoImage = document.getElementById('logoImage');
        const removeLogo = document.getElementById('removeLogo');

        // Click to upload
        logoUploadArea.addEventListener('click', () => {
            logoFile.click();
        });

        // File input change
        logoFile.addEventListener('change', (e) => {
            handleFile(e.target.files[0]);
        });

        // Drag and drop
        logoUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            logoUploadArea.classList.add('dragover');
        });

        logoUploadArea.addEventListener('dragleave', () => {
            logoUploadArea.classList.remove('dragover');
        });

        logoUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            logoUploadArea.classList.remove('dragover');

            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                logoFile.files = e.dataTransfer.files;
                handleFile(file);
            }
        });

        // Remove logo
        removeLogo.addEventListener('click', () => {
            logoFile.value = '';
            logoPreview.classList.remove('active');
            logoUploadArea.style.display = 'block';
        });

        // Handle file preview
        function handleFile(file) {
            if (!file) return;

            // Check file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('{{ app()->getLocale() == 'ar' ? 'حجم الملف يجب أن يكون أقل من 2 ميجابايت' : 'File size must be less than 2MB' }}');
                logoFile.value = '';
                return;
            }

            // Check file type
            if (!['image/png', 'image/jpeg', 'image/jpg'].includes(file.type)) {
                alert('{{ app()->getLocale() == 'ar' ? 'يرجى اختيار صورة بصيغة PNG أو JPG' : 'Please select a PNG or JPG image' }}');
                logoFile.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                logoImage.src = e.target.result;
                logoPreview.classList.add('active');
                logoUploadArea.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    </script>

    <!-- reCAPTCHA v3 Script -->
    @if(config('services.recaptcha.enabled'))
    <script>
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'submit'}).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    document.querySelector('form').submit();
                });
            });
        });
    </script>
    @endif
</body>
</html>
