<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() == 'ar' ? 'رمز التحقق' : 'Verification Code' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&family=Inter:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Cairo'" : "'Inter'" }}, 'Segoe UI', Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #561C04 0%, #7a2805 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 20px;
        }

        .email-header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .email-body {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 20px;
        }

        .message {
            font-size: 15px;
            color: #666666;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .otp-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px dashed #561C04;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }

        .otp-label {
            font-size: 14px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .otp-code {
            font-size: 42px;
            font-weight: 700;
            color: #561C04;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }

        .otp-expiry {
            font-size: 13px;
            color: #999999;
            margin-top: 15px;
        }

        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 6px;
        }

        [dir="rtl"] .warning-box {
            border-left: none;
            border-right: 4px solid #ffc107;
        }

        .warning-box p {
            margin: 0;
            font-size: 13px;
            color: #856404;
            line-height: 1.5;
        }

        .info-text {
            font-size: 14px;
            color: #666666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .footer-logo img {
            height: 40px;
            opacity: 0.7;
        }

        .footer-logo span {
            color: #808080;
            font-size: 13px;
            font-weight: 400;
            letter-spacing: 1.5px;
        }

        .footer-text {
            font-size: 12px;
            color: #999999;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #561C04;
            text-decoration: none;
            font-size: 12px;
        }

        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 25px 0;
        }

        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }

            .email-header {
                padding: 30px 20px;
            }

            .email-footer {
                padding: 20px;
            }

            .otp-code {
                font-size: 32px;
                letter-spacing: 5px;
            }

            .greeting {
                font-size: 16px;
            }

            .message {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="{{ asset('logo/logo.png') }}" alt="Logo" class="logo">
            <h1>{{ app()->getLocale() == 'ar' ? 'رمز التحقق' : 'Verification Code' }}</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                {{ app()->getLocale() == 'ar' ? 'مرحباً!' : 'Hello!' }}
            </div>

            <p class="message">
                {{ app()->getLocale() == 'ar'
                    ? 'شكراً لتسجيلك كبائع في منصتنا. لإكمال عملية التسجيل، يرجى استخدام رمز التحقق التالي:'
                    : 'Thank you for registering as a seller on our platform. To complete your registration, please use the following verification code:' }}
            </p>

            <!-- OTP Container -->
            <div class="otp-container">
                <div class="otp-label">
                    {{ app()->getLocale() == 'ar' ? 'رمز التحقق' : 'VERIFICATION CODE' }}
                </div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-expiry">
                    {{ app()->getLocale() == 'ar'
                        ? 'ينتهي صلاحية هذا الرمز خلال 10 دقائق'
                        : 'This code expires in 10 minutes' }}
                </div>
            </div>

            <!-- Warning Box -->
            <div class="warning-box">
                <p>
                    <strong>{{ app()->getLocale() == 'ar' ? '⚠️ تنبيه أمني:' : '⚠️ Security Warning:' }}</strong><br>
                    {{ app()->getLocale() == 'ar'
                        ? 'لا تشارك هذا الرمز مع أي شخص. فريقنا لن يطلب منك هذا الرمز أبداً.'
                        : 'Never share this code with anyone. Our team will never ask you for this code.' }}
                </p>
            </div>

            <p class="info-text">
                {{ app()->getLocale() == 'ar'
                    ? 'إذا لم تقم بطلب هذا الرمز، يرجى تجاهل هذا البريد الإلكتروني.'
                    : 'If you did not request this code, please ignore this email.' }}
            </p>

            <div class="divider"></div>

            <p class="info-text">
                {{ app()->getLocale() == 'ar'
                    ? 'إذا واجهت أي مشكلة، يرجى التواصل مع فريق الدعم.'
                    : 'If you encounter any issues, please contact our support team.' }}
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-logo">
                <span style="color: #808080; font-weight: 400;">BY</span>
                <img src="{{ asset('footer.png') }}" alt="EVORQ Logo">
                <span>EVORQ TECHNOLOGIES</span>
            </div>

            <p class="footer-text">
                {{ app()->getLocale() == 'ar'
                    ? 'هذا بريد إلكتروني تلقائي، يرجى عدم الرد عليه.'
                    : 'This is an automated email, please do not reply to it.' }}
            </p>

            <p class="footer-text">
                &copy; {{ date('Y') }} {{ config('app.name', 'EcommAli') }}. {{ app()->getLocale() == 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
            </p>

            <div class="social-links">
                <a href="#">{{ app()->getLocale() == 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' }}</a> |
                <a href="#">{{ app()->getLocale() == 'ar' ? 'الشروط والأحكام' : 'Terms of Service' }}</a> |
                <a href="#">{{ app()->getLocale() == 'ar' ? 'الدعم' : 'Support' }}</a>
            </div>
        </div>
    </div>
</body>
</html>
