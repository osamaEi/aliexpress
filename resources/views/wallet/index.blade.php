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

    @if(auth()->check() && auth()->user()->user_type === 'marketer')
        <div class="alert alert-info d-flex align-items-center gap-2 mb-4" style="border-radius:12px;">
            <i class="ri-hand-coin-line"></i>
            <span>{{ app()->getLocale() == 'ar' ? 'رصيدك يمثّل أرباح كوبونات المتاجر العالمية. يمكنك طلب سحبها في أي وقت.' : 'Your balance represents Global Stores coupon earnings. You can request a withdrawal anytime.' }}</span>
        </div>
    @endif

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

    @php $hasWithdrawalMethod = auth()->user()->withdrawalMethods()->exists(); @endphp

    {{-- Warn if the user has no saved withdrawal method --}}
    @unless($hasWithdrawalMethod)
    <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <span>
            <i class="ri-error-warning-line me-2"></i>
            {{ app()->getLocale()=='ar'
                ? 'لم تقم بإضافة أي طريقة سحب بعد. أضف طريقة سحب لتتمكن من سحب رصيدك.'
                : 'You have not added any withdrawal method yet. Add one to be able to withdraw your balance.' }}
        </span>
        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-warning">
            <i class="ri-add-line me-1"></i>{{ app()->getLocale()=='ar' ? 'إضافة طريقة سحب' : 'Add withdrawal method' }}
        </a>
    </div>
    @endunless

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

                <!-- Withdraw -->
                <div class="col-md-3">
                    @if($hasWithdrawalMethod)
                    <a href="{{ route('wallet.withdrawal.create') }}" class="btn btn-primary w-100">
                        <i class="ri-money-dollar-circle-line me-1"></i>
                        {{ __('messages.withdraw') }}
                    </a>
                    @else
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary w-100"
                       title="{{ app()->getLocale()=='ar' ? 'أضف طريقة سحب أولاً' : 'Add a withdrawal method first' }}">
                        <i class="ri-money-dollar-circle-line me-1"></i>
                        {{ __('messages.withdraw') }}
                    </a>
                    @endif
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
                        {{ __('messages.deposit') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        {{ __('messages.deposit_info') }}
                    </div>

                    <form action="{{ route('wallet.deposit.ziina') }}" method="POST" id="ziina-deposit-form">
                        @csrf
                        <!-- Amount Selection -->
                        <div class="mb-4">
                            <label for="deposit_amount" class="form-label">
                                {{ __('messages.amount_to_deposit') }}
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg" style="direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};">
                                @if(app()->getLocale() == 'ar')
                                    <input type="number"
                                           class="form-control"
                                           id="deposit_amount"
                                           name="amount"
                                           min="2"
                                           max="100000"
                                           step="0.01"
                                           required
                                           placeholder="0.00"
                                           style="text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};">
                                    <span class="input-group-text">{!! currency_symbol('AED', true) !!}</span>
                                @else
                                    <span class="input-group-text">{!! currency_symbol('AED', true) !!}</span>
                                    <input type="number"
                                           class="form-control"
                                           id="deposit_amount"
                                           name="amount"
                                           min="2"
                                           max="100000"
                                           step="0.01"
                                           required
                                           placeholder="0.00"
                                           style="text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};">
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ app()->getLocale() == 'ar' ? 'الحد الأدنى للإيداع:' : 'Minimum deposit:' }} 2 <x-session-currency-icon width="16" height="16" />
                            </small>
                        </div>

                  

                        <!-- Fee Breakdown -->
                        <div class="mb-4" id="fee-breakdown" style="display: none;">
                            <div class="card" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">
                                <div class="card-body">
                                    <!-- Amount to receive -->
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>{{ app()->getLocale() == 'ar' ? 'المبلغ المراد إيداعه' : 'Amount to deposit' }}:</span>
                                        <strong id="net-amount">0.00 {!! currency_symbol('AED', true) !!}</strong>
                                    </div>

                                    <!-- Gateway Fees -->
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>{{ app()->getLocale() == 'ar' ? 'رسوم البوابة' : 'Gateway fees' }}:</span>
                                        <strong class="text-warning" id="total-fee">0.00 {!! currency_symbol('AED', true) !!}</strong>
                                    </div>

                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-0">{{ app()->getLocale() == 'ar' ? 'المجموع الكلي' : 'Total amount' }}:</h6>
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
                        <p class="mb-0 small">{{ __('messages.secure') }}</p>
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
                                @php
                                    $txType = $transaction->transaction_type ?? '';
                                    $meta   = $transaction->metadata ?? [];
                                    // Normalise legacy records where full description was saved as transaction_type
                                    if (str_starts_with($txType, 'Order payment for order') || str_starts_with($txType, 'Payment for order')) {
                                        preg_match('/#([\w-]+)/', $txType, $m);
                                        $normType = 'order_payment';
                                        $txLabel  = __('messages.order_payment');
                                        $txDesc   = __('messages.order_payment_desc', ['number' => $m[1] ?? '']);
                                    } elseif (str_starts_with($txType, 'Subscription payment') || str_starts_with($txType, 'Payment for') && str_contains($txType, 'subscription')) {
                                        $normType = 'subscription_payment';
                                        $txLabel  = __('messages.subscription_payment');
                                        $txDesc   = __('messages.subscription_payment_desc', ['name' => $meta['subscription_name'] ?? '']);
                                    } elseif ($txType === 'order_payment') {
                                        $normType = $txType;
                                        $txLabel  = __('messages.order_payment');
                                        $num = $meta['order_number'] ?? ($meta['order_id'] ?? '');
                                        $txDesc = __('messages.order_payment_desc', ['number' => $num]);
                                    } elseif ($txType === 'subscription_payment') {
                                        $normType = $txType;
                                        $txLabel  = __('messages.subscription_payment');
                                        $txDesc   = __('messages.subscription_payment_desc', ['name' => $meta['subscription_name'] ?? '']);
                                    } elseif ($txType === 'admin_credit' || str_starts_with($txType, 'Admin credit')) {
                                        $normType = 'admin_credit';
                                        $txLabel  = __('messages.admin_credit');
                                        $note     = $transaction->description && $transaction->description !== 'Admin credit' ? ' — ' . $transaction->description : '';
                                        $txDesc   = __('messages.admin_credit_desc') . $note;
                                    } elseif ($txType === 'distributor_order_earning') {
                                        $normType = 'distributor_order_earning';
                                        $txLabel  = app()->getLocale() === 'ar' ? 'أرباح طلب' : 'Order Earning';
                                        $num      = $meta['order_id'] ?? '';
                                        $txDesc   = app()->getLocale() === 'ar'
                                            ? 'أرباح بيع منتج عبر طلب' . ($num ? ' #' . $num : '')
                                            : 'Earning from a product sold' . ($num ? ' (order #' . $num . ')' : '');
                                    } elseif ($txType === 'coupon_commission') {
                                        $normType = 'coupon_commission';
                                        $txLabel  = app()->getLocale() === 'ar' ? 'أرباح كوبونات المتاجر العالمية' : 'Global Stores Coupon Earning';
                                        $txDesc   = $transaction->description ?? '';
                                    } else {
                                        $normType = $txType;
                                        $txLabel  = ucfirst(str_replace('_', ' ', $txType));
                                        $txDesc   = $transaction->description ?? '';
                                    }
                                @endphp
                                <div>
                                    <strong>{{ $txLabel }}</strong>
                                    @if($txDesc)
                                        <div class="text-muted small">{{ $txDesc }}</div>
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

<!-- Hidden currency symbol template for JavaScript -->
<span id="currency-symbol-template" style="display: none;">{!! currency_symbol('AED', true) !!}</span>

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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        const currencySymbol = document.getElementById('currency-symbol-template')?.innerHTML || 'AED';

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

        // Also calculate on blur (when user leaves the field)
        amountInput.addEventListener('change', function() {
            const amount = parseFloat(this.value);
            if (amount >= 2) {
                calculateFees(amount);
            } else {
                feeBreakdown.style.display = 'none';
            }
        });

        // Function to calculate fees via AJAX
        function calculateFees(amount) {
            // Show loading state
            feeBreakdown.style.display = 'block';
            document.getElementById('net-amount').innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch('{{ route("wallet.deposit.calculate-fees") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ amount: parseFloat(amount) })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update fee breakdown with simplified information
                    document.getElementById('net-amount').innerHTML = data.data.net_amount.toFixed(2) + ' ' + currencySymbol;
                    document.getElementById('total-fee').innerHTML = data.data.fee.toFixed(2) + ' ' + currencySymbol;
                    document.getElementById('gross-amount').innerHTML = data.data.gross_amount.toFixed(2) + ' ' + currencySymbol;

                    // Show fee breakdown
                    feeBreakdown.style.display = 'block';
                } else {
                    console.error('Fee calculation failed:', data.message);
                    feeBreakdown.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error calculating fees:', error);
                // Fallback: Calculate fees locally
                calculateFeesLocally(amount);
            });
        }

        // Fallback function to calculate fees locally if AJAX fails
        function calculateFeesLocally(amount) {
            const fixedFee = 2.00;
            const percentageRate = 0.079;
            const percentageFee = amount * percentageRate;
            const totalFee = fixedFee + percentageFee;
            const grossAmount = amount + totalFee;

            document.getElementById('net-amount').innerHTML = amount.toFixed(2) + ' ' + currencySymbol;
            document.getElementById('total-fee').innerHTML = totalFee.toFixed(2) + ' ' + currencySymbol;
            document.getElementById('gross-amount').innerHTML = grossAmount.toFixed(2) + ' ' + currencySymbol;

            feeBreakdown.style.display = 'block';
        }

        // Handle form submission
        document.getElementById('ziina-deposit-form').addEventListener('submit', function() {
            document.getElementById('ziina-loading').style.display = 'block';
            document.getElementById('ziina-pay-button').disabled = true;
        });

        // Calculate fees on page load if there's already a value
        const initialAmount = parseFloat(amountInput.value);
        if (initialAmount >= 2) {
            calculateFees(initialAmount);
        }
    });
</script>
@endsection
