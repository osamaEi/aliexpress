<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل بائع جديد - الخطوة 3</title>
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
            max-width: 600px;
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
            width: 60px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .otp-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(86, 28, 4, 0.1);
            outline: none;
        }

        .btn-verify {
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

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(86, 28, 4, 0.4);
        }

        .btn-resend {
            background: transparent;
            color: var(--primary-color);
            border: none;
            font-weight: 600;
            text-decoration: underline;
            padding: 10px;
            cursor: pointer;
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

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-card">
            <!-- Header -->
            <div class="card-header-custom">
                <div class="icon-box pulse">
                    <i class="ri-mail-check-line"></i>
                </div>
                <h2>تحقق من بريدك الإلكتروني</h2>
                <p>أدخل الرمز المرسل إليك</p>
            </div>

            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step completed">
                    <div class="step-circle"><i class="ri-check-line"></i></div>
                    <div class="step-label">المعلومات الأساسية</div>
                </div>
                <div class="step completed">
                    <div class="step-circle"><i class="ri-check-line"></i></div>
                    <div class="step-label">النشاط التجاري</div>
                </div>
                <div class="step active">
                    <div class="step-circle">3</div>
                    <div class="step-label">التحقق</div>
                </div>
            </div>

            <!-- Form -->
            <div class="card-body-custom">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4" style="border-radius: 12px;">
                        <i class="ri-error-warning-line me-2"></i>
                        <strong>{{ $errors->first() }}</strong>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success mb-4" style="border-radius: 12px;">
                        <i class="ri-checkbox-circle-line me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="otp-info">
                    <p>تم إرسال رمز التحقق المكون من 6 أرقام إلى:</p>
                    <strong>{{ $email }}</strong>
                    <p class="mt-3 mb-0 small text-muted">يرجى التحقق من صندوق الوارد أو البريد المزعج</p>
                </div>

                <form action="{{ route('seller.register.verify-otp') }}" method="POST" id="otpForm">
                    @csrf

                    <div class="text-center">
                        <label class="form-label fw-bold mb-3">أدخل رمز التحقق</label>
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

                    <button type="submit" class="btn btn-verify">
                        <i class="ri-check-double-line me-2"></i> تحقق وأكمل التسجيل
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="text-muted mb-2">لم تستلم الرمز؟</p>
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
