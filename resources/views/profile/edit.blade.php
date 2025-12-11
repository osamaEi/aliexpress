@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card mb-6">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ri-user-settings-line me-2"></i>{{ __('messages.profile_settings') }}
            </h5>
        </div>
        <div class="card-body">
            @if(session('status') === 'profile-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ __('messages.profile_updated') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('status') === 'password-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ __('messages.password_updated') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="row g-4">
                    <!-- Avatar Section -->
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-4">
                            <div class="avatar avatar-xl me-3">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle" id="avatarPreview" />
                                @else
                                    <span class="avatar-initial rounded-circle bg-label-primary" style="font-size: 2rem;" id="avatarInitials">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                <label for="avatar" class="btn btn-primary btn-sm mb-2">
                                    <i class="ri-upload-2-line me-1"></i>{{ __('messages.upload_photo') }}
                                </label>
                                <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                                <p class="text-muted small mb-0">{{ __('messages.allowed_formats') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="col-12">
                        <h6 class="text-primary mb-3">
                            <i class="ri-information-line me-1"></i>{{ __('messages.basic_information') }}
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label for="full_name" class="form-label">{{ __('messages.full_name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                               id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required>
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label">{{ __('messages.phone') }}</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">{{ __('messages.email') }} <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(!$user->email_verified_at)
                            <small class="text-warning">
                                <i class="ri-error-warning-line me-1"></i>{{ __('messages.email_not_verified') }}
                            </small>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="company_name" class="form-label">{{ __('messages.company_name') }}</label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                               id="company_name" name="company_name" value="{{ old('company_name', $user->company_name) }}">
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="country" class="form-label">{{ __('messages.country') }}</label>
                        <select class="form-select @error('country') is-invalid @enderror" id="country" name="country">
                            <option value="">{{ __('messages.select_country') }}</option>
                            @php
                                $countries = [
                                    'AE' => ['flag' => '🇦🇪', 'name_ar' => 'الإمارات العربية المتحدة', 'name_en' => 'United Arab Emirates'],
                                    'SA' => ['flag' => '🇸🇦', 'name_ar' => 'المملكة العربية السعودية', 'name_en' => 'Saudi Arabia'],
                                    'EG' => ['flag' => '🇪🇬', 'name_ar' => 'مصر', 'name_en' => 'Egypt'],
                                    'KW' => ['flag' => '🇰🇼', 'name_ar' => 'الكويت', 'name_en' => 'Kuwait'],
                                    'QA' => ['flag' => '🇶🇦', 'name_ar' => 'قطر', 'name_en' => 'Qatar'],
                                    'BH' => ['flag' => '🇧🇭', 'name_ar' => 'البحرين', 'name_en' => 'Bahrain'],
                                    'OM' => ['flag' => '🇴🇲', 'name_ar' => 'عمان', 'name_en' => 'Oman'],
                                    'JO' => ['flag' => '🇯🇴', 'name_ar' => 'الأردن', 'name_en' => 'Jordan'],
                                    'LB' => ['flag' => '🇱🇧', 'name_ar' => 'لبنان', 'name_en' => 'Lebanon'],
                                    'IQ' => ['flag' => '🇮🇶', 'name_ar' => 'العراق', 'name_en' => 'Iraq'],
                                    'SY' => ['flag' => '🇸🇾', 'name_ar' => 'سوريا', 'name_en' => 'Syria'],
                                    'YE' => ['flag' => '🇾🇪', 'name_ar' => 'اليمن', 'name_en' => 'Yemen'],
                                    'PS' => ['flag' => '🇵🇸', 'name_ar' => 'فلسطين', 'name_en' => 'Palestine'],
                                    'MA' => ['flag' => '🇲🇦', 'name_ar' => 'المغرب', 'name_en' => 'Morocco'],
                                    'DZ' => ['flag' => '🇩🇿', 'name_ar' => 'الجزائر', 'name_en' => 'Algeria'],
                                    'TN' => ['flag' => '🇹🇳', 'name_ar' => 'تونس', 'name_en' => 'Tunisia'],
                                    'LY' => ['flag' => '🇱🇾', 'name_ar' => 'ليبيا', 'name_en' => 'Libya'],
                                    'SD' => ['flag' => '🇸🇩', 'name_ar' => 'السودان', 'name_en' => 'Sudan'],
                                    'US' => ['flag' => '🇺🇸', 'name_ar' => 'الولايات المتحدة', 'name_en' => 'United States'],
                                    'GB' => ['flag' => '🇬🇧', 'name_ar' => 'المملكة المتحدة', 'name_en' => 'United Kingdom'],
                                    'FR' => ['flag' => '🇫🇷', 'name_ar' => 'فرنسا', 'name_en' => 'France'],
                                    'DE' => ['flag' => '🇩🇪', 'name_ar' => 'ألمانيا', 'name_en' => 'Germany'],
                                    'IT' => ['flag' => '🇮🇹', 'name_ar' => 'إيطاليا', 'name_en' => 'Italy'],
                                    'ES' => ['flag' => '🇪🇸', 'name_ar' => 'إسبانيا', 'name_en' => 'Spain'],
                                    'TR' => ['flag' => '🇹🇷', 'name_ar' => 'تركيا', 'name_en' => 'Turkey'],
                                    'CN' => ['flag' => '🇨🇳', 'name_ar' => 'الصين', 'name_en' => 'China'],
                                    'IN' => ['flag' => '🇮🇳', 'name_ar' => 'الهند', 'name_en' => 'India'],
                                    'PK' => ['flag' => '🇵🇰', 'name_ar' => 'باكستان', 'name_en' => 'Pakistan'],
                                    'BD' => ['flag' => '🇧🇩', 'name_ar' => 'بنغلاديش', 'name_en' => 'Bangladesh'],
                                    'MY' => ['flag' => '🇲🇾', 'name_ar' => 'ماليزيا', 'name_en' => 'Malaysia'],
                                    'ID' => ['flag' => '🇮🇩', 'name_ar' => 'إندونيسيا', 'name_en' => 'Indonesia'],
                                    'SG' => ['flag' => '🇸🇬', 'name_ar' => 'سنغافورة', 'name_en' => 'Singapore'],
                                    'JP' => ['flag' => '🇯🇵', 'name_ar' => 'اليابان', 'name_en' => 'Japan'],
                                    'KR' => ['flag' => '🇰🇷', 'name_ar' => 'كوريا الجنوبية', 'name_en' => 'South Korea'],
                                    'AU' => ['flag' => '🇦🇺', 'name_ar' => 'أستراليا', 'name_en' => 'Australia'],
                                    'CA' => ['flag' => '🇨🇦', 'name_ar' => 'كندا', 'name_en' => 'Canada'],
                                    'BR' => ['flag' => '🇧🇷', 'name_ar' => 'البرازيل', 'name_en' => 'Brazil'],
                                    'MX' => ['flag' => '🇲🇽', 'name_ar' => 'المكسيك', 'name_en' => 'Mexico'],
                                    'RU' => ['flag' => '🇷🇺', 'name_ar' => 'روسيا', 'name_en' => 'Russia'],
                                    'ZA' => ['flag' => '🇿🇦', 'name_ar' => 'جنوب أفريقيا', 'name_en' => 'South Africa'],
                                ];
                                $locale = app()->getLocale();
                            @endphp
                            @foreach($countries as $code => $country)
                                <option value="{{ $code }}" {{ old('country', $user->country) == $code ? 'selected' : '' }}>
                                    {{ $country['flag'] }} {{ $locale == 'ar' ? $country['name_ar'] : $country['name_en'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="user_type" class="form-label">{{ __('messages.user_type') }}</label>
                        <select class="form-select @error('user_type') is-invalid @enderror" id="user_type" name="user_type" disabled>
                            <option value="admin" {{ $user->user_type == 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                            <option value="seller" {{ $user->user_type == 'seller' ? 'selected' : '' }}>{{ __('messages.seller') }}</option>
                            <option value="buyer" {{ $user->user_type == 'buyer' ? 'selected' : '' }}>{{ __('messages.buyer') }}</option>
                        </select>
                        <small class="text-muted">{{ __('messages.contact_admin_change') }}</small>
                        @error('user_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Business Information -->
                    <div class="col-12 mt-4">
                        <h6 class="text-primary mb-3">
                            <i class="ri-briefcase-line me-1"></i>{{ __('messages.business_information') }}
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label for="main_activity" class="form-label">{{ __('messages.main_activity') }}</label>
                        <select class="form-select @error('main_activity') is-invalid @enderror" id="main_activity" name="main_activity">
                            <option value="">{{ __('messages.select_activity') }}</option>
                            @php
                                $mainActivities = [
                                    'retail' => ['ar' => 'تجارة تجزئة', 'en' => 'Retail'],
                                    'wholesale' => ['ar' => 'تجارة جملة', 'en' => 'Wholesale'],
                                    'ecommerce' => ['ar' => 'تجارة إلكترونية', 'en' => 'E-commerce'],
                                    'manufacturing' => ['ar' => 'تصنيع', 'en' => 'Manufacturing'],
                                    'services' => ['ar' => 'خدمات', 'en' => 'Services'],
                                    'food_beverage' => ['ar' => 'أغذية ومشروبات', 'en' => 'Food & Beverage'],
                                    'technology' => ['ar' => 'تكنولوجيا', 'en' => 'Technology'],
                                    'healthcare' => ['ar' => 'رعاية صحية', 'en' => 'Healthcare'],
                                    'education' => ['ar' => 'تعليم', 'en' => 'Education'],
                                    'real_estate' => ['ar' => 'عقارات', 'en' => 'Real Estate'],
                                    'construction' => ['ar' => 'بناء وإنشاءات', 'en' => 'Construction'],
                                    'transportation' => ['ar' => 'نقل ومواصلات', 'en' => 'Transportation'],
                                    'agriculture' => ['ar' => 'زراعة', 'en' => 'Agriculture'],
                                    'tourism' => ['ar' => 'سياحة وسفر', 'en' => 'Tourism & Travel'],
                                ];
                            @endphp
                            @foreach($mainActivities as $code => $activity)
                                <option value="{{ $code }}" {{ old('main_activity', $user->main_activity) == $code ? 'selected' : '' }}>
                                    {{ app()->getLocale() == 'ar' ? $activity['ar'] : $activity['en'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('main_activity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sub_activity" class="form-label">{{ __('messages.sub_activity') }}</label>
                        <select class="form-select @error('sub_activity') is-invalid @enderror" id="sub_activity" name="sub_activity">
                            <option value="">{{ __('messages.select_sub_activity') }}</option>
                            @php
                                $subActivities = [
                                    'electronics' => ['ar' => 'إلكترونيات', 'en' => 'Electronics'],
                                    'clothing' => ['ar' => 'ملابس', 'en' => 'Clothing & Fashion'],
                                    'home_garden' => ['ar' => 'منزل وحديقة', 'en' => 'Home & Garden'],
                                    'beauty_health' => ['ar' => 'جمال وصحة', 'en' => 'Beauty & Health'],
                                    'sports' => ['ar' => 'رياضة', 'en' => 'Sports & Outdoors'],
                                    'toys_kids' => ['ar' => 'ألعاب وأطفال', 'en' => 'Toys & Kids'],
                                    'automotive' => ['ar' => 'سيارات', 'en' => 'Automotive'],
                                    'books_media' => ['ar' => 'كتب ووسائط', 'en' => 'Books & Media'],
                                    'jewelry' => ['ar' => 'مجوهرات', 'en' => 'Jewelry & Accessories'],
                                    'food_grocery' => ['ar' => 'أغذية ومواد غذائية', 'en' => 'Food & Grocery'],
                                    'pet_supplies' => ['ar' => 'مستلزمات حيوانات أليفة', 'en' => 'Pet Supplies'],
                                    'office_supplies' => ['ar' => 'مستلزمات مكتبية', 'en' => 'Office Supplies'],
                                    'furniture' => ['ar' => 'أثاث', 'en' => 'Furniture'],
                                    'appliances' => ['ar' => 'أجهزة منزلية', 'en' => 'Appliances'],
                                ];
                            @endphp
                            @foreach($subActivities as $code => $activity)
                                <option value="{{ $code }}" {{ old('sub_activity', $user->sub_activity) == $code ? 'selected' : '' }}>
                                    {{ app()->getLocale() == 'ar' ? $activity['ar'] : $activity['en'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('sub_activity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Financial Information -->
                    <div class="col-12 mt-4">
                        <h6 class="text-primary mb-3">
                            <i class="ri-money-dollar-circle-line me-1"></i>{{ __('messages.financial_information') }}
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label for="withdrawal_method" class="form-label">{{ __('messages.withdrawal_method') }}</label>
                        <select class="form-select @error('withdrawal_method') is-invalid @enderror" id="withdrawal_method" name="withdrawal_method">
                            <option value="">{{ __('messages.select_withdrawal_method') }}</option>
                            <option value="paypal" {{ old('withdrawal_method', $user->withdrawal_method) == 'paypal' ? 'selected' : '' }}>
                                PayPal
                            </option>
                            <option value="e_wallet" {{ old('withdrawal_method', $user->withdrawal_method) == 'e_wallet' ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'ar' ? 'محفظة إلكترونية' : 'E-Wallet' }}
                            </option>
                            <option value="uae_bank" {{ old('withdrawal_method', $user->withdrawal_method) == 'uae_bank' ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'ar' ? 'حساب بنكي إماراتي' : 'UAE Bank Account' }}
                            </option>
                        </select>
                        @error('withdrawal_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="marketing_code" class="form-label">{{ __('messages.marketing_code') }}</label>
                        <input type="text" class="form-control @error('marketing_code') is-invalid @enderror"
                               id="marketing_code" name="marketing_code" value="{{ old('marketing_code', $user->marketing_code) }}"
                               placeholder="{{ __('messages.enter_marketing_code') }}"
                               {{ $user->marketing_code && $user->marketing_code_approved ? 'readonly' : '' }}>
                        @error('marketing_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($user->marketing_code && !$user->marketing_code_approved)
                            <small class="text-warning">
                                <i class="ri-time-line me-1"></i>{{ __('messages.marketing_code_pending') }}
                            </small>
                        @elseif($user->marketing_code && $user->marketing_code_approved)
                            <small class="text-success">
                                <i class="ri-checkbox-circle-line me-1"></i>{{ __('messages.marketing_code_approved') }}
                            </small>
                        @else
                            <small class="text-muted">
                                <i class="ri-information-line me-1"></i>{{ __('messages.marketing_code_note') }}
                            </small>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i>{{ __('messages.save_changes') }}
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="ri-close-line me-1"></i>{{ __('messages.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="card mb-6">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ri-lock-password-line me-2"></i>{{ __('messages.change_password') }}
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="current_password" class="form-label">{{ __('messages.current_password') }} <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12"></div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">{{ __('messages.new_password') }} <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('messages.minimum_8_characters') }}</small>
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">{{ __('messages.confirm_new_password') }} <span class="text-danger">*</span></label>
                        <input type="password" class="form-control"
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-lock-line me-1"></i>{{ __('messages.update_password') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Avatar preview
    document.getElementById('avatar')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const avatarPreview = document.getElementById('avatarPreview');
                const avatarInitials = document.getElementById('avatarInitials');

                if (avatarPreview) {
                    avatarPreview.src = e.target.result;
                } else if (avatarInitials) {
                    // Replace initials with image
                    const avatar = avatarInitials.parentElement;
                    avatar.innerHTML = '<img src="' + e.target.result + '" alt="Avatar" class="rounded-circle" id="avatarPreview" />';
                }
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
