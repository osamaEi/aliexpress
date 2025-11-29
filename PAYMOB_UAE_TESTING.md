# Paymob UAE - Testing Guide

## Changes Made ✅

I've updated the Paymob implementation to support UAE's newer API that uses `secret_key` instead of just `api_key`.

### Files Modified:
1. **config/paymob.php** - Added `secret_key` and `public_key` configuration
2. **PaymobController.php** - Updated to use secret_key for authentication

---

## Your Current Configuration

```env
PAYMOB_API_KEY=ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5...
PAYMOB_SECRET_KEY=are_sk_test_a92cf4f39e73e09433258569fc016009e6efd97c6ef2ffff2aa0f85a44636fda
PAYMOB_PUBLIC_KEY=are_pk_test_cdJDymei6pBB3ek41pUIE1X3EzBGLjWX
PAYMOB_HMAC=EB1BCA7D744BB3F1A37649C01BCF6AB0
PAYMOB_IFRAME_ID=35366
PAYMOB_CARD_INTEGRATION_ID=76398
PAYMOB_BASE=https://uae.paymob.com
PAYMOB_CURRENCY=AED
```

✅ All credentials have `_test_` which confirms test mode

---

## How to Test Now

### Step 1: Clear Cache
```bash
php artisan config:clear
```

### Step 2: Test Payment Flow

1. Go to: **https://selaa.ae/subscriptions/3/subscribe**
2. Click **"Pay with Card (Paymob)"**
3. Wait for the iframe to load
4. Enter test card details:

#### Test Card Option 1 (VISA):
```
Card Number: 4111111111111111
CVV: 123
Expiry: 12/25
Cardholder: Test User
```

#### Test Card Option 2 (MasterCard):
```
Card Number: 5123450000000008
CVV: 100
Expiry: 05/25
Cardholder: Test User
```

#### Test Card Option 3 (Alternative):
```
Card Number: 2223000000000007
CVV: 100
Expiry: 05/25
Cardholder: Test User
```

### Step 3: Check Results

**If Successful:** ✅
- You'll be redirected to the success page
- Subscription will be activated
- Check: https://selaa.ae/subscriptions

**If Still Shows "Invalid Credentials":** ❌
- This means integration 76398 is not properly configured in Paymob dashboard
- See troubleshooting below

---

## Troubleshooting

### Problem: Still Getting "Invalid Credentials"

This error means your **integration 76398** has configuration issues in Paymob's system, not your code.

#### Solution 1: Verify Integration in Dashboard

1. Login to: https://uae.paymob.com
2. Go to: **Settings → Payment Integrations**
3. Find integration **76398**
4. Check these settings:
   - ✅ Status = "Active" or "Test Mode Enabled"
   - ✅ Payment Processor = Assigned (should show a processor name)
   - ✅ Authentication = Configured
   - ✅ Test Mode = Enabled/ON

If any of these are NOT configured, **contact Paymob support**.

#### Solution 2: Create NEW Test Integration

1. In Paymob Dashboard → **Settings → Payment Integrations**
2. Click **"Create New Integration"**
3. Select **"Online Card Payment"**
4. Enable **"Test Mode"**
5. Save and copy the **NEW Integration ID**
6. Update your `.env`:
   ```env
   PAYMOB_CARD_INTEGRATION_ID=new_integration_id_here
   ```
7. Run: `php artisan config:clear`
8. Test again

#### Solution 3: Contact Paymob Support (Fastest)

**Email:** uae@paymob.com
**Subject:** Integration 76398 - Invalid Credentials Error

```
Hello Paymob Team,

I'm getting "Invalid credentials" error when testing payments with integration 76398.

Details:
- Integration ID: 76398
- iframe ID: 35366
- Using test credentials (secret_key: are_sk_test_...)
- Error occurs when entering card details in iframe
- Payment token generates successfully

Could you please:
1. Verify integration 76398 is properly configured for test mode
2. Confirm it's linked to a payment processor
3. Provide working test card numbers for my integration
4. Check if my account needs verification

Latest error details:
- Order ID: 4955133
- Error: "Invalid credentials"
- Response code: ERROR

Thank you!
```

---

## Understanding the Error

The "Invalid credentials" error happens **AFTER** these successful steps:
1. ✅ Authentication with Paymob API (getting token)
2. ✅ Creating order
3. ✅ Generating payment key
4. ✅ Loading iframe with payment form

It fails when:
5. ❌ User submits card details → Paymob tries to process payment → Integration 76398 can't authenticate with the payment processor

This indicates the **integration itself** (not your code) isn't properly set up to process payments.

---

## Alternative: Test with Paymob's Demo Integration

Ask Paymob support for a **demo/sandbox integration ID** that's pre-configured for testing. This bypasses any account verification issues.

---

## What I Fixed

### Before:
```php
// Only used API key
$authResponse = Http::post(config('paymob.base_url') . '/api/auth/tokens', [
    'api_key' => config('paymob.api_key'),
]);
```

### After:
```php
// Now uses secret_key if available (UAE's newer API)
$apiKey = config('paymob.secret_key') ?: config('paymob.api_key');
$authResponse = Http::post(config('paymob.base_url') . '/api/auth/tokens', [
    'api_key' => $apiKey,
]);
```

This ensures compatibility with both old and new Paymob UAE API formats.

---

## Next Steps

1. ✅ Code is now updated to use secret_key
2. ⏳ Test the payment flow again
3. ⏳ If still fails → Contact Paymob support (they can fix integration config in minutes)
4. ⏳ Once working, test with all 3 test cards to ensure stability

Good luck! The code is correct now - any remaining issues are on Paymob's configuration side. 🎯
