# Paymob UAE Integration Setup Guide

## Current Error: "Invalid credentials"

The error `data.message=Invalid+credentials` means your Integration ID doesn't match your API credentials.

---

## How to Fix: Get Correct Credentials from Paymob Dashboard

### Step 1: Login to Paymob UAE Dashboard
1. Go to https://uae.paymob.com
2. Login with your account

### Step 2: Get Your API Key
1. Click on **Settings** (gear icon) in the left sidebar
2. Click on **Account Info** or **API Keys**
3. Copy your **API Key** (looks like: `ZXlKaGJHY2lPaUpJVXpVeE1p...`)
   - For test mode: Look for "Test API Key"
   - For live mode: Look for "Live API Key"

### Step 3: Get Your Integration IDs
1. Click on **Settings** → **Payment Integrations**
2. You'll see a list of integrations
3. Find your **Card Payment** integration
4. Click on it to see details
5. Copy these values:
   - **Integration ID** (e.g., 76398)
   - **HMAC** (e.g., EB1BCA7D744BB3F1...)
   - **iframe ID** (e.g., 35366)

### Step 4: IMPORTANT - Make Sure They Match
- If you're using **Test API Key**, use **Test Integration ID**
- If you're using **Live API Key**, use **Live Integration ID**
- **They must be from the SAME mode (test or live)**

### Step 5: Update Your .env File

Replace the values in your `.env` file with the correct ones from the dashboard:

```env
# Use TEST credentials (recommended for testing)
PAYMOB_API_KEY=your_test_api_key_from_dashboard
PAYMOB_HMAC=your_hmac_from_integration_page
PAYMOB_IFRAME_ID=your_iframe_id_from_integration_page
PAYMOB_CARD_INTEGRATION_ID=your_test_integration_id
PAYMOB_WALLET_INTEGRATION_ID=your_test_integration_id
PAYMOB_BASE=https://uae.paymob.com
PAYMOB_CURRENCY=AED
PAYMOB_EXCHANGE_RATE=3.67

# These keys might not be needed - remove if causing issues
# PAYMOB_SECRET_KEY=
# PAYMOB_PUBLIC_KEY=
```

### Step 6: Clear Config Cache

After updating `.env`:

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 7: Test Again

1. Go to https://selaa.ae/subscriptions/3/subscribe
2. Click "Pay with Card (Paymob)"
3. Use test card:
   - **Card:** 4111111111111111
   - **CVV:** 123
   - **Expiry:** 12/25

---

## Common Issues

### Issue 1: Different modes mixed
❌ **Wrong:**
- API Key from test mode
- Integration ID from live mode

✅ **Correct:**
- API Key from test mode
- Integration ID from test mode

### Issue 2: Old/deleted integration
If integration 76398 was deleted or recreated, you need the NEW integration ID.

### Issue 3: Account not activated
Contact Paymob if your test account needs activation:
- Email: uae@paymob.com
- Subject: "Test account activation for [your email]"

---

## Still Not Working?

If you still get "Invalid credentials" after matching the credentials:

1. **Create a NEW integration in Paymob dashboard:**
   - Settings → Payment Integrations
   - Click "Create New Integration"
   - Choose "Card Payment"
   - Copy the NEW integration ID

2. **Contact Paymob Support:**
   - Email: uae@paymob.com
   - Include: Your account email, integration ID, error message

---

## Test Cards (After fixing credentials)

Once credentials are correct, use these test cards:

1. **VISA:** 4111111111111111, CVV: 123, Expiry: 12/25
2. **MasterCard:** 5123450000000008, CVV: 100, Expiry: 05/25
3. **Generic:** 2223000000000007, CVV: 100, Expiry: 05/25

---

## Need Help?

- Paymob UAE Support: uae@paymob.com
- Paymob Documentation: https://docs.paymob.com
- Dashboard: https://uae.paymob.com
