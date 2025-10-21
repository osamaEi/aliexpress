# AliExpress API Access Guide

## Current Status

You have:
- ✅ API Key: `517420`
- ✅ API Secret: `y86kcMc4Yyyima1vDkUSJspmuuMc38iT`
- ❌ Access Token: **NOT SET** (Required!)
- ❌ Tracking ID: **NOT SET** (Optional for affiliate features)

## The Problem

The AliExpress Dropshipping API (`aliexpress.ds.*` methods) **requires an access_token** for all requests. This is obtained through OAuth2 authorization.

## How to Get Access Token

### Method 1: OAuth2 Authorization Flow (Recommended)

1. **Register Your Application**
   - Go to: https://openservice.aliexpress.com/
   - Login with your AliExpress account
   - Navigate to "My Apps" → "Create App"
   - Fill in your app details

2. **Get Authorization URL**
   ```
   https://api-sg.aliexpress.com/oauth/authorize?response_type=code&client_id=517420&redirect_uri=YOUR_CALLBACK_URL&state=random_state
   ```

   Replace:
   - `client_id`: Your API Key (517420)
   - `redirect_uri`: A URL on your server to receive the callback
   - `state`: A random string for security

3. **User Authorization**
   - Open the URL in browser
   - Login with your AliExpress seller account
   - Authorize the application
   - You'll be redirected to your callback URL with a `code` parameter

4. **Exchange Code for Access Token**
   Make a POST request to:
   ```
   https://api-sg.aliexpress.com/oauth/token
   ```

   Parameters:
   ```
   client_id=517420
   client_secret=y86kcMc4Yyyima1vDkUSJspmuuMc38iT
   grant_type=authorization_code
   code=THE_CODE_FROM_STEP_3
   redirect_uri=YOUR_CALLBACK_URL
   ```

5. **Save the Token**
   The response will contain:
   ```json
   {
     "access_token": "50000901234...",
     "refresh_token": "50001234...",
     "expires_in": 86400,
     "refresh_token_valid_time": 31536000
   }
   ```

   Add to your `.env`:
   ```
   ALIEXPRESS_ACCESS_TOKEN=50000901234...
   ```

### Method 2: Using AliExpress Partner Portal

1. Go to: https://portals.aliexpress.com/
2. Login as an affiliate partner
3. Navigate to API settings
4. Generate access token directly

### Method 3: Test with Product ID Import (Temporary Workaround)

Since you don't have an access token yet, you can:
- Manually copy product IDs from AliExpress website
- Use the import form directly with product IDs
- This doesn't require API search

## Alternative: Use Web Scraping (Not Recommended)

If you can't get API access, you could:
- Build a Chrome extension to grab product data
- Use AliExpress affiliate links to get product info
- Manually import products

**Note:** This violates AliExpress ToS and may get your account banned.

## Quick Test (Once You Have Token)

1. Add token to `.env`:
   ```
   ALIEXPRESS_ACCESS_TOKEN=your_token_here
   ```

2. Run test:
   ```bash
   php artisan aliexpress:test 1005006340579394
   ```

3. If successful, you'll see product details!

## Need Help?

If you're stuck getting the access token:
1. Check if you have a verified AliExpress seller/affiliate account
2. Ensure your app is approved in the AliExpress developer portal
3. Some API features require manual approval from AliExpress team
4. Contact AliExpress API support: api-support@aliexpress.com

## Current Workaround

For now, you can:
1. Browse products on AliExpress.com
2. Copy product IDs from URLs (e.g., `1005006340579394`)
3. Use the "Import from AliExpress" page to import by ID
4. Once imported, products can be synced (if token is added later)
