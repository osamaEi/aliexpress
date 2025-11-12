# Settings Usage Guide

This document explains how to use the new settings added to the system: Language, Currency, and Banner.

## Settings Added

1. **Site Language** (`site_language`)
   - Type: Select (dropdown)
   - Options: Arabic (`ar`), English (`en`)
   - Default: `ar`
   - Description: Default language for the website interface

2. **Site Currency** (`site_currency`)
   - Type: Select (dropdown)
   - Options: AED, SAR, USD, EUR, EGP, KWD, QAR, OMR, BHD
   - Default: `AED`
   - Description: Currency used for all transactions

3. **Site Banner** (`site_banner`)
   - Type: Image upload
   - Default: `null`
   - Description: Main banner image for the home page

## How to Access Settings

### In Controllers/Backend PHP Code

```php
// Get site language
$language = setting('site_language', 'ar');

// Get site currency
$currency = setting('site_currency', 'AED');

// Get banner image URL
$bannerUrl = setting_image('site_banner');
// Or with a default fallback
$bannerUrl = setting_image('site_banner', asset('images/default-banner.jpg'));
```

### In Blade Views

```blade
{{-- Get language --}}
{{ setting('site_language') }}

{{-- Get currency --}}
{{ setting('site_currency') }}

{{-- Display banner image --}}
@if(setting('site_banner'))
    <img src="{{ setting_image('site_banner') }}" alt="Site Banner" class="banner-image">
@endif

{{-- Or with a default --}}
<img src="{{ setting_image('site_banner', asset('images/default-banner.jpg')) }}" alt="Site Banner">
```

### Example: Using Currency in Product Display

```blade
<div class="product-price">
    <span class="currency">{{ setting('site_currency', 'AED') }}</span>
    <span class="amount">{{ number_format($product->price, 2) }}</span>
</div>
```

### Example: Displaying Banner on Home Page

```blade
@extends('layouts.app')

@section('content')
    {{-- Hero Banner Section --}}
    @if(setting('site_banner'))
        <section class="hero-banner">
            <img src="{{ setting_image('site_banner') }}"
                 alt="Welcome Banner"
                 class="img-fluid w-100">
        </section>
    @endif

    {{-- Rest of the content --}}
@endsection
```

### Example: Setting Locale Based on Site Language

In your `AppServiceProvider.php` or middleware:

```php
public function boot()
{
    // Set application locale based on setting
    $locale = setting('site_language', 'ar');
    app()->setLocale($locale);
}
```

## Managing Settings in Admin Panel

1. Navigate to: **Admin Panel → Settings**
2. Find **Localization & Currency Settings** section
3. Select desired language and currency from dropdowns
4. Upload banner image in the **Image Settings** section
5. Click **Save Settings**

## Database Structure

Settings are stored in the `settings` table with the following structure:

| Column | Type | Description |
|--------|------|-------------|
| key | string | Unique identifier (e.g., 'site_language') |
| value | text | Setting value |
| type | string | Setting type ('select', 'image', etc.) |
| description | text | Human-readable description |

## Available Helper Functions

1. `setting($key, $default = null)` - Get any setting value
2. `setting_image($key, $default = null)` - Get image setting URL
3. `admin_profit($amount)` - Calculate admin profit
4. `calculate_price_with_profit($basePrice)` - Calculate price with profit

## Notes

- Settings are cached for 1 hour for performance
- Cache is automatically cleared when settings are updated
- Image settings store the file path, use `setting_image()` to get full URL
- Banner images are stored in `storage/app/public/settings/`
