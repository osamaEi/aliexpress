@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div id="processing-state">
                        <div class="spinner-border text-primary mb-4" role="status" style="width: 4rem; height: 4rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h3 class="mb-3">{{ __('messages.processing_payment') }}</h3>
                        <p class="text-muted">{{ __('messages.please_wait_payment_confirmation') }}</p>
                        <p class="small text-muted">{{ __('messages.do_not_close_window') }}</p>
                    </div>

                    <div id="success-state" style="display: none;">
                        <div class="mb-4">
                            <i class="ri-checkbox-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="text-success mb-3">{{ __('messages.payment_successful') }}</h3>
                        <p class="text-muted mb-4">{{ __('messages.subscription_activated') }}</p>
                        <a href="{{ route('subscriptions.index') }}" class="btn btn-primary">
                            {{ __('messages.view_subscriptions') }}
                        </a>
                    </div>

                    <div id="failed-state" style="display: none;">
                        <div class="mb-4">
                            <i class="ri-close-circle-fill text-danger" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="text-danger mb-3">{{ __('messages.payment_failed') }}</h3>
                        <p class="text-muted mb-4" id="error-message">{{ __('messages.payment_not_completed') }}</p>
                        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-primary">
                            {{ __('messages.try_again') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Get query parameters
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const txnResponseCode = urlParams.get('txn_response_code');

    // Show appropriate state based on URL parameters
    if (success === 'true' && txnResponseCode === 'APPROVED') {
        // Payment successful
        document.getElementById('processing-state').style.display = 'none';
        document.getElementById('success-state').style.display = 'block';

        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = '{{ route("subscriptions.index") }}?payment=success';
        }, 3000);
    } else if (success === 'false' || txnResponseCode !== 'APPROVED') {
        // Payment failed
        document.getElementById('processing-state').style.display = 'none';
        document.getElementById('failed-state').style.display = 'block';

        const errorMsg = urlParams.get('data_message') || '{{ __("messages.payment_not_completed") }}';
        document.getElementById('error-message').textContent = errorMsg;
    } else {
        // Still processing - check status after a few seconds
        setTimeout(function() {
            // Redirect to subscriptions page
            window.location.href = '{{ route("subscriptions.index") }}';
        }, 5000);
    }

    // Listen for messages from Paymob iframe (if opened in popup)
    window.addEventListener('message', function(event) {
        console.log('Paymob callback message:', event.data);
        // Handle iframe callback if needed
    });
</script>
@endsection
