@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">
                    <i class="ri-history-line me-2"></i>
                    {{ __('messages.subscription_history') }}
                </h4>
                <p class="text-muted mb-0 mt-2">{{ __('messages.view_all_subscriptions') }}</p>
            </div>
            <a href="{{ route('subscriptions.index') }}" class="btn btn-primary">
                <i class="ri-arrow-left-line me-1"></i>
                {{ __('messages.back_to_plans') }}
            </a>
        </div>
    </div>

    <!-- Subscription History Table -->
    <div class="card">
        <div class="card-body">
            @if($subscriptions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('messages.plan') }}</th>
                            <th>{{ __('messages.price') }}</th>
                            <th>{{ __('messages.start_date') }}</th>
                            <th>{{ __('messages.end_date') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.payment_method') }}</th>
                            <th>{{ __('messages.invoice') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $subscription)
                        <tr>
                            <td>
                                <span class="badge" style="background-color: {{ $subscription->subscription->color }}">
                                    {{ $subscription->subscription->localized_name }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ format_currency($subscription->amount_paid) }}</strong>
                            </td>
                            <td>{{ $subscription->start_date->format('Y-m-d') }}</td>
                            <td>{{ $subscription->end_date->format('Y-m-d') }}</td>
                            <td>
                                @if($subscription->status === 'active' && $subscription->end_date >= now())
                                    <span class="badge bg-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>
                                        {{ __('messages.active') }}
                                    </span>
                                @elseif($subscription->status === 'expired' || $subscription->end_date < now())
                                    <span class="badge bg-danger">
                                        <i class="ri-close-circle-line me-1"></i>
                                        {{ __('messages.expired') }}
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="ri-time-line me-1"></i>
                                        {{ __('messages.cancelled') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($subscription->payment_method)
                                    <span class="badge bg-info">
                                        @if($subscription->payment_method === 'ziina')
                                            <i class="ri-secure-payment-line me-1"></i>Ziina
                                        @elseif($subscription->payment_method === 'wallet')
                                            <i class="ri-wallet-line me-1"></i>{{ __('messages.wallet') }}
                                        @elseif($subscription->payment_method === 'paymob')
                                            <i class="ri-bank-card-line me-1"></i>Paymob
                                        @else
                                            {{ ucfirst($subscription->payment_method) }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($subscription->transaction_id)
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewInvoice({{ $subscription->id }})">
                                        <i class="ri-file-text-line me-1"></i>
                                        {{ __('messages.view_invoice') }}
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($subscriptions->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $subscriptions->links() }}
            </div>
            @endif
            @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="ri-inbox-line" style="font-size: 4rem; color: #ccc;"></i>
                <h5 class="mt-3 text-muted">{{ __('messages.no_subscription_history') }}</h5>
                <p class="text-muted">{{ __('messages.start_subscription_now') }}</p>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-primary mt-3">
                    <i class="ri-add-line me-1"></i>
                    {{ __('messages.view_plans') }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-file-text-line me-2"></i>
                    {{ __('messages.invoice') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoiceContent">
                <!-- Invoice content will be loaded here -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('messages.loading') }}...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('messages.close') }}
                </button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    <i class="ri-printer-line me-1"></i>
                    {{ __('messages.print') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewInvoice(subscriptionId) {
    const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
    modal.show();

    // Load invoice content via AJAX
    fetch(`/subscriptions/${subscriptionId}/invoice`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('invoiceContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('invoiceContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>
                    {{ __('messages.error_loading_invoice') }}
                </div>
            `;
        });
}

function printInvoice() {
    const content = document.getElementById('invoiceContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>{{ __('messages.invoice') }}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .no-print { display: none; }
                    }
                    body { padding: 20px; }
                </style>
            </head>
            <body>
                ${content}
                <script>
                    window.onload = function() {
                        window.print();
                        window.onafterprint = function() {
                            window.close();
                        }
                    }
                <\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
}
</script>
@endsection
