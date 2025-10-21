@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ri-test-tube-line me-2"></i>
                AliExpress API Connection Test
            </h5>
            <span class="badge bg-label-{{ $status === 'success' ? 'success' : ($status === 'warning' ? 'warning' : 'danger') }}">
                {{ ucfirst($status) }}
            </span>
        </div>
        <div class="card-body">
            <!-- Configuration Status -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="ri-settings-3-line me-2"></i>
                        Configuration Status
                    </h6>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <strong>API Key:</strong><br>
                            <span class="badge bg-label-{{ $configuration['api_key'] ? 'success' : 'danger' }} mt-1">
                                {{ $configuration['api_key'] ?? 'NOT SET' }}
                            </span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <strong>API Secret:</strong><br>
                            <span class="badge bg-label-{{ $configuration['api_secret'] !== '❌ NOT SET' ? 'success' : 'danger' }} mt-1">
                                {{ $configuration['api_secret'] }}
                            </span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <strong>Access Token:</strong><br>
                            <span class="badge bg-label-{{ $configuration['access_token'] === '✅ SET' ? 'success' : 'danger' }} mt-1">
                                {{ $configuration['access_token'] }}
                            </span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <strong>API URL:</strong><br>
                            <code class="small">{{ Str::limit($configuration['api_url'], 25) }}</code>
                        </div>
                    </div>
                </div>
            </div>

            @if($status === 'success')
                <!-- Success Message -->
                <div class="alert alert-success d-flex align-items-start" role="alert">
                    <i class="ri-checkbox-circle-line me-2 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Connection Successful!</h6>
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                </div>

                <!-- Product Information -->
                @if(isset($apiResponse['title']))
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ri-product-hunt-line me-2"></i>
                            Test Product Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if(isset($apiResponse['image_url']) && $apiResponse['image_url'] !== 'N/A')
                            <div class="col-md-3 mb-3 mb-md-0">
                                <img src="{{ $apiResponse['image_url'] }}"
                                     alt="Product"
                                     class="img-fluid rounded border"
                                     style="max-height: 200px; object-fit: cover;">
                            </div>
                            @endif
                            <div class="col-md-9">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th style="width: 150px;">Product ID:</th>
                                            <td><code>{{ $apiResponse['product_id'] }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Title:</th>
                                            <td>{{ $apiResponse['title'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>Price:</th>
                                            <td>
                                                <span class="badge bg-success">
                                                    {{ $apiResponse['price'] }} {{ $apiResponse['currency'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Test Time:</th>
                                            <td>{{ $testInfo['timestamp'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Next Steps -->
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-white mb-3">
                            <i class="ri-lightbulb-line me-2"></i>
                            What's Next?
                        </h6>
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="d-flex align-items-start">
                                    <i class="ri-search-line me-2 mt-1"></i>
                                    <div>
                                        <strong>Search Products</strong>
                                        <p class="mb-0 small opacity-75">Use the API to search and import products</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="d-flex align-items-start">
                                    <i class="ri-flashlight-line me-2 mt-1"></i>
                                    <div>
                                        <strong>Test All Endpoints</strong>
                                        <p class="mb-0 small opacity-75">Run comprehensive API tests</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <i class="ri-shopping-cart-line me-2 mt-1"></i>
                                    <div>
                                        <strong>Start Importing</strong>
                                        <p class="mb-0 small opacity-75">Begin importing products to your store</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($status === 'warning')
                <!-- Warning Message -->
                <div class="alert alert-warning d-flex align-items-start" role="alert">
                    <i class="ri-alert-line me-2 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Warning</h6>
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                </div>

            @else
                <!-- Error Message -->
                <div class="alert alert-danger d-flex align-items-start" role="alert">
                    <i class="ri-error-warning-line me-2 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Connection Failed</h6>
                        <p class="mb-0">{{ $message }}</p>
                        @if(isset($error))
                            <hr>
                            <p class="mb-0"><strong>Error:</strong> {{ $error }}</p>
                        @endif
                    </div>
                </div>

                <!-- Troubleshooting -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="ri-tools-line me-2"></i>
                            Troubleshooting Steps
                        </h6>
                        <ol class="mb-0">
                            @if($configuration['access_token'] !== '✅ SET')
                                <li class="mb-2">
                                    <strong>Missing Access Token:</strong>
                                    You need to authorize your app and get an access token.
                                    <a href="{{ url('/aliexpress-oauth-start') }}" class="btn btn-sm btn-primary ms-2">
                                        Get Access Token
                                    </a>
                                </li>
                            @endif
                            @if(!$configuration['api_key'])
                                <li class="mb-2">
                                    <strong>Missing API Key:</strong>
                                    Set <code>ALIEXPRESS_API_KEY</code> in your <code>.env</code> file
                                </li>
                            @endif
                            @if($configuration['api_secret'] === '❌ NOT SET')
                                <li class="mb-2">
                                    <strong>Missing API Secret:</strong>
                                    Set <code>ALIEXPRESS_API_SECRET</code> in your <code>.env</code> file
                                </li>
                            @endif
                            <li class="mb-2">
                                Check your internet connection
                            </li>
                            <li class="mb-2">
                                Verify your API credentials are correct at
                                <a href="https://openservice.aliexpress.com/" target="_blank">
                                    AliExpress Open Platform <i class="ri-external-link-line"></i>
                                </a>
                            </li>
                            <li class="mb-0">
                                Run <code>php artisan config:clear</code> after updating <code>.env</code>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Debug Information -->
                @if(isset($debugInfo))
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="ri-bug-line me-2"></i>
                            Debug Information
                        </h6>
                        <ul class="list-unstyled mb-0 font-monospace small">
                            @foreach($debugInfo as $key => $value)
                            <li class="mb-2">
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                <code>{{ $value }}</code>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            @endif

            <!-- Action Buttons -->
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <button class="btn btn-primary" onclick="window.location.reload()">
                    <i class="ri-refresh-line me-2"></i>
                    Test Again
                </button>

                @if($status === 'success')
                <a href="{{ url('/test-aliexpress-all') }}" class="btn btn-success">
                    <i class="ri-flashlight-line me-2"></i>
                    Test All Endpoints
                </a>
                @else
                <a href="{{ url('/aliexpress-oauth-start') }}" class="btn btn-warning">
                    <i class="ri-key-2-line me-2"></i>
                    Get Access Token
                </a>
                @endif

                <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">
                    <i class="ri-home-line me-2"></i>
                    Dashboard
                </a>
            </div>

            <!-- API Response (Collapsible) -->
            <div class="mt-4">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#fullResponse">
                    <i class="ri-code-line me-1"></i>
                    Show Full API Response
                </button>

                <div class="collapse mt-3" id="fullResponse">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <pre class="text-white mb-0"><code>{{ json_encode([
                                'test_info' => $testInfo,
                                'configuration' => $configuration,
                                'status' => $status,
                                'message' => $message ?? null,
                                'api_response' => $apiResponse ?? null,
                                'error' => $error ?? null,
                                'debug_info' => $debugInfo ?? null,
                            ], JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Information -->
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="card-title mb-3">
                <i class="ri-information-line me-2"></i>
                About This Test
            </h6>
            <p class="mb-3">
                This test validates your AliExpress API connection by fetching product details for a sample product
                (ID: <code>{{ $testInfo['product_id_tested'] }}</code>).
            </p>
            <div class="row">
                <div class="col-md-4">
                    <strong>API Endpoint:</strong><br>
                    <code class="small">aliexpress.ds.product.get</code>
                </div>
                <div class="col-md-4">
                    <strong>Test Product ID:</strong><br>
                    <code class="small">{{ $testInfo['product_id_tested'] }}</code>
                </div>
                <div class="col-md-4">
                    <strong>Test Time:</strong><br>
                    <code class="small">{{ $testInfo['timestamp'] }}</code>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
