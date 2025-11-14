<x-mail::message>
# {{ __('Reset Password Notification') }}

{{ __('Hello') }},

{{ __('You are receiving this email because we received a password reset request for your account.') }}

<x-mail::button :url="$url">
{{ __('Reset Password') }}
</x-mail::button>

{{ __('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]) }}

{{ __('If you did not request a password reset, no further action is required.') }}

{{ __('Best regards,') }}<br>
{{ __('The') }} {{ setting('site_name', config('app.name')) }} {{ __('Team') }}

<x-slot:subcopy>
{{ __('If you\'re having trouble clicking the ":actionText" button, copy and paste the URL below into your web browser:', ['actionText' => __('Reset Password')]) }}

{{ $url }}
</x-slot:subcopy>
</x-mail::message>
