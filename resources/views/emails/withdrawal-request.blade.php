<x-mail::message>
# {{ __('Withdrawal Request Received') }}

{{ __('Hello') }} {{ $withdrawal->user->name }},

{{ __('We have received your withdrawal request and it is being processed.') }}

## {{ __('Withdrawal Details') }}

**{{ __('Amount:') }}** {{ setting('site_currency', 'AED') }} {{ number_format($withdrawal->amount, 2) }}
**{{ __('Request Date:') }}** {{ $withdrawal->created_at->format('M d, Y H:i') }}
**{{ __('Status:') }}** {{ ucfirst($withdrawal->status) }}
**{{ __('PayPal Email:') }}** {{ $withdrawal->paypal_email }}

@if($withdrawal->status === 'approved')
<x-mail::panel>
✅ {{ __('Your withdrawal has been approved and the payment has been sent to your PayPal account.') }}
</x-mail::panel>

<x-mail::button :url="url('/wallet')">
{{ __('View Wallet') }}
</x-mail::button>
@elseif($withdrawal->status === 'rejected')
<x-mail::panel>
❌ {{ __('Your withdrawal request has been rejected.') }}

@if($withdrawal->rejection_reason)
**{{ __('Reason:') }}** {{ $withdrawal->rejection_reason }}
@endif
</x-mail::panel>
@else
{{ __('Your request is currently pending review. We\'ll notify you once it\'s processed.') }}

<x-mail::button :url="url('/wallet')">
{{ __('Check Status') }}
</x-mail::button>
@endif

{{ __('If you have any questions, please contact our support team.') }}

{{ __('Best regards,') }}<br>
{{ __('The') }} {{ setting('site_name', config('app.name')) }} {{ __('Team') }}
</x-mail::message>
