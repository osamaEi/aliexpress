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
                <div class="card-header" style="background-color: #561C04;">
                    <h5 class="mb-0 text-white">
                        <i class="ri-wallet-3-line {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                        {{ __('messages.withdrawal_request') }}
                    </h5>
                </div>

                <div class="card-body">
                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ri-check-line {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                            <strong>{{ __('messages.validation_errors') ?? 'Validation Errors' }}:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

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

                            <input type="hidden" name="withdrawal_method" value="bank_transfer">

                            @php $user = auth()->user(); @endphp

                            {{-- Bank details summary from profile --}}
                            @if($user->bank_iban || $user->bank_name)
                            <div class="alert mb-4" style="background-color: rgba(86,28,4,0.06); border: 1px solid rgba(86,28,4,0.2);">
                                <h6 class="mb-2" style="color:#561C04;">
                                    <i class="ri-bank-line {{ app()->getLocale()=='ar'?'ms-1':'me-1' }}"></i>
                                    {{ __('messages.bank_transfer') }}
                                </h6>
                                <div class="row g-2 small">
                                    @if($user->bank_name)
                                    <div class="col-md-6"><span class="text-muted">{{ __('messages.bank_name') }}:</span> <strong>{{ $user->bank_name }}</strong></div>
                                    @endif
                                    @if($user->bank_account_name)
                                    <div class="col-md-6"><span class="text-muted">{{ __('messages.account_holder_name') }}:</span> <strong>{{ $user->bank_account_name }}</strong></div>
                                    @endif
                                    @if($user->bank_iban)
                                    <div class="col-md-6"><span class="text-muted">{{ __('messages.iban') }}:</span> <strong dir="ltr">{{ $user->bank_iban }}</strong></div>
                                    @endif
                                    @if($user->bank_swift_code)
                                    <div class="col-md-6"><span class="text-muted">{{ __('messages.swift_code') }}:</span> <strong dir="ltr">{{ $user->bank_swift_code }}</strong></div>
                                    @endif
                                    @if($user->bank_account_number)
                                    <div class="col-md-6"><span class="text-muted">{{ __('messages.account_number') }}:</span> <strong dir="ltr">{{ $user->bank_account_number }}</strong></div>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('profile.edit') }}" class="small" style="color:#561C04;">
                                        <i class="ri-edit-line me-1"></i>{{ app()->getLocale()=='ar' ? 'تعديل البيانات البنكية' : 'Edit bank details' }}
                                    </a>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-warning mb-4">
                                <i class="ri-error-warning-line me-2"></i>
                                {{ app()->getLocale()=='ar' ? 'يرجى إكمال بيانات التحويل البنكي في' : 'Please complete your bank details in' }}
                                <a href="{{ route('profile.edit') }}">{{ app()->getLocale()=='ar' ? 'الملف الشخصي' : 'your profile' }}</a>
                                {{ app()->getLocale()=='ar' ? 'أولاً.' : 'first.' }}
                            </div>
                            @endif

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
// Bank transfer is the only method — no toggling needed
</script>
@endsection
