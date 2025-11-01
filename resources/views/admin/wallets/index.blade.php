@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.wallet_management') }}</h4>
        <p class="text-muted">{{ __('messages.manage_all_user_wallets') }}</p>
    </div>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ri-wallet-3-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h5 class="mb-1">{{ $stats['total_wallets'] }}</h5>
                        <small class="text-muted">{{ __('messages.total_wallets') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ri-money-dollar-circle-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h5 class="mb-1">AED {{ number_format($stats['total_balance'], 2) }}</h5>
                        <small class="text-muted">{{ __('messages.total_balance') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ri-time-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h5 class="mb-1">AED {{ number_format($stats['total_pending'], 2) }}</h5>
                        <small class="text-muted">{{ __('messages.total_pending') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ri-check-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h5 class="mb-1">{{ $stats['active_wallets'] }}</h5>
                        <small class="text-muted">{{ __('messages.active_wallets') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="{{ route('admin.wallets.withdrawals') }}" class="btn btn-warning w-100">
                        <i class="ri-subtract-line me-1"></i>
                        {{ __('messages.withdrawal_requests') }}
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('admin.wallets.transactions') }}" class="btn btn-outline-primary w-100">
                        <i class="ri-history-line me-1"></i>
                        {{ __('messages.all_transactions') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.wallets.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">{{ __('messages.search') }}</label>
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ __('messages.search_by_name_email') }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="user_type" class="form-label">{{ __('messages.user_type') }}</label>
                        <select class="form-select" id="user_type" name="user_type">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="admin" {{ request('user_type') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="seller" {{ request('user_type') === 'seller' ? 'selected' : '' }}>Seller</option>
                            <option value="customer" {{ request('user_type') === 'customer' ? 'selected' : '' }}>Customer</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-search-line me-1"></i>
                            {{ __('messages.filter') }}
                        </button>
                        <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-secondary">
                            {{ __('messages.reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Wallets Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.all_wallets') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.user') }}</th>
                            <th>{{ __('messages.user_type') }}</th>
                            <th>{{ __('messages.balance') }}</th>
                            <th>{{ __('messages.pending_balance') }}</th>
                            <th>{{ __('messages.available_balance') }}</th>
                            <th>{{ __('messages.last_activity') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wallets as $wallet)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $wallet->user->name }}</strong>
                                    <div class="text-muted small">{{ $wallet->user->email }}</div>
                                </div>
                            </td>
                            <td>
                                @if($wallet->user->user_type === 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @elseif($wallet->user->user_type === 'seller')
                                    <span class="badge bg-primary">Seller</span>
                                @else
                                    <span class="badge bg-secondary">Customer</span>
                                @endif
                            </td>
                            <td>AED {{ number_format($wallet->balance, 2) }}</td>
                            <td>AED {{ number_format($wallet->pending_balance, 2) }}</td>
                            <td>
                                <strong class="text-success">AED {{ number_format($wallet->available_balance, 2) }}</strong>
                            </td>
                            <td>
                                @if($wallet->last_transaction_at)
                                    <small>{{ $wallet->last_transaction_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($wallet->is_active)
                                    <span class="badge bg-success">{{ __('messages.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.wallets.show', $wallet) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-eye-line"></i>
                                    {{ __('messages.view') }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                {{ __('messages.no_wallets_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($wallets->hasPages())
        <div class="card-footer">
            {{ $wallets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
