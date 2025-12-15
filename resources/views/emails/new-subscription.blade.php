<x-mail::message>
# New Subscription Purchase

A new subscription has been purchased.

**Subscription Plan:** {{ $userSubscription->subscription->localized_name }}

**Purchased by:** {{ $userSubscription->user->name }} ({{ $userSubscription->user->email }})

**Amount Paid:** {!! format_currency($userSubscription->amount_paid, 'AED', 2, true) !!}

**Payment Method:** {{ ucfirst($userSubscription->payment_method) }}

**Start Date:** {{ $userSubscription->start_date }}

**End Date:** {{ $userSubscription->end_date }}

**Duration:** {{ $userSubscription->subscription->duration_days }} days

**Status:** {{ ucfirst($userSubscription->status) }}

<x-mail::button :url="url('/admin/subscriptions')">
View Subscriptions
</x-mail::button>

This is an automated notification from the subscription management system.

Best regards,<br>
{{ config('app.name') }} System
</x-mail::message>
