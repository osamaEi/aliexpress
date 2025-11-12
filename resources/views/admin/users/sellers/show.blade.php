@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.seller_details') }}</h4>
        <p class="text-muted">{{ __('messages.view_seller_information') }}</p>
    </div>

    <!-- Seller Info Card -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.seller_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.name') }}:</div>
                        <div class="col-md-8">{{ $seller->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.email') }}:</div>
                        <div class="col-md-8">{{ $seller->email }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.company_name') }}:</div>
                        <div class="col-md-8">{{ $seller->company_name ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.country') }}:</div>
                        <div class="col-md-8">{{ $seller->country ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.main_activity') }}:</div>
                        <div class="col-md-8">{{ $seller->main_activity ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.sub_activity') }}:</div>
                        <div class="col-md-8">{{ $seller->sub_activity ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.verification_status') }}:</div>
                        <div class="col-md-8">
                            @if($seller->is_verified)
                                <span class="badge bg-success">{{ __('messages.verified') }}</span>
                            @else
                                <span class="badge bg-warning">{{ __('messages.unverified') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.registered_date') }}:</div>
                        <div class="col-md-8">{{ $seller->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Wallet Info -->
            @if($seller->wallet)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.wallet_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.balance') }}:</div>
                        <div class="col-md-8">{{ number_format($seller->wallet->balance, 2) }} {{ $seller->wallet->currency }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.pending_balance') }}:</div>
                        <div class="col-md-8">{{ number_format($seller->wallet->pending_balance, 2) }} {{ $seller->wallet->currency }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.wallet_status') }}:</div>
                        <div class="col-md-8">
                            @if($seller->wallet->is_active)
                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('messages.inactive') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.quick_actions') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sellers.update-verification', $seller) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_verified" value="{{ $seller->is_verified ? 0 : 1 }}">
                        <button type="submit" class="btn btn-{{ $seller->is_verified ? 'warning' : 'success' }} w-100">
                            <i class="ri-{{ $seller->is_verified ? 'close' : 'check' }}-line me-1"></i>
                            {{ $seller->is_verified ? __('messages.unverify_seller') : __('messages.verify_seller') }}
                        </button>
                    </form>

                    @if($seller->wallet)
                    <a href="{{ route('admin.wallets.show', $seller->wallet) }}" class="btn btn-info w-100 mb-3">
                        <i class="ri-wallet-line me-1"></i>
                        {{ __('messages.manage_wallet') }}
                    </a>
                    @endif

                    <form action="{{ route('admin.sellers.destroy', $seller) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="ri-delete-bin-line me-1"></i>
                            {{ __('messages.delete_seller') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.statistics') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">{{ __('messages.total_subscriptions') }}</small>
                        <h4>{{ $seller->subscriptions->count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">{{ __('messages.assigned_products') }}</small>
                        <h4>{{ $seller->assignedProducts->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscriptions History -->
    @if($seller->subscriptions->count() > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.subscription_history') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.subscription') }}</th>
                            <th>{{ __('messages.start_date') }}</th>
                            <th>{{ __('messages.end_date') }}</th>
                            <th>{{ __('messages.price') }}</th>
                            <th>{{ __('messages.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($seller->subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->subscription->name ?? '-' }}</td>
                            <td>{{ $subscription->start_date }}</td>
                            <td>{{ $subscription->end_date }}</td>
                            <td>{{ number_format($subscription->price, 2) }} {{ $subscription->currency }}</td>
                            <td>
                                <span class="badge bg-{{ $subscription->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $subscription->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('admin.sellers.index') }}" class="btn btn-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            {{ __('messages.back_to_sellers') }}
        </a>
    </div>
</div>
@endsection
