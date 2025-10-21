@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ri-key-2-line me-2"></i>
                OAuth Token Creation Test
            </h5>
            <span class="badge bg-label-{{ $status === 'success' ? 'success' : ($status === 'pending' ? 'warning' : 'danger') }}">
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
                        <div class="col-md-4 mb-2">
                            <strong>API Key:</strong><br>
                            <span class="badge bg-label-{{ $configuration['api_key'] ? 'success' : 'danger' }} mt-1">
                                {{ $configuration['api_key'] ?? 'NOT SET' }}
                            </span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>API Secret:</strong><br>
                            <span class="badge bg-label-{{ $configuration['api_secret'] !== '❌ NOT SET' ? 'success' : 'danger' }} mt-1">
                                {{ $configuration['api_secret'] }}
                            </span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Token URL:</strong><br>
                            <code class="small">{{ $configuration['token_url'] }}</code>
                        </div>
                    </div>
                </div>
            </div>

            @if($status === 'success')
                <!-- Success Message -->
                <div class="alert alert-success d-flex align-items-start" role="alert">
                    <i class="ri-checkbox-circle-line me-2 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-2">🎉 Token Created Successfully!</h6>
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                </div>

                <!-- Token Information -->
                @if(isset($apiResponse['access_token']))
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ri-shield-keyhole-line me-2"></i>
                            Access Token Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Access Token:</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace"
                                       id="accessToken"
                                       value="{{ $apiResponse['access_token'] }}"
                                       readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyToken('accessToken')">
                                    <i class="ri-file-copy-line"></i> Copy
                                </button>
                            </div>
                        </div>

                        @if(isset($apiResponse['refresh_token']))
                        <div class="mb-3">
                            <label class="form-label fw-medium">Refresh Token:</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace"
                                       id="refreshToken"
                                       value="{{ $apiResponse['refresh_token'] }}"
                                       readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyToken('refreshToken')">
                                    <i class="ri-file-copy-line"></i> Copy
                                </button>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            @if(isset($apiResponse['expires_in']))
                            <div class="col-md-6 mb-2">
                                <strong>Expires In:</strong><br>
                                <span class="badge bg-warning">
                                    {{ $apiResponse['expires_in'] }} seconds
                                    ({{ round($apiResponse['expires_in'] / 3600, 2) }} hours)
                                </span>
                            </div>
                            @endif

                            @if(isset($apiResponse['user_id']))
                            <div class="col-md-6 mb-2">
                                <strong>User ID:</strong><br>
                                <code>{{ $apiResponse['user_id'] }}</code>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Next Steps -->
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-white mb-3">
                            <i class="ri-footprint-line me-2"></i>
                            Next Steps
                        </h6>
                        <ol class="mb-0 ps-3">
                            <li class="mb-2">Click the "Copy" button next to the Access Token above</li>
                            <li class="mb-2">Open your <code class="bg-white bg-opacity-25 px-2 py-1 rounded">.env</code> file</li>
                            <li class="mb-2">Add or update: <code class="bg-white bg-opacity-25 px-2 py-1 rounded">ALIEXPRESS_ACCESS_TOKEN=your_token_here</code></li>
                            <li class="mb-2">Run command: <code class="bg-white bg-opacity-25 px-2 py-1 rounded">php artisan config:clear</code></li>
                            <li class="mb-0">Test your API connection using the button below</li>
                        </ol>
                    </div>
                </div>

            @elseif($status === 'pending')
                <!-- Pending - No Code Provided -->
                <div class="alert alert-warning d-flex align-items-start" role="alert">
                    <i class="ri-alert-line me-2 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Authorization Code Required</h6>
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                </div>

                <!-- OAuth Flow Instructions -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="ri-roadmap-line me-2"></i>
                            OAuth Flow Instructions
                        </h6>
                        @if(isset($oauthFlowInstructions))
                        <ol class="mb-0">
                            @foreach($oauthFlowInstructions as $key => $instruction)
                            <li class="mb-2">{{ $instruction }}</li>
                            @endforeach
                        </ol>
                        @endif
                    </div>
                </div>

                <!-- Authorization URL Example -->
                @if(isset($authorizationUrlExample))
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="ri-links-line me-2"></i>
                            Example Authorization URL
                        </h6>
                        <div class="bg-white p-3 rounded border font-monospace small text-break">
                            {{ $authorizationUrlExample }}
                        </div>
                        <p class="mt-3 mb-0 text-muted small">
                            Replace <code>YOUR_APP_KEY</code> and <code>YOUR_REDIRECT_URI</code> with your actual values.
                        </p>
                    </div>
                </div>
                @endif

                <!-- Manual Code Input -->
                <div class="card border-primary mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="ri-keyboard-line me-2"></i>
                            Enter Authorization Code
                        </h6>
                        <form method="GET" action="{{ url('/test-aliexpress-token') }}">
                            <div class="mb-3">
                                <label for="authCode" class="form-label">Authorization Code:</label>
                                <input type="text" class="form-control" id="authCode" name="code"
                                       placeholder="Paste your authorization code here..."
                                       required>
                                <div class="form-text">
                                    After authorizing your app on AliExpress, paste the authorization code here.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-send-plane-line me-2"></i>
                                Create Token
                            </button>
                        </form>
                    </div>
                </div>

            @else
                <!-- Error Message -->
                <div class="alert alert-danger d-flex align-items-start" role="alert">
                    <i class="ri-error-warning-line me-2 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Token Creation Failed</h6>
                        <p class="mb-0">{{ $message }}</p>
                        @if(isset($error))
                            <hr>
                            <p class="mb-0"><strong>Error:</strong> {{ $error }}</p>
                        @endif
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

                <!-- Retry Form -->
                <div class="card border-warning mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="ri-refresh-line me-2"></i>
                            Try Again
                        </h6>
                        <form method="GET" action="{{ url('/test-aliexpress-token') }}">
                            <div class="mb-3">
                                <label for="authCode" class="form-label">Authorization Code:</label>
                                <input type="text" class="form-control" id="authCode" name="code"
                                       placeholder="Paste your authorization code here..."
                                       value="{{ $requestData['code_provided'] ?? '' }}"
                                       required>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="ri-send-plane-line me-2"></i>
                                Retry Token Creation
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                @if($status === 'success')
                    <a href="{{ url('/test-aliexpress') }}" class="btn btn-success">
                        <i class="ri-test-tube-line me-2"></i>
                        Test API Connection
                    </a>
                @endif

                <a href="{{ url('/aliexpress-oauth-start') }}" class="btn btn-primary">
                    <i class="ri-login-circle-line me-2"></i>
                    Start OAuth Flow
                </a>

                <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">
                    <i class="ri-home-line me-2"></i>
                    Dashboard
                </a>
            </div>

            <!-- Full Response (Collapsible) -->
            @if($status !== 'pending')
            <div class="mt-4">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#fullResponse">
                    <i class="ri-code-line me-1"></i>
                    Show Full API Response
                </button>

                <div class="collapse mt-3" id="fullResponse">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <pre class="text-white mb-0"><code>{{ json_encode([
                                'test_info' => $testInfo ?? null,
                                'configuration' => $configuration,
                                'request_data' => $requestData ?? null,
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
            @endif
        </div>
    </div>

    <!-- Information Card -->
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="card-title mb-3">
                <i class="ri-information-line me-2"></i>
                About OAuth Token Creation
            </h6>
            <p class="mb-3">
                This endpoint exchanges your authorization code for an access token. The authorization code is obtained
                through the OAuth2 authorization flow after you approve your app on AliExpress.
            </p>
            <div class="row">
                <div class="col-md-6">
                    <strong>Recommended Approach:</strong>
                    <p class="mb-0 small text-muted">
                        Use the <a href="{{ url('/aliexpress-oauth-start') }}">automated OAuth flow</a> which handles
                        the entire process automatically.
                    </p>
                </div>
                <div class="col-md-6">
                    <strong>Manual Approach:</strong>
                    <p class="mb-0 small text-muted">
                        Get the authorization code manually and paste it in the form above to create your access token.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToken(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices

    navigator.clipboard.writeText(input.value).then(() => {
        // Show success feedback
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="ri-check-line"></i> Copied!';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');

        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);
    });
}
</script>
@endsection
