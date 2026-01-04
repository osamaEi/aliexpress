@extends('dashboard')

@section('title', __('messages.withdrawal_request'))

@section('content')
@php
    $balanceConverted = $currentCurrency->convertFrom($wallet->balance, 'AED');
@endphp
<div class="container py-5" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: #561C04;">
                    <h5 class="mb-0">
                        <i class="ri-wallet-3-line {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                        {{ __('messages.withdrawal_request') }}
                    </h5>
                </div>

                <div class="card-body">
                    <!-- Wallet Balance Info -->
                    <div class="alert mb-4" style="background-color: rgba(86, 28, 4, 0.1); border: 1px solid rgba(86, 28, 4, 0.2);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1" style="color: #561C04;">{{ __('messages.available_balance') }}</h6>
                                <h4 class="mb-0 d-inline-flex align-items-center gap-1" style="color: #561C04; direction: ltr;">
                                    <x-session-currency-icon width="24" height="24" />
                                    {{ number_format($balanceConverted, 2) }}
                                </h4>
                            </div>
                            <i class="ri-wallet-3-line" style="font-size: 3rem; opacity: 0.5; color: #561C04;"></i>
                        </div>
                    </div>

                    @if($wallet->balance < 10)
                        <div class="alert alert-warning">
                            <i class="ri-error-warning-line {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                            {{ __('messages.minimum_withdrawal_amount', ['amount' => 10]) }}
                        </div>
                    @else
                        <form action="{{ route('wallet.withdrawal.store') }}" method="POST" id="withdrawalForm">
                            @csrf

                            <!-- Withdrawal Method Selection -->
                            <div class="mb-4">
                                <label class="form-label">
                                    {{ __('messages.withdrawal_method') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="row g-3">
                                    <!-- PayPal Option -->
                                    <div class="col-md-4">
                                        <div class="form-check card h-100 method-option">
                                            <input class="form-check-input d-none" type="radio" name="withdrawal_method" id="method_paypal" value="paypal" {{ old('withdrawal_method', 'paypal') == 'paypal' ? 'checked' : '' }}>
                                            <label class="form-check-label card-body text-center method-card" for="method_paypal">
                                                <i class="ri-paypal-line mb-2" style="font-size: 2.5rem; color: #561C04;"></i>
                                                <h6 class="mb-0">{{ __('messages.paypal') }}</h6>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Bank Transfer (IBAN) Option -->
                                    <div class="col-md-4">
                                        <div class="form-check card h-100 method-option">
                                            <input class="form-check-input d-none" type="radio" name="withdrawal_method" id="method_bank" value="bank_transfer" {{ old('withdrawal_method') == 'bank_transfer' ? 'checked' : '' }}>
                                            <label class="form-check-label card-body text-center method-card" for="method_bank">
                                                <i class="ri-bank-line mb-2" style="font-size: 2.5rem; color: #561C04;"></i>
                                                <h6 class="mb-0">{{ __('messages.bank_transfer') }}</h6>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Mobile Wallet Option -->
                                    <div class="col-md-4">
                                        <div class="form-check card h-100 method-option">
                                            <input class="form-check-input d-none" type="radio" name="withdrawal_method" id="method_wallet" value="mobile_wallet" {{ old('withdrawal_method') == 'mobile_wallet' ? 'checked' : '' }}>
                                            <label class="form-check-label card-body text-center method-card" for="method_wallet">
                                                <i class="ri-smartphone-line mb-2" style="font-size: 2.5rem; color: #561C04;"></i>
                                                <h6 class="mb-0">{{ __('messages.mobile_wallet') }}</h6>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('withdrawal_method')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- PayPal Fields -->
                            <div id="paypal_fields" class="method-fields">
                                <div class="mb-3">
                                    <label for="paypal_email" class="form-label">
                                        {{ __('messages.paypal_email') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text" style="background-color: rgba(86, 28, 4, 0.1); border-color: #561C04;">
                                            <i class="ri-paypal-line" style="color: #561C04;"></i>
                                        </span>
                                        <input type="email"
                                               class="form-control @error('paypal_email') is-invalid @enderror"
                                               id="paypal_email"
                                               name="paypal_email"
                                               value="{{ old('paypal_email') }}"
                                               placeholder="{{ __('messages.enter_paypal_email') }}"
                                               style="border-color: #ddd;">
                                    </div>
                                    @error('paypal_email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        {{ __('messages.paypal_email_note') }}
                                    </small>
                                </div>
                            </div>

                            <!-- Bank Transfer (IBAN) Fields -->
                            <div id="bank_fields" class="method-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="iban" class="form-label">
                                            {{ __('messages.iban') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background-color: rgba(86, 28, 4, 0.1); border-color: #561C04;">
                                                <i class="ri-bank-card-line" style="color: #561C04;"></i>
                                            </span>
                                            <input type="text"
                                                   class="form-control @error('iban') is-invalid @enderror"
                                                   id="iban"
                                                   name="iban"
                                                   value="{{ old('iban') }}"
                                                   placeholder="{{ __('messages.enter_iban') }}"
                                                   style="direction: ltr;">
                                        </div>
                                        @error('iban')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('messages.iban_note') }}</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="swift_code" class="form-label">
                                            {{ __('messages.swift_code') }}
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background-color: rgba(86, 28, 4, 0.1); border-color: #561C04;">
                                                <i class="ri-global-line" style="color: #561C04;"></i>
                                            </span>
                                            <input type="text"
                                                   class="form-control @error('swift_code') is-invalid @enderror"
                                                   id="swift_code"
                                                   name="swift_code"
                                                   value="{{ old('swift_code') }}"
                                                   placeholder="{{ __('messages.enter_swift_code') }}"
                                                   style="direction: ltr;">
                                        </div>
                                        @error('swift_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="bank_name" class="form-label">
                                            {{ __('messages.bank_name') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background-color: rgba(86, 28, 4, 0.1); border-color: #561C04;">
                                                <i class="ri-bank-line" style="color: #561C04;"></i>
                                            </span>
                                            <input type="text"
                                                   class="form-control @error('bank_name') is-invalid @enderror"
                                                   id="bank_name"
                                                   name="bank_name"
                                                   value="{{ old('bank_name') }}"
                                                   placeholder="{{ __('messages.enter_bank_name') }}">
                                        </div>
                                        @error('bank_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="account_holder_name" class="form-label">
                                            {{ __('messages.account_holder_name') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background-color: rgba(86, 28, 4, 0.1); border-color: #561C04;">
                                                <i class="ri-user-line" style="color: #561C04;"></i>
                                            </span>
                                            <input type="text"
                                                   class="form-control @error('account_holder_name') is-invalid @enderror"
                                                   id="account_holder_name"
                                                   name="account_holder_name"
                                                   value="{{ old('account_holder_name') }}"
                                                   placeholder="{{ __('messages.enter_account_holder_name') }}">
                                        </div>
                                        @error('account_holder_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Wallet Fields -->
                            <div id="wallet_fields" class="method-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="wallet_provider" class="form-label">
                                            {{ __('messages.mobile_wallet_provider') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('wallet_provider') is-invalid @enderror"
                                                id="wallet_provider"
                                                name="wallet_provider">
                                            <option value="">{{ __('messages.select_wallet_provider') }}</option>
                                            <option value="apple_pay" {{ old('wallet_provider') == 'apple_pay' ? 'selected' : '' }}>Apple Pay</option>
                                            <option value="google_pay" {{ old('wallet_provider') == 'google_pay' ? 'selected' : '' }}>Google Pay</option>
                                            <option value="samsung_pay" {{ old('wallet_provider') == 'samsung_pay' ? 'selected' : '' }}>Samsung Pay</option>
                                            <option value="stc_pay" {{ old('wallet_provider') == 'stc_pay' ? 'selected' : '' }}>STC Pay</option>
                                            <option value="urpay" {{ old('wallet_provider') == 'urpay' ? 'selected' : '' }}>URPay</option>
                                            <option value="other" {{ old('wallet_provider') == 'other' ? 'selected' : '' }}>{{ __('messages.other') }}</option>
                                        </select>
                                        @error('wallet_provider')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="wallet_mobile_number" class="form-label">
                                            {{ __('messages.mobile_number') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="background-color: rgba(86, 28, 4, 0.1); border-color: #561C04;">
                                                <i class="ri-smartphone-line" style="color: #561C04;"></i>
                                            </span>
                                            <input type="tel"
                                                   class="form-control @error('wallet_mobile_number') is-invalid @enderror"
                                                   id="wallet_mobile_number"
                                                   name="wallet_mobile_number"
                                                   value="{{ old('wallet_mobile_number') }}"
                                                   placeholder="{{ __('messages.enter_mobile_number') }}"
                                                   style="direction: ltr;">
                                        </div>
                                        @error('wallet_mobile_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ __('messages.mobile_wallet_note') }}</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="wallet_holder_name" class="form-label">
                                        {{ __('messages.wallet_holder_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text" style="background-color: rgba(86, 28, 4, 0.1); border-color: #561C04;">
                                            <i class="ri-user-line" style="color: #561C04;"></i>
                                        </span>
                                        <input type="text"
                                               class="form-control @error('wallet_holder_name') is-invalid @enderror"
                                               id="wallet_holder_name"
                                               name="wallet_holder_name"
                                               value="{{ old('wallet_holder_name') }}"
                                               placeholder="{{ __('messages.enter_wallet_holder_name') }}">
                                    </div>
                                    @error('wallet_holder_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="mb-3">
                                <label for="amount" class="form-label">
                                    {{ __('messages.withdrawal_amount') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group" style="direction: ltr;">
                                    <span class="input-group-text" style="background-color: rgba(86, 28, 4, 0.1); border-color: #561C04;">
                                        <x-session-currency-icon width="18" height="18" />
                                    </span>
                                    <input type="number"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           id="amount"
                                           name="amount"
                                           value="{{ old('amount') }}"
                                           min="10"
                                           max="{{ $wallet->balance }}"
                                           step="0.01"
                                           required
                                           placeholder="{{ __('messages.enter_amount') }}"
                                           style="direction: ltr; text-align: left;">
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    {{ __('messages.withdrawal_limits', ['min' => 10, 'max' => number_format($balanceConverted, 2)]) }}
                                </small>
                            </div>

                            <!-- Seller Note -->
                            <div class="mb-4">
                                <label for="seller_note" class="form-label">
                                    {{ __('messages.note') }}
                                    <small class="text-muted">({{ __('messages.optional') }})</small>
                                </label>
                                <textarea class="form-control @error('seller_note') is-invalid @enderror"
                                          id="seller_note"
                                          name="seller_note"
                                          rows="3"
                                          maxlength="1000"
                                          placeholder="{{ __('messages.withdrawal_note_placeholder') }}">{{ old('seller_note') }}</textarea>
                                @error('seller_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Important Notes -->
                            <div class="alert" style="background-color: #fff3cd; border-color: #ffc107;">
                                <h6 class="alert-heading" style="color: #856404;">
                                    <i class="ri-information-line {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                                    {{ __('messages.important_notes') }}
                                </h6>
                                <ul class="mb-0 {{ app()->getLocale() == 'ar' ? 'pe-3' : 'ps-3' }}" style="color: #856404;">
                                    <li>{{ __('messages.withdrawal_note_1') }}</li>
                                    <li>{{ __('messages.withdrawal_note_2') }}</li>
                                    <li>{{ __('messages.withdrawal_note_3') }}</li>
                                    <li>{{ __('messages.withdrawal_note_4') }}</li>
                                </ul>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn text-white" style="background-color: #561C04;">
                                    <i class="ri-send-plane-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                    {{ __('messages.submit_withdrawal_request') }}
                                </button>
                                <a href="{{ route('wallet.index') }}" class="btn btn-outline-secondary">
                                    <i class="ri-arrow-left-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                    {{ __('messages.back_to_wallet') }}
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Recent Withdrawals -->
            <div class="card shadow-sm mt-4">
                <div class="card-header" style="background-color: rgba(86, 28, 4, 0.05);">
                    <h6 class="mb-0" style="color: #561C04;">
                        <i class="ri-history-line {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                        {{ __('messages.recent_withdrawals') }}
                    </h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('wallet.withdrawal.history') }}" class="btn w-100" style="background-color: #561C04; color: white;">
                        {{ __('messages.view_withdrawal_history') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .method-card {
        border: 2px solid transparent;
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .method-card:hover {
        border-color: #561C04;
        background-color: rgba(86, 28, 4, 0.05);
    }

    input[type="radio"]:checked + .method-card {
        border-color: #561C04;
        background-color: rgba(86, 28, 4, 0.1);
        box-shadow: 0 0 0 3px rgba(86, 28, 4, 0.2);
    }

    .form-check.card {
        padding: 0;
        margin: 0;
    }

    .form-check.card .card-body {
        padding: 1.5rem 1rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #561C04;
        box-shadow: 0 0 0 0.2rem rgba(86, 28, 4, 0.15);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(86, 28, 4, 0.25);
        transition: all 0.3s ease;
    }

    .input-group-text {
        border-color: #ddd;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodRadios = document.querySelectorAll('input[name="withdrawal_method"]');
    const paypalFields = document.getElementById('paypal_fields');
    const bankFields = document.getElementById('bank_fields');
    const walletFields = document.getElementById('wallet_fields');

    function toggleFields() {
        const selectedMethod = document.querySelector('input[name="withdrawal_method"]:checked')?.value;

        // Hide all fields first
        paypalFields.style.display = 'none';
        bankFields.style.display = 'none';
        walletFields.style.display = 'none';

        // Remove required from all fields
        document.querySelectorAll('.method-fields input, .method-fields select').forEach(field => {
            field.removeAttribute('required');
        });

        // Show and set required for selected method
        if (selectedMethod === 'paypal') {
            paypalFields.style.display = 'block';
            document.getElementById('paypal_email').setAttribute('required', 'required');
        } else if (selectedMethod === 'bank_transfer') {
            bankFields.style.display = 'block';
            document.getElementById('iban').setAttribute('required', 'required');
            document.getElementById('bank_name').setAttribute('required', 'required');
            document.getElementById('account_holder_name').setAttribute('required', 'required');
        } else if (selectedMethod === 'mobile_wallet') {
            walletFields.style.display = 'block';
            document.getElementById('wallet_provider').setAttribute('required', 'required');
            document.getElementById('wallet_mobile_number').setAttribute('required', 'required');
            document.getElementById('wallet_holder_name').setAttribute('required', 'required');
        }
    }

    // Add event listeners
    methodRadios.forEach(radio => {
        radio.addEventListener('change', toggleFields);
    });

    // Initialize on page load
    toggleFields();
});
</script>
@endsection
