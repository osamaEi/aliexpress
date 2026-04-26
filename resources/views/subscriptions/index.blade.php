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

    @php $isDistributor = ($userType === 'distributor'); @endphp
    <style>
        .plan-role-banner { background: <?php echo $isDistributor ? 'linear-gradient(135deg,#e8f5e9,#f1f8e9)' : 'linear-gradient(135deg,#e3f2fd,#f3e5f5)'; ?>; }
        .plan-role-circle { width:44px; height:44px; background: <?php echo $isDistributor ? '#4caf50' : '#1976d2'; ?>; }
    </style>

    <!-- Role banner -->
    <div class="alert border-0 mb-4 d-flex align-items-center gap-3 py-3 plan-role-banner">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 plan-role-circle">
            <i class="{{ $isDistributor ? 'ri-truck-line' : 'ri-store-line' }} text-white" style="font-size:1.3rem;"></i>
        </div>
        <div>
            <div class="fw-semibold" style="font-size:.95rem;">
                @if($isDistributor)
                    {{ app()->getLocale() == 'ar' ? 'خطط الاشتراك للموزعين' : 'Distributor Subscription Plans' }}
                @else
                    {{ app()->getLocale() == 'ar' ? 'خطط الاشتراك للتجار' : 'Seller Subscription Plans' }}
                @endif
            </div>
            <div class="text-muted" style="font-size:.83rem;">
                {{ app()->getLocale() == 'ar' ? 'الخطط المعروضة مخصصة لحسابك' : 'Plans shown are tailored to your account type' }}
            </div>
        </div>
    </div>

    <!-- Subscription Plans -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ __('messages.subscription_plans') }}</h5>
                <p class="text-muted mb-0">{{ __('messages.choose_best_plan') }}</p>
            </div>
            <a href="{{ route('subscriptions.history') }}" class="btn btn-outline-primary">
                <i class="ri-history-line me-1"></i>
                {{ app()->getLocale() == 'ar' ? 'عرض الاشتراكات' : 'View Subscriptions' }}
            </a>
        </div>

        <div class="card-body">
            @if($subscriptions->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="ri-inbox-line" style="font-size:3rem;"></i>
                <p class="mt-2">{{ app()->getLocale() == 'ar' ? 'لا توجد خطط متاحة لحسابك حالياً' : 'No plans available for your account type yet.' }}</p>
            </div>
            @else
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
                                @if($subscription->role !== 'both')
                                <div class="mb-1">
                                    <span class="badge {{ $subscription->role === 'distributor' ? 'bg-label-success' : 'bg-label-primary' }}" style="font-size:.75rem;">
                                        <i class="{{ $subscription->role === 'distributor' ? 'ri-truck-line' : 'ri-store-line' }} me-1"></i>
                                        {{ $subscription->localized_role }}
                                    </span>
                                </div>
                                @endif
                                @if($currentSubscription && $currentSubscription->subscription_id == $subscription->id)
                                    <div class="badge bg-success mb-2">
                                        <i class="ri-checkbox-circle-line me-1"></i>
                                        {{ __('messages.active_now') }}
                                    </div>
                                @endif
                                <h2 class="mb-0 d-inline-flex align-items-center gap-1" style="direction: ltr;"><x-session-currency-icon width="24" height="24" /> {{ number_format($currentCurrency->convertFrom($subscription->price, 'AED'), 2) }}</h2>
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
                                <img src="{{ asset('vector.png') }}" alt="Taif" style="height: 18px; vertical-align: middle;" class="{{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}">
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
                                <a href="{{ route('subscriptions.subscribe', $subscription) }}" class="btn btn-primary w-100">
                                    <i class="ri-secure-payment-line me-1"></i>
                                    {{ __('messages.subscribe_now') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
