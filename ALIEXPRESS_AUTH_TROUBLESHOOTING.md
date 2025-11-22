# AliExpress OAuth Authorization Troubleshooting Guide

## Error: "param-appkey.not.exists"

This error occurs when AliExpress doesn't recognize your App Key during OAuth authorization.

### Common Causes

1. **Invalid App Key** - The App Key doesn't exist in AliExpress's system
2. **Unregistered Redirect URI** - The callback URL isn't registered in your app settings
3. **Deactivated App** - Your AliExpress app has been deactivated
4. **Wrong Environment** - Using sandbox credentials in production or vice versa

---

## Step-by-Step Solution

### 1. Verify Your App Key

1. Login to [AliExpress Open Platform](https://openservice.aliexpress.com/)
2. Go to **My Apps** → [https://openservice.aliexpress.com/myapp/index.htm](https://openservice.aliexpress.com/myapp/index.htm)
3. Find your application and verify:
   - **App Key**: Should match `ALIEXPRESS_APP_KEY` in your `.env` file
   - **Status**: Must be "Active" or "In Production"
   - If you see "Sandbox" or "Deleted", you need to create a new app or activate it

### 2. Register Your Redirect URI

**This is the most common issue!**

1. In your AliExpress app settings, find the **"Redirect URI"** or **"Callback URL"** section
2. Add your production callback URL:
   ```
   https://selaa.ae/admin/tokens/callback
   ```
3. For local development, also add:
   ```
   http://localhost:8000/admin/tokens/callback
   ```
4. **Important**: Save the settings and wait 5-10 minutes for the changes to propagate

### 3. Check Your Environment Variables

Open your `.env` file and verify:

```env
ALIEXPRESS_APP_KEY=517420
ALIEXPRESS_APP_SECRET=y86kcMc4Yyyima1vDkUSJspmuuMc38iT
```

**Current Configuration:**
- App Key: `517420`
- Callback URL: `https://selaa.ae/admin/tokens/callback`

### 4. Verify App Permissions

1. In AliExpress Developer Console, check your app has the required permissions:
   - **Dropshipping APIs** (if using dropshipping features)
   - **OAuth Authorization**
   - **Order Management** (if placing orders)

2. If permissions are missing, request them and wait for approval

### 5. Test the Authorization Flow

After making changes:

1. Visit: `/admin/tokens` on your application
2. Click **"Generate New Token"**
3. You should be redirected to AliExpress OAuth page
4. After authorization, you'll return to your callback URL with a code

---

## Alternative Solutions

### Option 1: Create a New App

If your current app is having issues:

1. Go to [AliExpress Open Platform](https://openservice.aliexpress.com/)
2. Create a new application
3. Get new App Key and App Secret
4. Update your `.env` file with the new credentials
5. Register your redirect URI in the new app

### Option 2: Manual Token Generation

If OAuth flow continues to fail, you can manually generate a token:

1. Use AliExpress's Token Generation Tool (if available in your developer console)
2. Copy the access token and refresh token
3. Manually insert them into your database using:

```php
php artisan tinker
```

```php
$token = new \App\Models\AliExpressToken([
    'account' => 'default',
    'access_token' => encrypt('YOUR_ACCESS_TOKEN_HERE'),
    'refresh_token' => encrypt('YOUR_REFRESH_TOKEN_HERE'),
    'expires_at' => now()->addDays(30),
    'refresh_expires_at' => now()->addDays(90),
    'account_platform' => 'AE'
]);
$token->save();
```

---

## Debugging Steps

### Check Laravel Logs

View recent logs for detailed error information:

```bash
tail -f storage/logs/laravel.log
```

Look for entries containing:
- `AliExpress Authorization URL Generated`
- `AliExpress Token Creation Request`
- `AliExpress Token Creation Response`

### Check Browser Network Tab

1. Open browser Developer Tools (F12)
2. Go to Network tab
3. Click "Generate New Token"
4. Check the redirect URL parameters:
   - `client_id` should match your App Key
   - `redirect_uri` should be URL-encoded correctly
   - `response_type` should be `code`

### Verify API Endpoint

The current OAuth endpoint is:
```
https://oauth.aliexpress.com/authorize
```

If this doesn't work, try the alternative:
```
https://oauth.aliexpress.com/oauth/authorize
```

---

## Contact AliExpress Support

If none of the above solutions work:

1. Contact AliExpress Developer Support
2. Provide them with:
   - Your App Key: `517420`
   - Error Code: `param-appkey.not.exists`
   - Redirect URI: `https://selaa.ae/admin/tokens/callback`
   - Screenshot of the error

Support channels:
- Developer Forum: [https://developers.aliexpress.com/](https://developers.aliexpress.com/)
- Email: Usually available in the developer console

---

## Quick Checklist

- [ ] App Key `517420` exists and is active in AliExpress console
- [ ] App Secret is correct in `.env` file
- [ ] Redirect URI `https://selaa.ae/admin/tokens/callback` is registered
- [ ] App has required API permissions
- [ ] No typos in App Key or Secret
- [ ] Waited 5-10 minutes after changing redirect URI
- [ ] Cleared Laravel config cache: `php artisan config:clear`
- [ ] Checked Laravel logs for detailed errors

---

## Additional Resources

- [AliExpress Open Platform Documentation](https://developers.aliexpress.com/en/doc.htm)
- [OAuth 2.0 Flow Documentation](https://oauth.net/2/)
- [AliExpress API Forum](https://openservice.aliexpress.com/forum/)
