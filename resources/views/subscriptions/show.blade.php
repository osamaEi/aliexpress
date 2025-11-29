@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="ri-arrow-left-line me-1"></i>
            {{ __('messages.back') }}
        </a>
        <h4 class="mb-1">{{ __('messages.subscription_details') }}</h4>
        <p class="text-muted">{{ __('messages.review_and_pay') }}</p>
    </div>

    <div class="row">
        <!-- Subscription Plan Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.plan_details') }}</h5>
                </div>
                <div class="card-body">
                    <!-- Plan Name -->
                    <div class="text-center mb-4">
                        <span class="badge mb-3" style="background-color: {{ $subscription->color }}; font-size: 1.5rem; padding: 12px 24px;">
                            {{ $subscription->localized_name }}
                        </span>
                        <h2 class="mb-0">{{ number_format($subscription->price, 2) }} AED</h2>
                        <small class="text-muted">{{ __('messages.per_month') }}</small>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <h6>{{ __('messages.description') }}</h6>
                        <p class="text-muted">{{ $subscription->localized_description }}</p>
                    </div>

                    <!-- Plan Features -->
                    <div class="mb-4">
                        <h6 class="mb-3">{{ __('messages.plan_features') }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="ri-check-line text-success me-2"></i>
                                        <strong>{{ $subscription->max_products }}</strong> {{ __('messages.max_products') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="ri-check-line text-success me-2"></i>
                                        <strong>{{ $subscription->max_orders_per_month }}</strong> {{ __('messages.orders_per_month') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="ri-check-line text-success me-2"></i>
                                        <strong>{{ $subscription->commission_rate }}%</strong> {{ __('messages.commission_rate') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="ri-check-line text-success me-2"></i>
                                        <strong>{{ $subscription->duration_days }}</strong> {{ __('messages.duration_days') }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="ri-{{ $subscription->priority_support ? 'check' : 'close' }}-line {{ $subscription->priority_support ? 'text-success' : 'text-muted' }} me-2"></i>
                                        {{ __('messages.priority_support') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="ri-{{ $subscription->analytics_access ? 'check' : 'close' }}-line {{ $subscription->analytics_access ? 'text-success' : 'text-muted' }} me-2"></i>
                                        {{ __('messages.analytics_access') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="ri-{{ $subscription->bulk_import ? 'check' : 'close' }}-line {{ $subscription->bulk_import ? 'text-success' : 'text-muted' }} me-2"></i>
                                        {{ __('messages.bulk_import') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="ri-{{ $subscription->api_access ? 'check' : 'close' }}-line {{ $subscription->api_access ? 'text-success' : 'text-muted' }} me-2"></i>
                                        {{ __('messages.api_access') }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Plan & Upgrade -->
        <div class="col-lg-4">
            <!-- Current Subscription -->
            @if($currentSubscription)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('messages.current_subscription') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge" style="background-color: {{ $currentSubscription->subscription->color }}">
                            {{ $currentSubscription->subscription->localized_name }}
                        </span>
                    </div>
                    <p class="mb-2 small">
                        <i class="ri-calendar-line me-1"></i>
                        {{ __('messages.expires_on') }}: <strong>{{ $currentSubscription->end_date->format('Y-m-d') }}</strong>
                    </p>
                    <p class="mb-0 small text-muted">
                        <i class="ri-time-line me-1"></i>
                        {{ __('messages.days_remaining') }}: <strong>{{ $currentSubscription->days_remaining }}</strong>
                    </p>
                </div>
            </div>
            @endif

            <!-- Subscribe Button -->
            <div class="card">
                <div class="card-body text-center">
                    @if($currentSubscription && $currentSubscription->subscription_id == $subscription->id)
                        <div class="alert alert-info mb-0">
                            <i class="ri-information-line me-2"></i>
                            {{ __('messages.already_have_active_subscription') }}
                        </div>
                    @else
                        <h6 class="mb-3">{{ __('messages.payment_amount') }}</h6>
                        <h3 class="mb-4">{{ number_format($subscription->price, 2) }} AED</h3>

                        <a href="{{ route('subscriptions.subscribe', $subscription) }}" class="btn btn-success btn-lg w-100">
                            <i class="ri-secure-payment-line me-2"></i>
                            @if($currentSubscription)
                                {{ __('messages.upgrade_plan') }}
                            @else
                                {{ __('messages.subscribe_now') }}
                            @endif
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
