<x-mail::message>
# New Wallet Deposit

A new wallet deposit has been completed.

**User:** {{ $transaction->wallet->user->name }} ({{ $transaction->wallet->user->email }})

**Amount:** {!! format_currency($transaction->amount, 'AED', 2, true) !!}

**Transaction Type:** {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}

**Payment Method:** {{ isset($transaction->metadata['payment_method']) ? ucfirst($transaction->metadata['payment_method']) : 'N/A' }}

**Transaction ID:** #{{ $transaction->id }}

**Date:** {{ $transaction->created_at->format('Y-m-d H:i:s') }}

**Status:** {{ ucfirst($transaction->status) }}

**Balance After:** {!! format_currency($transaction->balance_after, 'AED', 2, true) !!}

@if($transaction->description)
**Description:** {{ $transaction->description }}
@endif

<x-mail::button :url="url('/admin/wallets')">
View Wallet Transactions
</x-mail::button>

This is an automated notification from the wallet management system.

Best regards,<br>
{{ config('app.name') }} System
</x-mail::message>
