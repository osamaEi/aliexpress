<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل بائع جديد - الخطوة 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <!-- Cairo Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            filter: brightness(0) invert(1);
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
            text-align: center;
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

        .icon-box {
            width: 100px;
            height: 100px;
            background: rgba(86, 28, 4, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }

        .icon-box i {
            font-size: 50px;
            color: var(--primary-color);
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        .otp-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }

        .otp-info p {
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }

        .otp-info strong {
            color: var(--primary-color);
            font-size: 18px;
        }

        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
        }

        .otp-input {
            width: 55px;
            height: 55px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .otp-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(86, 28, 4, 0.08);
            outline: none;
        }

        .btn-verify {
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
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(86, 28, 4, 0.25);
        }

        .btn-resend {
            background: transparent;
            color: var(--primary-color);
            border: none;
            font-weight: 600;
            text-decoration: underline;
            padding: 10px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-resend:hover {
            color: var(--secondary-color);
        }

        .btn-resend:disabled {
            color: #999;
            cursor: not-allowed;
            text-decoration: none;
        }

        .timer {
            color: #999;
            font-size: 14px;
            margin-top: 10px;
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

        .alert-success {
            background: #efe;
            color: #0a0;
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

            .otp-input {
                width: 45px;
                height: 45px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-wrapper">
                <img src="{{ asset('logo/logo.png') }}" alt="Logo">
                <h3>تسجيل البائع</h3>
            </div>

            <div class="progress-sidebar">
                <div class="progress-step completed">
                    <div class="step-number"><i class="ri-check-line"></i></div>
                    <div class="step-content">
                        <h4>المعلومات الأساسية</h4>
                        <p>البيانات الشخصية والتواصل</p>
                    </div>
                </div>

                <div class="progress-step completed">
                    <div class="step-number"><i class="ri-check-line"></i></div>
                    <div class="step-content">
                        <h4>النشاط التجاري</h4>
                        <p>حدد مجال عملك</p>
                    </div>
                </div>

                <div class="progress-step active">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>التحقق</h4>
                        <p>تأكيد البريد الإلكتروني</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="content-header">
                <div class="icon-box pulse">
                    <i class="ri-mail-check-line"></i>
                </div>
                <h2>تحقق من بريدك الإلكتروني</h2>
                <p>أدخل الرمز المرسل إليك لإتمام التسجيل</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line"></i>
                    <strong>{{ $errors->first() }}</strong>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    <i class="ri-checkbox-circle-line"></i>
                    {{ session('success') }}
                </div>
            @endif

            <div class="otp-info">
                <p>تم إرسال رمز التحقق المكون من 6 أرقام إلى:</p>
                <strong>{{ $email }}</strong>
                <p style="margin-top: 15px; margin-bottom: 0; font-size: 13px; color: #999;">يرجى التحقق من صندوق الوارد أو البريد المزعج</p>
            </div>

            <form action="{{ route('seller.register.verify-otp') }}" method="POST" id="otpForm">
                @csrf

                <div style="text-align: center;">
                    <label style="display: block; font-weight: 600; margin-bottom: 15px; color: #333;">أدخل رمز التحقق</label>
                    <div class="otp-inputs">
                        <input type="text" class="otp-input" maxlength="1" id="otp1" />
                        <input type="text" class="otp-input" maxlength="1" id="otp2" />
                        <input type="text" class="otp-input" maxlength="1" id="otp3" />
                        <input type="text" class="otp-input" maxlength="1" id="otp4" />
                        <input type="text" class="otp-input" maxlength="1" id="otp5" />
                        <input type="text" class="otp-input" maxlength="1" id="otp6" />
                    </div>
                    <input type="hidden" name="otp" id="otpHidden" />
                </div>

                <button type="submit" class="btn-verify">
                    <i class="ri-check-double-line"></i>
                    تحقق وأكمل التسجيل
                </button>
            </form>

            <div style="text-align: center; margin-top: 30px;">
                <p style="color: #999; margin-bottom: 10px; font-size: 14px;">لم تستلم الرمز؟</p>
                <form action="{{ route('seller.register.resend-otp') }}" method="POST" id="resendForm">
                    @csrf
                    <button type="submit" class="btn-resend" id="resendBtn">
                        إعادة إرسال الرمز
                    </button>
                </form>
                <div class="timer" id="timer"></div>
            </div>
        </div>
    </div>

    <script>
        // OTP Input Handling
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpHidden = document.getElementById('otpHidden');
        const otpForm = document.getElementById('otpForm');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;

                // Only allow numbers
                if (!/^\d$/.test(value)) {
                    e.target.value = '';
                    return;
                }

                // Move to next input
                if (value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }

                // Update hidden input
                updateOTPValue();
            });

            input.addEventListener('keydown', (e) => {
                // Handle backspace
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').slice(0, 6);

                pastedData.split('').forEach((char, i) => {
                    if (otpInputs[i] && /^\d$/.test(char)) {
                        otpInputs[i].value = char;
                    }
                });

                updateOTPValue();
                otpInputs[Math.min(pastedData.length, 5)].focus();
            });
        });

        function updateOTPValue() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            otpHidden.value = otp;
        }

        // Timer for resend button
        let timeLeft = 60;
        const resendBtn = document.getElementById('resendBtn');
        const timerDiv = document.getElementById('timer');

        function startTimer() {
            timeLeft = 60;
            resendBtn.disabled = true;

            const interval = setInterval(() => {
                timeLeft--;
                timerDiv.textContent = `يمكنك إعادة الإرسال بعد ${timeLeft} ثانية`;

                if (timeLeft <= 0) {
                    clearInterval(interval);
                    resendBtn.disabled = false;
                    timerDiv.textContent = '';
                }
            }, 1000);
        }

        // Start timer on page load
        startTimer();

        // Handle resend form submission
        document.getElementById('resendForm').addEventListener('submit', () => {
            startTimer();
        });

        // Auto-focus first input
        otpInputs[0].focus();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
