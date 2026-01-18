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

            @if(session('status') === 'logo-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ __('messages.logo_updated') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Logo Upload Section (Separate Form) -->
            <form id="logoForm" method="POST" action="{{ route('profile.logo.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-4">
                            <div class="logo-container me-3" style="width: 80px; height: 80px; border: 2px dashed #d9dee3; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                @if(auth()->user()->logo)
                                    <img src="{{ asset('storage/' . auth()->user()->logo) }}" alt="{{ auth()->user()->name }}" style="max-width: 100%; max-height: 100%; object-fit: contain;" id="logoPreview" />
                                @else
                                    <span class="text-muted" style="font-size: 0.75rem; text-align: center;" id="logoPlaceholder">
                                        <i class="ri-image-line d-block" style="font-size: 1.5rem;"></i>
                                        {{ __('messages.no_logo') }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                <label for="logo" class="btn btn-primary btn-sm mb-2">
                                    <i class="ri-upload-2-line me-1"></i>{{ __('messages.upload_logo') }}
                                </label>
                                <input type="file" id="logo" name="logo" class="d-none" accept="image/*">
                                <p class="text-muted small mb-0">{{ __('messages.allowed_formats') }}</p>
                                @error('logo')
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
                        <div class="input-group">
                            <select class="form-select @error('phone_code') is-invalid @enderror"
                                    id="phone_code"
                                    name="phone_code"
                                    style="max-width: 140px;">
                                @php
                                    $arabCountryCodes = [
                                        ['code' => '+971', 'flag' => '🇦🇪', 'name' => 'الإمارات'],
                                        ['code' => '+966', 'flag' => '🇸🇦', 'name' => 'السعودية'],
                                        ['code' => '+965', 'flag' => '🇰🇼', 'name' => 'الكويت'],
                                        ['code' => '+974', 'flag' => '🇶🇦', 'name' => 'قطر'],
                                        ['code' => '+973', 'flag' => '🇧🇭', 'name' => 'البحرين'],
                                        ['code' => '+968', 'flag' => '🇴🇲', 'name' => 'عُمان'],
                                        ['code' => '+962', 'flag' => '🇯🇴', 'name' => 'الأردن'],
                                        ['code' => '+961', 'flag' => '🇱🇧', 'name' => 'لبنان'],
                                        ['code' => '+963', 'flag' => '🇸🇾', 'name' => 'سوريا'],
                                        ['code' => '+970', 'flag' => '🇵🇸', 'name' => 'فلسطين'],
                                        ['code' => '+964', 'flag' => '🇮🇶', 'name' => 'العراق'],
                                        ['code' => '+20', 'flag' => '🇪🇬', 'name' => 'مصر'],
                                        ['code' => '+218', 'flag' => '🇱🇾', 'name' => 'ليبيا'],
                                        ['code' => '+216', 'flag' => '🇹🇳', 'name' => 'تونس'],
                                        ['code' => '+213', 'flag' => '🇩🇿', 'name' => 'الجزائر'],
                                        ['code' => '+212', 'flag' => '🇲🇦', 'name' => 'المغرب'],
                                        ['code' => '+222', 'flag' => '🇲🇷', 'name' => 'موريتانيا'],
                                        ['code' => '+249', 'flag' => '🇸🇩', 'name' => 'السودان'],
                                        ['code' => '+967', 'flag' => '🇾🇪', 'name' => 'اليمن'],
                                        ['code' => '+252', 'flag' => '🇸🇴', 'name' => 'الصومال'],
                                        ['code' => '+253', 'flag' => '🇩🇯', 'name' => 'جيبوتي'],
                                        ['code' => '+269', 'flag' => '🇰🇲', 'name' => 'جزر القمر'],
                                    ];
                                @endphp
                                @foreach($arabCountryCodes as $country)
                                    <option value="{{ $country['code'] }}" {{ old('phone_code', $user->phone_code ?? '+971') == $country['code'] ? 'selected' : '' }}>
                                        {{ $country['flag'] }} {{ $country['code'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'رقم الهاتف' : 'Phone number' }}"
                                   dir="ltr" style="text-align: left;">
                        </div>
                        @error('phone_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">{{ __('messages.email') }} <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}" required
                               dir="ltr" style="text-align: left;">
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
                        @php
                            $countries = [
                                'AF' => ['name_ar' => 'أفغانستان', 'name_en' => 'Afghanistan'],
                                'AL' => ['name_ar' => 'ألبانيا', 'name_en' => 'Albania'],
                                'DZ' => ['name_ar' => 'الجزائر', 'name_en' => 'Algeria'],
                                'AD' => ['name_ar' => 'أندورا', 'name_en' => 'Andorra'],
                                'AO' => ['name_ar' => 'أنغولا', 'name_en' => 'Angola'],
                                'AG' => ['name_ar' => 'أنتيغوا وبربودا', 'name_en' => 'Antigua and Barbuda'],
                                'AR' => ['name_ar' => 'الأرجنتين', 'name_en' => 'Argentina'],
                                'AM' => ['name_ar' => 'أرمينيا', 'name_en' => 'Armenia'],
                                'AU' => ['name_ar' => 'أستراليا', 'name_en' => 'Australia'],
                                'AT' => ['name_ar' => 'النمسا', 'name_en' => 'Austria'],
                                'AZ' => ['name_ar' => 'أذربيجان', 'name_en' => 'Azerbaijan'],
                                'BS' => ['name_ar' => 'الباهاما', 'name_en' => 'Bahamas'],
                                'BH' => ['name_ar' => 'البحرين', 'name_en' => 'Bahrain'],
                                'BD' => ['name_ar' => 'بنغلاديش', 'name_en' => 'Bangladesh'],
                                'BB' => ['name_ar' => 'بربادوس', 'name_en' => 'Barbados'],
                                'BY' => ['name_ar' => 'بيلاروسيا', 'name_en' => 'Belarus'],
                                'BE' => ['name_ar' => 'بلجيكا', 'name_en' => 'Belgium'],
                                'BZ' => ['name_ar' => 'بليز', 'name_en' => 'Belize'],
                                'BJ' => ['name_ar' => 'بنين', 'name_en' => 'Benin'],
                                'BT' => ['name_ar' => 'بوتان', 'name_en' => 'Bhutan'],
                                'BO' => ['name_ar' => 'بوليفيا', 'name_en' => 'Bolivia'],
                                'BA' => ['name_ar' => 'البوسنة والهرسك', 'name_en' => 'Bosnia and Herzegovina'],
                                'BW' => ['name_ar' => 'بوتسوانا', 'name_en' => 'Botswana'],
                                'BR' => ['name_ar' => 'البرازيل', 'name_en' => 'Brazil'],
                                'BN' => ['name_ar' => 'بروناي', 'name_en' => 'Brunei'],
                                'BG' => ['name_ar' => 'بلغاريا', 'name_en' => 'Bulgaria'],
                                'BF' => ['name_ar' => 'بوركينا فاسو', 'name_en' => 'Burkina Faso'],
                                'BI' => ['name_ar' => 'بوروندي', 'name_en' => 'Burundi'],
                                'KH' => ['name_ar' => 'كمبوديا', 'name_en' => 'Cambodia'],
                                'CM' => ['name_ar' => 'الكاميرون', 'name_en' => 'Cameroon'],
                                'CA' => ['name_ar' => 'كندا', 'name_en' => 'Canada'],
                                'CV' => ['name_ar' => 'الرأس الأخضر', 'name_en' => 'Cape Verde'],
                                'CF' => ['name_ar' => 'جمهورية أفريقيا الوسطى', 'name_en' => 'Central African Republic'],
                                'TD' => ['name_ar' => 'تشاد', 'name_en' => 'Chad'],
                                'CL' => ['name_ar' => 'تشيلي', 'name_en' => 'Chile'],
                                'CN' => ['name_ar' => 'الصين', 'name_en' => 'China'],
                                'CO' => ['name_ar' => 'كولومبيا', 'name_en' => 'Colombia'],
                                'KM' => ['name_ar' => 'جزر القمر', 'name_en' => 'Comoros'],
                                'CG' => ['name_ar' => 'الكونغو', 'name_en' => 'Congo'],
                                'CR' => ['name_ar' => 'كوستاريكا', 'name_en' => 'Costa Rica'],
                                'HR' => ['name_ar' => 'كرواتيا', 'name_en' => 'Croatia'],
                                'CU' => ['name_ar' => 'كوبا', 'name_en' => 'Cuba'],
                                'CY' => ['name_ar' => 'قبرص', 'name_en' => 'Cyprus'],
                                'CZ' => ['name_ar' => 'التشيك', 'name_en' => 'Czech Republic'],
                                'DK' => ['name_ar' => 'الدنمارك', 'name_en' => 'Denmark'],
                                'DJ' => ['name_ar' => 'جيبوتي', 'name_en' => 'Djibouti'],
                                'DM' => ['name_ar' => 'دومينيكا', 'name_en' => 'Dominica'],
                                'DO' => ['name_ar' => 'جمهورية الدومينيكان', 'name_en' => 'Dominican Republic'],
                                'EC' => ['name_ar' => 'الإكوادور', 'name_en' => 'Ecuador'],
                                'EG' => ['name_ar' => 'مصر', 'name_en' => 'Egypt'],
                                'SV' => ['name_ar' => 'السلفادور', 'name_en' => 'El Salvador'],
                                'GQ' => ['name_ar' => 'غينيا الاستوائية', 'name_en' => 'Equatorial Guinea'],
                                'ER' => ['name_ar' => 'إريتريا', 'name_en' => 'Eritrea'],
                                'EE' => ['name_ar' => 'إستونيا', 'name_en' => 'Estonia'],
                                'ET' => ['name_ar' => 'إثيوبيا', 'name_en' => 'Ethiopia'],
                                'FJ' => ['name_ar' => 'فيجي', 'name_en' => 'Fiji'],
                                'FI' => ['name_ar' => 'فنلندا', 'name_en' => 'Finland'],
                                'FR' => ['name_ar' => 'فرنسا', 'name_en' => 'France'],
                                'GA' => ['name_ar' => 'الغابون', 'name_en' => 'Gabon'],
                                'GM' => ['name_ar' => 'غامبيا', 'name_en' => 'Gambia'],
                                'GE' => ['name_ar' => 'جورجيا', 'name_en' => 'Georgia'],
                                'DE' => ['name_ar' => 'ألمانيا', 'name_en' => 'Germany'],
                                'GH' => ['name_ar' => 'غانا', 'name_en' => 'Ghana'],
                                'GR' => ['name_ar' => 'اليونان', 'name_en' => 'Greece'],
                                'GD' => ['name_ar' => 'غرينادا', 'name_en' => 'Grenada'],
                                'GT' => ['name_ar' => 'غواتيمالا', 'name_en' => 'Guatemala'],
                                'GN' => ['name_ar' => 'غينيا', 'name_en' => 'Guinea'],
                                'GW' => ['name_ar' => 'غينيا بيساو', 'name_en' => 'Guinea-Bissau'],
                                'GY' => ['name_ar' => 'غيانا', 'name_en' => 'Guyana'],
                                'HT' => ['name_ar' => 'هايتي', 'name_en' => 'Haiti'],
                                'HN' => ['name_ar' => 'هندوراس', 'name_en' => 'Honduras'],
                                'HU' => ['name_ar' => 'المجر', 'name_en' => 'Hungary'],
                                'IS' => ['name_ar' => 'آيسلندا', 'name_en' => 'Iceland'],
                                'IN' => ['name_ar' => 'الهند', 'name_en' => 'India'],
                                'ID' => ['name_ar' => 'إندونيسيا', 'name_en' => 'Indonesia'],
                                'IR' => ['name_ar' => 'إيران', 'name_en' => 'Iran'],
                                'IQ' => ['name_ar' => 'العراق', 'name_en' => 'Iraq'],
                                'IE' => ['name_ar' => 'أيرلندا', 'name_en' => 'Ireland'],
                                'IL' => ['name_ar' => 'إسرائيل', 'name_en' => 'Israel'],
                                'IT' => ['name_ar' => 'إيطاليا', 'name_en' => 'Italy'],
                                'JM' => ['name_ar' => 'جامايكا', 'name_en' => 'Jamaica'],
                                'JP' => ['name_ar' => 'اليابان', 'name_en' => 'Japan'],
                                'JO' => ['name_ar' => 'الأردن', 'name_en' => 'Jordan'],
                                'KZ' => ['name_ar' => 'كازاخستان', 'name_en' => 'Kazakhstan'],
                                'KE' => ['name_ar' => 'كينيا', 'name_en' => 'Kenya'],
                                'KI' => ['name_ar' => 'كيريباتي', 'name_en' => 'Kiribati'],
                                'KP' => ['name_ar' => 'كوريا الشمالية', 'name_en' => 'North Korea'],
                                'KR' => ['name_ar' => 'كوريا الجنوبية', 'name_en' => 'South Korea'],
                                'KW' => ['name_ar' => 'الكويت', 'name_en' => 'Kuwait'],
                                'KG' => ['name_ar' => 'قيرغيزستان', 'name_en' => 'Kyrgyzstan'],
                                'LA' => ['name_ar' => 'لاوس', 'name_en' => 'Laos'],
                                'LV' => ['name_ar' => 'لاتفيا', 'name_en' => 'Latvia'],
                                'LB' => ['name_ar' => 'لبنان', 'name_en' => 'Lebanon'],
                                'LS' => ['name_ar' => 'ليسوتو', 'name_en' => 'Lesotho'],
                                'LR' => ['name_ar' => 'ليبيريا', 'name_en' => 'Liberia'],
                                'LY' => ['name_ar' => 'ليبيا', 'name_en' => 'Libya'],
                                'LI' => ['name_ar' => 'ليختنشتاين', 'name_en' => 'Liechtenstein'],
                                'LT' => ['name_ar' => 'ليتوانيا', 'name_en' => 'Lithuania'],
                                'LU' => ['name_ar' => 'لوكسمبورغ', 'name_en' => 'Luxembourg'],
                                'MK' => ['name_ar' => 'مقدونيا الشمالية', 'name_en' => 'North Macedonia'],
                                'MG' => ['name_ar' => 'مدغشقر', 'name_en' => 'Madagascar'],
                                'MW' => ['name_ar' => 'مالاوي', 'name_en' => 'Malawi'],
                                'MY' => ['name_ar' => 'ماليزيا', 'name_en' => 'Malaysia'],
                                'MV' => ['name_ar' => 'المالديف', 'name_en' => 'Maldives'],
                                'ML' => ['name_ar' => 'مالي', 'name_en' => 'Mali'],
                                'MT' => ['name_ar' => 'مالطا', 'name_en' => 'Malta'],
                                'MH' => ['name_ar' => 'جزر مارشال', 'name_en' => 'Marshall Islands'],
                                'MR' => ['name_ar' => 'موريتانيا', 'name_en' => 'Mauritania'],
                                'MU' => ['name_ar' => 'موريشيوس', 'name_en' => 'Mauritius'],
                                'MX' => ['name_ar' => 'المكسيك', 'name_en' => 'Mexico'],
                                'FM' => ['name_ar' => 'ميكرونيزيا', 'name_en' => 'Micronesia'],
                                'MD' => ['name_ar' => 'مولدوفا', 'name_en' => 'Moldova'],
                                'MC' => ['name_ar' => 'موناكو', 'name_en' => 'Monaco'],
                                'MN' => ['name_ar' => 'منغوليا', 'name_en' => 'Mongolia'],
                                'ME' => ['name_ar' => 'الجبل الأسود', 'name_en' => 'Montenegro'],
                                'MA' => ['name_ar' => 'المغرب', 'name_en' => 'Morocco'],
                                'MZ' => ['name_ar' => 'موزمبيق', 'name_en' => 'Mozambique'],
                                'MM' => ['name_ar' => 'ميانمار', 'name_en' => 'Myanmar'],
                                'NA' => ['name_ar' => 'ناميبيا', 'name_en' => 'Namibia'],
                                'NR' => ['name_ar' => 'ناورو', 'name_en' => 'Nauru'],
                                'NP' => ['name_ar' => 'نيبال', 'name_en' => 'Nepal'],
                                'NL' => ['name_ar' => 'هولندا', 'name_en' => 'Netherlands'],
                                'NZ' => ['name_ar' => 'نيوزيلندا', 'name_en' => 'New Zealand'],
                                'NI' => ['name_ar' => 'نيكاراغوا', 'name_en' => 'Nicaragua'],
                                'NE' => ['name_ar' => 'النيجر', 'name_en' => 'Niger'],
                                'NG' => ['name_ar' => 'نيجيريا', 'name_en' => 'Nigeria'],
                                'NO' => ['name_ar' => 'النرويج', 'name_en' => 'Norway'],
                                'OM' => ['name_ar' => 'عمان', 'name_en' => 'Oman'],
                                'PK' => ['name_ar' => 'باكستان', 'name_en' => 'Pakistan'],
                                'PW' => ['name_ar' => 'بالاو', 'name_en' => 'Palau'],
                                'PS' => ['name_ar' => 'فلسطين', 'name_en' => 'Palestine'],
                                'PA' => ['name_ar' => 'بنما', 'name_en' => 'Panama'],
                                'PG' => ['name_ar' => 'بابوا غينيا الجديدة', 'name_en' => 'Papua New Guinea'],
                                'PY' => ['name_ar' => 'باراغواي', 'name_en' => 'Paraguay'],
                                'PE' => ['name_ar' => 'بيرو', 'name_en' => 'Peru'],
                                'PH' => ['name_ar' => 'الفلبين', 'name_en' => 'Philippines'],
                                'PL' => ['name_ar' => 'بولندا', 'name_en' => 'Poland'],
                                'PT' => ['name_ar' => 'البرتغال', 'name_en' => 'Portugal'],
                                'QA' => ['name_ar' => 'قطر', 'name_en' => 'Qatar'],
                                'RO' => ['name_ar' => 'رومانيا', 'name_en' => 'Romania'],
                                'RU' => ['name_ar' => 'روسيا', 'name_en' => 'Russia'],
                                'RW' => ['name_ar' => 'رواندا', 'name_en' => 'Rwanda'],
                                'KN' => ['name_ar' => 'سانت كيتس ونيفيس', 'name_en' => 'Saint Kitts and Nevis'],
                                'LC' => ['name_ar' => 'سانت لوسيا', 'name_en' => 'Saint Lucia'],
                                'VC' => ['name_ar' => 'سانت فنسنت والغرينادين', 'name_en' => 'Saint Vincent and the Grenadines'],
                                'WS' => ['name_ar' => 'ساموا', 'name_en' => 'Samoa'],
                                'SM' => ['name_ar' => 'سان مارينو', 'name_en' => 'San Marino'],
                                'ST' => ['name_ar' => 'ساو تومي وبرينسيب', 'name_en' => 'Sao Tome and Principe'],
                                'SA' => ['name_ar' => 'المملكة العربية السعودية', 'name_en' => 'Saudi Arabia'],
                                'SN' => ['name_ar' => 'السنغال', 'name_en' => 'Senegal'],
                                'RS' => ['name_ar' => 'صربيا', 'name_en' => 'Serbia'],
                                'SC' => ['name_ar' => 'سيشل', 'name_en' => 'Seychelles'],
                                'SL' => ['name_ar' => 'سيراليون', 'name_en' => 'Sierra Leone'],
                                'SG' => ['name_ar' => 'سنغافورة', 'name_en' => 'Singapore'],
                                'SK' => ['name_ar' => 'سلوفاكيا', 'name_en' => 'Slovakia'],
                                'SI' => ['name_ar' => 'سلوفينيا', 'name_en' => 'Slovenia'],
                                'SB' => ['name_ar' => 'جزر سليمان', 'name_en' => 'Solomon Islands'],
                                'SO' => ['name_ar' => 'الصومال', 'name_en' => 'Somalia'],
                                'ZA' => ['name_ar' => 'جنوب أفريقيا', 'name_en' => 'South Africa'],
                                'SS' => ['name_ar' => 'جنوب السودان', 'name_en' => 'South Sudan'],
                                'ES' => ['name_ar' => 'إسبانيا', 'name_en' => 'Spain'],
                                'LK' => ['name_ar' => 'سريلانكا', 'name_en' => 'Sri Lanka'],
                                'SD' => ['name_ar' => 'السودان', 'name_en' => 'Sudan'],
                                'SR' => ['name_ar' => 'سورينام', 'name_en' => 'Suriname'],
                                'SE' => ['name_ar' => 'السويد', 'name_en' => 'Sweden'],
                                'CH' => ['name_ar' => 'سويسرا', 'name_en' => 'Switzerland'],
                                'SY' => ['name_ar' => 'سوريا', 'name_en' => 'Syria'],
                                'TW' => ['name_ar' => 'تايوان', 'name_en' => 'Taiwan'],
                                'TJ' => ['name_ar' => 'طاجيكستان', 'name_en' => 'Tajikistan'],
                                'TZ' => ['name_ar' => 'تنزانيا', 'name_en' => 'Tanzania'],
                                'TH' => ['name_ar' => 'تايلاند', 'name_en' => 'Thailand'],
                                'TL' => ['name_ar' => 'تيمور الشرقية', 'name_en' => 'Timor-Leste'],
                                'TG' => ['name_ar' => 'توغو', 'name_en' => 'Togo'],
                                'TO' => ['name_ar' => 'تونغا', 'name_en' => 'Tonga'],
                                'TT' => ['name_ar' => 'ترينيداد وتوباغو', 'name_en' => 'Trinidad and Tobago'],
                                'TN' => ['name_ar' => 'تونس', 'name_en' => 'Tunisia'],
                                'TR' => ['name_ar' => 'تركيا', 'name_en' => 'Turkey'],
                                'TM' => ['name_ar' => 'تركمانستان', 'name_en' => 'Turkmenistan'],
                                'TV' => ['name_ar' => 'توفالو', 'name_en' => 'Tuvalu'],
                                'UG' => ['name_ar' => 'أوغندا', 'name_en' => 'Uganda'],
                                'UA' => ['name_ar' => 'أوكرانيا', 'name_en' => 'Ukraine'],
                                'AE' => ['name_ar' => 'الإمارات العربية المتحدة', 'name_en' => 'United Arab Emirates'],
                                'GB' => ['name_ar' => 'المملكة المتحدة', 'name_en' => 'United Kingdom'],
                                'US' => ['name_ar' => 'الولايات المتحدة', 'name_en' => 'United States'],
                                'UY' => ['name_ar' => 'أوروغواي', 'name_en' => 'Uruguay'],
                                'UZ' => ['name_ar' => 'أوزبكستان', 'name_en' => 'Uzbekistan'],
                                'VU' => ['name_ar' => 'فانواتو', 'name_en' => 'Vanuatu'],
                                'VA' => ['name_ar' => 'الفاتيكان', 'name_en' => 'Vatican City'],
                                'VE' => ['name_ar' => 'فنزويلا', 'name_en' => 'Venezuela'],
                                'VN' => ['name_ar' => 'فيتنام', 'name_en' => 'Vietnam'],
                                'YE' => ['name_ar' => 'اليمن', 'name_en' => 'Yemen'],
                                'ZM' => ['name_ar' => 'زامبيا', 'name_en' => 'Zambia'],
                                'ZW' => ['name_ar' => 'زيمبابوي', 'name_en' => 'Zimbabwe'],
                            ];
                            $locale = app()->getLocale();
                            $userCountry = old('country', $user->country);
                            $userCountryName = '';
                            $userCountryFlag = '';
                            if ($userCountry && isset($countries[$userCountry])) {
                                $userCountryName = $locale == 'ar' ? $countries[$userCountry]['name_ar'] : $countries[$userCountry]['name_en'];
                                $userCountryFlag = strtolower($userCountry);
                            }
                        @endphp
                        <div class="custom-select-wrapper">
                            <div class="custom-select-display" id="customSelectDisplay">
                                <span class="selected-flag">
                                    @if($userCountryFlag)
                                        <img src="https://flagcdn.com/{{ $userCountryFlag }}.svg" alt="{{ $userCountry }}" style="width:20px;height:14px;object-fit:cover;border-radius:2px;" onerror="this.style.display='none'" />
                                    @endif
                                </span>
                                <span class="selected-text">{{ $userCountryName ?: __('messages.select_country') }}</span>
                                <i class="ri-arrow-down-s-line"></i>
                            </div>
                            <div class="custom-select-dropdown" id="customSelectDropdown">
                                <div class="custom-select-search">
                                    <input type="text" id="countrySearch" placeholder="{{ __('messages.search') }}...">
                                </div>
                                <div class="custom-select-options" id="customSelectOptions">
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
                                        <div class="custom-select-option" data-value="{{ $code }}" data-flag="{{ strtolower($code) }}" data-name="{{ $locale == 'ar' ? $country['name_ar'] : $country['name_en'] }}">
                                            <img src="https://flagcdn.com/{{ strtolower($code) }}.svg" alt="{{ $code }}" style="width:20px;height:14px;object-fit:cover;border-radius:2px;margin-right:8px;" onerror="this.style.display='none'" />
                                            <span>{{ $locale == 'ar' ? $country['name_ar'] : $country['name_en'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <select class="d-none @error('country') is-invalid @enderror" id="country" name="country">
                                <option value="">{{ __('messages.select_country') }}</option>
                                @foreach($countries as $code => $country)
                                    <option value="{{ $code }}" {{ old('country', $user->country) == $code ? 'selected' : '' }}>
                                        {{ $locale == 'ar' ? $country['name_ar'] : $country['name_en'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('country')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="user_type" class="form-label">{{ __('messages.user_type') }}</label>
                        <select class="form-select @error('user_type') is-invalid @enderror" id="user_type" name="user_type" disabled>
                            <option value="admin" {{ $user->user_type == 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                            <option value="seller" {{ $user->user_type == 'seller' ? 'selected' : '' }}>{{ __('messages.seller') }}</option>
                            <option value="distributor" {{ $user->user_type == 'distributor' ? 'selected' : '' }}>{{ __('messages.distributor') }}</option>
                            <option value="buyer" {{ $user->user_type == 'buyer' ? 'selected' : '' }}>{{ __('messages.buyer') }}</option>
                        </select>
                        <small class="text-muted">{{ __('messages.contact_admin_change') }}</small>
                        @error('user_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($user->user_type == 'distributor' && ($user->store_name || $user->store_slug || $user->commercial_register || $user->freelance_document))
                    <!-- Distributor Registration Information (Read Only) -->
                    <div class="col-12 mt-4">
                        <h6 class="text-primary mb-3">
                            <i class="ri-store-2-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'معلومات المتجر' : 'Store Information' }}
                        </h6>
                    </div>

                    @if($user->store_name)
                    <div class="col-md-6">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'اسم المتجر' : 'Store Name' }}</label>
                        <input type="text" class="form-control" value="{{ $user->store_name }}" readonly disabled>
                    </div>
                    @endif

                    @if($user->store_slug)
                    <div class="col-md-6">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'رابط المتجر' : 'Store URL' }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $user->store_slug }}" readonly disabled>
                            <span class="input-group-text">.selaa.com</span>
                        </div>
                    </div>
                    @endif

                    @if($user->default_currency)
                    <div class="col-md-6">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'العملة الافتراضية' : 'Default Currency' }}</label>
                        <input type="text" class="form-control" value="{{ $user->default_currency }}" readonly disabled>
                    </div>
                    @endif

                    @if($user->commercial_register)
                    <div class="col-md-6">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'السجل التجاري' : 'Commercial Register' }}</label>
                        <div class="d-flex align-items-center">
                            <a href="{{ asset('storage/' . $user->commercial_register) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="ri-file-text-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'عرض المستند' : 'View Document' }}
                            </a>
                            <span class="badge bg-success ms-2">
                                <i class="ri-checkbox-circle-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'تم الرفع' : 'Uploaded' }}
                            </span>
                        </div>
                    </div>
                    @endif

                    @if($user->freelance_document)
                    <div class="col-md-6">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'وثيقة العمل الحر' : 'Freelance Document' }}</label>
                        <div class="d-flex align-items-center">
                            <a href="{{ asset('storage/' . $user->freelance_document) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="ri-file-user-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'عرض المستند' : 'View Document' }}
                            </a>
                            <span class="badge bg-success ms-2">
                                <i class="ri-checkbox-circle-line me-1"></i>{{ app()->getLocale() == 'ar' ? 'تم الرفع' : 'Uploaded' }}
                            </span>
                        </div>
                    </div>
                    @endif

                    @if($user->social_media_accounts && is_array($user->social_media_accounts) && count(array_filter($user->social_media_accounts)) > 0)
                    <div class="col-12">
                        <label class="form-label">{{ app()->getLocale() == 'ar' ? 'حسابات التواصل الاجتماعي' : 'Social Media Accounts' }}</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($user->social_media_accounts as $platform => $url)
                                @if($url)
                                <a href="{{ $url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    @if($platform == 'instagram')
                                        <i class="ri-instagram-line me-1"></i>
                                    @elseif($platform == 'facebook')
                                        <i class="ri-facebook-line me-1"></i>
                                    @elseif($platform == 'twitter' || $platform == 'x')
                                        <i class="ri-twitter-x-line me-1"></i>
                                    @elseif($platform == 'tiktok')
                                        <i class="ri-tiktok-line me-1"></i>
                                    @elseif($platform == 'youtube')
                                        <i class="ri-youtube-line me-1"></i>
                                    @elseif($platform == 'snapchat')
                                        <i class="ri-snapchat-line me-1"></i>
                                    @elseif($platform == 'whatsapp')
                                        <i class="ri-whatsapp-line me-1"></i>
                                    @else
                                        <i class="ri-link me-1"></i>
                                    @endif
                                    {{ ucfirst($platform) }}
                                </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endif

                    <!-- Withdrawal Method Section -->
                    @if($user->user_type == 'seller' || $user->user_type == 'distributor')
                    <div class="col-12 mt-4">
                        <h6 class="text-primary mb-3">
                            <i class="ri-bank-card-line me-1"></i>{{ __('messages.withdrawal_method_settings') }}
                        </h6>
                        <p class="text-muted small mb-3">{{ __('messages.withdrawal_method_description') }}</p>
                    </div>

                    <div class="col-md-6">
                        <label for="withdrawal_method" class="form-label">{{ __('messages.withdrawal_method') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('withdrawal_method') is-invalid @enderror" id="withdrawal_method" name="withdrawal_method">
                            <option value="">{{ __('messages.select_withdrawal_method') }}</option>
                            <option value="paypal" {{ old('withdrawal_method', $user->withdrawal_method) == 'paypal' ? 'selected' : '' }}>{{ __('messages.paypal') }}</option>
                            <option value="bank_transfer" {{ old('withdrawal_method', $user->withdrawal_method) == 'bank_transfer' ? 'selected' : '' }}>{{ __('messages.bank_transfer') }}</option>
                            <option value="mobile_wallet" {{ old('withdrawal_method', $user->withdrawal_method) == 'mobile_wallet' ? 'selected' : '' }}>{{ __('messages.mobile_wallet') }}</option>
                        </select>
                        @error('withdrawal_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- PayPal Fields -->
                    <div class="col-md-6 withdrawal-fields paypal-fields" style="{{ old('withdrawal_method', $user->withdrawal_method) == 'paypal' ? '' : 'display: none;' }}">
                        <label for="paypal_email" class="form-label">{{ __('messages.paypal_email') }} <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('paypal_email') is-invalid @enderror"
                               id="paypal_email" name="paypal_email" value="{{ old('paypal_email', $user->paypal_email) }}"
                               placeholder="example@email.com"
                               dir="ltr" style="text-align: left;">
                        @error('paypal_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Bank Transfer Fields -->
                    <div class="col-md-6 withdrawal-fields bank-fields" style="{{ old('withdrawal_method', $user->withdrawal_method) == 'bank_transfer' ? '' : 'display: none;' }}">
                        <label for="bank_name" class="form-label">{{ __('messages.bank_name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('bank_name') is-invalid @enderror"
                               id="bank_name" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}">
                        @error('bank_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 withdrawal-fields bank-fields" style="{{ old('withdrawal_method', $user->withdrawal_method) == 'bank_transfer' ? '' : 'display: none;' }}">
                        <label for="bank_account_name" class="form-label">{{ __('messages.account_holder_name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('bank_account_name') is-invalid @enderror"
                               id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name', $user->bank_account_name) }}">
                        @error('bank_account_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 withdrawal-fields bank-fields" style="{{ old('withdrawal_method', $user->withdrawal_method) == 'bank_transfer' ? '' : 'display: none;' }}">
                        <label for="bank_account_number" class="form-label">{{ __('messages.account_number') }}</label>
                        <input type="text" class="form-control @error('bank_account_number') is-invalid @enderror"
                               id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}"
                               dir="ltr" style="text-align: left;">
                        @error('bank_account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 withdrawal-fields bank-fields" style="{{ old('withdrawal_method', $user->withdrawal_method) == 'bank_transfer' ? '' : 'display: none;' }}">
                        <label for="bank_iban" class="form-label">{{ __('messages.iban') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('bank_iban') is-invalid @enderror"
                               id="bank_iban" name="bank_iban" value="{{ old('bank_iban', $user->bank_iban) }}"
                               dir="ltr" style="text-align: left;"
                               placeholder="AE000000000000000000000">
                        @error('bank_iban')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 withdrawal-fields bank-fields" style="{{ old('withdrawal_method', $user->withdrawal_method) == 'bank_transfer' ? '' : 'display: none;' }}">
                        <label for="bank_swift_code" class="form-label">{{ __('messages.swift_code') }}</label>
                        <input type="text" class="form-control @error('bank_swift_code') is-invalid @enderror"
                               id="bank_swift_code" name="bank_swift_code" value="{{ old('bank_swift_code', $user->bank_swift_code) }}">
                        @error('bank_swift_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Mobile Wallet Fields -->
                    <div class="col-md-6 withdrawal-fields wallet-fields" style="{{ old('withdrawal_method', $user->withdrawal_method) == 'mobile_wallet' ? '' : 'display: none;' }}">
                        <label for="wallet_provider" class="form-label">{{ __('messages.wallet_provider') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('wallet_provider') is-invalid @enderror" id="wallet_provider" name="wallet_provider">
                            <option value="">{{ __('messages.select_wallet_provider') }}</option>
                            <option value="vodafone_cash" {{ old('wallet_provider', $user->wallet_provider) == 'vodafone_cash' ? 'selected' : '' }}>Vodafone Cash</option>
                            <option value="orange_money" {{ old('wallet_provider', $user->wallet_provider) == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                            <option value="etisalat_cash" {{ old('wallet_provider', $user->wallet_provider) == 'etisalat_cash' ? 'selected' : '' }}>Etisalat Cash</option>
                            <option value="stc_pay" {{ old('wallet_provider', $user->wallet_provider) == 'stc_pay' ? 'selected' : '' }}>STC Pay</option>
                            <option value="apple_pay" {{ old('wallet_provider', $user->wallet_provider) == 'apple_pay' ? 'selected' : '' }}>Apple Pay</option>
                            <option value="other" {{ old('wallet_provider', $user->wallet_provider) == 'other' ? 'selected' : '' }}>{{ __('messages.other') }}</option>
                        </select>
                        @error('wallet_provider')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 withdrawal-fields wallet-fields" style="{{ old('withdrawal_method', $user->withdrawal_method) == 'mobile_wallet' ? '' : 'display: none;' }}">
                        <label for="wallet_phone_number" class="form-label">{{ __('messages.wallet_phone_number') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('wallet_phone_number') is-invalid @enderror"
                               id="wallet_phone_number" name="wallet_phone_number" value="{{ old('wallet_phone_number', $user->wallet_phone_number) }}"
                               dir="ltr" style="text-align: left;">
                        @error('wallet_phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 withdrawal-fields wallet-fields" style="{{ old('withdrawal_method', $user->withdrawal_method) == 'mobile_wallet' ? '' : 'display: none;' }}">
                        <label for="wallet_holder_name" class="form-label">{{ __('messages.wallet_holder_name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('wallet_holder_name') is-invalid @enderror"
                               id="wallet_holder_name" name="wallet_holder_name" value="{{ old('wallet_holder_name', $user->wallet_holder_name) }}">
                        @error('wallet_holder_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    <!-- Financial Information -->
                    <div class="col-12 mt-4">
                        <h6 class="text-primary mb-3">
                            <i class="ri-money-dollar-circle-line me-1"></i>{{ __('messages.financial_information') }}
                        </h6>
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
    /* Custom Select Styles */
    .custom-select-wrapper {
        position: relative;
        width: 100%;
    }

    .custom-select-display {
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        padding: 0.4375rem 2.5rem 0.4375rem 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        min-height: calc(1.5em + 0.875rem + 2px);
    }

    .custom-select-display:hover {
        border-color: #b4bdc6;
    }

    .custom-select-display i {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        transition: transform 0.3s;
    }

    .custom-select-wrapper.active .custom-select-display i {
        transform: translateY(-50%) rotate(180deg);
    }

    .selected-flag {
        margin-right: 8px;
        display: inline-flex;
        align-items: center;
    }

    .selected-flag img {
        width: 20px;
        height: 14px;
        object-fit: cover;
        border-radius: 2px;
    }

    .custom-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        margin-top: 4px;
        max-height: 300px;
        overflow: hidden;
        display: none;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .custom-select-wrapper.active .custom-select-dropdown {
        display: block;
    }

    .custom-select-search {
        padding: 8px;
        border-bottom: 1px solid #d9dee3;
    }

    .custom-select-search input {
        width: 100%;
        padding: 6px 12px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        outline: none;
    }

    .custom-select-search input:focus {
        border-color: #696cff;
    }

    .custom-select-options {
        max-height: 250px;
        overflow-y: auto;
    }

    .custom-select-option {
        padding: 8px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: background 0.2s;
    }

    .custom-select-option:hover {
        background: #f5f5f9;
    }

    .custom-select-option.selected {
        background: #696cff;
        color: #fff;
    }

    .custom-select-option img {
        flex-shrink: 0;
    }

    /* RTL support */
    [dir="rtl"] .custom-select-display {
        padding: 0.4375rem 0.875rem 0.4375rem 2.5rem;
    }

    [dir="rtl"] .custom-select-display i {
        right: auto;
        left: 12px;
    }

    [dir="rtl"] .selected-flag {
        margin-right: 0;
        margin-left: 8px;
    }

    [dir="rtl"] .custom-select-option img {
        margin-right: 0;
        margin-left: 8px;
    }
</style>

<script>
    // Logo preview and auto-submit
    document.getElementById('logo')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const logoPreview = document.getElementById('logoPreview');
                const logoPlaceholder = document.getElementById('logoPlaceholder');

                if (logoPreview) {
                    logoPreview.src = e.target.result;
                } else if (logoPlaceholder) {
                    // Replace placeholder with image
                    const logoContainer = logoPlaceholder.parentElement;
                    logoContainer.innerHTML = '<img src="' + e.target.result + '" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;" id="logoPreview" />';
                }
            };
            reader.readAsDataURL(file);

            // Auto-submit the logo form
            document.getElementById('logoForm').submit();
        }
    });

    // Custom Select Implementation
    const customSelectWrapper = document.querySelector('.custom-select-wrapper');
    const customSelectDisplay = document.getElementById('customSelectDisplay');
    const customSelectDropdown = document.getElementById('customSelectDropdown');
    const customSelectOptions = document.getElementById('customSelectOptions');
    const countrySearch = document.getElementById('countrySearch');
    const countrySelect = document.getElementById('country');

    if (customSelectWrapper && customSelectDisplay && customSelectDropdown) {
        // Function to update display without triggering click events
        function updateCountryDisplay(value, flag, name) {
            const selectedFlag = customSelectDisplay.querySelector('.selected-flag');
            const selectedText = customSelectDisplay.querySelector('.selected-text');

            if (value) {
                selectedFlag.innerHTML = `<img src="https://flagcdn.com/${flag}.svg" alt="${flag}" style="width:20px;height:14px;object-fit:cover;border-radius:2px;" onerror="this.style.display='none'" />`;
                selectedText.textContent = name;
            } else {
                selectedFlag.innerHTML = '';
                selectedText.textContent = '{{ __("messages.select_country") }}';
            }
        }

        // Toggle dropdown
        customSelectDisplay.addEventListener('click', function(e) {
            e.stopPropagation();
            customSelectWrapper.classList.toggle('active');
            if (customSelectWrapper.classList.contains('active')) {
                countrySearch.focus();
            }
        });

        // Select option
        const options = customSelectOptions.querySelectorAll('.custom-select-option');
        options.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.dataset.value;
                const flag = this.dataset.flag;
                const name = this.dataset.name;

                // Update hidden select
                countrySelect.value = value;

                // Update display
                updateCountryDisplay(value, flag, name);

                // Update selected state
                options.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                // Close dropdown
                customSelectWrapper.classList.remove('active');
            });
        });

        // Search functionality
        countrySearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            options.forEach(option => {
                const name = option.dataset.name.toLowerCase();
                if (name.includes(searchTerm)) {
                    option.style.display = 'flex';
                } else {
                    option.style.display = 'none';
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!customSelectWrapper.contains(e.target)) {
                customSelectWrapper.classList.remove('active');
            }
        });

        // Prevent dropdown from closing when clicking inside
        customSelectDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Initialize with current value from the hidden select
        const currentValue = countrySelect.value;
        if (currentValue) {
            const currentOption = customSelectOptions.querySelector(`[data-value="${currentValue}"]`);
            if (currentOption) {
                // Update display without clicking (to avoid triggering events)
                const flag = currentOption.dataset.flag;
                const name = currentOption.dataset.name;
                updateCountryDisplay(currentValue, flag, name);
                currentOption.classList.add('selected');
            }
        }
    }

    // Withdrawal Method Toggle
    const withdrawalMethodSelect = document.getElementById('withdrawal_method');
    if (withdrawalMethodSelect) {
        function toggleWithdrawalFields() {
            const method = withdrawalMethodSelect.value;

            // Hide all withdrawal fields
            document.querySelectorAll('.withdrawal-fields').forEach(el => {
                el.style.display = 'none';
            });

            // Show relevant fields based on selected method
            if (method === 'paypal') {
                document.querySelectorAll('.paypal-fields').forEach(el => {
                    el.style.display = '';
                });
            } else if (method === 'bank_transfer') {
                document.querySelectorAll('.bank-fields').forEach(el => {
                    el.style.display = '';
                });
            } else if (method === 'mobile_wallet') {
                document.querySelectorAll('.wallet-fields').forEach(el => {
                    el.style.display = '';
                });
            }
        }

        withdrawalMethodSelect.addEventListener('change', toggleWithdrawalFields);

        // Initialize on page load
        toggleWithdrawalFields();
    }
</script>
@endsection
