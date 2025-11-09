<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل بائع جديد - الخطوة 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <style>
        :root {
            --primary-color: #561C04;
            --secondary-color: #7a2805;
            --success-color: #10b981;
            --gradient: linear-gradient(135deg, #561C04 0%, #7a2805 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .registration-container {
            max-width: 800px;
            width: 100%;
        }

        .registration-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header-custom {
            background: var(--gradient);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .card-header-custom h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .card-header-custom p {
            opacity: 0.9;
            font-size: 16px;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            padding: 30px;
            background: #f8f9fa;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            right: -50%;
            height: 3px;
            background: #e0e0e0;
            z-index: -1;
        }

        .step:last-child::before {
            display: none;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transition: all 0.3s;
        }

        .step.active .step-circle {
            background: var(--gradient);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(86, 28, 4, 0.4);
        }

        .step.completed .step-circle {
            background: var(--success-color);
            color: white;
        }

        .step-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .step.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }

        .card-body-custom {
            padding: 40px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(86, 28, 4, 0.1);
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }

        .input-icon .form-control {
            padding-right: 45px;
        }

        .btn-next {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 14px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            width: 100%;
            margin-top: 20px;
        }

        .btn-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(86, 28, 4, 0.4);
        }

        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 12px 16px;
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(86, 28, 4, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .icon-box i {
            font-size: 40px;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-card">
            <!-- Header -->
            <div class="card-header-custom">
                <div class="icon-box">
                    <i class="ri-store-2-line"></i>
                </div>
                <h2>انضم إلى منصتنا كبائع</h2>
                <p>ابدأ رحلتك في البيع معنا بخطوات بسيطة</p>
            </div>

            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step active">
                    <div class="step-circle">1</div>
                    <div class="step-label">المعلومات الأساسية</div>
                </div>
                <div class="step">
                    <div class="step-circle">2</div>
                    <div class="step-label">النشاط التجاري</div>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <div class="step-label">التحقق</div>
                </div>
            </div>

            <!-- Form -->
            <div class="card-body-custom">
                @if ($errors->any())
                    <div class="alert alert-danger alert-custom mb-4">
                        <i class="ri-error-warning-line me-2"></i>
                        <strong>يرجى تصحيح الأخطاء التالية:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('seller.register.step1.process') }}" method="POST">
                    @csrf

                    <div class="row g-4">
                        <!-- Full Name -->
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">
                                <i class="ri-user-line me-1"></i> الاسم الكامل
                            </label>
                            <div class="input-icon">
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                       id="full_name" name="full_name"
                                       value="{{ old('full_name') }}"
                                       placeholder="أدخل اسمك الكامل" required>
                                <i class="ri-user-line"></i>
                            </div>
                            @error('full_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Company Name -->
                        <div class="col-md-6">
                            <label for="company_name" class="form-label">
                                <i class="ri-building-line me-1"></i> اسم الشركة
                            </label>
                            <div class="input-icon">
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                       id="company_name" name="company_name"
                                       value="{{ old('company_name') }}"
                                       placeholder="أدخل اسم شركتك" required>
                                <i class="ri-building-line"></i>
                            </div>
                            @error('company_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Country -->
                        <div class="col-md-6">
                            <label for="country" class="form-label">
                                <i class="ri-map-pin-line me-1"></i> الدولة
                            </label>
                            <select class="form-select @error('country') is-invalid @enderror"
                                    id="country" name="country" required>
                                <option value="">اختر الدولة</option>
                                <option value="الإمارات العربية المتحدة" {{ old('country') == 'الإمارات العربية المتحدة' ? 'selected' : '' }}>الإمارات العربية المتحدة</option>
                                <option value="السعودية" {{ old('country') == 'السعودية' ? 'selected' : '' }}>السعودية</option>
                                <option value="مصر" {{ old('country') == 'مصر' ? 'selected' : '' }}>مصر</option>
                                <option value="الكويت" {{ old('country') == 'الكويت' ? 'selected' : '' }}>الكويت</option>
                                <option value="قطر" {{ old('country') == 'قطر' ? 'selected' : '' }}>قطر</option>
                                <option value="البحرين" {{ old('country') == 'البحرين' ? 'selected' : '' }}>البحرين</option>
                                <option value="عُمان" {{ old('country') == 'عُمان' ? 'selected' : '' }}>عُمان</option>
                                <option value="الأردن" {{ old('country') == 'الأردن' ? 'selected' : '' }}>الأردن</option>
                                <option value="لبنان" {{ old('country') == 'لبنان' ? 'selected' : '' }}>لبنان</option>
                                <option value="العراق" {{ old('country') == 'العراق' ? 'selected' : '' }}>العراق</option>
                                <option value="فلسطين" {{ old('country') == 'فلسطين' ? 'selected' : '' }}>فلسطين</option>
                                <option value="المغرب" {{ old('country') == 'المغرب' ? 'selected' : '' }}>المغرب</option>
                                <option value="الجزائر" {{ old('country') == 'الجزائر' ? 'selected' : '' }}>الجزائر</option>
                                <option value="تونس" {{ old('country') == 'تونس' ? 'selected' : '' }}>تونس</option>
                                <option value="ليبيا" {{ old('country') == 'ليبيا' ? 'selected' : '' }}>ليبيا</option>
                                <option value="السودان" {{ old('country') == 'السودان' ? 'selected' : '' }}>السودان</option>
                                <option value="اليمن" {{ old('country') == 'اليمن' ? 'selected' : '' }}>اليمن</option>
                            </select>
                            @error('country')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">
                                <i class="ri-mail-line me-1"></i> البريد الإلكتروني
                            </label>
                            <div class="input-icon">
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email"
                                       value="{{ old('email') }}"
                                       placeholder="example@email.com" required>
                                <i class="ri-mail-line"></i>
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">سنرسل رمز التحقق إلى هذا البريد</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-next">
                        التالي <i class="ri-arrow-left-line ms-2"></i>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="text-muted mb-0">
                        لديك حساب بالفعل؟
                        <a href="{{ route('login') }}" class="text-decoration-none" style="color: var(--primary-color); font-weight: 600;">
                            تسجيل الدخول
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
