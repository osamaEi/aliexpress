<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Register') }} - {{ setting('site_name', 'EcommAli') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(app()->getLocale() == 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @else
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @endif

    @if(setting('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . setting('site_favicon')) }}" />
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    @endif

    <style>
        :root {
            --primary-color: #561C04;
            --secondary-color: #7a2805;
            --success-color: #10b981;
            --gradient: linear-gradient(135deg, #561C04 0%, #7a2805 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Cairo'" : "'Inter'" }}, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            max-width: 1200px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(86, 28, 4, 0.15);
            overflow: hidden;
            animation: slideIn 0.6s ease-out;
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

        .left-panel {
            background: var(--gradient);
            padding: 60px 50px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
        }

        .logo-container {
            margin-bottom: 40px;
            z-index: 1;
        }

        .logo-container img {
            max-width: 180px;
            height: auto;
            filter: brightness(0) invert(1);
        }

        .left-panel h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            z-index: 1;
        }

        .left-panel p {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.9;
            z-index: 1;
        }

        .features {
            margin-top: 40px;
            text-align: left;
            z-index: 1;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .feature-text h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .feature-text p {
            font-size: 14px;
            opacity: 0.8;
            margin: 0;
        }

        .right-panel {
            padding: 60px 50px;
            background: white;
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-label i {
            margin-right: 5px;
            color: var(--primary-color);
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            padding-left: 45px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(86, 28, 4, 0.1);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
            pointer-events: none;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }

        .btn-register {
            width: 100%;
            padding: 16px;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(86, 28, 4, 0.4);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 30px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }

        .divider span {
            padding: 0 15px;
            color: #999;
            font-size: 14px;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 15px;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .login-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: none;
        }

        .alert-danger {
            background: #fee;
            color: #c00;
        }

        .password-strength {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 2px;
        }

        @media (max-width: 992px) {
            .register-container {
                grid-template-columns: 1fr;
            }

            .left-panel {
                display: none;
            }

            .right-panel {
                padding: 40px 30px;
            }
        }

        @media (max-width: 576px) {
            .right-panel {
                padding: 30px 20px;
            }

            .form-header h2 {
                font-size: 24px;
            }

            .logo-container img {
                max-width: 140px;
            }
        }

        /* RTL Support */
        [dir="rtl"] .form-control {
            padding-left: 16px;
            padding-right: 45px;
        }

        [dir="rtl"] .input-icon {
            left: auto;
            right: 16px;
        }

        [dir="rtl"] .form-label i {
            margin-right: 0;
            margin-left: 5px;
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
    </style>
</head>
<body>
    <!-- Language Switcher -->
    <div class="language-switcher">
        <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">
            <span>🇬🇧</span>
            <span>English</span>
        </a>
        <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">
            <span>🇸🇦</span>
            <span>العربية</span>
        </a>
    </div>

    <div class="register-container">
        <!-- Left Panel -->
        <div class="left-panel">
            <div class="logo-container">
                <img src="{{ asset('logo/logo.png') }}" alt="{{ setting('site_name', 'EcommAli') }}">
            </div>

            <h1>{{ __('Join Our Platform') }}</h1>
            <p>{{ __('Start your journey with us today and unlock amazing opportunities') }}</p>

            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="ri-shield-check-line"></i>
                    </div>
                    <div class="feature-text">
                        <h4>{{ __('Secure & Safe') }}</h4>
                        <p>{{ __('Your data is protected with industry-standard encryption') }}</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="ri-rocket-line"></i>
                    </div>
                    <div class="feature-text">
                        <h4>{{ __('Quick Setup') }}</h4>
                        <p>{{ __('Get started in minutes with our easy registration process') }}</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="ri-customer-service-line"></i>
                    </div>
                    <div class="feature-text">
                        <h4>{{ __('24/7 Support') }}</h4>
                        <p>{{ __('Our team is always here to help you succeed') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <div class="form-header">
                <h2>{{ __('Create Account') }}</h2>
                <p>{{ __('Fill in your details to get started') }}</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    <strong>{{ __('Oops! Something went wrong') }}</strong>
                    <ul class="mb-0 mt-2" style="padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf

                <!-- Name -->
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="ri-user-line"></i> {{ __('Full Name') }}
                    </label>
                    <div class="input-wrapper">
                        <i class="input-icon ri-user-line"></i>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="{{ __('Enter your full name') }}"
                               required
                               autofocus>
                    </div>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="ri-mail-line"></i> {{ __('Email Address') }}
                    </label>
                    <div class="input-wrapper">
                        <i class="input-icon ri-mail-line"></i>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="{{ __('Enter your email') }}"
                               required>
                    </div>
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="ri-lock-line"></i> {{ __('Password') }}
                    </label>
                    <div class="input-wrapper">
                        <i class="input-icon ri-lock-line"></i>
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               placeholder="{{ __('Create a strong password') }}"
                               required>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        <i class="ri-lock-check-line"></i> {{ __('Confirm Password') }}
                    </label>
                    <div class="input-wrapper">
                        <i class="input-icon ri-lock-check-line"></i>
                        <input type="password"
                               class="form-control"
                               id="password_confirmation"
                               name="password_confirmation"
                               placeholder="{{ __('Confirm your password') }}"
                               required>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-register">
                    <i class="ri-user-add-line me-2"></i> {{ __('Create Account') }}
                </button>

                <!-- Login Link -->
                <div class="login-link">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}">{{ __('Sign In') }}</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer with EVORQ Logo -->
    <div style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 100; display: flex; align-items: center; gap: 8px;">
        <span style="color: #808080; font-size: 14px; font-weight: 400;">BY</span>
        <img src="{{ asset('footer.png') }}"
             alt="EVORQ Logo"
             style="height: 45px; opacity: 0.75; transition: opacity 0.3s;"
             onmouseover="this.style.opacity='1'"
             onmouseout="this.style.opacity='0.75'">
        <span style="color: #808080; font-size: 14px; font-weight: 400; letter-spacing: 2px;">EVORQ TECHNOLOGIES</span>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength += 25;
            if (password.match(/[a-z]+/)) strength += 25;
            if (password.match(/[A-Z]+/)) strength += 25;
            if (password.match(/[0-9]+/)) strength += 25;

            strengthBar.style.width = strength + '%';

            if (strength <= 25) {
                strengthBar.style.background = '#dc3545';
            } else if (strength <= 50) {
                strengthBar.style.background = '#ffc107';
            } else if (strength <= 75) {
                strengthBar.style.background = '#17a2b8';
            } else {
                strengthBar.style.background = '#28a745';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('{{ __("Passwords do not match!") }}');
                return false;
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
