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
                        @php
                            $convertedBalance = $currentCurrency->convertFrom($wallet->balance, $wallet->currency);
                        @endphp
                        <h5 class="mb-1">{{ $currentCurrency->format($convertedBalance) }}</h5>
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
                        @php
                            $convertedAvailable = $currentCurrency->convertFrom($wallet->available_balance, $wallet->currency);
                        @endphp
                        <h5 class="mb-1">{{ $currentCurrency->format($convertedAvailable) }}</h5>
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
                        @php
                            $convertedPending = $currentCurrency->convertFrom($wallet->pending_balance, $wallet->currency);
                        @endphp
                        <h5 class="mb-1">{{ $currentCurrency->format($convertedPending) }}</h5>
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
                <!-- Load Balance with PayPal -->
                <div class="col-md-3">
                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#loadBalanceModal">
                        <i class="ri-paypal-line me-1"></i>
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
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="loadBalanceModalLabel">
                        <i class="ri-paypal-line me-2"></i>
                        {{ __('messages.load_balance_with_paypal') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        {{ __('messages.paypal_deposit_info') }}
                    </div>

                    <!-- Amount Selection -->
                    <div class="mb-4">
                        <label for="deposit_amount" class="form-label">
                            {{ __('messages.amount_to_deposit') }}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">{{ $currentCurrency->symbol }}</span>
                            <input type="number"
                                   class="form-control"
                                   id="deposit_amount"
                                   name="amount"
                                   min="2"
                                   max="10000"
                                   step="0.01"
                                   value="50"
                                   required
                                   placeholder="0.00">
                        </div>
                        <small class="text-muted">
                            {{ __('messages.minimum_deposit') }}: $2.00
                        </small>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="mb-4">
                        <label class="form-label">{{ __('messages.quick_amounts') }}</label>
                        <div class="row g-2">
                            <div class="col-3">
                                <button type="button" class="btn btn-outline-primary w-100 quick-amount" data-amount="25">$25</button>
                            </div>
                            <div class="col-3">
                                <button type="button" class="btn btn-outline-primary w-100 quick-amount" data-amount="50">$50</button>
                            </div>
                            <div class="col-3">
                                <button type="button" class="btn btn-outline-primary w-100 quick-amount" data-amount="100">$100</button>
                            </div>
                            <div class="col-3">
                                <button type="button" class="btn btn-outline-primary w-100 quick-amount" data-amount="200">$200</button>
                            </div>
                        </div>
                    </div>

                    <!-- Note Field -->
                    <div class="mb-4">
                        <label for="deposit_note" class="form-label">
                            {{ __('messages.note') }}
                            <small class="text-muted">({{ __('messages.optional') }})</small>
                        </label>
                        <textarea class="form-control"
                                  id="deposit_note"
                                  name="note"
                                  rows="2"
                                  maxlength="500"
                                  placeholder="{{ __('messages.add_note_optional') }}"></textarea>
                    </div>

                    <!-- PayPal Smart Payment Buttons Container -->
                    <div class="mb-4">
                        <h6 class="mb-3">{{ __('messages.select_payment_method') }}</h6>
                        <div id="paypal-button-container" style="min-height: 150px;"></div>
                    </div>

                    <!-- Loading State -->
                    <div id="paypal-loading" class="text-center mb-3" style="display: none;">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">{{ __('messages.processing_payment') }}...</p>
                    </div>

                    <!-- Security Notice -->
                    <div class="alert alert-warning mb-0">
                        <h6 class="alert-heading">
                            <i class="ri-shield-check-line me-2"></i>
                            {{ __('messages.secure_payment') }}
                        </h6>
                        <ul class="mb-0 ps-3">
                            <li>{{ __('messages.paypal_secure_note_1') }}</li>
                            <li>{{ __('messages.paypal_secure_note_2') }}</li>
                            <li>{{ __('messages.paypal_secure_note_3') }}</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>
                        {{ __('messages.cancel') }}
                    </button>
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
                                    $convertedAmount = $currentCurrency->convertFrom($transaction->amount, $transaction->currency);
                                @endphp
                                <span class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ ($transaction->type === 'credit' ? '+' : '-') . $currentCurrency->format(abs($convertedAmount)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $convertedBalanceAfter = $currentCurrency->convertFrom($transaction->balance_after, $transaction->currency);
                                @endphp
                                {{ $currentCurrency->format($convertedBalanceAfter) }}
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

<!-- PayPal SDK -->
@php
    $paypalMode = config('paypal.mode', 'sandbox');
    $paypalClientId = config("paypal.{$paypalMode}.client_id");
@endphp
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ config('paypal.currency', 'USD') }}&intent=capture"></script>

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

    #paypal-button-container {
        min-height: 150px;
    }
</style>

<script>
    // Quick amount buttons functionality
    document.addEventListener('DOMContentLoaded', function() {
        const quickAmountButtons = document.querySelectorAll('.quick-amount');
        const amountInput = document.getElementById('deposit_amount');

        quickAmountButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                quickAmountButtons.forEach(btn => btn.classList.remove('active'));

                // Add active class to clicked button
                this.classList.add('active');

                // Set the amount in the input field
                const amount = this.getAttribute('data-amount');
                amountInput.value = amount;
            });
        });
    });

    // Initialize PayPal Buttons when modal is shown
    document.getElementById('loadBalanceModal').addEventListener('shown.bs.modal', function () {
        // Clear any existing PayPal buttons
        document.getElementById('paypal-button-container').innerHTML = '';

        // Check if PayPal SDK is loaded
        if (typeof paypal === 'undefined') {
            console.error('PayPal SDK failed to load. Please check your internet connection and PayPal credentials.');
            document.getElementById('paypal-button-container').innerHTML = '<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>Failed to load PayPal. Please refresh the page and try again.</div>';
            return;
        }

        console.log('PayPal SDK loaded successfully');

        // Initialize PayPal Smart Payment Buttons
        paypal.Buttons({
            style: {
                layout: 'vertical',
                color: 'gold',
                shape: 'rect',
                label: 'paypal',
                height: 55
            },

            // Create order directly using PayPal SDK
            createOrder: function(data, actions) {
                const amount = document.getElementById('deposit_amount').value;

                // Validate amount
                if (!amount || parseFloat(amount) < 2) {
                    alert('{{ __('messages.minimum_deposit') }}: $2.00');
                    return false;
                }

                if (parseFloat(amount) > 10000) {
                    alert('{{ __('messages.maximum_deposit') }}: $10,000.00');
                    return false;
                }

                console.log('Creating PayPal order for amount:', amount);

                return actions.order.create({
                    purchase_units: [{
                        description: 'Wallet Deposit - Balance Top-up',
                        amount: {
                            currency_code: '{{ config("paypal.currency", "USD") }}',
                            value: parseFloat(amount).toFixed(2)
                        },
                        custom_id: 'WALLET-{{ auth()->id() }}-' + Date.now()
                    }],
                    payer: {
                        name: {
                            given_name: '{{ auth()->user()->first_name ?? auth()->user()->name }}',
                            surname: '{{ auth()->user()->last_name ?? "" }}'
                        },
                        email_address: '{{ auth()->user()->email }}',
                        phone: {
                            phone_type: 'MOBILE',
                            phone_number: {
                                national_number: '{{ auth()->user()->phone ?? "" }}'
                            }
                        }
                    },
                    application_context: {
                        shipping_preference: 'NO_SHIPPING'
                    }
                }).then(function(orderId) {
                    console.log('PayPal order created:', orderId);
                    return orderId;
                });
            },

            // Approve order - Capture payment
            onApprove: function(data, actions) {
                console.log('Payment approved:', data);
                document.getElementById('paypal-loading').style.display = 'block';

                // Capture the payment
                return actions.order.capture().then(function(details) {
                    console.log('Payment captured:', details);

                    // Get the amount and note
                    const amount = document.getElementById('deposit_amount').value;
                    const note = document.getElementById('deposit_note').value;

                    // Send payment details to our backend
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    return fetch('{{ route("wallet.deposit.paypal") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            amount: amount,
                            note: note,
                            order_id: data.orderID,
                            payer_id: data.payerID,
                            details: details
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        document.getElementById('paypal-loading').style.display = 'none';

                        if (result.success) {
                            // Close modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('loadBalanceModal'));
                            modal.hide();

                            // Show success message and reload page
                            alert('{{ __('messages.payment_successful') }}! {{ __('messages.wallet_updated') }}');
                            window.location.reload();
                        } else {
                            throw new Error(result.message || 'Payment processing failed');
                        }
                    })
                    .catch(error => {
                        document.getElementById('paypal-loading').style.display = 'none';
                        console.error('Backend processing error:', error);
                        alert('Failed to process payment on our server. Please contact support if the amount was debited.');
                        throw error;
                    });
                });
            },

            // Handle errors
            onError: function(err) {
                console.error('PayPal Error:', err);
                document.getElementById('paypal-loading').style.display = 'none';
                alert('An error occurred with PayPal. Please try again or contact support.');
            },

            // Handle cancellation
            onCancel: function(data) {
                document.getElementById('paypal-loading').style.display = 'none';
                console.log('Payment cancelled');
            }
        }).render('#paypal-button-container')
        .then(function() {
            console.log('PayPal buttons rendered successfully');
        })
        .catch(function(error) {
            console.error('Failed to render PayPal buttons:', error);
            document.getElementById('paypal-button-container').innerHTML = '<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>Failed to load PayPal buttons. Please refresh the page and try again.</div>';
        });
    });
</script>
@endsection
