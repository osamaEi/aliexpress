<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() == 'ar' ? 'رمز التحقق' : 'Verification Code' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 40px 20px;
        }

        .email-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }

        .email-header {
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .logo {
            height: 32px;
            width: auto;
        }

        .login-btn {
            padding: 8px 20px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .email-body {
            padding: 40px;
            text-align: center;
        }

        .title {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 30px;
        }

        .otp-container {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .otp-code {
            font-size: 36px;
            font-weight: 700;
            color: #561C04;
            letter-spacing: 6px;
            font-family: 'Courier New', monospace;
        }

        .warning-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .note-text {
            font-size: 14px;
            color: #666;
        }

        .note-text strong {
            color: #333;
        }

        .email-footer {
            background-color: #f8f9fa;
            padding: 25px 40px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .footer-logo img {
            height: 20px;
            opacity: 0.6;
        }

        .footer-logo span {
            color: #888;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .footer-text {
            font-size: 11px;
            color: #999;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: 1px solid #e0e0e0;
            border-radius: 50%;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .social-links a:hover {
            border-color: #561C04;
            color: #561C04;
        }

        @media only screen and (max-width: 600px) {
            .email-header {
                padding: 20px;
            }

            .email-body {
                padding: 30px 20px;
            }

            .email-footer {
                padding: 20px;
            }

            .title {
                font-size: 20px;
            }

            .otp-code {
                font-size: 28px;
                letter-spacing: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="https://i.selaa.ae/logo/logo.png" alt="Selaa" class="logo">
            <a href="https://i.selaa.ae/login" class="login-btn">{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول' : 'Log in' }}</a>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h1 class="title">
                {{ app()->getLocale() == 'ar' ? 'رمز التحقق الخاص بك:' : 'Here is your verification code:' }}
            </h1>

            <div class="otp-container">
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <p class="warning-text">
                {{ app()->getLocale() == 'ar'
                    ? 'يرجى التأكد من عدم مشاركة هذا الرمز مع أي شخص.'
                    : 'Please make sure you never share this code with anyone.' }}
            </p>

            <p class="note-text">
                <strong>{{ app()->getLocale() == 'ar' ? 'ملاحظة:' : 'Note:' }}</strong>
                {{ app()->getLocale() == 'ar'
                    ? 'سينتهي صلاحية هذا الرمز خلال 10 دقائق.'
                    : 'The code will expire in 10 minutes.' }}
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-logo">
                <img src="{{ url('footer.png') }}" alt="EVORQ">
                <span>EVORQ TECHNOLOGIES</span>
            </div>

            <p class="footer-text">
                {{ app()->getLocale() == 'ar'
                    ? 'لقد تلقيت هذا البريد الإلكتروني لأنك مسجل في Selaa.'
                    : 'You have received this email because you are registered at Selaa.' }}
            </p>

            <div class="social-links">
                <a href="#" title="LinkedIn">in</a>
                <a href="#" title="Facebook">f</a>
                <a href="#" title="Instagram">@</a>
                <a href="#" title="Twitter">X</a>
            </div>
        </div>
    </div>
</body>
</html>
