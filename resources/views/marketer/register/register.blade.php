<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
@php $ar = app()->getLocale() == 'ar'; @endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ar ? 'تسجيل مسوّق جديد' : 'Marketer Registration' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #561C04; --secondary: #7a2805; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Cairo',sans-serif; background:linear-gradient(135deg,#561C04 0%,#7a2805 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        .reg-card { max-width:520px; width:100%; background:#fff; border-radius:20px; overflow:hidden; box-shadow:0 25px 50px rgba(0,0,0,.25); }
        .reg-head { background:linear-gradient(135deg,#561C04,#7a2805); color:#fff; padding:32px; text-align:center; }
        .reg-head .icon { width:64px; height:64px; border-radius:16px; background:rgba(255,255,255,.15); display:inline-flex; align-items:center; justify-content:center; font-size:32px; margin-bottom:12px; }
        .reg-body { padding:32px; }
        .form-label { font-weight:600; font-size:14px; color:#444; }
        .form-control, .form-select { border-radius:10px; padding:10px 14px; border:1px solid #e0d9d4; }
        .form-control:focus, .form-select:focus { border-color:var(--primary); box-shadow:0 0 0 .2rem rgba(86,28,4,.15); }
        .btn-reg { background:linear-gradient(135deg,#561C04,#7a2805); color:#fff; border:none; border-radius:10px; padding:12px; font-weight:700; width:100%; }
        .btn-reg:hover { opacity:.92; color:#fff; }
        a { color:var(--primary); }
    </style>
</head>
<body>
    <div class="reg-card">
        <div class="reg-head">
            <div class="icon"><i class="ri-megaphone-line"></i></div>
            <h3 class="mb-1">{{ $ar ? 'انضم كمسوّق' : 'Join as a Marketer' }}</h3>
            <p class="mb-0" style="opacity:.85;">{{ $ar ? 'روّج الكوبونات واربح عمولة على كل استخدام' : 'Promote coupons and earn commission on every use' }}</p>
        </div>
        <div class="reg-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('marketer.register.register') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ $ar ? 'الاسم الكامل' : 'Full Name' }} *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $ar ? 'البريد الإلكتروني' : 'Email' }} *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label">{{ $ar ? 'رقم الهاتف' : 'Phone' }} *</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">{{ $ar ? 'الدولة' : 'Country' }} *</label>
                        <select name="country" class="form-select" required>
                            <option value="">{{ $ar ? 'اختر' : 'Select' }}</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->code }}" {{ old('country') == $c->code ? 'selected' : '' }}>
                                    {{ $c->flag }} {{ $ar ? ($c->name_ar ?: $c->name) : $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">{{ $ar ? 'كلمة المرور' : 'Password' }} *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $ar ? 'تأكيد كلمة المرور' : 'Confirm Password' }} *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn-reg"><i class="ri-user-add-line me-1"></i>{{ $ar ? 'إنشاء الحساب' : 'Create Account' }}</button>
            </form>

            <div class="text-center mt-3">
                <span class="text-muted">{{ $ar ? 'لديك حساب؟' : 'Already have an account?' }}</span>
                <a href="{{ route('login') }}">{{ $ar ? 'تسجيل الدخول' : 'Login' }}</a>
            </div>
        </div>
    </div>
</body>
</html>
