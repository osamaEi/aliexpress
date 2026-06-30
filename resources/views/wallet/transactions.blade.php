@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">
                    <i class="ri-file-list-3-line me-2"></i>
                    {{ __('messages.all_transactions') }}
                </h4>
                <p class="text-muted mb-0 mt-2">{{ __('messages.view_all_wallet_transactions') }}</p>
            </div>
            <a href="{{ route('wallet.index') }}" class="btn btn-primary">
                <i class="ri-arrow-left-line me-1"></i>
                {{ __('messages.back_to_wallet') }}
            </a>
        </div>
    </div>

    <!-- Commission Statistics (for sellers only) -->
    @if(auth()->user()->user_type === 'seller')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card" style="border-left: 4px solid #561C04;">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('messages.total_commissions') }}</h6>
                            <h3 class="mb-0 d-inline-flex align-items-center gap-1" style="color: #561C04; direction: ltr;"><x-session-currency-icon width="20" height="20" /> {{ number_format($currentCurrency->convertFrom($commissionStats['total'] ?? 0, 'AED'), 2) }}</h3>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded" style="background-color: rgba(86, 28, 4, 0.1); color: #561C04;">
                                <x-session-currency-icon width="24" height="24" />
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card" style="border-left: 4px solid #28a745;">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('messages.paid_commissions') }}</h6>
                            <h3 class="mb-0 text-success d-inline-flex align-items-center gap-1" style="direction: ltr;"><x-session-currency-icon width="20" height="20" /> {{ number_format($currentCurrency->convertFrom($commissionStats['paid'] ?? 0, 'AED'), 2) }}</h3>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ri-checkbox-circle-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card" style="border-left: 4px solid #ffc107;">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('messages.unpaid_commissions') }}</h6>
                            <h3 class="mb-0 text-warning d-inline-flex align-items-center gap-1" style="direction: ltr;"><x-session-currency-icon width="20" height="20" /> {{ number_format($currentCurrency->convertFrom($commissionStats['unpaid'] ?? 0, 'AED'), 2) }}</h3>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ri-time-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Options -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('wallet.transactions') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="type_filter" class="form-label">{{ __('messages.transaction_type') }}</label>
                    <select name="type" id="type_filter" class="form-select">
                        <option value="">{{ __('messages.show_all') }}</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>{{ __('messages.credit_only') }}</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>{{ __('messages.debit_only') }}</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="transaction_type_filter" class="form-label">{{ __('messages.category') }}</label>
                    <select name="transaction_type" id="transaction_type_filter" class="form-select">
                        <option value="">{{ __('messages.show_all') }}</option>
                        <option value="deposit" {{ request('transaction_type') === 'deposit' ? 'selected' : '' }}>{{ __('messages.deposit') }}</option>
                        <option value="withdrawal" {{ request('transaction_type') === 'withdrawal' ? 'selected' : '' }}>{{ __('messages.withdrawal') }}</option>
                        <option value="commission" {{ request('transaction_type') === 'commission' ? 'selected' : '' }}>{{ __('messages.commission') }}</option>
                        <option value="order_payment" {{ request('transaction_type') === 'order_payment' ? 'selected' : '' }}>{{ __('messages.order_payment') }}</option>
                        <option value="distributor_order_earning" {{ request('transaction_type') === 'distributor_order_earning' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'أرباح الطلبات' : 'Order Earnings' }}</option>
                        <option value="coupon_commission" {{ request('transaction_type') === 'coupon_commission' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'أرباح الكوبونات' : 'Coupon Earnings' }}</option>
                        <option value="subscription_payment" {{ request('transaction_type') === 'subscription_payment' ? 'selected' : '' }}>{{ __('messages.subscription_payment') }}</option>
                        <option value="refund" {{ request('transaction_type') === 'refund' ? 'selected' : '' }}>{{ __('messages.refund') }}</option>
                        <option value="transfer_in" {{ request('transaction_type') === 'transfer_in' ? 'selected' : '' }}>{{ __('messages.transfer_in') }}</option>
                        <option value="transfer_out" {{ request('transaction_type') === 'transfer_out' ? 'selected' : '' }}>{{ __('messages.transfer_out') }}</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="status_filter" class="form-label">{{ __('messages.status') }}</label>
                    <select name="status" id="status_filter" class="form-select">
                        <option value="">{{ __('messages.show_all') }}</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('messages.failed') }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="from_date" class="form-label">{{ __('messages.from_date') }}</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>

                <div class="col-md-2">
                    <label for="to_date" class="form-label">{{ __('messages.to_date') }}</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-filter-line me-1"></i>
                        {{ __('messages.apply_filters') }}
                    </button>
                    <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-secondary">
                        <i class="ri-refresh-line me-1"></i>
                        {{ __('messages.reset_filters') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-body">
            @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('messages.date') }}</th>
                            <th>{{ __('messages.type') }}</th>
                            <th>{{ __('messages.category') }}</th>
                            <th>{{ __('messages.description') }}</th>
                            <th class="text-end">{{ __('messages.amount') }}</th>
                            <th class="text-end">{{ __('messages.balance_after') }}</th>
                            <th>{{ __('messages.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <div>{{ $transaction->created_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                @if($transaction->type === 'credit')
                                    <span class="badge bg-success">
                                        <i class="ri-arrow-down-line me-1"></i>
                                        {{ __('messages.credit') }}
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="ri-arrow-up-line me-1"></i>
                                        {{ __('messages.debit') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $rawTxType = $transaction->transaction_type ?? '';
                                    if (str_starts_with($rawTxType, 'Order payment') || str_starts_with($rawTxType, 'Payment for order')) {
                                        $badgeKey = 'order_payment';
                                    } elseif (str_starts_with($rawTxType, 'Subscription payment') || str_starts_with($rawTxType, 'Payment for') && str_contains($rawTxType, 'subscription')) {
                                        $badgeKey = 'subscription_payment';
                                    } elseif (str_starts_with($rawTxType, 'Admin credit')) {
                                        $badgeKey = 'admin_credit';
                                    } else {
                                        $badgeKey = $rawTxType;
                                    }
                                    $badgeText = $badgeKey === 'distributor_order_earning'
                                        ? (app()->getLocale() === 'ar' ? 'أرباح طلب' : 'Order Earning')
                                        : ($badgeKey === 'coupon_commission'
                                            ? (app()->getLocale() === 'ar' ? 'أرباح كوبونات' : 'Coupon Earning')
                                            : (__('messages.' . $badgeKey, [], null) ?? ucfirst(str_replace('_', ' ', $badgeKey))));
                                @endphp
                                <span class="badge bg-info">
                                    {{ $badgeText }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $txType = $transaction->transaction_type ?? '';
                                    $meta   = $transaction->metadata ?? [];
                                    if (str_starts_with($txType, 'Order payment for order') || str_starts_with($txType, 'Payment for order')) {
                                        preg_match('/#([\w-]+)/', $txType, $m);
                                        $txDesc = __('messages.order_payment_desc', ['number' => $m[1] ?? '']);
                                    } elseif (str_starts_with($txType, 'Subscription payment') || (str_starts_with($txType, 'Payment for') && str_contains($txType, 'subscription'))) {
                                        $txDesc = __('messages.subscription_payment_desc', ['name' => $meta['subscription_name'] ?? '']);
                                    } elseif ($txType === 'order_payment') {
                                        $num    = $meta['order_number'] ?? ($meta['order_id'] ?? '');
                                        $txDesc = __('messages.order_payment_desc', ['number' => $num]);
                                    } elseif ($txType === 'subscription_payment') {
                                        $txDesc = __('messages.subscription_payment_desc', ['name' => $meta['subscription_name'] ?? '']);
                                    } elseif ($txType === 'admin_credit' || str_starts_with($txType, 'Admin credit')) {
                                        $note   = $transaction->description && $transaction->description !== 'Admin credit' ? ' — ' . $transaction->description : '';
                                        $txDesc = __('messages.admin_credit_desc') . $note;
                                    } elseif ($txType === 'distributor_order_earning') {
                                        $num    = $meta['order_id'] ?? '';
                                        $txDesc = app()->getLocale() === 'ar'
                                            ? 'أرباح بيع منتج عبر طلب' . ($num ? ' #' . $num : '')
                                            : 'Earning from a product sold' . ($num ? ' (order #' . $num . ')' : '');
                                    } else {
                                        $txDesc = $transaction->description ?? '';
                                    }
                                @endphp
                                <div>{{ $txDesc }}</div>
                            </td>
                            <td class="text-end" style="direction: ltr;">
                                <strong class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }} d-inline-flex align-items-center gap-1">
                                    {{ $transaction->type === 'credit' ? '+' : '-' }} <x-session-currency-icon width="14" height="14" /> {{ number_format($currentCurrency->convertFrom(abs($transaction->amount), 'AED'), 2) }}
                                </strong>
                            </td>
                            <td class="text-end" style="direction: ltr;">
                                <span class="d-inline-flex align-items-center gap-1"><x-session-currency-icon width="14" height="14" /> {{ number_format($currentCurrency->convertFrom($transaction->balance_after, 'AED'), 2) }}</span>
                            </td>
                            <td>
                                @if($transaction->status === 'completed')
                                    <span class="badge bg-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>
                                        {{ __('messages.completed') }}
                                    </span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge bg-warning">
                                        <i class="ri-time-line me-1"></i>
                                        {{ __('messages.pending') }}
                                    </span>
                                @elseif($transaction->status === 'failed')
                                    <span class="badge bg-danger">
                                        <i class="ri-close-circle-line me-1"></i>
                                        {{ __('messages.failed') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="ri-indeterminate-circle-line me-1"></i>
                                        {{ __('messages.cancelled') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $transactions->links() }}
            </div>
            @endif
            @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="ri-inbox-line" style="font-size: 4rem; color: #ccc;"></i>
                <h5 class="mt-3 text-muted">{{ __('messages.no_transactions_found') }}</h5>
                <p class="text-muted">{{ __('messages.try_different_filters') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
