<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #561C04; color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 32px; color: #333; font-size: 15px; line-height: 1.6; }
        .field { margin-bottom: 12px; }
        .field strong { display: inline-block; min-width: 130px; color: #555; }
        .description { background: #f8f9fa; border-left: 4px solid #561C04; padding: 12px 16px; border-radius: 4px; margin: 16px 0; white-space: pre-wrap; }
        .btn { display: inline-block; background: #561C04; color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 15px; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 16px 32px; color: #888; font-size: 13px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ __('New Support Ticket Received') }}</h1>
        </div>
        <div class="body">
            <p>{{ __('A new support ticket has been submitted.') }}</p>

            <div class="field"><strong>{{ __('Ticket ID:') }}</strong> #{{ $ticket->id }}</div>
            <div class="field"><strong>{{ __('Subject:') }}</strong> {{ $ticket->subject }}</div>
            <div class="field"><strong>{{ __('Priority:') }}</strong> {{ ucfirst($ticket->priority) }}</div>
            <div class="field"><strong>{{ __('Status:') }}</strong> {{ ucfirst($ticket->status) }}</div>
            <div class="field"><strong>{{ __('Submitted by:') }}</strong> {{ $ticket->user->name }} ({{ $ticket->user->email }})</div>

            <p><strong>{{ __('Description:') }}</strong></p>
            <div class="description">{{ $ticket->description }}</div>

            <a href="{{ url('/admin/tickets/' . $ticket->id) }}" class="btn">{{ __('View Ticket') }}</a>

            <p>{{ __('Please respond to this ticket as soon as possible.') }}</p>
            <p>{{ __('Best regards,') }}<br>{{ __('The') }} {{ setting('site_name', config('app.name')) }} {{ __('Team') }}</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ setting('site_name', config('app.name')) }}
        </div>
    </div>
</body>
</html>
