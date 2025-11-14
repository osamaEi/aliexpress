<x-mail::message>
# {{ __('Welcome to') }} {{ setting('site_name', config('app.name')) }}!

{{ __('Hello') }} {{ $user->name }},

{{ __('Thank you for joining our platform. We\'re excited to have you on board!') }}

{{ __('Here\'s what you can do now:') }}

- {{ __('Browse thousands of products') }}
- {{ __('Start selling your products') }}
- {{ __('Connect with suppliers worldwide') }}
- {{ __('Manage your orders efficiently') }}

<x-mail::button :url="url('/dashboard')">
{{ __('Go to Dashboard') }}
</x-mail::button>

{{ __('If you need any help getting started, our support team is here to assist you.') }}

{{ __('Best regards,') }}<br>
{{ __('The') }} {{ setting('site_name', config('app.name')) }} {{ __('Team') }}
</x-mail::message>
