@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.my_wallet') }}</h4>
        <p class="text-muted">{{ __('messages.manage_wallet_balance') }}</p>
    </div>

    <!-- Wallet Balance Card -->
    <div class="row g-4 mb-4">
        <!-- Total Balance -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ri-wallet-3-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h5 class="mb-1">{{ $wallet->currency }} {{ number_format($wallet->balance, 2) }}</h5>
                        <small class="text-muted">{{ __('messages.total_balance') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Balance -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ri-money-dollar-circle-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h5 class="mb-1">{{ $wallet->currency }} {{ number_format($wallet->available_balance, 2) }}</h5>
                        <small class="text-muted">{{ __('messages.available_balance') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Balance -->
        <div class="col-md-4">
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
                        <h5 class="mb-1">{{ $wallet->currency }} {{ number_format($wallet->pending_balance, 2) }}</h5>
                        <small class="text-muted">{{ __('messages.pending_balance') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.quick_actions') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="{{ route('wallet.deposit') }}" class="btn btn-primary w-100">
                        <i class="ri-add-circle-line me-1"></i>
                        {{ __('messages.deposit') }}
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('wallet.withdrawal') }}" class="btn btn-outline-primary w-100">
                        <i class="ri-subtract-line me-1"></i>
                        {{ __('messages.withdrawal') }}
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('wallet.transfer') }}" class="btn btn-outline-primary w-100">
                        <i class="ri-exchange-line me-1"></i>
                        {{ __('messages.transfer') }}
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-secondary w-100">
                        <i class="ri-history-line me-1"></i>
                        {{ __('messages.transaction_history') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.recent_transactions') }}</h5>
            <a href="{{ route('wallet.transactions') }}" class="btn btn-sm btn-outline-primary">
                {{ __('messages.view_all') }}
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.date') }}</th>
                            <th>{{ __('messages.type') }}</th>
                            <th>{{ __('messages.description') }}</th>
                            <th>{{ __('messages.amount') }}</th>
                            <th>{{ __('messages.balance') }}</th>
                            <th>{{ __('messages.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <small>{{ $transaction->created_at->format('Y-m-d H:i') }}</small>
                            </td>
                            <td>
                                @if($transaction->type === 'credit')
                                    <span class="badge bg-success">{{ __('messages.credit') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('messages.debit') }}</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</strong>
                                    @if($transaction->description)
                                    <div class="text-muted small">{{ $transaction->description }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->formatted_amount }}
                                </span>
                            </td>
                            <td>{{ $transaction->currency }} {{ number_format($transaction->balance_after, 2) }}</td>
                            <td>
                                @if($transaction->status === 'completed')
                                    <span class="badge bg-success">{{ __('messages.completed') }}</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge bg-warning">{{ __('messages.pending') }}</span>
                                @elseif($transaction->status === 'failed')
                                    <span class="badge bg-danger">{{ __('messages.failed') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.cancelled') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                {{ __('messages.no_transactions_yet') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
