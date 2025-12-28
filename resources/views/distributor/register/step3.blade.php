<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() == 'ar' ? 'تسجيل متجر جديد - الخطوة 3' : 'Distributor Registration - Step 3' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #561C04;
            --secondary-color: #7a2805;
            --success-color: #10b981;
            --distributor-color: #1e40af;
            --distributor-secondary: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom right, #f0f9ff 0%, #e0f2fe 100%);
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
            box-shadow: 0 25px 50px rgba(30, 64, 175, 0.12);
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
            max-height: 100vh;
            overflow-y: auto;
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
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.08);
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
            box-shadow: 0 10px 25px rgba(30, 64, 175, 0.25);
        }

        .btn-back {
            width: 100%;
            padding: 15px;
            background: #f3f4f6;
            color: #333;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #e5e7eb;
            color: #333;
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

        /* Currency Section */
        .section-box {
            background: #f8fafc;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .section-title i {
            color: var(--distributor-color);
            font-size: 24px;
        }

        .currency-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .currency-option {
            position: relative;
        }

        .currency-option input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .currency-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 15px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .currency-option input:checked + label {
            border-color: var(--distributor-color);
            background: #eff6ff;
        }

        .currency-option label:hover {
            border-color: var(--distributor-secondary);
        }

        .currency-symbol {
            font-size: 24px;
            font-weight: 700;
            color: var(--distributor-color);
            margin-bottom: 8px;
        }

        .currency-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 12px;
            margin-bottom: 12px;
            color: var(--distributor-color);
        }

        .currency-option input:checked + label .currency-icon-wrapper {
            background: linear-gradient(135deg, var(--distributor-color) 0%, var(--distributor-secondary) 100%);
            color: white;
        }

        .currency-symbol-text {
            font-size: 24px;
            font-weight: 700;
        }

        .currency-name {
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }

        .currency-code {
            font-size: 12px;
            color: #666;
        }

        /* Social Media Section */
        .social-media-container {
            margin-top: 20px;
        }

        .social-media-item {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .social-media-item .form-select {
            width: 180px;
            flex-shrink: 0;
        }

        .social-media-item .form-control {
            flex: 1;
        }

        .social-media-item .btn-remove {
            padding: 13px 15px;
            background: #fee2e2;
            color: #ef4444;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            flex-shrink: 0;
        }

        .social-media-item .btn-remove:hover {
            background: #fecaca;
        }

        .btn-add-social {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: #eff6ff;
            color: var(--distributor-color);
            border: 2px dashed var(--distributor-secondary);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-add-social:hover {
            background: #dbeafe;
        }

        .social-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .social-icon.facebook { color: #1877f2; }
        .social-icon.instagram { color: #e4405f; }
        .social-icon.twitter { color: #1da1f2; }
        .social-icon.tiktok { color: #000; }
        .social-icon.snapchat { color: #fffc00; }
        .social-icon.youtube { color: #ff0000; }
        .social-icon.whatsapp { color: #25d366; }
        .social-icon.telegram { color: #0088cc; }
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
                <div class="sidebar-footer">
                    <img src="{{ asset('foot.png') }}" alt="EVORQ">
                </div>
                <h3>{{ app()->getLocale() == 'ar' ? 'تسجيل متجر جديد' : 'Distributor Registration' }}</h3>
                <div class="distributor-badge">
                    <i class="ri-truck-line"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'حساب متجر' : 'Distributor Account' }}</span>
                </div>
            </div>

            <div class="progress-sidebar">
                <div class="progress-step completed">
                    <div class="step-number"><i class="ri-check-line"></i></div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'المعلومات الأساسية' : 'Basic Information' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'بيانات المتجر والتواصل' : 'Store and contact details' }}</p>
                    </div>
                </div>

                <div class="progress-step completed">
                    <div class="step-number"><i class="ri-check-line"></i></div>
                    <div class="step-content">
                        <h4>{{ app()->getLocale() == 'ar' ? 'المستندات' : 'Documents' }}</h4>
                        <p>{{ app()->getLocale() == 'ar' ? 'السجل التجاري والشعار' : 'Commercial register & logo' }}</p>
                    </div>
                </div>

                <div class="progress-step active">
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
                <h2>{{ app()->getLocale() == 'ar' ? 'إعدادات المتجر' : 'Store Settings' }}</h2>
                <p>{{ app()->getLocale() == 'ar' ? 'اختر العملة الافتراضية وأضف حسابات التواصل الاجتماعي' : 'Choose default currency and add social media accounts' }}</p>
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

            <form action="{{ route('distributor.register.step3.process') }}" method="POST">
                @csrf

                <!-- Currency Selection -->
                <div class="section-box">
                    <div class="section-title">
                        <i class="ri-money-dollar-circle-line"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'العملة الافتراضية' : 'Default Currency' }} <span class="required">*</span></span>
                    </div>

                    <div class="currency-options">
                        @foreach($currencies as $currency)
                        <div class="currency-option">
                            <input type="radio"
                                   name="default_currency"
                                   id="currency_{{ $currency->code }}"
                                   value="{{ $currency->code }}"
                                   {{ old('default_currency', 'AED') == $currency->code ? 'checked' : '' }}
                                   required>
                            <label for="currency_{{ $currency->code }}">
                                <span class="currency-icon-wrapper">
                                    @if($currency->code === 'AED')
                                        <x-dirham-icon width="32" height="32" />
                                    @elseif($currency->code === 'SAR')
                                        <x-riyal-icon width="32" height="32" />
                                    @elseif($currency->code === 'USD')
                                        <x-dollar-icon width="32" height="32" />
                                    @else
                                        <span class="currency-symbol-text">{{ $currency->symbol }}</span>
                                    @endif
                                </span>
                                <span class="currency-name">{{ $currency->name }}</span>
                                <span class="currency-code">{{ $currency->code }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('default_currency')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Social Media Accounts -->
                <div class="section-box">
                    <div class="section-title">
                        <i class="ri-share-line"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'حسابات التواصل الاجتماعي' : 'Social Media Accounts' }}</span>
                    </div>

                    <p class="text-muted mb-3">
                        {{ app()->getLocale() == 'ar' ? 'أضف روابط حسابات التواصل الاجتماعي الخاصة بمتجرك (اختياري)' : 'Add your store social media account links (optional)' }}
                    </p>

                    <div class="social-media-container" id="socialMediaContainer">
                        <div class="social-media-item">
                            <select name="social_media[0][type]" class="form-select">
                                <option value="">{{ app()->getLocale() == 'ar' ? 'اختر المنصة' : 'Select Platform' }}</option>
                                <option value="instagram">Instagram</option>
                                <option value="facebook">Facebook</option>
                                <option value="twitter">Twitter / X</option>
                                <option value="tiktok">TikTok</option>
                                <option value="snapchat">Snapchat</option>
                                <option value="youtube">YouTube</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="telegram">Telegram</option>
                            </select>
                            <input type="url"
                                   name="social_media[0][url]"
                                   class="form-control"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'رابط الحساب' : 'Account URL' }}">
                            <button type="button" class="btn-remove" onclick="removeSocialMedia(this)" style="display: none;">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>

                    <button type="button" class="btn-add-social" id="addSocialMedia">
                        <i class="ri-add-line"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'إضافة حساب آخر' : 'Add Another Account' }}</span>
                    </button>
                </div>

                <button type="submit" class="btn-continue">
                    <span>{{ app()->getLocale() == 'ar' ? 'التالي' : 'Next' }}</span>
                    <i class="{{ app()->getLocale() == 'ar' ? 'ri-arrow-left-line' : 'ri-arrow-right-line' }}"></i>
                </button>

                <a href="{{ route('distributor.register.step2') }}" class="btn-back">
                    <i class="{{ app()->getLocale() == 'ar' ? 'ri-arrow-right-line' : 'ri-arrow-left-line' }}"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'رجوع' : 'Back' }}</span>
                </a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let socialMediaIndex = 1;

        document.getElementById('addSocialMedia').addEventListener('click', function() {
            const container = document.getElementById('socialMediaContainer');
            const newItem = document.createElement('div');
            newItem.className = 'social-media-item';
            newItem.innerHTML = `
                <select name="social_media[${socialMediaIndex}][type]" class="form-select">
                    <option value="">{{ app()->getLocale() == 'ar' ? 'اختر المنصة' : 'Select Platform' }}</option>
                    <option value="instagram">Instagram</option>
                    <option value="facebook">Facebook</option>
                    <option value="twitter">Twitter / X</option>
                    <option value="tiktok">TikTok</option>
                    <option value="snapchat">Snapchat</option>
                    <option value="youtube">YouTube</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="telegram">Telegram</option>
                </select>
                <input type="url"
                       name="social_media[${socialMediaIndex}][url]"
                       class="form-control"
                       placeholder="{{ app()->getLocale() == 'ar' ? 'رابط الحساب' : 'Account URL' }}">
                <button type="button" class="btn-remove" onclick="removeSocialMedia(this)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            `;
            container.appendChild(newItem);
            socialMediaIndex++;
            updateRemoveButtons();
        });

        function removeSocialMedia(button) {
            button.closest('.social-media-item').remove();
            updateRemoveButtons();
        }

        function updateRemoveButtons() {
            const items = document.querySelectorAll('.social-media-item');
            items.forEach((item, index) => {
                const removeBtn = item.querySelector('.btn-remove');
                if (removeBtn) {
                    removeBtn.style.display = items.length > 1 ? 'block' : 'none';
                }
            });
        }

        updateRemoveButtons();
    </script>
</body>
</html>
