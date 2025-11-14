# Default Arabic RTL Setup

This document explains the configuration for Arabic as the default language with RTL layout.

## Changes Made

### 1. Application Config ([config/app.php](config/app.php))

**Default Locale Changed to Arabic:**
```php
'locale' => env('APP_LOCALE', 'ar'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'ar'),
```

- System now defaults to Arabic (`ar`) instead of English
- Fallback locale also set to Arabic

### 2. Middleware ([SetLocale.php](app/Http/Middleware/SetLocale.php))

**Priority Order:**
```php
$locale = Session::get('locale', setting('site_language', 'ar'));
```

1. Session locale (user preference)
2. Site language setting (from database)
3. Default to Arabic (`ar`)

### 3. Database Migration

**Default Setting:**
```php
[
    'key' => 'site_language',
    'value' => 'ar',  // Arabic by default
    'type' => 'select',
    'description' => 'Default language for the website (ar = Arabic, en = English)',
]
```

### 4. Enhanced CSS for Sidebar Positioning ([partials/head.blade.php](resources/views/partials/head.blade.php))

**RTL (Arabic) - Default:**
```css
/* Sidebar on the RIGHT */
html[dir="rtl"] .layout-menu {
    right: 0 !important;
    left: auto !important;
}

html[dir="rtl"] .layout-page {
    margin-right: 260px !important;
    margin-left: 0 !important;
}
```

**LTR (English) - When Selected:**
```css
/* Sidebar on the LEFT */
html[dir="ltr"] .layout-menu {
    left: 0 !important;
    right: auto !important;
}

html[dir="ltr"] .layout-page {
    margin-left: 260px !important;
    margin-right: 0 !important;
}
```

## How It Works

### Default Behavior (Arabic):
```
System Starts → No Session → Check Site Setting → Default to 'ar' → RTL Layout → Sidebar RIGHT
```

### When User Switches to English:
```
Click English → Update Session → Locale = 'en' → Page Reload → LTR Layout → Sidebar LEFT
```

### When User Switches Back to Arabic:
```
Click Arabic → Update Session → Locale = 'ar' → Page Reload → RTL Layout → Sidebar RIGHT
```

## Visual Layout

### Arabic (Default - RTL):
```
┌──────────────────────────────────────────────┐
│  Content Area            │   Sidebar (Menu)  │
│                          │                    │
│  ← Text flows this way   │   🔲 Dashboard     │
│                          │   🔲 Products      │
│                          │   🔲 Orders        │
└──────────────────────────────────────────────┘
                RIGHT SIDE ↑
```

### English (LTR):
```
┌──────────────────────────────────────────────┐
│   Sidebar (Menu)  │         Content Area     │
│                   │                           │
│   🔲 Dashboard    │   Text flows this way →  │
│   🔲 Products     │                           │
│   🔲 Orders       │                           │
└──────────────────────────────────────────────┘
    ↑ LEFT SIDE
```

## Testing Checklist

### ✅ Test Default Arabic (RTL):
1. Clear browser cache and cookies
2. Visit the site (no login needed)
3. **Expected:** Arabic interface, sidebar on RIGHT
4. Login to dashboard
5. **Expected:** All text RTL, sidebar on RIGHT
6. Menu items should be right-aligned

### ✅ Test English Switch (LTR):
1. From Arabic view, click language switcher
2. Select "🇬🇧 English"
3. Page reloads
4. **Expected:** English interface, sidebar on LEFT
5. All text should be LTR
6. Menu items should be left-aligned

### ✅ Test Persistence:
1. Switch to English
2. Navigate to different pages
3. **Expected:** Language stays English, sidebar stays LEFT
4. Logout and login again
5. **Expected:** Language remembered in session

### ✅ Test Responsive:
1. Resize browser to mobile width (< 1200px)
2. **Expected:** Sidebar becomes drawer/overlay
3. Works for both Arabic and English

## Configuration Priority

The system uses this priority order for locale:

```
1. User Session (highest priority)
   ↓
2. Site Language Setting (from admin settings)
   ↓
3. Config File (config/app.php)
   ↓
4. Environment Variable (.env APP_LOCALE)
   ↓
5. Hard-coded Default ('ar')
```

## Optional: Set via .env

To change system-wide default, add to `.env`:

```env
APP_LOCALE=ar
APP_FALLBACK_LOCALE=ar
```

## Admin Settings

Admins can change the site-wide default language:
1. Navigate to **Settings → Localization & Currency Settings**
2. Select **Site Language**
3. Choose Arabic or English
4. Save Settings

When admin changes language via navbar, it automatically updates the site-wide setting.

## Important Notes

- ✅ **Sidebar positioning** uses `!important` to ensure proper placement
- ✅ **Smooth transitions** (0.3s ease) when switching languages
- ✅ **Responsive design** maintained for mobile devices
- ✅ **Template customizer** completely hidden
- ✅ **All child views** inherit the layout automatically

## Files Modified

1. **config/app.php** - Changed default locale to 'ar'
2. **app/Http/Middleware/SetLocale.php** - Updated fallback logic to Arabic
3. **resources/views/partials/head.blade.php** - Enhanced CSS for sidebar positioning
4. **database/migrations/2025_11_12_215456_add_language_currency_banner_settings.php** - Already defaulted to 'ar'

## No Additional Changes Needed

- ✅ Dashboard layout already dynamic
- ✅ Language controller already working
- ✅ Scripts already handling language switch
- ✅ Translations already available in both languages

## Summary

🎯 **System now defaults to Arabic (ar) with RTL layout**
🎯 **Sidebar on RIGHT for Arabic, LEFT for English**
🎯 **Automatic detection and switching**
🎯 **Persistent across sessions**
🎯 **Smooth transitions**
🎯 **Fully responsive**
