# Seller Trial Period System

## Overview
This document explains how the seller trial period and access control system works.

## Trial Period Duration
- **24 hours** from account creation (`users.created_at`)
- Sellers can access the full system during the trial period
- After trial expires, access is restricted unless they have an active subscription

## Seller Registration Flow

### Step 1: Profile Settings
- New seller completes profile information
- When saved, `setup_completed_at` is set to current timestamp
- Seller is redirected to profit settings page

### Step 2: Profit Settings
- Seller configures profit margins for their assigned subcategories
- When saved, `profit_settings_completed` is set to `true`
- Seller can now access the full system

## Access Control Logic

### During Trial Period (First 24 hours)
✅ **Full System Access** if setup is completed
- Dashboard
- Products search and assignment
- Orders
- Shipping tracking
- Wallet
- All other features

### After Trial Period Expires (Without Subscription)
❌ **Restricted Access** - Only these pages are accessible:
1. **Subscriptions** (`/subscriptions/*`)
   - View subscription plans
   - Subscribe to a plan
   - View subscription history

2. **Settings/Profile** (`/profile/edit`)
   - Edit profile information
   - Update profit settings
   - Change password

3. **Support** (`/seller/tickets/*`)
   - Create support tickets
   - View and reply to tickets

### With Active Subscription
✅ **Full System Access** regardless of trial period

## Technical Implementation

### Database Fields
```sql
-- Added to users table
setup_completed_at TIMESTAMP NULL
profit_settings_completed BOOLEAN DEFAULT FALSE
```

### Key Methods in User Model

```php
// Check if seller is in trial period
public function isInTrialPeriod(): bool
{
    return $this->created_at->addHours(24)->isFuture();
}

// Check if trial has expired without subscription
public function hasTrialExpired(): bool
{
    return !$this->isInTrialPeriod() && !$this->hasActiveSubscription();
}

// Check if initial setup is completed
public function hasCompletedSetup(): bool
{
    return $this->setup_completed_at !== null && $this->profit_settings_completed;
}
```

### Middleware: SellerAccessControl
- Applied globally to all web routes
- Only affects users with `user_type = 'seller'`
- Checks trial period and subscription status
- Redirects to appropriate page based on setup completion and trial status

**Location:** `app/Http/Middleware/SellerAccessControl.php`

**Registered in:** `bootstrap/app.php` (line 17)

## Always Accessible Routes (Even After Trial)

The following routes are always accessible for sellers:

### Subscriptions
- `subscriptions.index` - View plans
- `subscriptions.subscribe` - Subscribe
- `subscriptions.history` - Subscription history

### Profile/Settings
- `profile.edit` - Edit profile
- `profile.update` - Update profile
- `seller.profit-settings.*` - Manage profit settings

### Support
- `seller.tickets.*` - All ticket routes

### Payment
- `payment.subscription` - Payment for subscription
- `payment.success` - Payment success page
- `payment.error` - Payment error page

## Testing the System

### Test Case 1: New Seller Registration
1. Register new seller account
2. Complete profile settings → `setup_completed_at` is set
3. Complete profit settings → `profit_settings_completed = true`
4. Access full system for 24 hours

### Test Case 2: Trial Period Active
1. Create seller with `created_at` < 24 hours ago
2. Complete setup
3. ✅ Can access all features

### Test Case 3: Trial Expired, No Subscription
1. Create seller with `created_at` > 24 hours ago
2. Complete setup
3. No active subscription
4. ❌ Can only access: Subscriptions, Settings, Support
5. Attempting to access other pages redirects to `/subscriptions`

### Test Case 4: Trial Expired, With Subscription
1. Create seller with `created_at` > 24 hours ago
2. Complete setup
3. Has active subscription
4. ✅ Can access all features

## Error Messages

### English
- `please_complete_profile_settings` - "Please complete your profile settings first"
- `please_complete_profit_settings` - "Please complete your profit settings to continue"
- `trial_expired_please_subscribe` - "Your trial period has expired. Please subscribe to continue using the system"

### Arabic
- `please_complete_profile_settings` - "يرجى إكمال إعدادات الملف الشخصي أولاً"
- `please_complete_profit_settings` - "يرجى إكمال إعدادات الأرباح للمتابعة"
- `trial_expired_please_subscribe` - "انتهت فترة التجربة. يرجى الاشتراك لمتابعة استخدام النظام"

## Files Modified

1. **User Model** - `app/Models/User.php`
   - Added trial period methods
   - Added setup completion methods

2. **Middleware** - `app/Http/Middleware/SellerAccessControl.php`
   - Access control logic

3. **ProfileController** - `app/Http/Controllers/ProfileController.php`
   - Sets `setup_completed_at` when seller saves profile

4. **SellerSubcategoryProfitController** - `app/Http/Controllers/SellerSubcategoryProfitController.php`
   - Sets `profit_settings_completed` when seller saves profit settings

5. **Migration** - `database/migrations/2026_01_18_085834_add_seller_setup_fields_to_users_table.php`
   - Adds new database fields

6. **Bootstrap** - `bootstrap/app.php`
   - Registers middleware globally

7. **Translations**
   - `lang/en/messages.php`
   - `lang/ar/messages.php`

## Important Notes

⚠️ **The middleware is applied globally** to all web routes but only affects sellers.

⚠️ **Admin and Distributor users** are not affected by this middleware.

⚠️ **Trial period starts from `created_at`**, not from setup completion.

✅ **Subscription check** is done via `UserSubscription` model with `status = 'active'` and `end_date >= today`.
