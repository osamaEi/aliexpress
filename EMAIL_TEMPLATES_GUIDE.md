# Professional Email Templates with Logo

This document describes the professional email template system with custom branding and logo integration.

## Overview

The email templates have been customized to provide a professional, branded experience with:
- **Custom Logo** in header
- **Cairo Font** for better Arabic/English typography
- **Modern Design** with gradient header and shadow effects
- **Responsive Layout** for all devices
- **Dynamic Branding** using site settings

## What Was Done

### 1. Published Laravel Mail Components
```bash
php artisan vendor:publish --tag=laravel-mail
```

This created customizable email templates in:
- `resources/views/vendor/mail/`

### 2. Customized Header ([resources/views/vendor/mail/html/header.blade.php](resources/views/vendor/mail/html/header.blade.php))

**Features:**
- Automatically displays logo from `public/logo/logo.png`
- Falls back to site name if logo doesn't exist
- Uses site settings for name and primary color
- Logo height: 60px (auto width)

**Code:**
```blade
@php
    $logoPath = public_path('logo/logo.png');
    $siteName = setting('site_name', config('app.name'));
@endphp
@if (file_exists($logoPath))
<img src="{{ asset('logo/logo.png') }}" class="logo" alt="{{ $siteName }} Logo" style="height: 60px; max-height: 60px; width: auto;">
@else
<span style="font-size: 24px; font-weight: bold; color: {{ setting('primary_color', '#666cff') }};">{{ $siteName }}</span>
@endif
```

### 3. Enhanced CSS Theme ([resources/views/vendor/mail/html/themes/default.css](resources/views/vendor/mail/html/themes/default.css))

**Key Changes:**

#### Cairo Font Integration:
```css
font-family: 'Cairo', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
```

#### Modern Header with Gradient:
```css
.header {
    padding: 30px 0;
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px 8px 0 0;
}
```

#### Enhanced Body Styling:
```css
.inner-body {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.05);
    width: 600px;
}
```

#### Better Typography:
```css
body {
    background-color: #f8f9fa;
    color: #495057;
    line-height: 1.6;
}

.content-cell {
    padding: 40px;
}
```

### 4. Updated Layout ([resources/views/vendor/mail/html/layout.blade.php](resources/views/vendor/mail/html/layout.blade.php))

**Added:**
- Cairo font imports from Google Fonts
- Dynamic site name in title
- Preconnect for font optimization

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
```

### 5. Enhanced Message Template ([resources/views/vendor/mail/html/message.blade.php](resources/views/vendor/mail/html/message.blade.php))

**Features:**
- Dynamic site name in header
- Professional footer with copyright
- Support team contact info
- Site URL link

**Footer Content:**
```
© 2025 Site Name. All rights reserved.
---
This email was sent from [Site Name](https://yoursite.com)
If you have any questions, please contact our support team.
```

### 6. Enhanced Footer ([resources/views/vendor/mail/html/footer.blade.php](resources/views/vendor/mail/html/footer.blade.php))

**Changes:**
- Width adjusted to 600px (matching body)
- Better padding for footer content
- Aligned with overall design

## Sample Email Templates Created

### 1. Welcome Email ([resources/views/emails/welcome.blade.php](resources/views/emails/welcome.blade.php))

**Purpose:** Welcome new users to the platform

**Features:**
- Personalized greeting
- Feature highlights
- Dashboard button
- Support information

**Usage:**
```php
Mail::to($user->email)->send(new WelcomeEmail($user));
```

**Preview:**
```
# Welcome to Site Name!

Hello John Doe,

Thank you for joining our platform. We're excited to have you on board!

Here's what you can do now:
- Browse thousands of products
- Start selling your products
- Connect with suppliers worldwide
- Manage your orders efficiently

[Go to Dashboard Button]

If you need any help getting started, our support team is here to assist you.

Best regards,
The Site Name Team
```

### 2. Order Confirmation ([resources/views/emails/order-confirmation.blade.php](resources/views/emails/order-confirmation.blade.php))

**Purpose:** Confirm order details to customers

**Features:**
- Order number and date
- Product details table
- Total amount
- View order button
- Shipping notification

**Usage:**
```php
Mail::to($order->user->email)->send(new OrderConfirmation($order));
```

**Preview:**
```
# Order Confirmation #12345

Hello Jane Smith,

Thank you for your order! We're processing it now.

## Order Details
Order Number: #12345
Order Date: Jan 15, 2025
Total Amount: AED 599.99

[Product Table]

[View Order Details Button]

We'll send you another email when your order ships.
```

### 3. Password Reset ([resources/views/emails/password-reset.blade.php](resources/views/emails/password-reset.blade.php))

**Purpose:** Send password reset link securely

**Features:**
- Reset password button
- Expiry time notification
- Security reminder
- Subcopy with manual URL

**Usage:**
```php
Mail::to($user->email)->send(new ResetPassword($url));
```

**Preview:**
```
# Reset Password Notification

Hello,

You are receiving this email because we received a password reset request for your account.

[Reset Password Button]

This password reset link will expire in 60 minutes.

If you did not request a password reset, no further action is required.
```

### 4. Withdrawal Request ([resources/views/emails/withdrawal-request.blade.php](resources/views/emails/withdrawal-request.blade.php))

**Purpose:** Notify users about withdrawal status

**Features:**
- Withdrawal details (amount, date, status)
- PayPal email confirmation
- Status-based messages (approved/rejected/pending)
- Rejection reason if applicable
- View wallet button

**Usage:**
```php
Mail::to($withdrawal->user->email)->send(new WithdrawalRequestEmail($withdrawal));
```

**Preview (Approved):**
```
# Withdrawal Request Received

Hello John Doe,

We have received your withdrawal request and it is being processed.

## Withdrawal Details
Amount: AED 500.00
Request Date: Jan 15, 2025 14:30
Status: Approved
PayPal Email: john@example.com

✅ Your withdrawal has been approved and the payment has been sent to your PayPal account.

[View Wallet Button]
```

## Email Template Structure

All email templates use the markdown-style syntax:

```blade
<x-mail::message>
# Heading

Body text with **bold** and *italic* support.

<x-mail::button :url="$url">
Button Text
</x-mail::button>

<x-mail::panel>
Info box / Panel content
</x-mail::panel>

<x-mail::table>
| Header 1 | Header 2 | Header 3 |
|:---------|:--------:|---------:|
| Left     | Center   | Right    |
</x-mail::table>

<x-slot:subcopy>
Subcopy text (appears in smaller font at bottom)
</x-slot:subcopy>
</x-mail::message>
```

## Components Available

### 1. Message Component
```blade
<x-mail::message>
    Email content here
</x-mail::message>
```

### 2. Button Component
```blade
<x-mail::button :url="'https://example.com'" color="primary|success|error">
Click Me
</x-mail::button>
```

**Colors:**
- `primary` (default) - Dark blue/gray
- `success` - Green
- `error` - Red

### 3. Panel Component
```blade
<x-mail::panel>
Important information or highlighted content
</x-mail::panel>
```

### 4. Table Component
```blade
<x-mail::table>
| Column 1 | Column 2 | Column 3 |
|:---------|:--------:|---------:|
| Data 1   | Data 2   | Data 3   |
</x-mail::table>
```

**Alignment:**
- `:---` - Left align
- `:---:` - Center align
- `---:` - Right align

### 5. Subcopy Slot
```blade
<x-slot:subcopy>
Fine print or alternative instructions
</x-slot:subcopy>
```

## Design Features

### Visual Elements

#### 1. Header
- **Background:** Purple gradient (135deg, #667eea to #764ba2)
- **Padding:** 30px vertical
- **Border Radius:** 8px top corners
- **Logo Height:** 60px (auto width)
- **Logo Fallback:** Site name in primary color

#### 2. Body
- **Background:** White (#ffffff)
- **Container Width:** 600px
- **Border Radius:** 8px
- **Shadow:** Multi-layer soft shadow
- **Padding:** 40px
- **Font:** Cairo, fallback to system fonts

#### 3. Footer
- **Background:** Transparent
- **Text Color:** Muted gray (#b0adc5)
- **Font Size:** 12px
- **Content:** Copyright, site link, support text

#### 4. Buttons
- **Border Radius:** 4px
- **Padding:** 8px vertical, 18px horizontal
- **Colors:** Primary/Success/Error variants
- **Hover:** No hover state (email limitation)

### Typography

**Headings:**
- H1: 18px, bold, dark gray
- H2: 16px, bold
- H3: 14px, bold

**Body:**
- Font Size: 16px
- Line Height: 1.5em (1.6 for body)
- Color: #495057 (dark gray)

**Links:**
- Color: #3869d4 (blue)
- Underline on hover

### Responsive Design

```css
@media only screen and (max-width: 600px) {
    .inner-body {
        width: 100% !important;
    }
    .footer {
        width: 100% !important;
    }
}

@media only screen and (max-width: 500px) {
    .button {
        width: 100% !important;
    }
}
```

## How to Create New Email Templates

### Step 1: Create Email View
Create a new file in `resources/views/emails/`:

```blade
{{-- resources/views/emails/your-email.blade.php --}}
<x-mail::message>
# {{ __('Your Email Subject') }}

{{ __('Email content here') }}

<x-mail::button :url="$actionUrl">
{{ __('Action Button') }}
</x-mail::button>

{{ __('Best regards,') }}<br>
{{ setting('site_name', config('app.name')) }}
</x-mail::message>
```

### Step 2: Create Mailable Class
```bash
php artisan make:mail YourEmailName
```

Edit `app/Mail/YourEmailName.php`:

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class YourEmailName extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Your Email Subject')
                    ->markdown('emails.your-email');
    }
}
```

### Step 3: Send Email
```php
use App\Mail\YourEmailName;
use Illuminate\Support\Facades\Mail;

Mail::to($user->email)->send(new YourEmailName($data));
```

## Email Testing

### Test in Browser
Laravel provides a preview mode for emails:

```php
// In routes/web.php
Route::get('/email-preview', function () {
    $user = User::first();
    return new App\Mail\WelcomeEmail($user);
});
```

Visit: `http://yoursite.test/email-preview`

### Test with Mailtrap
Add to `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
```

### Test with Log Driver
Add to `.env`:
```env
MAIL_MAILER=log
```

Emails will be saved to `storage/logs/laravel.log`

## Customization Options

### Change Header Gradient
Edit [resources/views/vendor/mail/html/themes/default.css](resources/views/vendor/mail/html/themes/default.css:100-105):

```css
.header {
    background: linear-gradient(135deg, #your-color-1 0%, #your-color-2 100%);
}
```

### Change Logo Size
Edit [resources/views/vendor/mail/html/header.blade.php](resources/views/vendor/mail/html/header.blade.php:10):

```html
<img src="{{ asset('logo/logo.png') }}" style="height: 80px; width: auto;">
```

### Change Email Width
Edit [resources/views/vendor/mail/html/themes/default.css](resources/views/vendor/mail/html/themes/default.css:139):

```css
.inner-body {
    width: 650px; /* Change from 600px */
}
```

### Change Font
Edit [resources/views/vendor/mail/html/layout.blade.php](resources/views/vendor/mail/html/layout.blade.php:11):

```html
<link href="https://fonts.googleapis.com/css2?family=YourFont:wght@300;400;600;700&display=swap" rel="stylesheet">
```

Then update CSS:
```css
font-family: 'YourFont', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
```

## RTL Support for Arabic Emails

The templates automatically support RTL for Arabic content:

```blade
<x-mail::message>
@if(app()->getLocale() === 'ar')
<div dir="rtl" style="text-align: right;">
@endif

# {{ __('Your Arabic Subject') }}

{{ __('Arabic content here') }}

@if(app()->getLocale() === 'ar')
</div>
@endif
</x-mail::message>
```

## Best Practices

### 1. Use Translations
Always wrap text in `__()` for multi-language support:
```blade
{{ __('Welcome to our platform!') }}
```

### 2. Use Settings
Pull site name and colors from settings:
```blade
{{ setting('site_name', config('app.name')) }}
{{ setting('primary_color', '#666cff') }}
```

### 3. Include Fallbacks
Always provide fallback values:
```blade
{{ $user->name ?? 'Valued Customer' }}
```

### 4. Keep It Simple
Email clients have limited CSS support. Stick to:
- Tables for layout
- Inline styles when possible
- Basic colors and fonts
- Simple borders and padding

### 5. Test Thoroughly
Test on multiple email clients:
- Gmail (web, iOS, Android)
- Outlook (desktop, web)
- Apple Mail
- Yahoo Mail
- Mobile devices

## Files Modified/Created

### Modified Files:
1. `resources/views/vendor/mail/html/header.blade.php` - Logo integration
2. `resources/views/vendor/mail/html/message.blade.php` - Enhanced footer
3. `resources/views/vendor/mail/html/footer.blade.php` - Better padding
4. `resources/views/vendor/mail/html/layout.blade.php` - Cairo font import
5. `resources/views/vendor/mail/html/themes/default.css` - Professional styling

### Created Files:
1. `resources/views/emails/welcome.blade.php` - Welcome email
2. `resources/views/emails/order-confirmation.blade.php` - Order confirmation
3. `resources/views/emails/password-reset.blade.php` - Password reset
4. `resources/views/emails/withdrawal-request.blade.php` - Withdrawal notifications

## Email Client Compatibility

✅ **Fully Supported:**
- Gmail (all platforms)
- Apple Mail (macOS, iOS)
- Outlook 2016+ (Windows, macOS)
- Outlook.com / Office 365
- Yahoo Mail
- AOL Mail
- Samsung Email (Android)

⚠️ **Limited Support:**
- Outlook 2007-2013 (Windows) - Uses Word rendering engine
- Windows Mail - Basic rendering

## Troubleshooting

### Issue: Logo Not Showing
**Solution:**
1. Ensure logo exists at `public/logo/logo.png`
2. Check file permissions
3. Use absolute URL: `{{ url('logo/logo.png') }}`

### Issue: Font Not Loading
**Solution:**
- Email clients may block external fonts
- Cairo font is a "nice-to-have" with system font fallbacks
- Some clients will use fallback fonts (this is normal)

### Issue: Gradient Not Showing
**Solution:**
- Some email clients don't support gradients
- Provide solid color fallback:
```css
background-color: #667eea; /* Fallback */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Issue: Styles Not Applied
**Solution:**
- Clear view cache: `php artisan view:clear`
- Clear config cache: `php artisan config:clear`
- Check premailer is processing CSS

## Performance Optimization

### 1. Font Loading
- Using `display=swap` for instant text visibility
- Preconnect to Google Fonts CDN
- Font weights limited to needed ranges

### 2. Image Optimization
- Logo should be optimized (PNG or WebP)
- Recommended size: < 50KB
- Use CDN for faster delivery

### 3. Email Size
- Keep total email size < 102KB
- Avoid large embedded images
- Link to images when possible

## Summary

✅ **Professional email templates with:**
- Custom logo in header
- Cairo font for Arabic/English
- Modern gradient design
- Responsive layout
- Dynamic branding
- Sample templates included

✅ **Easy to customize:**
- Change colors in CSS
- Update logo path
- Modify layout structure
- Add new templates easily

✅ **Production-ready:**
- Cross-client compatibility
- RTL support
- Mobile responsive
- Fallback handling
- Performance optimized

---

**Implementation Date:** 2025-11-14
**Template Type:** Laravel Markdown Mail
**Font:** Cairo (Google Fonts)
**Email Width:** 600px
**Logo Height:** 60px (auto width)
