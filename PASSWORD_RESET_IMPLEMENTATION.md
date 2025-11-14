# Password Reset Implementation - Professional Views

Complete password reset flow with professional, branded views matching the login design.

## What Was Done

### 1. Forgot Password View ([resources/views/auth/forgot-password.blade.php](resources/views/auth/forgot-password.blade.php))

**Features:**
- ✅ **Two-column layout** matching login page design
- ✅ **Logo sidebar** with gradient background
- ✅ **Cairo font** for Arabic typography
- ✅ **Info box** explaining the process (60-minute expiry)
- ✅ **Session status alerts** for success messages
- ✅ **Error handling** with validation messages
- ✅ **Back to login link** for easy navigation
- ✅ **Responsive design** for mobile devices

**Key Elements:**
```html
<!-- Info Box -->
<div class="info-box">
    <i class="ri-information-line"></i>
    <p>سنرسل لك رسالة على بريدك الإلكتروني تحتوي على رابط آمن لإعادة تعيين كلمة المرور. الرابط صالح لمدة 60 دقيقة فقط.</p>
</div>

<!-- Email Input -->
<input type="email" name="email" placeholder="example@email.com" required>

<!-- Submit Button -->
<button type="submit">إرسال رابط إعادة التعيين</button>
```

**Route:** `GET /forgot-password` → `password.request`

**Action:** `POST /forgot-password` → `password.email`

---

### 2. Reset Password View ([resources/views/auth/reset-password.blade.php](resources/views/auth/reset-password.blade.php))

**Features:**
- ✅ **Matching design** with login and forgot password
- ✅ **Logo sidebar** with branded gradient
- ✅ **Three form fields**: Email, Password, Confirm Password
- ✅ **Password requirements box** showing security guidelines
- ✅ **Hidden token field** for security
- ✅ **Validation messages** for all fields
- ✅ **Professional styling** with Cairo font

**Password Requirements:**
```
✓ على الأقل 8 أحرف
✓ تحتوي على حروف كبيرة وصغيرة
✓ تحتوي على أرقام
```

**Form Structure:**
```html
<form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <!-- Email -->
    <input type="email" name="email" value="{{ $request->email }}" required>

    <!-- New Password -->
    <input type="password" name="password" required>

    <!-- Confirm Password -->
    <input type="password" name="password_confirmation" required>

    <button type="submit">إعادة تعيين كلمة المرور</button>
</form>
```

**Route:** `GET /reset-password/{token}` → `password.reset`

**Action:** `POST /reset-password` → `password.store`

---

## Complete Password Reset Flow

### Step 1: User Requests Password Reset
1. User clicks "نسيت كلمة المرور؟" on login page
2. Redirected to `/forgot-password`
3. Beautiful forgot password page displays with:
   - Logo sidebar
   - Email input field
   - Info box with instructions
   - Submit button

### Step 2: Email Sent
1. User enters email address
2. System validates email exists in database
3. Password reset link generated with secure token
4. Email sent using **professional email template** with logo
5. Success message displayed:
   ```
   تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني
   ```

### Step 3: User Clicks Email Link
1. User receives email with professional design:
   - **Logo in header**
   - **Cairo font** for better readability
   - **Reset Password button**
   - **Expiry warning** (60 minutes)
   - **Support information** in footer

2. Email template used: [resources/views/emails/password-reset.blade.php](resources/views/emails/password-reset.blade.php)

### Step 4: Reset Password Page
1. User clicks link in email
2. Redirected to `/reset-password/{token}`
3. Professional reset password page displays:
   - Logo sidebar
   - Email field (pre-filled)
   - New password field
   - Confirm password field
   - Password requirements box
   - Submit button

### Step 5: Password Updated
1. User enters new password and confirmation
2. System validates:
   - Email matches token
   - Password meets requirements (min 8 chars)
   - Passwords match
   - Token not expired (60 minutes)

3. Password updated successfully
4. User redirected to login page with success message
5. User can now login with new password

---

## Design Features

### Consistent Branding
All password reset pages share the same design language:
- **Primary Color:** `#561C04` (dark brown)
- **Secondary Color:** `#7a2805` (lighter brown)
- **Gradient Background:** `linear-gradient(135deg, #561C04 0%, #7a2805 100%)`
- **Font:** Cairo (Arabic optimized)
- **Border Radius:** 20px (main container), 10px (inputs/buttons)

### Layout Structure
```
┌─────────────────────────────────────────────┐
│  [SIDEBAR]        │    [CONTENT AREA]       │
│                   │                          │
│  Logo (white)     │    Heading              │
│  Title            │    Description          │
│  Description      │    Form Fields          │
│                   │    Submit Button        │
│  (Gradient BG)    │    (White BG)           │
└─────────────────────────────────────────────┘
     400px              Remaining width
```

### Sidebar Features
- **Gradient Background:** Purple-to-blue or brown-to-lighter-brown
- **Decorative Circles:** Subtle white circles for depth
- **Logo:** White-filtered, 180px max width
- **Text:** White, centered, with opacity
- **Responsive:** Stacks on mobile (< 992px)

### Form Elements
- **Input Fields:**
  - Padding: 13px 18px
  - Border: 2px solid #e5e7eb
  - Focus: Primary color border + shadow
  - Invalid: Red border (#ef4444)

- **Buttons:**
  - Full width
  - Gradient background
  - Hover: Lift effect (-2px translateY)
  - Icon + Text alignment

- **Alerts:**
  - Success: Green background (#d1fae5)
  - Error: Red background (#fee2e2)
  - Icon + Message layout

---

## Email Template Integration

The password reset email uses the professional template system:

### Template: [resources/views/emails/password-reset.blade.php](resources/views/emails/password-reset.blade.php)

**Features:**
```blade
<x-mail::message>
# Reset Password Notification

You are receiving this email because we received a password reset request for your account.

<x-mail::button :url="$url">
Reset Password
</x-mail::button>

This password reset link will expire in 60 minutes.

If you did not request a password reset, no further action is required.
</x-mail::message>
```

**Email Design:**
- ✅ Logo in header (from `public/logo/logo.png`)
- ✅ Gradient header background
- ✅ Cairo font
- ✅ Professional button styling
- ✅ Footer with copyright and support info
- ✅ Responsive for all email clients

---

## Routes & Controllers

### Routes (Defined in `routes/auth.php`):

```php
// Show forgot password form
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

// Send password reset email
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

// Show reset password form
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

// Update password
Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');
```

### Controllers Used:

1. **PasswordResetLinkController** (`app/Http/Controllers/Auth/PasswordResetLinkController.php`)
   - `create()` - Shows forgot password form
   - `store()` - Sends password reset email

2. **NewPasswordController** (`app/Http/Controllers/Auth/NewPasswordController.php`)
   - `create()` - Shows reset password form
   - `store()` - Updates the password

---

## Configuration

### Password Reset Settings (`.env`):

```env
# Default is 60 minutes
# Configured in config/auth.php
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yoursite.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Token Expiry:

**File:** `config/auth.php`

```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60, // 60 minutes
        'throttle' => 60, // 1 minute between requests
    ],
],
```

---

## Security Features

### 1. Token-Based Reset
- Secure random token generated for each request
- Token stored in `password_reset_tokens` table
- Token hashed in database
- Expires after 60 minutes

### 2. Rate Limiting
- Maximum 1 request per minute per email
- Prevents brute force attacks
- Throttle message shown if exceeded

### 3. Email Validation
- Email must exist in users table
- Token must match email
- Token must not be expired

### 4. Password Requirements
- Minimum 8 characters
- Must be confirmed (match)
- Hashed using bcrypt before storage

---

## Testing the Flow

### Test Forgot Password:

1. **Access Page:**
   ```
   http://yoursite.test/forgot-password
   ```

2. **Enter Email:**
   - Use existing user email
   - Click "إرسال رابط إعادة التعيين"

3. **Expected Result:**
   - Success message appears
   - Email sent with reset link
   - Check inbox or `storage/logs/laravel.log` if using log driver

### Test Reset Password:

1. **Click Email Link:**
   - Link format: `http://yoursite.test/reset-password/{token}?email=user@example.com`

2. **Fill Form:**
   - Email (pre-filled)
   - New password (min 8 chars)
   - Confirm password (must match)

3. **Expected Result:**
   - Password updated successfully
   - Redirected to login page
   - Can login with new password

### Test Email Template:

**Preview in Browser:**

Create route in `routes/web.php`:
```php
Route::get('/preview-password-reset', function () {
    return new \Illuminate\Auth\Notifications\ResetPassword('test-token');
});
```

Visit: `http://yoursite.test/preview-password-reset`

---

## Error Handling

### Common Errors & Solutions:

**Error:** "We can't find a user with that email address."
- **Cause:** Email doesn't exist in database
- **Solution:** Use registered email or create account

**Error:** "This password reset token is invalid."
- **Cause:** Token expired or already used
- **Solution:** Request new password reset

**Error:** "The password field confirmation does not match."
- **Cause:** Password and confirmation don't match
- **Solution:** Ensure both fields are identical

**Error:** "Too many password reset attempts."
- **Cause:** Rate limit exceeded (1 per minute)
- **Solution:** Wait 60 seconds before retrying

**Error:** "The password must be at least 8 characters."
- **Cause:** Password too short
- **Solution:** Use minimum 8 characters

---

## Mobile Responsiveness

### Breakpoint: 992px

**Desktop (> 992px):**
```
┌──────────────────────────────┐
│  Sidebar  │   Content Area   │
│  (400px)  │   (Remaining)    │
└──────────────────────────────┘
```

**Mobile (< 992px):**
```
┌──────────────────────────────┐
│        Sidebar               │
│      (Full Width)            │
├──────────────────────────────┤
│      Content Area            │
│      (Full Width)            │
└──────────────────────────────┘
```

**CSS:**
```css
@media (max-width: 992px) {
    .main-container {
        grid-template-columns: 1fr;
    }
    .sidebar {
        padding: 30px 25px;
    }
    .content-area {
        padding: 30px 25px;
    }
}
```

---

## Customization Options

### Change Colors:

Edit CSS variables in each view:
```css
:root {
    --primary-color: #561C04;      /* Your primary color */
    --secondary-color: #7a2805;    /* Your secondary color */
    --success-color: #10b981;      /* Success green */
}
```

### Change Logo:

Replace file at:
```
public/logo/logo.png
```

Or update path in views:
```html
<img src="{{ asset('path/to/your/logo.png') }}" alt="Logo">
```

### Change Expiry Time:

Edit `config/auth.php`:
```php
'expire' => 120, // 2 hours instead of 60 minutes
```

### Change Rate Limit:

Edit `config/auth.php`:
```php
'throttle' => 120, // 2 minutes between requests
```

---

## Files Modified/Created

### Created Files:
1. ✅ `resources/views/auth/forgot-password.blade.php` - Forgot password form
2. ✅ `resources/views/auth/reset-password.blade.php` - Reset password form
3. ✅ `resources/views/emails/password-reset.blade.php` - Password reset email

### Existing Files (Using Default Laravel):
- `app/Http/Controllers/Auth/PasswordResetLinkController.php`
- `app/Http/Controllers/Auth/NewPasswordController.php`
- `routes/auth.php`

### Email Template System:
- `resources/views/vendor/mail/html/header.blade.php` - With logo
- `resources/views/vendor/mail/html/message.blade.php` - Professional footer
- `resources/views/vendor/mail/html/themes/default.css` - Modern styling

---

## Summary

✅ **Forgot Password View** - Professional design matching login page
✅ **Reset Password View** - Complete form with validation
✅ **Email Template** - Branded email with logo and professional styling
✅ **Complete Flow** - Working end-to-end password reset
✅ **Security** - Token-based, rate-limited, validated
✅ **Responsive** - Mobile-friendly layouts
✅ **Cairo Font** - Arabic typography support
✅ **Error Handling** - User-friendly validation messages

---

**Implementation Date:** 2025-11-14
**Design Pattern:** Two-column layout with sidebar
**Font:** Cairo (Google Fonts)
**Token Expiry:** 60 minutes
**Rate Limit:** 1 request per minute
