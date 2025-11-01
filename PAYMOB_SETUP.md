# Paymob Payment Gateway Integration Setup

This application integrates with **Paymob UAE** payment gateway for processing payments for:
- Subscription plans
- Customer orders

## Prerequisites

1. Create a Paymob account at: https://uae.paymob.com
2. Complete the KYC verification process
3. Get your API credentials from the dashboard

## Configuration Steps

### 1. Get Your Credentials

Login to your Paymob dashboard and navigate to:
- **Settings** → **Account Info** → **API Keys**

You will need the following credentials:
- API Key
- Integration ID (for card payments)
- HMAC Secret
- Iframe ID

### 2. Update .env File

Add the following configuration to your `.env` file:

```env
# Paymob Payment Gateway Configuration (UAE)
PAYMOB_API_KEY=your_api_key_here
PAYMOB_INTEGRATION_ID=your_integration_id_here
PAYMOB_IFRAME_ID=your_iframe_id_here
PAYMOB_HMAC_SECRET=your_hmac_secret_here
PAYMOB_BASE_URL=https://uae.paymob.com/api
PAYMOB_CURRENCY=AED

# Paymob Callback URLs (Full URLs)
PAYMOB_CALLBACK_URL=https://yourdomain.com/payment/callback
PAYMOB_SUCCESS_URL=https://yourdomain.com/payment/success
PAYMOB_ERROR_URL=https://yourdomain.com/payment/error
```

### 3. Configure Paymob Dashboard

In your Paymob dashboard, configure the following URLs:

**Transaction Processed Callback:**
```
https://yourdomain.com/payment/callback
```

**Transaction Response (Success URL):**
```
https://yourdomain.com/payment/success?merchant_order_id=MERCHANT_ORDER_ID&id=TRANSACTION_ID
```

**Transaction Response (Error URL):**
```
https://yourdomain.com/payment/error?merchant_order_id=MERCHANT_ORDER_ID
```

## Testing

### Test Cards

Use these test cards in **staging environment**:

**Successful Payment:**
- Card Number: `4987654321098769`
- CVV: `123`
- Expiry: Any future date
- Cardholder Name: Any name

**Failed Payment:**
- Card Number: `4000000000000002`
- CVV: `123`
- Expiry: Any future date

### Testing Flow

1. **Subscription Payment Test:**
   - Go to `/subscriptions`
   - Click "Subscribe Now" on any plan
   - You'll be redirected to Paymob payment page
   - Use test card for payment
   - After payment, you'll be redirected back to subscriptions page
   - Check payment status in database: `payment_transactions` table

2. **Order Payment Test:**
   - Create an order from products page
   - Go to orders page
   - Click "Pay Now" button
   - Complete payment on Paymob page
   - Verify order status is updated to "processing"

## Database

The payment transactions are stored in `payment_transactions` table with the following structure:

- `id` - Primary key
- `user_id` - User who made the payment
- `transaction_id` - Paymob transaction ID
- `paymob_order_id` - Paymob order ID
- `merchant_order_id` - Our internal order ID (SUB-{id}-{timestamp} or ORD-{id}-{timestamp})
- `type` - 'order' or 'subscription'
- `amount` - Payment amount
- `currency` - Currency (AED)
- `status` - pending, success, failed, refunded
- `payment_method` - Payment method used
- `callback_data` - Full callback JSON from Paymob
- `is_refunded` - Refund status
- `paid_at` - Payment timestamp

## Payment Flow

### Subscription Payment:
1. User clicks "Subscribe Now"
2. System creates payment transaction record
3. System calls Paymob API to initiate payment
4. User is redirected to Paymob payment page
5. User completes payment
6. Paymob sends callback to `/payment/callback`
7. System verifies HMAC signature
8. System creates user subscription record
9. User is redirected to success page

### Order Payment:
1. User clicks "Pay Now" on order
2. System creates payment transaction record
3. System calls Paymob API to initiate payment
4. User is redirected to Paymob payment page
5. User completes payment
6. Paymob sends callback to `/payment/callback`
7. System verifies HMAC signature
8. System updates order payment status
9. User is redirected to success page

## Security

- All callbacks are verified using HMAC signature
- CSRF protection is disabled only for the callback route
- All payment transactions are logged
- Failed payments are tracked and logged

## Troubleshooting

### Payment callback not working:
- Check if callback URL is accessible from internet (not localhost)
- Verify HMAC secret matches in .env and Paymob dashboard
- Check Laravel logs: `storage/logs/laravel.log`
- Verify callback URL in Paymob dashboard settings

### Payment successful but subscription not activated:
- Check `payment_transactions` table for transaction status
- Check Laravel logs for any errors
- Verify user_subscriptions table for new entries

### Redirect issues:
- Make sure success/error URLs are full URLs (not relative paths)
- Check if URLs are properly configured in Paymob dashboard

## API Documentation

Full Paymob UAE API documentation:
https://developers.paymob.com/uae/getting-started-uae

## Support

For Paymob integration issues:
- Email: support@paymob.com
- Phone: Check Paymob UAE website for contact details
