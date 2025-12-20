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

            @if(session('status') === 'avatar-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ __('messages.avatar_updated') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Avatar Upload Section (Separate Form) -->
            <form id="avatarForm" method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-4 mb-4">
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
                                @error('avatar')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Profile Information Form (Separate) -->
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="row g-4">

                    <!-- Basic Information -->
                    <div class="col-12">
                        <h6 class="text-primary mb-3">
                            <i class="ri-information-line me-1"></i>{{ __('messages.basic_information') }}
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('messages.full_name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
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
                        <div class="position-relative">
                            <span id="countryFlag" class="position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); z-index: 10; pointer-events: none;"></span>
                            <select class="form-select @error('country') is-invalid @enderror" id="country" name="country" style="padding-left: 40px;">
                                <option value="">{{ __('messages.select_country') }}</option>
                                @php
                                    $countries = [
                                    'AF' => ['flag' => '🇦🇫', 'name_ar' => 'أفغانستان', 'name_en' => 'Afghanistan'],
                                    'AL' => ['flag' => '🇦🇱', 'name_ar' => 'ألبانيا', 'name_en' => 'Albania'],
                                    'DZ' => ['flag' => '🇩🇿', 'name_ar' => 'الجزائر', 'name_en' => 'Algeria'],
                                    'AD' => ['flag' => '🇦🇩', 'name_ar' => 'أندورا', 'name_en' => 'Andorra'],
                                    'AO' => ['flag' => '🇦🇴', 'name_ar' => 'أنغولا', 'name_en' => 'Angola'],
                                    'AG' => ['flag' => '🇦🇬', 'name_ar' => 'أنتيغوا وبربودا', 'name_en' => 'Antigua and Barbuda'],
                                    'AR' => ['flag' => '🇦🇷', 'name_ar' => 'الأرجنتين', 'name_en' => 'Argentina'],
                                    'AM' => ['flag' => '🇦🇲', 'name_ar' => 'أرمينيا', 'name_en' => 'Armenia'],
                                    'AU' => ['flag' => '🇦🇺', 'name_ar' => 'أستراليا', 'name_en' => 'Australia'],
                                    'AT' => ['flag' => '🇦🇹', 'name_ar' => 'النمسا', 'name_en' => 'Austria'],
                                    'AZ' => ['flag' => '🇦🇿', 'name_ar' => 'أذربيجان', 'name_en' => 'Azerbaijan'],
                                    'BS' => ['flag' => '🇧🇸', 'name_ar' => 'الباهاما', 'name_en' => 'Bahamas'],
                                    'BH' => ['flag' => '🇧🇭', 'name_ar' => 'البحرين', 'name_en' => 'Bahrain'],
                                    'BD' => ['flag' => '🇧🇩', 'name_ar' => 'بنغلاديش', 'name_en' => 'Bangladesh'],
                                    'BB' => ['flag' => '🇧🇧', 'name_ar' => 'بربادوس', 'name_en' => 'Barbados'],
                                    'BY' => ['flag' => '🇧🇾', 'name_ar' => 'بيلاروسيا', 'name_en' => 'Belarus'],
                                    'BE' => ['flag' => '🇧🇪', 'name_ar' => 'بلجيكا', 'name_en' => 'Belgium'],
                                    'BZ' => ['flag' => '🇧🇿', 'name_ar' => 'بليز', 'name_en' => 'Belize'],
                                    'BJ' => ['flag' => '🇧🇯', 'name_ar' => 'بنين', 'name_en' => 'Benin'],
                                    'BT' => ['flag' => '🇧🇹', 'name_ar' => 'بوتان', 'name_en' => 'Bhutan'],
                                    'BO' => ['flag' => '🇧🇴', 'name_ar' => 'بوليفيا', 'name_en' => 'Bolivia'],
                                    'BA' => ['flag' => '🇧🇦', 'name_ar' => 'البوسنة والهرسك', 'name_en' => 'Bosnia and Herzegovina'],
                                    'BW' => ['flag' => '🇧🇼', 'name_ar' => 'بوتسوانا', 'name_en' => 'Botswana'],
                                    'BR' => ['flag' => '🇧🇷', 'name_ar' => 'البرازيل', 'name_en' => 'Brazil'],
                                    'BN' => ['flag' => '🇧🇳', 'name_ar' => 'بروناي', 'name_en' => 'Brunei'],
                                    'BG' => ['flag' => '🇧🇬', 'name_ar' => 'بلغاريا', 'name_en' => 'Bulgaria'],
                                    'BF' => ['flag' => '🇧🇫', 'name_ar' => 'بوركينا فاسو', 'name_en' => 'Burkina Faso'],
                                    'BI' => ['flag' => '🇧🇮', 'name_ar' => 'بوروندي', 'name_en' => 'Burundi'],
                                    'KH' => ['flag' => '🇰🇭', 'name_ar' => 'كمبوديا', 'name_en' => 'Cambodia'],
                                    'CM' => ['flag' => '🇨🇲', 'name_ar' => 'الكاميرون', 'name_en' => 'Cameroon'],
                                    'CA' => ['flag' => '🇨🇦', 'name_ar' => 'كندا', 'name_en' => 'Canada'],
                                    'CV' => ['flag' => '🇨🇻', 'name_ar' => 'الرأس الأخضر', 'name_en' => 'Cape Verde'],
                                    'CF' => ['flag' => '🇨🇫', 'name_ar' => 'جمهورية أفريقيا الوسطى', 'name_en' => 'Central African Republic'],
                                    'TD' => ['flag' => '🇹🇩', 'name_ar' => 'تشاد', 'name_en' => 'Chad'],
                                    'CL' => ['flag' => '🇨🇱', 'name_ar' => 'تشيلي', 'name_en' => 'Chile'],
                                    'CN' => ['flag' => '🇨🇳', 'name_ar' => 'الصين', 'name_en' => 'China'],
                                    'CO' => ['flag' => '🇨🇴', 'name_ar' => 'كولومبيا', 'name_en' => 'Colombia'],
                                    'KM' => ['flag' => '🇰🇲', 'name_ar' => 'جزر القمر', 'name_en' => 'Comoros'],
                                    'CG' => ['flag' => '🇨🇬', 'name_ar' => 'الكونغو', 'name_en' => 'Congo'],
                                    'CR' => ['flag' => '🇨🇷', 'name_ar' => 'كوستاريكا', 'name_en' => 'Costa Rica'],
                                    'HR' => ['flag' => '🇭🇷', 'name_ar' => 'كرواتيا', 'name_en' => 'Croatia'],
                                    'CU' => ['flag' => '🇨🇺', 'name_ar' => 'كوبا', 'name_en' => 'Cuba'],
                                    'CY' => ['flag' => '🇨🇾', 'name_ar' => 'قبرص', 'name_en' => 'Cyprus'],
                                    'CZ' => ['flag' => '🇨🇿', 'name_ar' => 'التشيك', 'name_en' => 'Czech Republic'],
                                    'DK' => ['flag' => '🇩🇰', 'name_ar' => 'الدنمارك', 'name_en' => 'Denmark'],
                                    'DJ' => ['flag' => '🇩🇯', 'name_ar' => 'جيبوتي', 'name_en' => 'Djibouti'],
                                    'DM' => ['flag' => '🇩🇲', 'name_ar' => 'دومينيكا', 'name_en' => 'Dominica'],
                                    'DO' => ['flag' => '🇩🇴', 'name_ar' => 'جمهورية الدومينيكان', 'name_en' => 'Dominican Republic'],
                                    'EC' => ['flag' => '🇪🇨', 'name_ar' => 'الإكوادور', 'name_en' => 'Ecuador'],
                                    'EG' => ['flag' => '🇪🇬', 'name_ar' => 'مصر', 'name_en' => 'Egypt'],
                                    'SV' => ['flag' => '🇸🇻', 'name_ar' => 'السلفادور', 'name_en' => 'El Salvador'],
                                    'GQ' => ['flag' => '🇬🇶', 'name_ar' => 'غينيا الاستوائية', 'name_en' => 'Equatorial Guinea'],
                                    'ER' => ['flag' => '🇪🇷', 'name_ar' => 'إريتريا', 'name_en' => 'Eritrea'],
                                    'EE' => ['flag' => '🇪🇪', 'name_ar' => 'إستونيا', 'name_en' => 'Estonia'],
                                    'ET' => ['flag' => '🇪🇹', 'name_ar' => 'إثيوبيا', 'name_en' => 'Ethiopia'],
                                    'FJ' => ['flag' => '🇫🇯', 'name_ar' => 'فيجي', 'name_en' => 'Fiji'],
                                    'FI' => ['flag' => '🇫🇮', 'name_ar' => 'فنلندا', 'name_en' => 'Finland'],
                                    'FR' => ['flag' => '🇫🇷', 'name_ar' => 'فرنسا', 'name_en' => 'France'],
                                    'GA' => ['flag' => '🇬🇦', 'name_ar' => 'الغابون', 'name_en' => 'Gabon'],
                                    'GM' => ['flag' => '🇬🇲', 'name_ar' => 'غامبيا', 'name_en' => 'Gambia'],
                                    'GE' => ['flag' => '🇬🇪', 'name_ar' => 'جورجيا', 'name_en' => 'Georgia'],
                                    'DE' => ['flag' => '🇩🇪', 'name_ar' => 'ألمانيا', 'name_en' => 'Germany'],
                                    'GH' => ['flag' => '🇬🇭', 'name_ar' => 'غانا', 'name_en' => 'Ghana'],
                                    'GR' => ['flag' => '🇬🇷', 'name_ar' => 'اليونان', 'name_en' => 'Greece'],
                                    'GD' => ['flag' => '🇬🇩', 'name_ar' => 'غرينادا', 'name_en' => 'Grenada'],
                                    'GT' => ['flag' => '🇬🇹', 'name_ar' => 'غواتيمالا', 'name_en' => 'Guatemala'],
                                    'GN' => ['flag' => '🇬🇳', 'name_ar' => 'غينيا', 'name_en' => 'Guinea'],
                                    'GW' => ['flag' => '🇬🇼', 'name_ar' => 'غينيا بيساو', 'name_en' => 'Guinea-Bissau'],
                                    'GY' => ['flag' => '🇬🇾', 'name_ar' => 'غيانا', 'name_en' => 'Guyana'],
                                    'HT' => ['flag' => '🇭🇹', 'name_ar' => 'هايتي', 'name_en' => 'Haiti'],
                                    'HN' => ['flag' => '🇭🇳', 'name_ar' => 'هندوراس', 'name_en' => 'Honduras'],
                                    'HU' => ['flag' => '🇭🇺', 'name_ar' => 'المجر', 'name_en' => 'Hungary'],
                                    'IS' => ['flag' => '🇮🇸', 'name_ar' => 'آيسلندا', 'name_en' => 'Iceland'],
                                    'IN' => ['flag' => '🇮🇳', 'name_ar' => 'الهند', 'name_en' => 'India'],
                                    'ID' => ['flag' => '🇮🇩', 'name_ar' => 'إندونيسيا', 'name_en' => 'Indonesia'],
                                    'IR' => ['flag' => '🇮🇷', 'name_ar' => 'إيران', 'name_en' => 'Iran'],
                                    'IQ' => ['flag' => '🇮🇶', 'name_ar' => 'العراق', 'name_en' => 'Iraq'],
                                    'IE' => ['flag' => '🇮🇪', 'name_ar' => 'أيرلندا', 'name_en' => 'Ireland'],
                                    'IL' => ['flag' => '🇮🇱', 'name_ar' => 'إسرائيل', 'name_en' => 'Israel'],
                                    'IT' => ['flag' => '🇮🇹', 'name_ar' => 'إيطاليا', 'name_en' => 'Italy'],
                                    'JM' => ['flag' => '🇯🇲', 'name_ar' => 'جامايكا', 'name_en' => 'Jamaica'],
                                    'JP' => ['flag' => '🇯🇵', 'name_ar' => 'اليابان', 'name_en' => 'Japan'],
                                    'JO' => ['flag' => '🇯🇴', 'name_ar' => 'الأردن', 'name_en' => 'Jordan'],
                                    'KZ' => ['flag' => '🇰🇿', 'name_ar' => 'كازاخستان', 'name_en' => 'Kazakhstan'],
                                    'KE' => ['flag' => '🇰🇪', 'name_ar' => 'كينيا', 'name_en' => 'Kenya'],
                                    'KI' => ['flag' => '🇰🇮', 'name_ar' => 'كيريباتي', 'name_en' => 'Kiribati'],
                                    'KP' => ['flag' => '🇰🇵', 'name_ar' => 'كوريا الشمالية', 'name_en' => 'North Korea'],
                                    'KR' => ['flag' => '🇰🇷', 'name_ar' => 'كوريا الجنوبية', 'name_en' => 'South Korea'],
                                    'KW' => ['flag' => '🇰🇼', 'name_ar' => 'الكويت', 'name_en' => 'Kuwait'],
                                    'KG' => ['flag' => '🇰🇬', 'name_ar' => 'قيرغيزستان', 'name_en' => 'Kyrgyzstan'],
                                    'LA' => ['flag' => '🇱🇦', 'name_ar' => 'لاوس', 'name_en' => 'Laos'],
                                    'LV' => ['flag' => '🇱🇻', 'name_ar' => 'لاتفيا', 'name_en' => 'Latvia'],
                                    'LB' => ['flag' => '🇱🇧', 'name_ar' => 'لبنان', 'name_en' => 'Lebanon'],
                                    'LS' => ['flag' => '🇱🇸', 'name_ar' => 'ليسوتو', 'name_en' => 'Lesotho'],
                                    'LR' => ['flag' => '🇱🇷', 'name_ar' => 'ليبيريا', 'name_en' => 'Liberia'],
                                    'LY' => ['flag' => '🇱🇾', 'name_ar' => 'ليبيا', 'name_en' => 'Libya'],
                                    'LI' => ['flag' => '🇱🇮', 'name_ar' => 'ليختنشتاين', 'name_en' => 'Liechtenstein'],
                                    'LT' => ['flag' => '🇱🇹', 'name_ar' => 'ليتوانيا', 'name_en' => 'Lithuania'],
                                    'LU' => ['flag' => '🇱🇺', 'name_ar' => 'لوكسمبورغ', 'name_en' => 'Luxembourg'],
                                    'MK' => ['flag' => '🇲🇰', 'name_ar' => 'مقدونيا الشمالية', 'name_en' => 'North Macedonia'],
                                    'MG' => ['flag' => '🇲🇬', 'name_ar' => 'مدغشقر', 'name_en' => 'Madagascar'],
                                    'MW' => ['flag' => '🇲🇼', 'name_ar' => 'مالاوي', 'name_en' => 'Malawi'],
                                    'MY' => ['flag' => '🇲🇾', 'name_ar' => 'ماليزيا', 'name_en' => 'Malaysia'],
                                    'MV' => ['flag' => '🇲🇻', 'name_ar' => 'المالديف', 'name_en' => 'Maldives'],
                                    'ML' => ['flag' => '🇲🇱', 'name_ar' => 'مالي', 'name_en' => 'Mali'],
                                    'MT' => ['flag' => '🇲🇹', 'name_ar' => 'مالطا', 'name_en' => 'Malta'],
                                    'MH' => ['flag' => '🇲🇭', 'name_ar' => 'جزر مارشال', 'name_en' => 'Marshall Islands'],
                                    'MR' => ['flag' => '🇲🇷', 'name_ar' => 'موريتانيا', 'name_en' => 'Mauritania'],
                                    'MU' => ['flag' => '🇲🇺', 'name_ar' => 'موريشيوس', 'name_en' => 'Mauritius'],
                                    'MX' => ['flag' => '🇲🇽', 'name_ar' => 'المكسيك', 'name_en' => 'Mexico'],
                                    'FM' => ['flag' => '🇫🇲', 'name_ar' => 'ميكرونيزيا', 'name_en' => 'Micronesia'],
                                    'MD' => ['flag' => '🇲🇩', 'name_ar' => 'مولدوفا', 'name_en' => 'Moldova'],
                                    'MC' => ['flag' => '🇲🇨', 'name_ar' => 'موناكو', 'name_en' => 'Monaco'],
                                    'MN' => ['flag' => '🇲🇳', 'name_ar' => 'منغوليا', 'name_en' => 'Mongolia'],
                                    'ME' => ['flag' => '🇲🇪', 'name_ar' => 'الجبل الأسود', 'name_en' => 'Montenegro'],
                                    'MA' => ['flag' => '🇲🇦', 'name_ar' => 'المغرب', 'name_en' => 'Morocco'],
                                    'MZ' => ['flag' => '🇲🇿', 'name_ar' => 'موزمبيق', 'name_en' => 'Mozambique'],
                                    'MM' => ['flag' => '🇲🇲', 'name_ar' => 'ميانمار', 'name_en' => 'Myanmar'],
                                    'NA' => ['flag' => '🇳🇦', 'name_ar' => 'ناميبيا', 'name_en' => 'Namibia'],
                                    'NR' => ['flag' => '🇳🇷', 'name_ar' => 'ناورو', 'name_en' => 'Nauru'],
                                    'NP' => ['flag' => '🇳🇵', 'name_ar' => 'نيبال', 'name_en' => 'Nepal'],
                                    'NL' => ['flag' => '🇳🇱', 'name_ar' => 'هولندا', 'name_en' => 'Netherlands'],
                                    'NZ' => ['flag' => '🇳🇿', 'name_ar' => 'نيوزيلندا', 'name_en' => 'New Zealand'],
                                    'NI' => ['flag' => '🇳🇮', 'name_ar' => 'نيكاراغوا', 'name_en' => 'Nicaragua'],
                                    'NE' => ['flag' => '🇳🇪', 'name_ar' => 'النيجر', 'name_en' => 'Niger'],
                                    'NG' => ['flag' => '🇳🇬', 'name_ar' => 'نيجيريا', 'name_en' => 'Nigeria'],
                                    'NO' => ['flag' => '🇳🇴', 'name_ar' => 'النرويج', 'name_en' => 'Norway'],
                                    'OM' => ['flag' => '🇴🇲', 'name_ar' => 'عمان', 'name_en' => 'Oman'],
                                    'PK' => ['flag' => '🇵🇰', 'name_ar' => 'باكستان', 'name_en' => 'Pakistan'],
                                    'PW' => ['flag' => '🇵🇼', 'name_ar' => 'بالاو', 'name_en' => 'Palau'],
                                    'PS' => ['flag' => '🇵🇸', 'name_ar' => 'فلسطين', 'name_en' => 'Palestine'],
                                    'PA' => ['flag' => '🇵🇦', 'name_ar' => 'بنما', 'name_en' => 'Panama'],
                                    'PG' => ['flag' => '🇵🇬', 'name_ar' => 'بابوا غينيا الجديدة', 'name_en' => 'Papua New Guinea'],
                                    'PY' => ['flag' => '🇵🇾', 'name_ar' => 'باراغواي', 'name_en' => 'Paraguay'],
                                    'PE' => ['flag' => '🇵🇪', 'name_ar' => 'بيرو', 'name_en' => 'Peru'],
                                    'PH' => ['flag' => '🇵🇭', 'name_ar' => 'الفلبين', 'name_en' => 'Philippines'],
                                    'PL' => ['flag' => '🇵🇱', 'name_ar' => 'بولندا', 'name_en' => 'Poland'],
                                    'PT' => ['flag' => '🇵🇹', 'name_ar' => 'البرتغال', 'name_en' => 'Portugal'],
                                    'QA' => ['flag' => '🇶🇦', 'name_ar' => 'قطر', 'name_en' => 'Qatar'],
                                    'RO' => ['flag' => '🇷🇴', 'name_ar' => 'رومانيا', 'name_en' => 'Romania'],
                                    'RU' => ['flag' => '🇷🇺', 'name_ar' => 'روسيا', 'name_en' => 'Russia'],
                                    'RW' => ['flag' => '🇷🇼', 'name_ar' => 'رواندا', 'name_en' => 'Rwanda'],
                                    'KN' => ['flag' => '🇰🇳', 'name_ar' => 'سانت كيتس ونيفيس', 'name_en' => 'Saint Kitts and Nevis'],
                                    'LC' => ['flag' => '🇱🇨', 'name_ar' => 'سانت لوسيا', 'name_en' => 'Saint Lucia'],
                                    'VC' => ['flag' => '🇻🇨', 'name_ar' => 'سانت فنسنت والغرينادين', 'name_en' => 'Saint Vincent and the Grenadines'],
                                    'WS' => ['flag' => '🇼🇸', 'name_ar' => 'ساموا', 'name_en' => 'Samoa'],
                                    'SM' => ['flag' => '🇸🇲', 'name_ar' => 'سان مارينو', 'name_en' => 'San Marino'],
                                    'ST' => ['flag' => '🇸🇹', 'name_ar' => 'ساو تومي وبرينسيب', 'name_en' => 'Sao Tome and Principe'],
                                    'SA' => ['flag' => '🇸🇦', 'name_ar' => 'المملكة العربية السعودية', 'name_en' => 'Saudi Arabia'],
                                    'SN' => ['flag' => '🇸🇳', 'name_ar' => 'السنغال', 'name_en' => 'Senegal'],
                                    'RS' => ['flag' => '🇷🇸', 'name_ar' => 'صربيا', 'name_en' => 'Serbia'],
                                    'SC' => ['flag' => '🇸🇨', 'name_ar' => 'سيشل', 'name_en' => 'Seychelles'],
                                    'SL' => ['flag' => '🇸🇱', 'name_ar' => 'سيراليون', 'name_en' => 'Sierra Leone'],
                                    'SG' => ['flag' => '🇸🇬', 'name_ar' => 'سنغافورة', 'name_en' => 'Singapore'],
                                    'SK' => ['flag' => '🇸🇰', 'name_ar' => 'سلوفاكيا', 'name_en' => 'Slovakia'],
                                    'SI' => ['flag' => '🇸🇮', 'name_ar' => 'سلوفينيا', 'name_en' => 'Slovenia'],
                                    'SB' => ['flag' => '🇸🇧', 'name_ar' => 'جزر سليمان', 'name_en' => 'Solomon Islands'],
                                    'SO' => ['flag' => '🇸🇴', 'name_ar' => 'الصومال', 'name_en' => 'Somalia'],
                                    'ZA' => ['flag' => '🇿🇦', 'name_ar' => 'جنوب أفريقيا', 'name_en' => 'South Africa'],
                                    'SS' => ['flag' => '🇸🇸', 'name_ar' => 'جنوب السودان', 'name_en' => 'South Sudan'],
                                    'ES' => ['flag' => '🇪🇸', 'name_ar' => 'إسبانيا', 'name_en' => 'Spain'],
                                    'LK' => ['flag' => '🇱🇰', 'name_ar' => 'سريلانكا', 'name_en' => 'Sri Lanka'],
                                    'SD' => ['flag' => '🇸🇩', 'name_ar' => 'السودان', 'name_en' => 'Sudan'],
                                    'SR' => ['flag' => '🇸🇷', 'name_ar' => 'سورينام', 'name_en' => 'Suriname'],
                                    'SE' => ['flag' => '🇸🇪', 'name_ar' => 'السويد', 'name_en' => 'Sweden'],
                                    'CH' => ['flag' => '🇨🇭', 'name_ar' => 'سويسرا', 'name_en' => 'Switzerland'],
                                    'SY' => ['flag' => '🇸🇾', 'name_ar' => 'سوريا', 'name_en' => 'Syria'],
                                    'TW' => ['flag' => '🇹🇼', 'name_ar' => 'تايوان', 'name_en' => 'Taiwan'],
                                    'TJ' => ['flag' => '🇹🇯', 'name_ar' => 'طاجيكستان', 'name_en' => 'Tajikistan'],
                                    'TZ' => ['flag' => '🇹🇿', 'name_ar' => 'تنزانيا', 'name_en' => 'Tanzania'],
                                    'TH' => ['flag' => '🇹🇭', 'name_ar' => 'تايلاند', 'name_en' => 'Thailand'],
                                    'TL' => ['flag' => '🇹🇱', 'name_ar' => 'تيمور الشرقية', 'name_en' => 'Timor-Leste'],
                                    'TG' => ['flag' => '🇹🇬', 'name_ar' => 'توغو', 'name_en' => 'Togo'],
                                    'TO' => ['flag' => '🇹🇴', 'name_ar' => 'تونغا', 'name_en' => 'Tonga'],
                                    'TT' => ['flag' => '🇹🇹', 'name_ar' => 'ترينيداد وتوباغو', 'name_en' => 'Trinidad and Tobago'],
                                    'TN' => ['flag' => '🇹🇳', 'name_ar' => 'تونس', 'name_en' => 'Tunisia'],
                                    'TR' => ['flag' => '🇹🇷', 'name_ar' => 'تركيا', 'name_en' => 'Turkey'],
                                    'TM' => ['flag' => '🇹🇲', 'name_ar' => 'تركمانستان', 'name_en' => 'Turkmenistan'],
                                    'TV' => ['flag' => '🇹🇻', 'name_ar' => 'توفالو', 'name_en' => 'Tuvalu'],
                                    'UG' => ['flag' => '🇺🇬', 'name_ar' => 'أوغندا', 'name_en' => 'Uganda'],
                                    'UA' => ['flag' => '🇺🇦', 'name_ar' => 'أوكرانيا', 'name_en' => 'Ukraine'],
                                    'AE' => ['flag' => '🇦🇪', 'name_ar' => 'الإمارات العربية المتحدة', 'name_en' => 'United Arab Emirates'],
                                    'GB' => ['flag' => '🇬🇧', 'name_ar' => 'المملكة المتحدة', 'name_en' => 'United Kingdom'],
                                    'US' => ['flag' => '🇺🇸', 'name_ar' => 'الولايات المتحدة', 'name_en' => 'United States'],
                                    'UY' => ['flag' => '🇺🇾', 'name_ar' => 'أوروغواي', 'name_en' => 'Uruguay'],
                                    'UZ' => ['flag' => '🇺🇿', 'name_ar' => 'أوزبكستان', 'name_en' => 'Uzbekistan'],
                                    'VU' => ['flag' => '🇻🇺', 'name_ar' => 'فانواتو', 'name_en' => 'Vanuatu'],
                                    'VA' => ['flag' => '🇻🇦', 'name_ar' => 'الفاتيكان', 'name_en' => 'Vatican City'],
                                    'VE' => ['flag' => '🇻🇪', 'name_ar' => 'فنزويلا', 'name_en' => 'Venezuela'],
                                    'VN' => ['flag' => '🇻🇳', 'name_ar' => 'فيتنام', 'name_en' => 'Vietnam'],
                                    'YE' => ['flag' => '🇾🇪', 'name_ar' => 'اليمن', 'name_en' => 'Yemen'],
                                    'ZM' => ['flag' => '🇿🇲', 'name_ar' => 'زامبيا', 'name_en' => 'Zambia'],
                                    'ZW' => ['flag' => '🇿🇼', 'name_ar' => 'زيمبابوي', 'name_en' => 'Zimbabwe'],
                                ];
                                $locale = app()->getLocale();
                            @endphp
                            @foreach($countries as $code => $country)
                                <option value="{{ $code }}" {{ old('country', $user->country) == $code ? 'selected' : '' }} data-flag="{{ strtolower($code) }}">
                                    {{ $locale == 'ar' ? $country['name_ar'] : $country['name_en'] }}
                                </option>
                            @endforeach
                            </select>
                        </div>
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

                    @if(auth()->user()->hasActiveSubscription())
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
                    @endif

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

<style>
    /* RTL support for country flag */
    [dir="rtl"] #countryFlag {
        left: auto !important;
        right: 12px !important;
    }

    [dir="rtl"] #country {
        padding-left: 12px !important;
        padding-right: 40px !important;
    }
</style>

<script>
    // Avatar preview and auto-submit
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

            // Auto-submit the avatar form
            document.getElementById('avatarForm').submit();
        }
    });

    // Update country flag on select change
    const countrySelect = document.getElementById('country');
    const countryFlagSpan = document.getElementById('countryFlag');

    if (countrySelect && countryFlagSpan) {
        function updateCountryFlag() {
            const selectedOption = countrySelect.options[countrySelect.selectedIndex];
            const flagCode = selectedOption.dataset.flag;

            if (flagCode) {
                countryFlagSpan.innerHTML = `<img src="https://flagcdn.com/w20/${flagCode}.png" alt="${flagCode}" style="width:20px;height:14px;object-fit:cover;border-radius:2px;" onerror="this.style.display='none'" />`;
            } else {
                countryFlagSpan.innerHTML = '';
            }
        }

        // Update on page load
        updateCountryFlag();

        // Update on change
        countrySelect.addEventListener('change', updateCountryFlag);
    }
</script>
@endsection
