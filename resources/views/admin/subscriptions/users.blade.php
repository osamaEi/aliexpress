@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.user_subscriptions') }}</h4>
        <p class="text-muted">{{ __('messages.manage_subscriptions') }}</p>
    </div>

    <!-- User Subscriptions Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.user_subscriptions') }}</h5>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="ri-arrow-left-line me-1"></i>
                {{ __('messages.back') }}
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.subscriber') }}</th>
                            <th>{{ __('messages.plan_name') }}</th>
                            <th>{{ __('messages.start_date') }}</th>
                            <th>{{ __('messages.end_date') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.amount_paid') }}</th>
                            <th>{{ __('messages.payment_method') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userSubscriptions as $userSubscription)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <strong>{{ $userSubscription->user->name }}</strong>
                                        <div class="text-muted small">{{ $userSubscription->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $userSubscription->subscription->color }}">
                                    {{ $userSubscription->subscription->localized_name }}
                                </span>
                            </td>
                            <td>{{ $userSubscription->start_date }}</td>
                            <td>
                                {{ $userSubscription->end_date }}
                                @if($userSubscription->status === 'active')
                                <div class="text-muted small">
                                    {{ $userSubscription->days_remaining }} {{ __('messages.days_remaining') }}
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($userSubscription->status === 'active')
                                    <span class="badge bg-success">{{ __('messages.active') }}</span>
                                @elseif($userSubscription->status === 'expired')
                                    <span class="badge bg-secondary">{{ __('messages.expired') }}</span>
                                @elseif($userSubscription->status === 'cancelled')
                                    <span class="badge bg-danger">{{ __('messages.cancelled') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1" style="direction: ltr;">
                                    <x-session-currency-icon width="16" height="16" />
                                    {{ number_format(convert_price($userSubscription->amount_paid), 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-label-info">
                                    {{ ucfirst($userSubscription->payment_method) }}
                                </span>
                            </td>
                            <td>
                                @if($userSubscription->status === 'active')
                                    <form method="POST" action="{{ route('admin.subscriptions.close', $userSubscription->id) }}" style="display: inline;" onsubmit="return confirm('{{ __('messages.confirm_close_subscription') }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="ri-close-line me-1"></i>
                                            {{ __('messages.close') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                {{ __('messages.no_subscriptions_yet') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($userSubscriptions->hasPages())
        <div class="card-footer">
            {{ $userSubscriptions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
