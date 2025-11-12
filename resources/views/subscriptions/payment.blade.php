@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Subscription Details Card -->
            <div class="card mb-4">
                <div class="card-header text-center" style="background: linear-gradient(135deg, {{ $subscription->color }} 0%, {{ $subscription->color }}dd 100%); color: white;">
                    <h4 class="mb-1">{{ __('messages.confirm_subscription') }}</h4>
                    <p class="mb-0">{{ __('messages.review_and_pay') }}</p>
                </div>

                <div class="card-body p-4">
                    <!-- Plan Details -->
                    <div class="row mb-4">
                        <div class="col-12 text-center mb-4">
                            <span class="badge mb-3" style="background-color: {{ $subscription->color }}; font-size: 1.3rem; padding: 10px 20px;">
                                {{ $subscription->localized_name }}
                            </span>
                            <h1 class="mb-1">${{ number_format($subscription->price, 2) }}</h1>
                            <p class="text-muted">{{ __('messages.per_month') }}</p>
                        </div>
                    </div>

                    <!-- Features List -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">{{ __('messages.included_features') }}</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="ri-check-line text-success fs-5 me-3"></i>
                                    <div>
                                        <strong>{{ $subscription->max_products }}</strong> {{ __('messages.max_products') }}
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="ri-check-line text-success fs-5 me-3"></i>
                                    <div>
                                        <strong>{{ $subscription->max_orders_per_month }}</strong> {{ __('messages.orders_per_month') }}
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="ri-check-line text-success fs-5 me-3"></i>
                                    <div>
                                        <strong>{{ $subscription->commission_rate }}%</strong> {{ __('messages.commission_rate') }}
                                    </div>
                                </li>
                                @if($subscription->priority_support)
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="ri-check-line text-success fs-5 me-3"></i>
                                    <div>{{ __('messages.priority_support') }}</div>
                                </li>
                                @endif
                                @if($subscription->analytics_access)
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="ri-check-line text-success fs-5 me-3"></i>
                                    <div>{{ __('messages.analytics_access') }}</div>
                                </li>
                                @endif
                                @if($subscription->bulk_import)
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="ri-check-line text-success fs-5 me-3"></i>
                                    <div>{{ __('messages.bulk_import') }}</div>
                                </li>
                                @endif
                                @if($subscription->api_access)
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="ri-check-line text-success fs-5 me-3"></i>
                                    <div>{{ __('messages.api_access') }}</div>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Billing Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="mb-3">{{ __('messages.billing_summary') }}</h5>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('messages.subscription_plan') }}:</span>
                                        <strong>{{ $subscription->localized_name }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('messages.duration') }}:</span>
                                        <strong>{{ $subscription->duration_days }} {{ __('messages.days') }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('messages.price') }}:</span>
                                        <strong>${{ number_format($subscription->price, 2) }}</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <h5 class="mb-0">{{ __('messages.total') }}:</h5>
                                        <h5 class="mb-0 text-primary">${{ number_format($subscription->price, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">{{ __('messages.select_payment_method') }}</h5>

                            <!-- PayPal Smart Payment Buttons Container -->
                            <div id="paypal-button-container" class="mb-4"></div>

                            <!-- Loading State -->
                            <div id="paypal-loading" class="text-center mb-3" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Processing payment...</p>
                            </div>

                            <!-- OR Divider -->
                            <div class="position-relative my-4">
                                <hr>
                                <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted">
                                    {{ __('messages.or') }}
                                </span>
                            </div>

                            <!-- Alternative: Wallet Payment (if available) -->
                            @if(auth()->user()->wallet && auth()->user()->wallet->balance >= $subscription->price)
                            <form action="{{ route('subscriptions.pay-with-wallet', $subscription) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-lg btn-outline-success w-100" onclick="return confirm('{{ __('messages.confirm_wallet_payment') }}')">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="ri-wallet-3-line fs-4 me-2"></i>
                                        <span class="fs-5">{{ __('messages.pay_with_wallet') }}</span>
                                        <span class="badge bg-success ms-2">${{ number_format(auth()->user()->wallet->balance, 2) }}</span>
                                    </div>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="ri-shield-check-line fs-4 me-3"></i>
                                <div>
                                    <strong>{{ __('messages.secure_payment') }}</strong>
                                    <p class="mb-0 small">{{ __('messages.payment_security_notice') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cancel Button -->
                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-link text-muted">
                                <i class="ri-arrow-left-line me-1"></i>
                                {{ __('messages.cancel_and_go_back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .list-group-item {
        border-left: none;
        border-right: none;
        padding: 15px 0;
    }

    .list-group-item:first-child {
        border-top: none;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    #paypal-button-container {
        min-height: 150px;
    }
</style>

<!-- PayPal SDK -->
@php
    $paypalMode = config('paypal.mode', 'sandbox');
    $paypalClientId = config("paypal.{$paypalMode}.client_id");
@endphp
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ config('paypal.currency', 'USD') }}"></script>

<script>
    // Check if PayPal SDK is loaded
    if (typeof paypal === 'undefined') {
        console.error('PayPal SDK failed to load. Please check your internet connection and PayPal credentials.');
        document.getElementById('paypal-button-container').innerHTML = '<div class="alert alert-danger">Failed to load PayPal. Please refresh the page or use wallet payment.</div>';
    } else {
        console.log('PayPal SDK loaded successfully');

        // Initialize PayPal Smart Payment Buttons
        paypal.Buttons({
            style: {
                layout: 'vertical',
                color: 'gold',
                shape: 'rect',
                label: 'paypal',
                height: 55
            },

            // Create order directly using PayPal SDK
            createOrder: function(data, actions) {
                console.log('Creating PayPal order...');

                return actions.order.create({
                    purchase_units: [{
                        description: '{{ $subscription->localized_name }} - Subscription Plan',
                        amount: {
                            currency_code: '{{ config("paypal.currency", "USD") }}',
                            value: '{{ number_format($subscription->price, 2, ".", "") }}'
                        },
                        custom_id: 'SUB-{{ $subscription->id }}-' + Date.now()
                    }],
                    application_context: {
                        shipping_preference: 'NO_SHIPPING'
                    }
                }).then(function(orderId) {
                    console.log('PayPal order created:', orderId);
                    return orderId;
                });
            },

            // Approve order - Capture payment
            onApprove: function(data, actions) {
                console.log('Payment approved:', data);
                document.getElementById('paypal-loading').style.display = 'block';

                // Capture the payment
                return actions.order.capture().then(function(details) {
                    console.log('Payment captured:', details);

                    // Send payment details to our backend
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    return fetch('{{ route("subscriptions.process-payment", $subscription) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            order_id: data.orderID,
                            payer_id: data.payerID,
                            details: details
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        document.getElementById('paypal-loading').style.display = 'none';

                        if (result.success) {
                            // Redirect to subscriptions page with success message
                            window.location.href = '{{ route("subscriptions.index") }}?payment=success';
                        } else {
                            throw new Error(result.message || 'Payment processing failed');
                        }
                    })
                    .catch(error => {
                        document.getElementById('paypal-loading').style.display = 'none';
                        console.error('Backend processing error:', error);
                        alert('Failed to process payment on our server. Please contact support if the amount was debited.');
                        throw error;
                    });
                });
            },

            // Handle errors
            onError: function(err) {
                console.error('PayPal Error:', err);
                document.getElementById('paypal-loading').style.display = 'none';
                alert('An error occurred with PayPal. Please try again or use an alternative payment method.');
            },

            // Handle cancellation
            onCancel: function(data) {
                document.getElementById('paypal-loading').style.display = 'none';
                console.log('Payment cancelled');
                alert('Payment was cancelled. You can try again when ready.');
            }
        }).render('#paypal-button-container')
        .then(function() {
            console.log('PayPal buttons rendered successfully');
        })
        .catch(function(error) {
            console.error('Failed to render PayPal buttons:', error);
            document.getElementById('paypal-button-container').innerHTML = '<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>Failed to load PayPal buttons. Please refresh the page or use wallet payment.</div>';
        });
    }
</script>
@endsection
