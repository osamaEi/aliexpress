# Subscription Payment Flow with PayPal

## Overview

This document explains the complete flow for sellers to subscribe to a plan using PayPal payment integration.

## Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    SUBSCRIPTION PAYMENT FLOW                     │
└─────────────────────────────────────────────────────────────────┘

1. SELLER VIEWS PLANS
   ├─► User visits: /subscriptions
   ├─► SubscriptionController::index()
   └─► Displays available subscription plans

2. SELLER CHOOSES PLAN
   ├─► User clicks "Subscribe" button
   ├─► POST /subscriptions/{subscription}/subscribe
   ├─► SubscriptionController::subscribe()
   └─► Redirects to: /payment/subscription/{subscription}

3. PAYMENT INITIATION
   ├─► POST /payment/subscription/{subscription}
   ├─► PaymentController::initiateSubscriptionPayment()
   ├─► Creates PaymentTransaction (status: pending)
   ├─► Calls PayPalService::createOrder()
   ├─► Gets PayPal approval URL
   └─► Redirects to PayPal checkout

4. USER PAYS ON PAYPAL
   ├─► User completes payment on PayPal
   ├─► PayPal redirects to: /payment/callback?token={order_id}&PayerID={payer_id}
   └─► Or cancels to: /payment/error

5. PAYMENT CALLBACK (Success)
   ├─► GET /payment/callback
   ├─► PaymentController::callback()
   ├─► Finds PaymentTransaction by paypal_order_id
   ├─► Calls PayPalService::captureOrder()
   ├─► Updates PaymentTransaction (status: success)
   └─► Calls processSubscriptionPayment()

6. SUBSCRIPTION ACTIVATION
   ├─► processSubscriptionPayment()
   ├─► Extracts subscription_id from merchant_order_id
   ├─► Creates UserSubscription record:
   │   ├─ user_id
   │   ├─ subscription_id
   │   ├─ start_date: today
   │   ├─ end_date: today + duration_days
   │   ├─ status: 'active'
   │   ├─ amount_paid
   │   └─ payment_method: 'paypal'
   └─► Subscription is now active!

7. SUCCESS REDIRECT
   ├─► Redirects to: /payment/success
   ├─► PaymentController::success()
   └─► Redirects to: /subscriptions (with success message)

8. USER SEES CONFIRMATION
   ├─► User is back at /subscriptions
   ├─► Success message: "Subscription successful!"
   └─► Current subscription badge is displayed
```

## Code Flow

### 1. User Chooses Plan

**File**: `resources/views/subscriptions/index.blade.php`

```blade
<form method="POST" action="{{ route('subscriptions.subscribe', $subscription) }}">
    @csrf
    <button type="submit" class="btn btn-primary">
        Subscribe Now
    </button>
</form>
```

**Route**: `POST /subscriptions/{subscription}/subscribe`

**Controller**: `SubscriptionController::subscribe()`

```php
public function subscribe(Request $request, Subscription $subscription)
{
    $user = Auth::user();

    // Check if user already has an active subscription
    if ($user->hasActiveSubscription()) {
        return redirect()->route('subscriptions.index')
            ->with('error', __('messages.already_have_active_subscription'));
    }

    // Redirect to PayPal payment
    return redirect()->route('payment.subscription', $subscription);
}
```

### 2. Payment Initiation

**Route**: `POST /payment/subscription/{subscription}`

**Controller**: `PaymentController::initiateSubscriptionPayment()`

```php
public function initiateSubscriptionPayment(Request $request, Subscription $subscription)
{
    $user = Auth::user();

    // Create payment transaction record
    $merchantOrderId = 'SUB-' . $subscription->id . '-' . time();
    $paymentTransaction = PaymentTransaction::create([
        'user_id' => $user->id,
        'merchant_order_id' => $merchantOrderId,
        'type' => 'subscription',
        'amount' => $subscription->price,
        'currency' => config('paypal.currency'),
        'status' => 'pending',
    ]);

    // Create PayPal order
    $paypalOrder = $this->paypalService->createOrder(
        $subscription->price,
        config('paypal.currency'),
        $subscription->localized_name . ' - Subscription Plan',
        $merchantOrderId
    );

    // Update transaction with PayPal order ID
    $paymentTransaction->update([
        'paypal_order_id' => $paypalOrder['id']
    ]);

    // Get approval URL and redirect user to PayPal
    $approvalUrl = $this->paypalService->getApprovalUrl($paypalOrder);
    return redirect($approvalUrl);
}
```

### 3. Payment Callback

**Route**: `GET /payment/callback`

**Controller**: `PaymentController::callback()`

```php
public function callback(Request $request)
{
    $token = $request->query('token'); // PayPal order ID
    $payerId = $request->query('PayerID');

    // Find transaction
    $transaction = PaymentTransaction::where('paypal_order_id', $token)->first();

    // Capture payment
    $captureResult = $this->paypalService->captureOrder($token);

    if ($captureResult['status'] === 'COMPLETED') {
        // Update transaction
        $transaction->update([
            'transaction_id' => $captureResult['purchase_units'][0]['payments']['captures'][0]['id'],
            'status' => 'success',
            'payment_method' => 'paypal',
            'callback_data' => $captureResult,
            'paid_at' => now(),
        ]);

        // Process subscription
        if ($transaction->type === 'subscription') {
            $this->processSubscriptionPayment($transaction);
        }

        return redirect()->route('payment.success', [
            'id' => $transactionId,
            'merchant_order_id' => $transaction->merchant_order_id
        ]);
    }
}
```

### 4. Subscription Activation

**Method**: `PaymentController::processSubscriptionPayment()`

```php
protected function processSubscriptionPayment(PaymentTransaction $transaction)
{
    DB::transaction(function () use ($transaction) {
        // Extract subscription ID from merchant_order_id (SUB-{id}-{timestamp})
        preg_match('/SUB-(\d+)-/', $transaction->merchant_order_id, $matches);
        $subscriptionId = $matches[1];

        $subscription = Subscription::find($subscriptionId);

        // Create user subscription
        UserSubscription::create([
            'user_id' => $transaction->user_id,
            'subscription_id' => $subscription->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays($subscription->duration_days)->toDateString(),
            'status' => 'active',
            'amount_paid' => $transaction->amount,
            'payment_method' => 'paypal',
        ]);
    });
}
```

## Database Tables Involved

### 1. `payment_transactions`

Stores all payment attempts:

```
id | user_id | merchant_order_id | paypal_order_id | type | amount | status | paid_at
---|---------|-------------------|-----------------|------|--------|--------|--------
1  | 5       | SUB-3-1699123456  | 8AB12CD34EF56   | sub  | 99.00  | success| 2024...
```

### 2. `user_subscriptions`

Stores active subscriptions:

```
id | user_id | subscription_id | start_date | end_date   | status | amount_paid | payment_method
---|---------|-----------------|------------|------------|--------|-------------|---------------
1  | 5       | 3               | 2024-11-08 | 2024-12-08 | active | 99.00       | paypal
```

### 3. `subscriptions`

Available subscription plans:

```
id | name     | price | duration_days | max_products | is_active
---|----------|-------|---------------|--------------|----------
1  | Basic    | 29.00 | 30            | 100          | 1
2  | Pro      | 49.00 | 30            | 500          | 1
3  | Premium  | 99.00 | 30            | 1000         | 1
```

## Routes Summary

| Method | URL | Action | Description |
|--------|-----|--------|-------------|
| GET | `/subscriptions` | `index()` | View plans |
| POST | `/subscriptions/{id}/subscribe` | `subscribe()` | Choose plan |
| POST | `/payment/subscription/{id}` | `initiateSubscriptionPayment()` | Create PayPal order |
| GET | `/payment/callback` | `callback()` | PayPal return URL |
| GET | `/payment/success` | `success()` | Success redirect |
| GET | `/payment/error` | `error()` | Error redirect |
| POST | `/subscriptions/cancel` | `cancel()` | Cancel subscription |

## Testing Flow

### 1. Local Testing (Sandbox Mode)

```bash
# Ensure PayPal sandbox credentials are set
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=your_client_id
PAYPAL_SANDBOX_CLIENT_SECRET=your_secret
```

### 2. Test Steps

1. Login as a seller
2. Go to `/subscriptions`
3. Click "Subscribe" on any plan
4. You'll be redirected to PayPal Sandbox
5. Use PayPal sandbox test account to complete payment
6. You'll be redirected back to your app
7. Subscription should be active

### 3. PayPal Sandbox Test Accounts

Create test accounts at: https://developer.paypal.com/dashboard/accounts

- **Buyer Account**: Use to make payments
- **Seller Account**: Receives payments (your app)

## Error Handling

### 1. User Already Has Subscription

```php
if ($user->hasActiveSubscription()) {
    return redirect()->route('subscriptions.index')
        ->with('error', __('messages.already_have_active_subscription'));
}
```

### 2. Payment Failed

```php
if ($captureStatus !== 'COMPLETED') {
    $transaction->markAsFailed();
    return redirect()->route('payment.error')
        ->with('error', __('messages.payment_failed'));
}
```

### 3. Transaction Not Found

```php
if (!$transaction) {
    return redirect()->route('payment.error')
        ->with('error', __('messages.transaction_not_found'));
}
```

## Security Measures

1. **CSRF Protection**: All POST routes protected by Laravel's CSRF middleware
2. **User Authentication**: All routes require authentication
3. **Transaction Verification**: PayPal order ID must match pending transaction
4. **Payment Capture**: Actual money capture happens in callback
5. **Database Transactions**: Subscription creation wrapped in DB transaction

## Production Checklist

Before going live:

- [ ] Switch to PayPal live credentials
- [ ] Set `PAYPAL_MODE=live` in `.env`
- [ ] Test complete flow with real PayPal account
- [ ] Verify webhook URLs are accessible
- [ ] Enable SSL/HTTPS on production
- [ ] Set correct `APP_URL` in `.env`
- [ ] Monitor logs for payment errors
- [ ] Set up payment failure notifications

## Troubleshooting

### Issue: User stuck after PayPal payment

**Solution**: Check logs for PayPal callback errors. Ensure `APP_URL` is correct.

### Issue: Subscription not activated after payment

**Solution**: Check `payment_transactions` table. Verify callback was received and processed.

### Issue: PayPal returns error

**Solution**: Verify credentials, check PayPal sandbox/live mode matches config.

## Support

For PayPal integration issues:
- PayPal Developer Docs: https://developer.paypal.com/docs/
- PayPal Sandbox: https://developer.paypal.com/dashboard/
