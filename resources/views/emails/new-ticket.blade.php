<x-mail::message>
# New Support Ticket Received

A new support ticket has been submitted.

**Ticket ID:** #{{ $ticket->id }}

**Subject:** {{ $ticket->subject }}

**Priority:** <span style="text-transform: uppercase;">{{ $ticket->priority }}</span>

**Status:** {{ ucfirst($ticket->status) }}

**Submitted by:** {{ $ticket->user->name }} ({{ $ticket->user->email }})

**Description:**

{{ $ticket->description }}

<x-mail::button :url="url('/admin/tickets/' . $ticket->id)">
View Ticket
</x-mail::button>

Please respond to this ticket as soon as possible.

Best regards,<br>
{{ config('app.name') }} System
</x-mail::message>
