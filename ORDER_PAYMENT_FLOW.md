# Order Payment Flow with PayPal

## Overview

This document explains the complete flow for sellers to create orders and complete payment using PayPal integration.

## Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                      ORDER PAYMENT FLOW                          │
└─────────────────────────────────────────────────────────────────┘

1. SELLER CREATES ORDER
   ├─► User visits: /orders/create
   ├─► OrderController::create()
   └─► Displays order creation form

2. SELLER SUBMITS ORDER
   ├─► User fills in order details
   ├─► POST /orders
   ├─► OrderController::store()
   ├─► Creates Order record (status: pending, payment_status: pending)
   └─► Redirects to: /payment/order/{order}

3. PAYMENT INITIATION
   ├─► GET /payment/order/{order}
   ├─► PaymentController::initiateOrderPayment()
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
   └─► Calls processOrderPayment()

6. ORDER ACTIVATION
   ├─► processOrderPayment()
   ├─► Extracts order_id from merchant_order_id
   ├─► Updates Order record:
   │   ├─ payment_status: 'paid'
   │   └─ status: 'processing'
   └─► Order is now paid and ready to be placed!

7. SUCCESS REDIRECT
   ├─► Redirects to: /payment/success
   ├─► PaymentController::success()
   └─► Redirects to: /orders (with success message)

8. USER SEES CONFIRMATION
   ├─► User is back at /orders
   ├─► Success message: "Payment successful!"
   └─► Order shows payment_status as 'paid'

9. PLACE ORDER ON ALIEXPRESS
   ├─► User clicks "Place on AliExpress" button
   ├─► POST /orders/{order}/place-on-aliexpress
   ├─► OrderController::placeOnAliexpress()
   ├─► Verifies payment_status === 'paid'
   └─► Places order on AliExpress
```

## Code Flow

### 1. Create Order Form

**File**: `resources/views/orders/create.blade.php`

```blade
<form method="POST" action="{{ route('orders.store') }}">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <!-- Customer and shipping fields -->
    <button type="submit" class="btn btn-primary">
        Create Order
    </button>
</form>
```

**Route**: `POST /orders`

**Controller**: `OrderController::store()`

```php
public function store(Request $request)
{
    // Validate order data
    $validated = $request->validate([...]);

    // Calculate pricing
    $totalPrice = $unitPrice * $quantity;

    // Create order
    $order = Order::create([
        'user_id' => auth()->id(),
        'order_number' => Order::generateOrderNumber(),
        'total_price' => $totalPrice,
        'total_amount' => $totalPrice, // For payment
        'status' => 'pending',
        'payment_status' => 'pending',
        // ... other fields
    ]);

    // Redirect to PayPal payment
    return redirect()->route('payment.order', $order)
        ->with('info', 'Order created! Please complete payment. Order Number: ' . $order->order_number);
}
```

### 2. Payment Initiation

**Route**: `GET /payment/order/{order}`

**Controller**: `PaymentController::initiateOrderPayment()`

```php
public function initiateOrderPayment(Request $request, Order $order)
{
    $user = Auth::user();

    // Check authorization
    if ($order->user_id !== $user->id) {
        abort(403);
    }

    // Check if already paid
    if ($order->payment_status === 'paid') {
        return redirect()->route('orders.show', $order)
            ->with('error', __('messages.order_already_paid'));
    }

    // Create payment transaction record
    $merchantOrderId = 'ORD-' . $order->id . '-' . time();
    $paymentTransaction = PaymentTransaction::create([
        'user_id' => $user->id,
        'merchant_order_id' => $merchantOrderId,
        'type' => 'order',
        'amount' => $order->total_amount,
        'currency' => config('paypal.currency'),
        'status' => 'pending',
    ]);

    // Create PayPal order
    $paypalOrder = $this->paypalService->createOrder(
        $order->total_amount,
        config('paypal.currency'),
        'Order #' . $order->id,
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

        // Process order payment
        if ($transaction->type === 'order') {
            $this->processOrderPayment($transaction);
        }

        return redirect()->route('payment.success', [
            'id' => $transactionId,
            'merchant_order_id' => $transaction->merchant_order_id
        ]);
    }
}
```

### 4. Order Payment Processing

**Method**: `PaymentController::processOrderPayment()`

```php
protected function processOrderPayment(PaymentTransaction $transaction)
{
    DB::transaction(function () use ($transaction) {
        // Extract order ID from merchant_order_id (ORD-{id}-{timestamp})
        preg_match('/ORD-(\d+)-/', $transaction->merchant_order_id, $matches);
        $orderId = $matches[1];

        $order = Order::find($orderId);

        // Update order payment status
        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);
    });
}
```

## Database Tables Involved

### 1. `orders`

Stores all order information:

```
id | user_id | order_number | product_id | total_price | total_amount | status | payment_status
---|---------|--------------|------------|-------------|--------------|--------|---------------
1  | 5       | ORD-20241108 | 12         | 150.00      | 150.00       | proc.. | paid
```

### 2. `payment_transactions`

Stores all payment attempts:

```
id | user_id | merchant_order_id | paypal_order_id | type  | amount | status  | paid_at
---|---------|-------------------|-----------------|-------|--------|---------|--------
1  | 5       | ORD-1-1699123456  | 8AB12CD34EF56   | order | 150.00 | success | 2024...
```

## Order Status Flow

```
pending (payment_status: pending)
    ↓
    [User completes PayPal payment]
    ↓
processing (payment_status: paid)
    ↓
    [Admin/Seller places on AliExpress]
    ↓
placed (aliexpress_order_id set)
    ↓
    [Order ships from AliExpress]
    ↓
shipped (tracking_number set)
    ↓
    [Order delivered to customer]
    ↓
delivered
```

## Routes Summary

| Method | URL | Action | Description |
|--------|-----|--------|-------------|
| GET | `/orders/create` | `create()` | Show order form |
| POST | `/orders` | `store()` | Create order and redirect to payment |
| GET | `/payment/order/{id}` | `initiateOrderPayment()` | Create PayPal order |
| GET | `/payment/callback` | `callback()` | PayPal return URL |
| GET | `/payment/success` | `success()` | Success redirect |
| GET | `/payment/error` | `error()` | Error redirect |
| POST | `/orders/{id}/place-on-aliexpress` | `placeOnAliexpress()` | Place order on AliExpress |

## Key Changes from Previous Flow

### Before (Direct Order Creation)

```php
$order = Order::create([...]);
return redirect()->route('orders.show', $order)
    ->with('success', 'Order created successfully!');
```

### After (Payment Required)

```php
$order = Order::create([
    'status' => 'pending',
    'payment_status' => 'pending',
    'total_amount' => $totalPrice,
    // ...
]);

return redirect()->route('payment.order', $order)
    ->with('info', 'Order created! Please complete payment.');
```

## Order Model Updates

### New Fields

- `total_amount` (decimal): Amount to be charged (same as total_price)
- `payment_status` (enum): 'pending', 'paid', 'failed', 'refunded'

### Updated Methods

```php
// Can only place order on AliExpress after payment
public function canBePlaced(): bool
{
    return $this->status === 'pending' && $this->payment_status === 'paid';
}

// Get payment status badge color
public function getPaymentStatusBadgeColor(): string
{
    return match($this->payment_status) {
        'pending' => 'warning',
        'paid' => 'success',
        'failed' => 'danger',
        'refunded' => 'info',
        default => 'secondary',
    };
}
```

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
2. Go to `/orders/create`
3. Select a product and fill in order details
4. Click "Create Order"
5. You'll be redirected to PayPal Sandbox
6. Use PayPal sandbox test account to complete payment
7. You'll be redirected back to your app
8. Order should show `payment_status: 'paid'` and `status: 'processing'`
9. Now you can place the order on AliExpress

### 3. PayPal Sandbox Test Accounts

Create test accounts at: https://developer.paypal.com/dashboard/accounts

- **Buyer Account**: Use to make payments
- **Seller Account**: Receives payments (your app)

## Error Handling

### 1. Order Already Paid

```php
if ($order->payment_status === 'paid') {
    return redirect()->route('orders.show', $order)
        ->with('error', __('messages.order_already_paid'));
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

### 3. Unauthorized Access

```php
if ($order->user_id !== $user->id) {
    abort(403);
}
```

## Security Measures

1. **CSRF Protection**: All POST routes protected by Laravel's CSRF middleware
2. **User Authentication**: All routes require authentication
3. **Order Ownership**: Verify user owns the order before processing payment
4. **Transaction Verification**: PayPal order ID must match pending transaction
5. **Payment Capture**: Actual money capture happens in callback
6. **Database Transactions**: Order update wrapped in DB transaction

## Translation Keys

Add these to `lang/en/messages.php` and `lang/ar/messages.php`:

```php
'order_already_paid' => 'This order has already been paid.',
'payment_initiation_failed' => 'Failed to initiate payment. Please try again.',
'payment_successful' => 'Payment completed successfully!',
'payment_failed' => 'Payment failed. Please try again.',
'payment_processing_error' => 'Error processing payment. Please contact support.',
'transaction_not_found' => 'Transaction not found.',
```

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
- [ ] Run migration: `php artisan migrate`

## Troubleshooting

### Issue: User stuck after PayPal payment

**Solution**: Check logs for PayPal callback errors. Ensure `APP_URL` is correct.

### Issue: Order payment status not updated after payment

**Solution**: Check `payment_transactions` table. Verify callback was received and `processOrderPayment()` was executed.

### Issue: Cannot place order on AliExpress

**Solution**: Verify order has `payment_status: 'paid'`. Check `canBePlaced()` method logic.

### Issue: PayPal returns error

**Solution**: Verify credentials, check PayPal sandbox/live mode matches config.

## Support

For PayPal integration issues:
- PayPal Developer Docs: https://developer.paypal.com/docs/
- PayPal Sandbox: https://developer.paypal.com/dashboard/
