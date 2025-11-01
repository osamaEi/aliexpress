@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.subscription_management') }}</h4>
        <p class="text-muted">{{ __('messages.manage_subscriptions') }}</p>
    </div>

    <!-- Subscription Plans -->
    <div class="row g-4 mb-4">
        @foreach($subscriptions as $subscription)
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <!-- Plan Header -->
                    <div class="text-center mb-4">
                        <span class="badge mb-2" style="background-color: {{ $subscription->color }}; font-size: 1.1rem;">
                            {{ $subscription->localized_name }}
                        </span>
                        <h2 class="mb-0">${{ number_format($subscription->price, 2) }}</h2>
                        <small class="text-muted">{{ __('messages.per_month') }}</small>
                    </div>

                    <!-- Statistics -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('messages.total_subscriptions') }}:</span>
                            <strong>{{ $subscription->user_subscriptions_count }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('messages.active_users') }}:</span>
                            <strong class="text-success">{{ $subscription->active_subscriptions_count }}</strong>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="mb-4">
                        <h6 class="mb-3">{{ __('messages.plan_features') }}</h6>
                        <ul class="list-unstyled">
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
                    </div>

                    <!-- Action Button -->
                    <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-primary w-100">
                        <i class="ri-edit-line me-1"></i>
                        {{ __('messages.edit') }}
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- View User Subscriptions Link -->
    <div class="text-center">
        <a href="{{ route('admin.subscriptions.users') }}" class="btn btn-outline-primary">
            <i class="ri-user-line me-1"></i>
            {{ __('messages.user_subscriptions') }}
        </a>
    </div>
</div>
@endsection
