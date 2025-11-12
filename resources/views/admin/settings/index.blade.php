@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.site_settings') }}</h4>
        <p class="text-muted">{{ __('messages.manage_site_settings') }}</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Localization & Currency Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.localization_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $siteLanguage = $settings->get('select', collect())->firstWhere('key', 'site_language');
                                $siteCurrency = $settings->get('select', collect())->firstWhere('key', 'site_currency');
                            @endphp

                            <div class="col-md-6 mb-3">
                                <label for="site_language" class="form-label">
                                    {{ __('messages.site_language') }}
                                    @if($siteLanguage?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $siteLanguage->description }}"></i>
                                    @endif
                                </label>
                                <select name="settings[site_language]" id="site_language" class="form-control">
                                    <option value="ar" {{ old('settings.site_language', $siteLanguage?->value ?? 'ar') === 'ar' ? 'selected' : '' }}>
                                        العربية (Arabic)
                                    </option>
                                    <option value="en" {{ old('settings.site_language', $siteLanguage?->value ?? 'ar') === 'en' ? 'selected' : '' }}>
                                        English
                                    </option>
                                </select>
                                <small class="text-muted">{{ __('messages.site_language_hint') }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="site_currency" class="form-label">
                                    {{ __('messages.site_currency') }}
                                    @if($siteCurrency?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $siteCurrency->description }}"></i>
                                    @endif
                                </label>
                                <select name="settings[site_currency]" id="site_currency" class="form-control">
                                    <option value="AED" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'AED' ? 'selected' : '' }}>
                                        AED - درهم إماراتي
                                    </option>
                                    <option value="SAR" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'SAR' ? 'selected' : '' }}>
                                        SAR - ريال سعودي
                                    </option>
                                    <option value="USD" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'USD' ? 'selected' : '' }}>
                                        USD - دولار أمريكي
                                    </option>
                                    <option value="EUR" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'EUR' ? 'selected' : '' }}>
                                        EUR - يورو
                                    </option>
                                    <option value="EGP" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'EGP' ? 'selected' : '' }}>
                                        EGP - جنيه مصري
                                    </option>
                                    <option value="KWD" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'KWD' ? 'selected' : '' }}>
                                        KWD - دينار كويتي
                                    </option>
                                    <option value="QAR" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'QAR' ? 'selected' : '' }}>
                                        QAR - ريال قطري
                                    </option>
                                    <option value="OMR" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'OMR' ? 'selected' : '' }}>
                                        OMR - ريال عماني
                                    </option>
                                    <option value="BHD" {{ old('settings.site_currency', $siteCurrency?->value ?? 'AED') === 'BHD' ? 'selected' : '' }}>
                                        BHD - دينار بحريني
                                    </option>
                                </select>
                                <small class="text-muted">{{ __('messages.site_currency_hint') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- General Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.general_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($settings->get('text', collect())->merge($settings->get('textarea', collect())) as $setting)
                            @if(!in_array($setting->key, ['admin_profit_type']))
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>
                                @if($setting->type === 'textarea')
                                <textarea
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    rows="3">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                @else
                                <input
                                    type="text"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}">
                                @endif
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.email_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($settings->get('email', collect()) as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>
                                <input
                                    type="email"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.image_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($settings->get('image', collect()) as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>

                                @if($setting->value)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $setting->value) }}"
                                         alt="{{ $setting->key }}"
                                         class="img-thumbnail"
                                         style="max-width: 200px; max-height: 200px;">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger ms-2 delete-image"
                                        data-key="{{ $setting->key }}">
                                        <i class="ri-delete-bin-line"></i> {{ __('messages.delete') }}
                                    </button>
                                </div>
                                @endif

                                <input
                                    type="file"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    accept="image/*">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Profit Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.admin_profit_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $profitType = $settings->get('text', collect())->firstWhere('key', 'admin_profit_type');
                                $profitPercentage = $settings->get('number', collect())->firstWhere('key', 'admin_profit_percentage');
                                $profitFixed = $settings->get('number', collect())->firstWhere('key', 'admin_profit_fixed');
                            @endphp

                            <div class="col-md-12 mb-3">
                                <label for="admin_profit_type" class="form-label">
                                    {{ __('messages.profit_type') }}
                                </label>
                                <select name="settings[admin_profit_type]" id="admin_profit_type" class="form-control">
                                    <option value="percentage" {{ old('settings.admin_profit_type', $profitType?->value) === 'percentage' ? 'selected' : '' }}>
                                        {{ __('messages.percentage') }}
                                    </option>
                                    <option value="fixed" {{ old('settings.admin_profit_type', $profitType?->value) === 'fixed' ? 'selected' : '' }}>
                                        {{ __('messages.fixed_amount') }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3" id="percentage_field">
                                <label for="admin_profit_percentage" class="form-label">
                                    {{ __('messages.profit_percentage') }}
                                    @if($profitPercentage?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $profitPercentage->description }}"></i>
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="settings[admin_profit_percentage]"
                                        id="admin_profit_percentage"
                                        class="form-control"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        value="{{ old('settings.admin_profit_percentage', $profitPercentage?->value) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3" id="fixed_field">
                                <label for="admin_profit_fixed" class="form-label">
                                    {{ __('messages.fixed_profit_amount') }}
                                    @if($profitFixed?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $profitFixed->description }}"></i>
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="settings[admin_profit_fixed]"
                                        id="admin_profit_fixed"
                                        class="form-control"
                                        step="0.01"
                                        min="0"
                                        value="{{ old('settings.admin_profit_fixed', $profitFixed?->value) }}">
                                    <span class="input-group-text">{{ $settings->get('text', collect())->firstWhere('key', 'currency')?->value ?? 'AED' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Numeric Settings (excluding profit settings) -->
            @php
                $numericSettings = $settings->get('number', collect())->filter(function($setting) {
                    return !in_array($setting->key, ['admin_profit_percentage', 'admin_profit_fixed']);
                });
            @endphp

            @if($numericSettings->count() > 0)
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.other_numeric_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($numericSettings as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>
                                <input
                                    type="number"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    step="0.01"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Theme Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.theme_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $primaryColor = $settings->get('color', collect())->firstWhere('key', 'primary_color');
                                $primaryLightColor = $settings->get('color', collect())->firstWhere('key', 'primary_light_color');
                                $themeStyle = $settings->get('select', collect())->firstWhere('key', 'theme_style');
                            @endphp

                            <div class="col-md-6 mb-3">
                                <label for="primary_color" class="form-label">
                                    {{ __('messages.primary_color') }}
                                    @if($primaryColor?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $primaryColor->description }}"></i>
                                    @endif
                                </label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input
                                        type="color"
                                        id="primary_color_picker"
                                        class="form-control form-control-color"
                                        value="{{ old('settings.primary_color', $primaryColor?->value ?? '#666cff') }}"
                                        style="width: 60px; height: 38px;">
                                    <input
                                        type="text"
                                        name="settings[primary_color]"
                                        id="primary_color"
                                        class="form-control"
                                        value="{{ old('settings.primary_color', $primaryColor?->value ?? '#666cff') }}"
                                        placeholder="#666cff"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        maxlength="7">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="reset_primary_color" title="Reset to default">
                                        <i class="ri-restart-line"></i>
                                    </button>
                                </div>
                                <small class="text-muted">{{ __('messages.primary_color_hint') }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="primary_light_color" class="form-label">
                                    {{ __('messages.primary_light_color') }}
                                    @if($primaryLightColor?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $primaryLightColor->description }}"></i>
                                    @endif
                                </label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input
                                        type="color"
                                        id="primary_light_color_picker"
                                        class="form-control form-control-color"
                                        value="{{ old('settings.primary_light_color', $primaryLightColor?->value ?? '#e7e7ff') }}"
                                        style="width: 60px; height: 38px;">
                                    <input
                                        type="text"
                                        name="settings[primary_light_color]"
                                        id="primary_light_color"
                                        class="form-control"
                                        value="{{ old('settings.primary_light_color', $primaryLightColor?->value ?? '#e7e7ff') }}"
                                        placeholder="#e7e7ff"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        maxlength="7">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="auto_generate_light" title="Auto-generate from primary">
                                        <i class="ri-magic-line"></i>
                                    </button>
                                </div>
                                <small class="text-muted">{{ __('messages.primary_light_color_hint') }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="theme_style" class="form-label">
                                    {{ __('messages.theme_style') }}
                                    @if($themeStyle?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $themeStyle->description }}"></i>
                                    @endif
                                </label>
                                <select name="settings[theme_style]" id="theme_style" class="form-control">
                                    <option value="light" {{ old('settings.theme_style', $themeStyle?->value) === 'light' ? 'selected' : '' }}>
                                        {{ __('messages.light') }}
                                    </option>
                                    <option value="dark" {{ old('settings.theme_style', $themeStyle?->value) === 'dark' ? 'selected' : '' }}>
                                        {{ __('messages.dark') }}
                                    </option>
                                </select>
                                <small class="text-muted">{{ __('messages.theme_style_hint') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="ri-save-line me-1"></i> {{ __('messages.save_settings') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle profit type change
    const profitTypeSelect = document.getElementById('admin_profit_type');
    const percentageField = document.getElementById('percentage_field');
    const fixedField = document.getElementById('fixed_field');

    function toggleProfitFields() {
        if (profitTypeSelect.value === 'percentage') {
            percentageField.style.display = 'block';
            fixedField.style.display = 'none';
        } else {
            percentageField.style.display = 'none';
            fixedField.style.display = 'block';
        }
    }

    profitTypeSelect.addEventListener('change', toggleProfitFields);
    toggleProfitFields(); // Initial state

    // Handle color picker and hex input sync
    const colorPicker = document.getElementById('primary_color_picker');
    const colorHex = document.getElementById('primary_color');
    const resetColorBtn = document.getElementById('reset_primary_color');
    const defaultColor = '#666cff';

    const lightColorPicker = document.getElementById('primary_light_color_picker');
    const lightColorHex = document.getElementById('primary_light_color');
    const autoGenerateLightBtn = document.getElementById('auto_generate_light');
    const defaultLightColor = '#e7e7ff';

    // Validate hex color
    function isValidHex(hex) {
        return /^#[0-9A-Fa-f]{6}$/i.test(hex);
    }

    // Convert hex to RGB
    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    // Convert RGB to hex
    function rgbToHex(r, g, b) {
        return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
    }

    // Generate lighter version of a color
    function lightenColor(hex, percent = 60) {
        const rgb = hexToRgb(hex);
        if (!rgb) return hex;

        const r = Math.min(255, Math.round(rgb.r + (255 - rgb.r) * (percent / 100)));
        const g = Math.min(255, Math.round(rgb.g + (255 - rgb.g) * (percent / 100)));
        const b = Math.min(255, Math.round(rgb.b + (255 - rgb.b) * (percent / 100)));

        return rgbToHex(r, g, b);
    }

    // Setup color input sync
    function setupColorSync(picker, hexInput, resetBtn, defaultVal, autoUpdateLight = false) {
        if (!picker || !hexInput) return;

        // Sync picker to hex input
        picker.addEventListener('input', function() {
            hexInput.value = this.value.toUpperCase();
            hexInput.classList.remove('is-invalid');
            hexInput.classList.add('is-valid');

            // Auto-update light color when primary changes
            if (autoUpdateLight && lightColorPicker && lightColorHex) {
                const lightColor = lightenColor(this.value);
                lightColorPicker.value = lightColor;
                lightColorHex.value = lightColor;
                lightColorHex.classList.remove('is-invalid');
                lightColorHex.classList.add('is-valid');
            }
        });

        // Sync hex input to color picker
        hexInput.addEventListener('input', function() {
            let value = this.value.trim();

            // Auto-add # if missing
            if (value && !value.startsWith('#')) {
                value = '#' + value;
                this.value = value;
            }

            // Convert to uppercase
            this.value = value.toUpperCase();

            // Update picker if valid
            if (isValidHex(value)) {
                picker.value = value;
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else if (value) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });

        // Reset to default color
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                picker.value = defaultVal;
                hexInput.value = defaultVal;
                hexInput.classList.remove('is-invalid', 'is-valid');
            });
        }
    }

    // Setup primary color sync (with auto light color update)
    setupColorSync(colorPicker, colorHex, resetColorBtn, defaultColor, true);

    // Setup light color sync
    setupColorSync(lightColorPicker, lightColorHex, null, defaultLightColor, false);

    // Auto-generate light color from primary
    if (autoGenerateLightBtn) {
        autoGenerateLightBtn.addEventListener('click', function() {
            const primaryValue = colorHex.value;
            if (isValidHex(primaryValue)) {
                const lightColor = lightenColor(primaryValue);
                lightColorPicker.value = lightColor;
                lightColorHex.value = lightColor;
                lightColorHex.classList.remove('is-invalid');
                lightColorHex.classList.add('is-valid');
            }
        });
    }

    // Handle image deletion
    document.querySelectorAll('.delete-image').forEach(button => {
        button.addEventListener('click', function() {
            const key = this.getAttribute('data-key');

            if (confirm('{{ __('messages.confirm_delete_image') }}')) {
                fetch('{{ route('admin.settings.delete-image') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ key: key })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __('messages.error_deleting_image') }}');
                });
            }
        });
    });
});
</script>
@endpush
@endsection
