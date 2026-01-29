<x-mail::message>
# {{ __('New Support Ticket Received') }}

{{ __('A new support ticket has been submitted.') }}

**{{ __('Ticket ID:') }}** #{{ $ticket->id }}

**{{ __('Subject:') }}** {{ $ticket->subject }}

**{{ __('Priority:') }}** {{ ucfirst($ticket->priority) }}

**{{ __('Status:') }}** {{ ucfirst($ticket->status) }}

**{{ __('Submitted by:') }}** {{ $ticket->user->name }} ({{ $ticket->user->email }})

**{{ __('Description:') }}**

{{ $ticket->description }}

<x-mail::button :url="url('/admin/tickets/' . $ticket->id)">
{{ __('View Ticket') }}
</x-mail::button>

{{ __('Please respond to this ticket as soon as possible.') }}

{{ __('Best regards,') }}<br>
{{ __('The') }} {{ setting('site_name', config('app.name')) }} {{ __('Team') }}
</x-mail::message>
