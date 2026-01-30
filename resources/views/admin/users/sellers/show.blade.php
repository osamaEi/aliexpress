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
                    @if($seller->phone)
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.phone') }}:</div>
                        <div class="col-md-8">
                            @if($seller->phone_code)
                                @php
                                    $countryCode = strtolower(str_replace('+', '', $seller->phone_code));
                                    $flagMap = [
                                        '971' => 'ae', '966' => 'sa', '965' => 'kw', '974' => 'qa',
                                        '973' => 'bh', '968' => 'om', '962' => 'jo', '961' => 'lb',
                                        '963' => 'sy', '970' => 'ps', '964' => 'iq', '20' => 'eg',
                                        '218' => 'ly', '216' => 'tn', '213' => 'dz', '212' => 'ma',
                                        '222' => 'mr', '249' => 'sd', '967' => 'ye', '252' => 'so',
                                        '253' => 'dj', '269' => 'km'
                                    ];
                                    $flagCode = $flagMap[$countryCode] ?? 'ae';
                                @endphp
                                <img src="https://flagcdn.com/w20/{{ $flagCode }}.png" style="width: 20px; height: 15px; margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 5px; border-radius: 2px; vertical-align: middle;">
                                <span style="direction: ltr; display: inline-block;">{{ $seller->phone_code }} {{ $seller->phone }}</span>
                            @else
                                {{ $seller->phone }}
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.company_name') }}:</div>
                        <div class="col-md-8">{{ $seller->company_name ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.country') }}:</div>
                        <div class="col-md-8">
                            @if($seller->country)
                                @php
                                    $countryFlagMap = [
                                        'AE' => ['flag' => 'ae', 'name_ar' => 'الإمارات العربية المتحدة', 'name_en' => 'United Arab Emirates'],
                                        'SA' => ['flag' => 'sa', 'name_ar' => 'المملكة العربية السعودية', 'name_en' => 'Saudi Arabia'],
                                        'KW' => ['flag' => 'kw', 'name_ar' => 'الكويت', 'name_en' => 'Kuwait'],
                                        'QA' => ['flag' => 'qa', 'name_ar' => 'قطر', 'name_en' => 'Qatar'],
                                        'BH' => ['flag' => 'bh', 'name_ar' => 'البحرين', 'name_en' => 'Bahrain'],
                                        'OM' => ['flag' => 'om', 'name_ar' => 'سلطنة عُمان', 'name_en' => 'Oman'],
                                        'JO' => ['flag' => 'jo', 'name_ar' => 'الأردن', 'name_en' => 'Jordan'],
                                        'LB' => ['flag' => 'lb', 'name_ar' => 'لبنان', 'name_en' => 'Lebanon'],
                                        'SY' => ['flag' => 'sy', 'name_ar' => 'سوريا', 'name_en' => 'Syria'],
                                        'PS' => ['flag' => 'ps', 'name_ar' => 'فلسطين', 'name_en' => 'Palestine'],
                                        'IQ' => ['flag' => 'iq', 'name_ar' => 'العراق', 'name_en' => 'Iraq'],
                                        'EG' => ['flag' => 'eg', 'name_ar' => 'مصر', 'name_en' => 'Egypt'],
                                        'LY' => ['flag' => 'ly', 'name_ar' => 'ليبيا', 'name_en' => 'Libya'],
                                        'TN' => ['flag' => 'tn', 'name_ar' => 'تونس', 'name_en' => 'Tunisia'],
                                        'DZ' => ['flag' => 'dz', 'name_ar' => 'الجزائر', 'name_en' => 'Algeria'],
                                        'MA' => ['flag' => 'ma', 'name_ar' => 'المغرب', 'name_en' => 'Morocco'],
                                        'MR' => ['flag' => 'mr', 'name_ar' => 'موريتانيا', 'name_en' => 'Mauritania'],
                                        'SD' => ['flag' => 'sd', 'name_ar' => 'السودان', 'name_en' => 'Sudan'],
                                        'YE' => ['flag' => 'ye', 'name_ar' => 'اليمن', 'name_en' => 'Yemen'],
                                        'SO' => ['flag' => 'so', 'name_ar' => 'الصومال', 'name_en' => 'Somalia'],
                                        'DJ' => ['flag' => 'dj', 'name_ar' => 'جيبوتي', 'name_en' => 'Djibouti'],
                                        'KM' => ['flag' => 'km', 'name_ar' => 'جزر القمر', 'name_en' => 'Comoros'],
                                    ];
                                    $countryData = $countryFlagMap[$seller->country] ?? null;
                                @endphp
                                @if($countryData)
                                    <img src="https://flagcdn.com/w20/{{ $countryData['flag'] }}.png" style="width: 20px; height: 15px; margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 5px; border-radius: 2px; vertical-align: middle;">
                                    {{ app()->getLocale() == 'ar' ? $countryData['name_ar'] : $countryData['name_en'] }}
                                @else
                                    {{ $seller->country }}
                                @endif
                            @else
                                -
                            @endif
                        </div>
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

            <!-- Withdrawal Method Info -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="ri-bank-card-line me-2"></i>{{ __('messages.withdrawal_method_settings') }}</h5>
                </div>
                <div class="card-body">
                    @if($seller->withdrawal_method)
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">{{ __('messages.withdrawal_method') }}:</div>
                            <div class="col-md-8">
                                @if($seller->withdrawal_method == 'paypal')
                                    <span class="badge bg-info"><i class="ri-paypal-line me-1"></i>{{ __('messages.paypal') }}</span>
                                @elseif($seller->withdrawal_method == 'bank_transfer')
                                    <span class="badge bg-success"><i class="ri-bank-line me-1"></i>{{ __('messages.bank_transfer') }}</span>
                                @elseif($seller->withdrawal_method == 'mobile_wallet')
                                    <span class="badge bg-warning"><i class="ri-smartphone-line me-1"></i>{{ __('messages.mobile_wallet') }}</span>
                                @endif
                            </div>
                        </div>

                        @if($seller->withdrawal_method == 'paypal')
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">{{ __('messages.paypal_email') }}:</div>
                                <div class="col-md-8">{{ $seller->paypal_email ?: '-' }}</div>
                            </div>
                        @elseif($seller->withdrawal_method == 'bank_transfer')
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">{{ __('messages.bank_name') }}:</div>
                                <div class="col-md-8">{{ $seller->bank_name ?: '-' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">{{ __('messages.account_holder_name') }}:</div>
                                <div class="col-md-8">{{ $seller->bank_account_name ?: '-' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">{{ __('messages.account_number') }}:</div>
                                <div class="col-md-8">{{ $seller->bank_account_number ?: '-' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">{{ __('messages.iban') }}:</div>
                                <div class="col-md-8">{{ $seller->bank_iban ?: '-' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">{{ __('messages.swift_code') }}:</div>
                                <div class="col-md-8">{{ $seller->bank_swift_code ?: '-' }}</div>
                            </div>
                        @elseif($seller->withdrawal_method == 'mobile_wallet')
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">{{ __('messages.wallet_provider') }}:</div>
                                <div class="col-md-8">{{ $seller->wallet_provider ?: '-' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">{{ __('messages.wallet_phone_number') }}:</div>
                                <div class="col-md-8">{{ $seller->wallet_phone_number ?: '-' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">{{ __('messages.wallet_holder_name') }}:</div>
                                <div class="col-md-8">{{ $seller->wallet_holder_name ?: '-' }}</div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="ri-error-warning-line me-2"></i>
                            {{ __('messages.no_withdrawal_method_set') }}
                        </div>
                    @endif
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
