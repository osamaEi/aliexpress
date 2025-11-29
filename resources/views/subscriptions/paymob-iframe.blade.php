@extends('dashboard')

@section('title', __('messages.payment'))

@section('content')
<div class="container-fluid py-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Subscription Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="ri-bank-card-line me-2"></i>
                        {{ __('messages.complete_payment') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="text-muted mb-1">{{ __('messages.subscription_plan') }}</h6>
                            <h4 class="mb-2">{{ $subscription->localized_name }}</h4>
                            <p class="text-muted mb-0">
                                <i class="ri-time-line me-1"></i>
                                {{ $subscription->duration_days }} {{ __('messages.days') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <h6 class="text-muted mb-1">{{ __('messages.total_amount') }}</h6>
                            <h3 class="text-primary mb-0">{{ number_format($subscription->price, 2) }} {{ __('messages.aed') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loading-state" class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">{{ __('messages.loading') }}</span>
                    </div>
                    <h5 class="mb-2">{{ __('messages.initializing_payment') }}</h5>
                    <p class="text-muted">{{ __('messages.please_wait') }}...</p>
                </div>
            </div>

            <!-- Payment Iframe Container -->
            <div id="iframe-container" style="display: none;">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <iframe id="paymob-iframe"
                                style="width: 100%; height: 800px; border: none;"
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div id="error-state" class="card shadow-sm" style="display: none;">
                <div class="card-body text-center py-5">
                    <i class="ri-error-warning-line text-danger mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-danger mb-2">{{ __('messages.payment_initialization_failed') }}</h5>
                    <p class="text-muted mb-4" id="error-message"></p>
                    <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-primary">
                        <i class="ri-arrow-left-line me-1"></i>
                        {{ __('messages.back_to_subscriptions') }}
                    </a>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-3">
                <a href="{{ route('subscriptions.subscribe.show', $subscription) }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i>
                    {{ __('messages.back') }}
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize payment on page load
    initializePayment();
});

function initializePayment() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('{{ route("subscriptions.initialize-paymob", $subscription) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.paymentToken && data.iframeId) {
            // Hide loading state
            document.getElementById('loading-state').style.display = 'none';

            // Build iframe URL
            const iframeUrl = '{{ config("paymob.base_url") }}/api/acceptance/iframes/' +
                            data.iframeId + '?payment_token=' + data.paymentToken;

            // Set iframe source
            const iframe = document.getElementById('paymob-iframe');
            iframe.src = iframeUrl;

            // Show iframe container
            document.getElementById('iframe-container').style.display = 'block';

            // Listen for iframe messages
            window.addEventListener('message', function(event) {
                console.log('Payment iframe message:', event.data);

                // Handle payment completion
                if (event.data && event.data.success) {
                    window.location.href = '{{ route("paymob.callback") }}?success=true';
                } else if (event.data && event.data.error) {
                    window.location.href = '{{ route("paymob.callback") }}?success=false';
                }
            });

        } else {
            showError(data.message || '{{ __("messages.payment_initialization_failed") }}');
        }
    })
    .catch(error => {
        console.error('Payment initialization error:', error);
        showError('{{ __("messages.payment_initialization_failed") }}');
    });
}

function showError(message) {
    document.getElementById('loading-state').style.display = 'none';
    document.getElementById('error-state').style.display = 'block';
    document.getElementById('error-message').textContent = message;
}
</script>

<style>
    @media (max-width: 768px) {
        #paymob-iframe {
            height: 600px;
        }
    }
</style>
@endsection
