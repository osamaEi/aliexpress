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
                            <h1 class="mb-1">{{ number_format($subscription->price, 2) }} AED</h1>
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

                    <!-- Current Subscription Info (if upgrading) -->
                    @if($isUpgrade ?? false)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="mb-2">
                                    <i class="ri-information-line me-2"></i>{{ __('messages.upgrading_subscription') }}
                                </h6>
                                <p class="mb-2 small">
                                    {{ __('messages.current_plan') }}: <strong>{{ $currentSubscription->subscription->localized_name }}</strong>
                                </p>
                                <p class="mb-0 small">
                                    {{ __('messages.remaining_days') }}: <strong>{{ $remainingDays ?? 0 }} {{ __('messages.days') }}</strong>
                                    <br>
                                    <span class="text-success">{{ __('messages.remaining_days_will_be_added') }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

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
                                        <span>{{ __('messages.new_plan_duration') }}:</span>
                                        <strong>{{ $subscription->duration_days }} {{ __('messages.days') }}</strong>
                                    </div>
                                    @if($isUpgrade ?? false)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('messages.remaining_days_bonus') }}:</span>
                                        <strong class="text-success">+ {{ $remainingDays ?? 0 }} {{ __('messages.days') }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('messages.total_subscription_days') }}:</span>
                                        <strong class="text-primary">{{ $totalDays ?? $subscription->duration_days }} {{ __('messages.days') }}</strong>
                                    </div>
                                    @endif
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('messages.price') }}:</span>
                                        <strong>{{ number_format($subscription->price, 2) }} AED</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <h5 class="mb-0">{{ __('messages.total') }}:</h5>
                                        <h5 class="mb-0 text-primary">{{ number_format($subscription->price, 2) }} AED</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">{{ __('messages.select_payment_method') }}</h5>

                            <!-- Paymob Payment Button -->
                            <form action="{{ route('subscriptions.pay-with-paymob', $subscription) }}" method="POST" id="paymob-form">
                                @csrf
                                <button type="submit" class="btn btn-lg btn-primary w-100 mb-4" id="paymob-button">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="ri-bank-card-line fs-4 me-2"></i>
                                        <span class="fs-5">{{ __('messages.pay_with_card_paymob') }}</span>
                                    </div>
                                </button>
                            </form>

                            <!-- Loading State -->
                            <div id="payment-loading" class="text-center mb-3" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">{{ __('messages.loading') }}...</span>
                                </div>
                                <p class="mt-2 text-muted">{{ __('messages.processing_payment') }}...</p>
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

<script>
    // Show loading on Paymob form submission
    document.getElementById('paymob-form').addEventListener('submit', function() {
        document.getElementById('payment-loading').style.display = 'block';
        document.getElementById('paymob-button').disabled = true;
    });
</script>
@endsection
