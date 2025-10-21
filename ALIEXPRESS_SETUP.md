# AliExpress Dropshipping Integration Setup Guide

This guide will help you set up the AliExpress Dropshipping integration for your ecommerce platform.

## Prerequisites

1. An AliExpress account
2. Access to the AliExpress Open Platform
3. Enrollment in the AliExpress Dropshipping Program

## Step 1: Register on AliExpress Open Platform

1. Visit [AliExpress Open Platform](https://openservice.aliexpress.com/)
2. Log in with your AliExpress account
3. Complete the registration process
4. Navigate to "My Apps" section

## Step 2: Create an Application

1. Click "Create App" button
2. Fill in your application details:
   - App Name: Your store name
   - App Description: Brief description of your dropshipping business
   - App Category: E-commerce
3. Submit the application for review
4. Once approved, you'll receive:
   - **App Key** (API Key)
   - **App Secret** (API Secret)

## Step 3: Join the Dropshipping Program

1. Visit [AliExpress Dropshipping Center](https://ds.aliexpress.com/)
2. Log in with your AliExpress account
3. Read and accept the "AliExpress Dropshipping Program User Agreement"
4. Complete your dropshipper profile
5. Your account is now a dropshipping-enabled account

## Step 4: Generate Access Token (OAuth2)

The access token is required for authenticated API requests. There are two methods:

### Method A: Using OAuth2 Flow (Recommended)

1. Construct the authorization URL:
   ```
   https://oauth.aliexpress.com/authorize?response_type=code&client_id=YOUR_APP_KEY&redirect_uri=YOUR_CALLBACK_URL&state=RANDOM_STRING
   ```

2. Visit this URL in your browser
3. Log in and authorize the application
4. You'll be redirected to your callback URL with an authorization code
5. Exchange the code for an access token using this API call:
   ```
   POST https://oauth.aliexpress.com/token
   Parameters:
   - grant_type: authorization_code
   - client_id: YOUR_APP_KEY
   - client_secret: YOUR_APP_SECRET
   - code: AUTHORIZATION_CODE
   - redirect_uri: YOUR_CALLBACK_URL
   ```

6. The response will contain your access token

### Method B: Using a Plugin (Alternative)

If you're using plugins like Ali2Woo, they may provide a simplified token generation interface.

## Step 5: Configure Your Application

1. Open the `.env` file in your Laravel application
2. Add your AliExpress credentials:

```env
# AliExpress API Configuration
ALIEXPRESS_API_KEY=your_app_key_here
ALIEXPRESS_API_SECRET=your_app_secret_here
ALIEXPRESS_TRACKING_ID=your_tracking_id_here
ALIEXPRESS_ACCESS_TOKEN=your_access_token_here
ALIEXPRESS_API_URL=https://api-sg.aliexpress.com/sync
```

3. Save the file

## Step 6: Run Migrations

Run the database migrations to create the necessary tables:

```bash
php artisan migrate
```

## Step 7: Test the Integration

You can test the integration by importing a product:

```bash
php artisan tinker
```

Then run:

```php
$service = app(\App\Services\AliExpressService::class);
$products = $service->searchProducts('phone case');
dd($products);
```

## Important Notes

### Access Token Expiration

- Access tokens typically expire after **30 days**
- You'll need to refresh or regenerate the token periodically
- Implement a token refresh mechanism or manually update the token monthly

### API Rate Limits

- AliExpress API has rate limits (typically 5000 requests per day)
- Monitor your API usage to avoid hitting limits
- Implement caching for frequently accessed data

### Dropshipping Account Requirements

- Your AliExpress account MUST be enrolled in the dropshipping program
- Regular accounts cannot access dropshipping-specific APIs
- Some APIs may require additional permissions or higher tier access

## Available Features

Once set up, you can:

1. **Search Products**: Search AliExpress catalog by keywords
2. **Import Products**: Import products directly into your database
3. **Sync Pricing**: Keep product prices and availability updated
4. **Create Orders**: Automatically create dropship orders on AliExpress
5. **Track Orders**: Monitor order status and shipping information
6. **Generate Affiliate Links**: Create affiliate links for commission

## Troubleshooting

### "Access Token is empty" Error

- Ensure your account is enrolled in the dropshipping program
- Verify the OAuth flow was completed successfully
- Check that the token is properly set in the `.env` file

### "Invalid Signature" Error

- Verify your App Key and App Secret are correct
- Ensure there are no extra spaces in the `.env` file
- Check that the timestamp is being generated correctly

### "Permission Denied" Error

- Your app may not have the required permissions
- Contact AliExpress support to request additional API access
- Ensure your account is in good standing

## Support

For issues with:
- **API Access**: Contact AliExpress Open Platform support
- **Dropshipping Program**: Visit the Dropshipping Center help section
- **This Integration**: Check the application logs at `storage/logs/laravel.log`

## Security Best Practices

1. Never commit your `.env` file to version control
2. Keep your API keys and access tokens secret
3. Use HTTPS for all API communications
4. Regularly rotate your access tokens
5. Monitor API usage for unusual activity

## Additional Resources

- [AliExpress Open Platform Documentation](https://openservice.aliexpress.com/doc/doc.htm)
- [AliExpress Dropshipping Center](https://ds.aliexpress.com/)
- [API Reference](https://openservice.aliexpress.com/doc/api.htm)
