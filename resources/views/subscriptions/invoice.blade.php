@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
@endphp

<div class="invoice-container" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
    <!-- Invoice Header -->
    <div class="invoice-header" style="border-bottom: 3px solid #561C04; padding-bottom: 20px; margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div style="flex: 1;">
                @if(setting_image('site_logo'))
                <img src="{{ setting_image('site_logo') }}"
                     alt="{{ setting('site_name', 'Taif') }}"
                     style="max-width: 150px; max-height: 80px; margin-bottom: 10px;">
                @endif
                <h2 style="margin: 0; color: #561C04; font-size: 28px;">{{ setting('site_name', 'Taif') }}</h2>
                @if(setting('company_address'))
                <p style="margin: 5px 0; color: #666;">{{ setting('company_address') }}</p>
                @endif
                @if(setting('company_phone'))
                <p style="margin: 5px 0; color: #666;">{{ __('messages.phone') }}: {{ setting('company_phone') }}</p>
                @endif
                @if(setting('company_email'))
                <p style="margin: 5px 0; color: #666;">{{ __('messages.email') }}: {{ setting('company_email') }}</p>
                @endif
            </div>
            <div style="text-align: {{ $isRtl ? 'left' : 'right' }};">
                <h1 style="margin: 0; color: #561C04; font-size: 32px;">{{ __('messages.invoice') }}</h1>
                <p style="margin: 10px 0; font-size: 16px; color: #666;">
                    <strong>{{ __('messages.invoice_number') }}:</strong> INV-{{ str_pad($userSubscription->id, 6, '0', STR_PAD_LEFT) }}
                </p>
                <p style="margin: 5px 0; font-size: 14px; color: #666;">
                    <strong>{{ __('messages.date') }}:</strong> {{ $userSubscription->created_at->format('Y-m-d') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-info" style="margin-bottom: 30px; background-color: #f9f9f9; padding: 20px; border-radius: 8px;">
        <h3 style="margin: 0 0 15px 0; color: #561C04; font-size: 18px;">{{ __('messages.customer_information') }}</h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
            <div>
                <strong>{{ __('messages.name') }}:</strong> {{ $userSubscription->user->name }}
            </div>
            <div>
                <strong>{{ __('messages.email') }}:</strong> {{ $userSubscription->user->email }}
            </div>
            @if($userSubscription->user->phone)
            <div>
                <strong>{{ __('messages.phone') }}:</strong> {{ $userSubscription->user->phone }}
            </div>
            @endif
            <div>
                <strong>{{ __('messages.user_id') }}:</strong> {{ $userSubscription->user->id }}
            </div>
        </div>
    </div>

    <!-- Subscription Details -->
    <div class="subscription-details" style="margin-bottom: 30px;">
        <h3 style="margin: 0 0 15px 0; color: #561C04; font-size: 18px;">{{ __('messages.subscription_details') }}</h3>
        <table style="width: 100%; border-collapse: collapse; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <thead>
                <tr style="background-color: #561C04; color: white;">
                    <th style="padding: 12px; text-align: {{ $isRtl ? 'right' : 'left' }}; border: 1px solid #ddd;">{{ __('messages.description') }}</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd;">{{ __('messages.start_date') }}</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #ddd;">{{ __('messages.end_date') }}</th>
                    <th style="padding: 12px; text-align: {{ $isRtl ? 'left' : 'right' }}; border: 1px solid #ddd;">{{ __('messages.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 15px; border: 1px solid #ddd;">
                        <strong style="color: #561C04; font-size: 16px;">{{ $userSubscription->subscription->localized_name }}</strong>
                        <br>
                        <small style="color: #666;">{{ __('messages.subscription_plan') }}</small>
                    </td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;">
                        {{ $userSubscription->start_date->format('Y-m-d') }}
                    </td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;">
                        {{ $userSubscription->end_date->format('Y-m-d') }}
                    </td>
                    <td style="padding: 15px; text-align: {{ $isRtl ? 'left' : 'right' }}; border: 1px solid #ddd;">
                        <strong style="font-size: 16px;">{{ format_currency($userSubscription->amount_paid) }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Payment Information -->
    <div class="payment-info" style="margin-bottom: 30px; background-color: #f9f9f9; padding: 20px; border-radius: 8px;">
        <h3 style="margin: 0 0 15px 0; color: #561C04; font-size: 18px;">{{ __('messages.payment_information') }}</h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
            <div>
                <strong>{{ __('messages.payment_method') }}:</strong>
                @if($userSubscription->payment_method === 'ziina')
                    <span style="color: #561C04;">Ziina</span>
                @elseif($userSubscription->payment_method === 'wallet')
                    <span style="color: #561C04;">{{ __('messages.wallet') }}</span>
                @elseif($userSubscription->payment_method === 'paymob')
                    <span style="color: #561C04;">Paymob</span>
                @else
                    <span style="color: #561C04;">{{ ucfirst($userSubscription->payment_method) }}</span>
                @endif
            </div>
            @if($userSubscription->transaction_id)
            <div>
                <strong>{{ __('messages.transaction_id') }}:</strong>
                <code style="background-color: #e9ecef; padding: 2px 6px; border-radius: 3px; font-size: 12px;">{{ $userSubscription->transaction_id }}</code>
            </div>
            @endif
            <div>
                <strong>{{ __('messages.payment_date') }}:</strong> {{ $userSubscription->created_at->format('Y-m-d H:i') }}
            </div>
            <div>
                <strong>{{ __('messages.status') }}:</strong>
                @if($userSubscription->status === 'active' && $userSubscription->end_date >= now())
                    <span style="color: #28a745; font-weight: bold;">{{ __('messages.active') }}</span>
                @elseif($userSubscription->status === 'expired' || $userSubscription->end_date < now())
                    <span style="color: #dc3545; font-weight: bold;">{{ __('messages.expired') }}</span>
                @else
                    <span style="color: #ffc107; font-weight: bold;">{{ __('messages.cancelled') }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Total Amount -->
    <div class="total-section" style="margin-bottom: 30px; text-align: {{ $isRtl ? 'left' : 'right' }};">
        <div style="display: inline-block; background-color: #561C04; color: white; padding: 20px 40px; border-radius: 8px;">
            <p style="margin: 0; font-size: 14px; opacity: 0.9;">{{ __('messages.total_amount') }}</p>
            <p style="margin: 5px 0 0 0; font-size: 32px; font-weight: bold;">{{ format_currency($userSubscription->amount_paid) }}</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="invoice-footer" style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #561C04; text-align: center; color: #666; font-size: 12px;">
        <p style="margin: 5px 0;">{{ __('messages.invoice_footer_text') }}</p>
        <p style="margin: 5px 0;">
            {{ __('messages.generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}
        </p>
        @if(setting('company_website'))
        <p style="margin: 5px 0;">
            <a href="{{ setting('company_website') }}" style="color: #561C04; text-decoration: none;">{{ setting('company_website') }}</a>
        </p>
        @endif
    </div>
</div>

<style>
    @media print {
        body {
            margin: 0;
            padding: 20px;
        }
        .invoice-container {
            max-width: 100% !important;
        }
    }
</style>
