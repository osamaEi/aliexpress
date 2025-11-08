# PayPal Credentials Setup Guide

## Problem

You're seeing this error:
```
PayPal Authentication Error: Failed to get PayPal access token:
{"error":"invalid_client","error_description":"Client Authentication failed"}
```

This means the PayPal credentials in your `.env` file are invalid or expired.

## Solution: Get Valid PayPal Sandbox Credentials

Follow these steps to get valid PayPal sandbox credentials:

### Step 1: Create/Login to PayPal Developer Account

1. Go to: https://developer.paypal.com/
2. Click "Log In" (use your PayPal account or create a new one)
3. If you don't have a developer account, you'll be prompted to create one

### Step 2: Access the Dashboard

1. After logging in, you'll be on the PayPal Developer Dashboard
2. Click on "Apps & Credentials" in the top menu

### Step 3: Switch to Sandbox Mode

1. Look for the toggle at the top that says "Live" and "Sandbox"
2. Make sure **"Sandbox"** is selected (it should be highlighted)

### Step 4: Create a New App (or use existing)

**Option A: Create New App**
1. Under "REST API apps" section, click "Create App" button
2. Enter an App Name (e.g., "EcommAli Store")
3. Select "Merchant" as the App Type
4. Click "Create App"

**Option B: Use Existing App**
1. If you already have an app listed, click on it to view details

### Step 5: Copy Your Credentials

After creating/selecting your app, you'll see:

```
Client ID
AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz123456

Secret
Show | Copy
```

1. Copy the **Client ID** (long string visible by default)
2. Click "Show" next to Secret to reveal it
3. Copy the **Secret** (another long string)

### Step 6: Update Your `.env` File

Open your `.env` file and update these lines with your new credentials:

```env
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=your_copied_client_id_here
PAYPAL_SANDBOX_CLIENT_SECRET=your_copied_secret_here
PAYPAL_CURRENCY=USD
```

**Example:**
```env
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz123456
PAYPAL_SANDBOX_CLIENT_SECRET=EE1a2B3c4D5e6F7g8H9i0J1k2L3m4N5o6P7q8R9s0T1u2V3w4X5y6Z
PAYPAL_CURRENCY=USD
```

### Step 7: Clear Config Cache

After updating `.env`, run this command to clear Laravel's config cache:

```bash
php artisan config:clear
```

### Step 8: Test the Integration

1. Try subscribing to a plan or creating an order
2. You should be redirected to PayPal sandbox
3. Use a PayPal sandbox test account to complete payment

---

## Creating PayPal Sandbox Test Accounts

You'll need test accounts to test payments:

### Step 1: Access Sandbox Accounts

1. In PayPal Developer Dashboard, click "Sandbox" → "Accounts"
2. You should see default test accounts already created

### Step 2: View Test Account Credentials

1. Click on a "Personal" account (this is the buyer)
2. Click on the "..." menu and select "View/Edit Account"
3. You'll see:
   - Email address (e.g., sb-xxxxx@personal.example.com)
   - Password
   - Balance

### Step 3: Use Test Account for Payment

When testing payments:
1. The app redirects you to PayPal sandbox
2. Login with the **Personal (Buyer)** test account email and password
3. Complete the payment
4. You'll be redirected back to your app

---

## Common Issues

### Issue 1: Still Getting "invalid_client" Error

**Solutions:**
- Double-check you copied the entire Client ID (no spaces)
- Double-check you copied the entire Secret (no spaces)
- Make sure you're using **Sandbox** credentials (not Live)
- Run `php artisan config:clear` after updating `.env`
- Restart your Laravel server

### Issue 2: Credentials Work But Payment Fails

**Solution:**
- Check that return URLs are configured correctly in `config/paypal.php`:
  ```php
  'return_url' => env('APP_URL') . '/payment/callback',
  'cancel_url' => env('APP_URL') . '/payment/error',
  ```
- Make sure `APP_URL` in `.env` is correct (e.g., `http://localhost`)

### Issue 3: "This payment is not approved yet"

**Solution:**
- Make sure you're using a **Personal (Buyer)** test account to make payments
- Don't use your main PayPal account for sandbox testing
- The buyer account should have sufficient balance

---

## Moving to Production (Live Mode)

When you're ready to accept real payments:

### Step 1: Get Live Credentials

1. Go to PayPal Developer Dashboard
2. Switch from "Sandbox" to **"Live"**
3. Create a new app or use existing one
4. Copy the Live Client ID and Secret

### Step 2: Update `.env` for Production

```env
PAYPAL_MODE=live
PAYPAL_LIVE_CLIENT_ID=your_live_client_id
PAYPAL_LIVE_CLIENT_SECRET=your_live_secret
PAYPAL_CURRENCY=USD
```

### Step 3: Enable Advanced Features (Optional)

In your Live app settings, you may need to enable:
- [ ] Checkout (should be enabled by default)
- [ ] Vault (for storing payment methods)
- [ ] Refunds

---

## Quick Reference

### Current Invalid Credentials

The credentials in your `.env` are:
```
Client ID: AQR7Z7MdfkVukdpyglGSZTZdxxoLsdXMkspUozCt0NP5wRwSFvPXaZtLHOUTRILWrgtLYsIe0Hf-ShDF
Secret: EOucExkWeles5mJC-Of9AA6KJYBG5LFzMdLPjg4G9TII4uecatIspUjVDB1Vek2dnI_lMADkiXRF7wrT
```

These credentials are **not valid**. You need to get new ones from your PayPal Developer account.

### Where to Get Credentials

**Sandbox:** https://developer.paypal.com/dashboard/applications/sandbox

**Live:** https://developer.paypal.com/dashboard/applications/live

---

## Summary Checklist

- [ ] Login to PayPal Developer Dashboard
- [ ] Go to "Apps & Credentials"
- [ ] Switch to "Sandbox" mode
- [ ] Create new app or select existing
- [ ] Copy Client ID and Secret
- [ ] Update `.env` file with new credentials
- [ ] Run `php artisan config:clear`
- [ ] Test payment flow with sandbox test account

---

## Support

If you continue to have issues:

1. **Check PayPal Status**: https://www.paypal-status.com/
2. **PayPal Developer Forums**: https://www.paypal-community.com/
3. **PayPal Support**: Contact through Developer Dashboard

---

## Testing Your Credentials

You can test if your credentials are valid by running this in your browser after updating `.env`:

1. Create a test route (optional):
   ```php
   // In routes/web.php (temporary, for testing only)
   Route::get('/test-paypal', function () {
       $service = new \App\Services\PayPalService();
       try {
           $order = $service->createOrder(10.00, 'USD', 'Test Payment');
           return 'PayPal credentials are valid! Order ID: ' . $order['id'];
       } catch (\Exception $e) {
           return 'Error: ' . $e->getMessage();
       }
   });
   ```

2. Visit: `http://localhost/test-paypal`

3. If you see "PayPal credentials are valid!", your setup is correct!

4. **Remember to remove this test route after testing!**

---

Good luck! Once you have valid credentials, the payment flow will work perfectly.
