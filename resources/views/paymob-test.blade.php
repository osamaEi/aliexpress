<!DOCTYPE html>
<html>
<head>
    <title>Paymob Test Diagnostic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Paymob UAE Test Diagnostic</h4>
                    </div>
                    <div class="card-body">
                        <h5>Current Configuration:</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>Base URL:</th>
                                <td><code>{{ config('paymob.base_url') }}</code></td>
                            </tr>
                            <tr>
                                <th>Currency:</th>
                                <td><code>{{ config('paymob.currency') }}</code></td>
                            </tr>
                            <tr>
                                <th>Integration ID:</th>
                                <td><code>{{ config('paymob.card_integration_id') }}</code></td>
                            </tr>
                            <tr>
                                <th>Iframe ID:</th>
                                <td><code>{{ config('paymob.iframe_id') }}</code></td>
                            </tr>
                            <tr>
                                <th>API Key (first 20 chars):</th>
                                <td><code>{{ substr(config('paymob.api_key'), 0, 20) }}...</code></td>
                            </tr>
                            <tr>
                                <th>Secret Key Type:</th>
                                <td>
                                    @if(str_contains(config('paymob.secret_key', ''), 'test'))
                                        <span class="badge bg-warning">TEST MODE ✓</span>
                                    @else
                                        <span class="badge bg-danger">LIVE MODE</span>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <div class="alert alert-info mt-4">
                            <h6><strong>Test Cards for UAE Paymob (Try these in order):</strong></h6>
                            <ol>
                                <li>
                                    <strong>Card 1:</strong> <code>4111111111111111</code><br>
                                    CVV: <code>123</code>, Expiry: <code>12/25</code>
                                </li>
                                <li>
                                    <strong>Card 2:</strong> <code>5123450000000008</code><br>
                                    CVV: <code>100</code>, Expiry: <code>05/25</code>
                                </li>
                                <li>
                                    <strong>Card 3:</strong> <code>2223000000000007</code><br>
                                    CVV: <code>100</code>, Expiry: <code>05/25</code>
                                </li>
                            </ol>
                        </div>

                        <div class="alert alert-warning">
                            <h6><strong>⚠️ If cards are still declined:</strong></h6>
                            <p class="mb-2">Your integration might be in <strong>LIVE mode</strong> even though you have test keys. This is common with UAE Paymob.</p>
                            <p class="mb-0"><strong>Solution:</strong> Contact Paymob UAE support at <a href="mailto:uae@paymob.com">uae@paymob.com</a> and ask them to:</p>
                            <ol>
                                <li>Confirm integration <code>76398</code> is in TEST mode</li>
                                <li>Provide the correct test card numbers for your account</li>
                                <li>Enable test transactions on your integration</li>
                            </ol>
                        </div>

                        <div class="mt-4">
                            <a href="{{ url('/subscriptions') }}" class="btn btn-primary">Go to Subscriptions</a>
                            <a href="https://uae.paymob.com" target="_blank" class="btn btn-secondary">Open Paymob Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
