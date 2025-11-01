@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Current Subscription -->
    @if($currentSubscription)
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-2">
                        <span class="badge" style="background-color: {{ $currentSubscription->subscription->color }}">
                            {{ $currentSubscription->subscription->localized_name }}
                        </span>
                        {{ __('messages.current_plan') }}
                    </h5>
                    <p class="mb-1">{{ __('messages.expires_on') }}: {{ $currentSubscription->end_date->format('Y-m-d') }}</p>
                    <p class="mb-0 text-muted">{{ __('messages.days_remaining') }}: <strong>{{ $currentSubscription->days_remaining }}</strong></p>
                </div>
                <form method="POST" action="{{ route('subscriptions.cancel') }}" onsubmit="return confirm('{{ __('messages.confirm_cancel_subscription') }}')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="ri-close-circle-line me-1"></i>
                        {{ __('messages.cancel_subscription') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Subscription Plans -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.subscription_plans') }}</h5>
            <p class="text-muted mb-0">{{ __('messages.choose_best_plan') }}</p>
        </div>

        <div class="card-body">
            <div class="row g-4">
                @foreach($subscriptions as $subscription)
                <div class="col-md-4">
                    <div class="card border {{ $currentSubscription && $currentSubscription->subscription_id == $subscription->id ? 'border-primary' : '' }} h-100">
                        <div class="card-body">
                            <!-- Plan Name -->
                            <div class="text-center mb-4">
                                <span class="badge mb-2" style="background-color: {{ $subscription->color }}; font-size: 1.1rem;">
                                    {{ $subscription->localized_name }}
                                </span>
                                <h2 class="mb-0">${{ number_format($subscription->price, 2) }}</h2>
                                <small class="text-muted">{{ __('messages.per_month') }}</small>
                            </div>

                            <!-- Description -->
                            <p class="text-muted mb-4">{{ $subscription->localized_description }}</p>

                            <!-- Features -->
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    {{ $subscription->max_products }} {{ __('messages.max_products') }}
                                </li>
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    {{ $subscription->max_orders_per_month }} {{ __('messages.orders_per_month') }}
                                </li>
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    {{ $subscription->commission_rate }}% {{ __('messages.commission_rate') }}
                                </li>
                                @if($subscription->priority_support)
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    {{ __('messages.priority_support') }}
                                </li>
                                @endif
                                @if($subscription->analytics_access)
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    {{ __('messages.analytics_access') }}
                                </li>
                                @endif
                                @if($subscription->bulk_import)
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    {{ __('messages.bulk_import') }}
                                </li>
                                @endif
                                @if($subscription->api_access)
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    {{ __('messages.api_access') }}
                                </li>
                                @endif
                            </ul>

                            <!-- Action Button -->
                            @if($currentSubscription && $currentSubscription->subscription_id == $subscription->id)
                                <button class="btn btn-outline-secondary w-100" disabled>
                                    {{ __('messages.current_plan') }}
                                </button>
                            @elseif($currentSubscription)
                                <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-primary w-100">
                                    {{ __('messages.upgrade_plan') }}
                                </a>
                            @else
                                <form method="POST" action="{{ route('payment.subscription', $subscription) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-secure-payment-line me-1"></i>
                                        {{ __('messages.subscribe_now') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Subscription History Link -->
    <div class="text-center mt-4">
        <a href="{{ route('subscriptions.history') }}" class="btn btn-outline-secondary">
            <i class="ri-history-line me-1"></i>
            {{ __('messages.view_subscription_history') }}
        </a>
    </div>
</div>
@endsection
