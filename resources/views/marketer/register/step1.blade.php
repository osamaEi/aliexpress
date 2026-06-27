<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
@php $ar = app()->getLocale() == 'ar'; @endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ar ? 'تسجيل مسوّق - الخطوة 1' : 'Marketer Registration - Step 1' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#561C04; --secondary:#7a2805; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Cairo',sans-serif; background:linear-gradient(to bottom right,#f8f9fa,#e9ecef); min-height:100vh; padding:24px; }
        .lang-switch { position:fixed; top:16px; {{ $ar ? 'left' : 'right' }}:16px; display:flex; gap:8px; background:#fff; padding:6px 10px; border-radius:30px; box-shadow:0 2px 8px rgba(0,0,0,.08); z-index:10; }
        .lang-switch a { display:flex; align-items:center; gap:5px; text-decoration:none; color:#555; font-size:13px; padding:4px 8px; border-radius:20px; }
        .lang-switch a.active { background:var(--primary); color:#fff; }
        .wrap { max-width:1050px; margin:0 auto; display:grid; grid-template-columns:340px 1fr; background:#fff; border-radius:20px; overflow:hidden; box-shadow:0 25px 50px rgba(86,28,4,.12); }
        .side { background:linear-gradient(160deg,#561C04,#7a2805); color:#fff; padding:40px 32px; }
        .side .brand { text-align:center; margin-bottom:36px; }
        .side .brand .ic { width:72px; height:72px; border-radius:18px; background:rgba(255,255,255,.15); display:inline-flex; align-items:center; justify-content:center; font-size:36px; margin-bottom:14px; }
        .side .brand h3 { font-size:20px; }
        .pstep { display:flex; gap:14px; margin-bottom:26px; opacity:.5; }
        .pstep.active { opacity:1; }
        .pstep .n { width:38px; height:38px; border-radius:50%; background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; }
        .pstep.active .n { background:#fff; color:var(--primary); }
        .pstep h4 { font-size:15px; margin-bottom:2px; }
        .pstep p { font-size:12px; opacity:.8; }
        .body { padding:40px; }
        .body h2 { color:var(--primary); margin-bottom:6px; }
        .body .sub { color:#888; margin-bottom:24px; font-size:14px; }
        .form-label { font-weight:600; font-size:13.5px; color:#444; margin-bottom:5px; }
        .form-control, .form-select { border-radius:10px; padding:10px 14px; border:1px solid #e0d9d4; font-size:14px; }
        .form-control:focus, .form-select:focus { border-color:var(--primary); box-shadow:0 0 0 .2rem rgba(86,28,4,.15); }
        .section-h { font-weight:700; color:var(--primary); font-size:15px; margin:24px 0 12px; padding-bottom:8px; border-bottom:1px solid #f0eae6; display:flex; align-items:center; gap:8px; }
        .btn-next { background:linear-gradient(135deg,#561C04,#7a2805); color:#fff; border:none; border-radius:10px; padding:13px; font-weight:700; width:100%; font-size:15px; }
        .btn-next:hover { opacity:.92; color:#fff; }
        .input-group-text { background:#faf7f5; border:1px solid #e0d9d4; color:var(--primary); }
        @media (max-width:768px){ .wrap{ grid-template-columns:1fr; } .side{ padding:28px; } }
    </style>
</head>
<body>
    <div class="lang-switch">
        <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale()=='en'?'active':'' }}"><img src="https://flagcdn.com/w20/gb.png" style="width:18px;height:13px;"> EN</a>
        <a href="{{ route('lang.switch', 'ar') }}" class="{{ app()->getLocale()=='ar'?'active':'' }}"><img src="https://flagcdn.com/w20/ae.png" style="width:18px;height:13px;"> ع</a>
    </div>

    <div class="wrap">
        {{-- Sidebar progress --}}
        <div class="side">
            <div class="brand">
                <div class="ic"><i class="ri-megaphone-line"></i></div>
                <h3>{{ $ar ? 'تسجيل المسوّق' : 'Marketer Registration' }}</h3>
            </div>
            <div class="pstep active">
                <div class="n">1</div>
                <div><h4>{{ $ar ? 'المعلومات والتفاصيل' : 'Info & Details' }}</h4><p>{{ $ar ? 'بياناتك وقنوات التسويق' : 'Your data & marketing channels' }}</p></div>
            </div>
            <div class="pstep">
                <div class="n">2</div>
                <div><h4>{{ $ar ? 'تحقق البريد' : 'Email Verification' }}</h4><p>{{ $ar ? 'رمز عبر البريد' : 'Code via email' }}</p></div>
            </div>
            <div class="pstep">
                <div class="n">3</div>
                <div><h4>{{ $ar ? 'تحقق واتساب' : 'WhatsApp Verification' }}</h4><p>{{ $ar ? 'رمز عبر واتساب' : 'Code via WhatsApp' }}</p></div>
            </div>
        </div>

        {{-- Form --}}
        <div class="body">
            <h2>{{ $ar ? 'أنشئ حساب مسوّق' : 'Create a Marketer Account' }}</h2>
            <p class="sub">{{ $ar ? 'املأ بياناتك لتبدأ الترويج للكوبونات وكسب العمولات' : 'Fill in your details to start promoting coupons and earning commissions' }}</p>

            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('marketer.register.step1.process') }}" enctype="multipart/form-data">
                @csrf

                <div class="section-h"><i class="ri-user-3-line"></i>{{ $ar ? 'البيانات الأساسية' : 'Basic Information' }}</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ $ar ? 'الاسم الكامل' : 'Full Name' }} *</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $ar ? 'البريد الإلكتروني' : 'Email' }} *</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">{{ $ar ? 'الدولة' : 'Country' }} *</label>
                        <select name="country" id="country" class="form-select" required onchange="syncCode()">
                            <option value="">{{ $ar ? 'اختر' : 'Select' }}</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->code }}" data-code="{{ ltrim($c->phone_code, '+') }}" {{ old('country')==$c->code?'selected':'' }}>
                                    {{ $c->flag }} {{ $ar ? ($c->name_ar ?: $c->name) : $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ $ar ? 'رمز الدولة' : 'Code' }} *</label>
                        <div class="input-group">
                            <span class="input-group-text">+</span>
                            <input type="text" name="phone_code" id="phone_code" value="{{ old('phone_code') }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $ar ? 'رقم الهاتف' : 'Phone' }} *</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $ar ? 'كلمة المرور' : 'Password' }} *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $ar ? 'تأكيد كلمة المرور' : 'Confirm Password' }} *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ $ar ? 'الشعار / الصورة الشخصية' : 'Logo / Avatar' }}</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="section-h"><i class="ri-bar-chart-box-line"></i>{{ $ar ? 'تفاصيل التسويق' : 'Marketing Details' }}</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ $ar ? 'سنوات الخبرة' : 'Years of Experience' }}</label>
                        <input type="number" name="experience_years" value="{{ old('experience_years') }}" min="0" max="80" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $ar ? 'حجم الجمهور (متابعين)' : 'Audience Size (followers)' }}</label>
                        <input type="text" name="audience_size" value="{{ old('audience_size') }}" class="form-control" placeholder="{{ $ar ? 'مثال: 10K - 50K' : 'e.g. 10K - 50K' }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ $ar ? 'الموقع الإلكتروني' : 'Website' }}</label>
                        <input type="url" name="website_url" value="{{ old('website_url') }}" class="form-control" placeholder="https://...">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ $ar ? 'قنوات التسويق' : 'Marketing Channels' }}</label>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            @foreach(['social'=>$ar?'سوشيال ميديا':'Social Media','website'=>$ar?'موقع/مدونة':'Website/Blog','whatsapp'=>$ar?'مجموعات واتساب':'WhatsApp Groups','telegram'=>'Telegram','email'=>$ar?'البريد':'Email','ads'=>$ar?'إعلانات مدفوعة':'Paid Ads'] as $val=>$lbl)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="marketing_channels[]" value="{{ $val }}" id="ch_{{ $val }}">
                                    <label class="form-check-label" for="ch_{{ $val }}">{{ $lbl }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ $ar ? 'نبذة عنك' : 'About You (bio)' }}</label>
                        <textarea name="bio" rows="3" class="form-control" placeholder="{{ $ar ? 'اكتب نبذة عن خبرتك في التسويق...' : 'Tell us about your marketing experience...' }}">{{ old('bio') }}</textarea>
                    </div>
                </div>

                <div class="section-h"><i class="ri-share-line"></i>{{ $ar ? 'حسابات التواصل الاجتماعي' : 'Social Media Accounts' }}</div>
                <div class="row g-3">
                    @php
                        $socials = [
                            'social_facebook'=>['Facebook','ri-facebook-circle-line'],
                            'social_instagram'=>['Instagram','ri-instagram-line'],
                            'social_tiktok'=>['TikTok','ri-tiktok-line'],
                            'social_youtube'=>['YouTube','ri-youtube-line'],
                            'social_snapchat'=>['Snapchat','ri-snapchat-line'],
                            'social_x'=>['X (Twitter)','ri-twitter-x-line'],
                        ];
                    @endphp
                    @foreach($socials as $name=>$meta)
                        <div class="col-md-6">
                            <label class="form-label">{{ $meta[0] }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="{{ $meta[1] }}"></i></span>
                                <input type="text" name="{{ $name }}" value="{{ old($name) }}" class="form-control" placeholder="@username / URL">
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn-next mt-4"><i class="ri-arrow-{{ $ar ? 'left' : 'right' }}-line me-1"></i>{{ $ar ? 'التالي: تحقق البريد' : 'Next: Email Verification' }}</button>

                <div class="text-center mt-3">
                    <span class="text-muted">{{ $ar ? 'لديك حساب؟' : 'Already have an account?' }}</span>
                    <a href="{{ route('login') }}" style="color:var(--primary);">{{ $ar ? 'تسجيل الدخول' : 'Login' }}</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-fill the phone code from the selected country
        function syncCode() {
            const sel = document.getElementById('country');
            const opt = sel.options[sel.selectedIndex];
            const code = opt ? opt.getAttribute('data-code') : '';
            if (code) document.getElementById('phone_code').value = code;
        }
    </script>
</body>
</html>
