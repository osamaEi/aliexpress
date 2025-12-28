@extends('dashboard')

@section('content')
@php
    // Convert wallet balances to selected currency
    $balanceConverted = $currentCurrency->convertFrom($wallet->balance, 'AED');
    $availableBalanceConverted = $currentCurrency->convertFrom($wallet->available_balance, 'AED');
    $pendingBalanceConverted = $currentCurrency->convertFrom($wallet->pending_balance, 'AED');
@endphp
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
                        <h5 class="mb-1">{!! $currentCurrency->format($balanceConverted) !!}</h5>
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
                        <h5 class="mb-1">{!! $currentCurrency->format($availableBalanceConverted) !!}</h5>
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
                        <h5 class="mb-1">{!! $currentCurrency->format($pendingBalanceConverted) !!}</h5>
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
                <!-- Load Balance with Ziina -->
                <div class="col-md-3">
                    <button type="button" class="btn w-100" data-bs-toggle="modal" data-bs-target="#loadBalanceModal" style="background-color: #561C04; color: white;">
                        <i class="ri-secure-payment-line me-1"></i>
                        {{ __('messages.load_balance') }}
                    </button>
                </div>

                <!-- Withdraw to PayPal -->
                <div class="col-md-3">
                    <a href="{{ route('wallet.withdrawal.create') }}" class="btn btn-primary w-100">
                        <i class="ri-money-dollar-circle-line me-1"></i>
                        {{ __('messages.withdraw') }}
                    </a>
                </div>

                <!-- Withdrawal History -->
                <div class="col-md-3">
                    <a href="{{ route('wallet.withdrawal.history') }}" class="btn btn-outline-primary w-100">
                        <i class="ri-history-line me-1"></i>
                        {{ __('messages.withdrawal_history') }}
                    </a>
                </div>

                <!-- Transaction History -->
                <div class="col-md-3">
                    <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-secondary w-100">
                        <i class="ri-file-list-3-line me-1"></i>
                        {{ __('messages.all_transactions') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Load Balance Modal -->
    <div class="modal fade" id="loadBalanceModal" tabindex="-1" aria-labelledby="loadBalanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #561C04;">
                    <h5 class="modal-title" id="loadBalanceModalLabel">
                        <i class="ri-secure-payment-line me-2"></i>
                        {{ __('messages.deposit_with_ziina') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        {{ __('messages.ziina_deposit_info') }}
                    </div>

                    <form action="{{ route('wallet.deposit.ziina') }}" method="POST" id="ziina-deposit-form">
                        @csrf
                        <!-- Amount Selection -->
                        <div class="mb-4">
                            <label for="deposit_amount" class="form-label">
                                {{ __('messages.amount_to_deposit') }}
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">{!! currency_symbol('AED', true) !!}</span>
                                <input type="number"
                                       class="form-control"
                                       id="deposit_amount"
                                       name="amount"
                                       min="2"
                                       max="100000"
                                       step="0.01"
                                       value="50"
                                       required
                                       placeholder="0.00">
                            </div>
                            <small class="text-muted">
                                {{ __('messages.minimum_deposit_aed') }}
                            </small>
                        </div>

                        <!-- Quick Amount Buttons -->
                        <div class="mb-4">
                            <label class="form-label">{{ __('messages.quick_amounts') }}</label>
                            <div class="row g-2">
                                <div class="col-3">
                                    <button type="button" class="btn btn-outline-primary w-100 quick-amount" data-amount="50">50</button>
                                </div>
                                <div class="col-3">
                                    <button type="button" class="btn btn-outline-primary w-100 quick-amount" data-amount="100">100</button>
                                </div>
                                <div class="col-3">
                                    <button type="button" class="btn btn-outline-primary w-100 quick-amount" data-amount="200">200</button>
                                </div>
                                <div class="col-3">
                                    <button type="button" class="btn btn-outline-primary w-100 quick-amount" data-amount="500">500</button>
                                </div>
                            </div>
                        </div>

                        <!-- Fee Breakdown -->
                        <div class="mb-4" id="fee-breakdown" style="display: none;">
                            <div class="card" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">
                                <div class="card-body">
                                    <h6 class="mb-3">
                                        <i class="ri-calculator-line me-1"></i>{{ __('messages.fee_breakdown') }}
                                    </h6>

                                    <!-- Amount to receive -->
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('messages.amount_to_deposit') }}:</span>
                                        <strong id="net-amount">0.00 {!! currency_symbol('AED', true) !!}</strong>
                                    </div>

                                    <!-- Gateway Fees Details -->
                                    <div class="mb-3 p-3" style="background-color: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                                        <h6 class="mb-2 text-warning small">
                                            <i class="ri-information-line me-1"></i>{{ __('messages.total_gateway_fees') }}
                                        </h6>
                                        <div class="d-flex justify-content-between mb-1 small">
                                            <span class="text-muted">{{ __('messages.fixed_gateway_fee') }}:</span>
                                            <span id="fixed-fee">0.00 {!! currency_symbol('AED', true) !!}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1 small">
                                            <span class="text-muted">{{ __('messages.percentage_gateway_fee') }} (7.9%):</span>
                                            <span id="percentage-fee">0.00 {!! currency_symbol('AED', true) !!}</span>
                                        </div>
                                        <div class="d-flex justify-content-between pt-2 border-top">
                                            <strong class="small">{{ __('messages.total_gateway_fees') }}:</strong>
                                            <strong class="text-warning small" id="total-fee">0.00 {!! currency_symbol('AED', true) !!}</strong>
                                        </div>
                                        <p class="mb-0 mt-2 small text-muted">
                                            <i class="ri-shield-check-line me-1"></i>{{ __('messages.gateway_fee_note') }}
                                        </p>
                                    </div>

                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-0">{{ __('messages.final_amount_to_pay') }}:</h6>
                                        <h6 class="mb-0 text-primary" id="gross-amount">0.00 {!! currency_symbol('AED', true) !!}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-lg text-white" style="background-color: #561C04;" id="ziina-pay-button">
                                <i class="ri-secure-payment-line me-2"></i>
                                {{ __('messages.proceed_to_payment') }}
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div id="ziina-loading" class="text-center mt-3" style="display: none;">
                            <div class="spinner-border" style="color: #561C04;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">{{ __('messages.processing_payment') }}...</p>
                        </div>
                    </form>

                    <!-- Security Notice -->
                    <div class="alert alert-success mt-4 mb-0">
                        <h6 class="alert-heading">
                            <i class="ri-shield-check-line me-2"></i>
                            {{ __('messages.secure_payment') }}
                        </h6>
                        <p class="mb-0 small">{{ __('messages.ziina_secure_notice') }}</p>
                    </div>
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
                                @php
                                    $amountConverted = $currentCurrency->convertFrom(abs($transaction->amount), 'AED');
                                @endphp
                                <span class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {!! ($transaction->type === 'credit' ? '+' : '-') . ' ' . $currentCurrency->format($amountConverted) !!}
                                </span>
                            </td>
                            <td>
                                @php
                                    $balanceAfterConverted = $currentCurrency->convertFrom($transaction->balance_after, 'AED');
                                @endphp
                                {!! $currentCurrency->format($balanceAfterConverted) !!}
                            </td>
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

<style>
    .quick-amount.active {
        background-color: var(--bs-primary);
        color: white;
        border-color: var(--bs-primary);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    /* AED Currency SVG Icon Styling */
    svg.inline-block {
        display: inline-block;
        vertical-align: middle;
        margin: 0 4px;
    }

    [dir="rtl"] svg.inline-block {
        margin: 0 4px 0 0;
    }

    [dir="ltr"] svg.inline-block {
        margin: 0 0 0 4px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quickAmountButtons = document.querySelectorAll('.quick-amount');
        const amountInput = document.getElementById('deposit_amount');
        const feeBreakdown = document.getElementById('fee-breakdown');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Quick amount buttons functionality
        quickAmountButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                quickAmountButtons.forEach(btn => btn.classList.remove('active'));

                // Add active class to clicked button
                this.classList.add('active');

                // Set the amount in the input field
                const amount = this.getAttribute('data-amount');
                amountInput.value = amount;

                // Calculate fees
                calculateFees(amount);
            });
        });

        // Calculate fees when amount changes
        amountInput.addEventListener('input', function() {
            const amount = parseFloat(this.value);
            if (amount >= 2) {
                calculateFees(amount);
            } else {
                feeBreakdown.style.display = 'none';
            }
        });

        // Function to calculate fees via AJAX
        function calculateFees(amount) {
            fetch('{{ route("wallet.deposit.calculate-fees") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ amount: amount })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update fee breakdown with detailed information
                    document.getElementById('net-amount').innerHTML = data.data.net_amount.toFixed(2) + ' {!! currency_symbol("AED", true) !!}';
                    document.getElementById('fixed-fee').innerHTML = data.data.fixed_fee.toFixed(2) + ' {!! currency_symbol("AED", true) !!}';
                    document.getElementById('percentage-fee').innerHTML = data.data.percentage_fee.toFixed(2) + ' {!! currency_symbol("AED", true) !!}';
                    document.getElementById('total-fee').innerHTML = data.data.fee.toFixed(2) + ' {!! currency_symbol("AED", true) !!}';
                    document.getElementById('gross-amount').innerHTML = data.data.gross_amount.toFixed(2) + ' {!! currency_symbol("AED", true) !!}';

                    // Show fee breakdown
                    feeBreakdown.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error calculating fees:', error);
            });
        }

        // Handle form submission
        document.getElementById('ziina-deposit-form').addEventListener('submit', function() {
            document.getElementById('ziina-loading').style.display = 'block';
            document.getElementById('ziina-pay-button').disabled = true;
        });
    });
</script>
@endsection
