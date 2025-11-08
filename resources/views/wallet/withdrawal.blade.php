@extends('layouts.app')

@section('title', __('messages.withdrawal_request'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="ri-wallet-3-line me-2"></i>
                        {{ __('messages.withdrawal_request') }}
                    </h5>
                </div>

                <div class="card-body">
                    <!-- Wallet Balance Info -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ __('messages.available_balance') }}</h6>
                                <h4 class="mb-0 text-primary">
                                    {{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}
                                </h4>
                            </div>
                            <i class="ri-money-dollar-circle-line" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    </div>

                    @if($wallet->balance < 10)
                        <div class="alert alert-warning">
                            <i class="ri-error-warning-line me-2"></i>
                            {{ __('messages.minimum_withdrawal_amount', ['amount' => 10]) }}
                        </div>
                    @else
                        <form action="{{ route('wallet.withdrawal.store') }}" method="POST">
                            @csrf

                            <!-- PayPal Email -->
                            <div class="mb-3">
                                <label for="paypal_email" class="form-label">
                                    {{ __('messages.paypal_email') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="ri-paypal-line"></i>
                                    </span>
                                    <input type="email"
                                           class="form-control @error('paypal_email') is-invalid @enderror"
                                           id="paypal_email"
                                           name="paypal_email"
                                           value="{{ old('paypal_email') }}"
                                           required
                                           placeholder="{{ __('messages.enter_paypal_email') }}">
                                </div>
                                @error('paypal_email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    {{ __('messages.paypal_email_note') }}
                                </small>
                            </div>

                            <!-- Amount -->
                            <div class="mb-3">
                                <label for="amount" class="form-label">
                                    {{ __('messages.withdrawal_amount') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           id="amount"
                                           name="amount"
                                           value="{{ old('amount') }}"
                                           min="10"
                                           max="{{ $wallet->balance }}"
                                           step="0.01"
                                           required
                                           placeholder="{{ __('messages.enter_amount') }}">
                                    <span class="input-group-text">{{ $wallet->currency }}</span>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    {{ __('messages.withdrawal_limits', ['min' => 10, 'max' => number_format($wallet->balance, 2)]) }}
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
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="ri-information-line me-2"></i>
                                    {{ __('messages.important_notes') }}
                                </h6>
                                <ul class="mb-0 ps-3">
                                    <li>{{ __('messages.withdrawal_note_1') }}</li>
                                    <li>{{ __('messages.withdrawal_note_2') }}</li>
                                    <li>{{ __('messages.withdrawal_note_3') }}</li>
                                    <li>{{ __('messages.withdrawal_note_4') }}</li>
                                </ul>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-send-plane-line me-1"></i>
                                    {{ __('messages.submit_withdrawal_request') }}
                                </button>
                                <a href="{{ route('wallet.index') }}" class="btn btn-outline-secondary">
                                    <i class="ri-arrow-left-line me-1"></i>
                                    {{ __('messages.back_to_wallet') }}
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Recent Withdrawals -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="ri-history-line me-2"></i>
                        {{ __('messages.recent_withdrawals') }}
                    </h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('wallet.withdrawal.history') }}" class="btn btn-outline-primary w-100">
                        {{ __('messages.view_withdrawal_history') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
