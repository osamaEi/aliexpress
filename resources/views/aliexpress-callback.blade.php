@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ri-checkbox-circle-line me-2"></i>
                OAuth Callback Result
            </h5>
            <span class="badge bg-label-{{ $status === 'success' ? 'success' : 'danger' }}">
                {{ ucfirst($status) }}
            </span>
        </div>
        <div class="card-body">
            @if($status === 'success')
                <!-- Success Message -->
                <div class="alert alert-success d-flex align-items-start" role="alert">
                    <i class="ri-checkbox-circle-line me-2 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-2">🎉 Authorization Successful!</h6>
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                </div>

                <!-- Token Information -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="ri-key-line me-2"></i>
                            Access Token Details
                        </h6>

                        @if(isset($tokenData['access_token']))
                        <div class="mb-3">
                            <label class="form-label fw-medium">Access Token:</label>
                            <div class="input-group mb-2">
                                <input type="password" class="form-control font-monospace"
                                       id="accessToken"
                                       value="{{ $tokenData['access_token'] }}"
                                       readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility('accessToken', this)">
                                    <i class="ri-eye-line"></i> Show
                                </button>
                                <button class="btn btn-primary" type="button" onclick="copyToken('accessToken')">
                                    <i class="ri-file-copy-line"></i> Copy
                                </button>
                            </div>
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="ri-information-line me-2"></i>
                                <small>
                                    <strong>Quick Copy:</strong> Click "Show" to reveal the token, then click "Copy" to copy it to clipboard.
                                </small>
                            </div>
                        </div>
                        @endif

                        @if(isset($tokenData['refresh_token']))
                        <div class="mb-3">
                            <label class="form-label fw-medium">Refresh Token:</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace"
                                       id="refreshToken"
                                       value="{{ $tokenData['refresh_token'] }}"
                                       readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyToken('refreshToken')">
                                    <i class="ri-file-copy-line"></i> Copy
                                </button>
                            </div>
                        </div>
                        @endif

                        @if(isset($tokenData['expires_in']))
                        <div class="mb-0">
                            <label class="form-label fw-medium">Expires In:</label>
                            <div class="text-muted">
                                {{ $tokenData['expires_in'] }} seconds
                                ({{ round($tokenData['expires_in'] / 3600, 2) }} hours)
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-white mb-3">
                            <i class="ri-footprint-line me-2"></i>
                            Next Steps
                        </h6>
                        <ol class="mb-0 ps-3">
                            <li class="mb-2">Copy the Access Token above using the "Copy" button</li>
                            <li class="mb-2">Open your <code class="bg-white bg-opacity-25 px-2 py-1 rounded">.env</code> file</li>
                            <li class="mb-2">Add or update: <code class="bg-white bg-opacity-25 px-2 py-1 rounded">ALIEXPRESS_ACCESS_TOKEN=your_token_here</code></li>
                            <li class="mb-2">Run command: <code class="bg-white bg-opacity-25 px-2 py-1 rounded">php artisan config:clear</code></li>
                            <li class="mb-0">Test your API connection using the button below</li>
                        </ol>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ url('/test-aliexpress') }}" class="btn btn-success">
                        <i class="ri-test-tube-line me-2"></i>
                        Test API Connection
                    </a>
                    <a href="{{ url('/test-aliexpress-all') }}" class="btn btn-primary">
                        <i class="ri-flashlight-line me-2"></i>
                        Test All Endpoints
                    </a>
                    <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">
                        <i class="ri-home-line me-2"></i>
                        Back to Dashboard
                    </a>
                </div>

                <!-- Full Response (Collapsible) -->
                <div class="mt-4">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#fullResponse">
                        <i class="ri-code-line me-1"></i>
                        Show Full API Response
                    </button>

                    <div class="collapse mt-3" id="fullResponse">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <pre class="text-white mb-0"><code>{{ json_encode($tokenData, JSON_PRETTY_PRINT) }}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <!-- Error Message -->
                <div class="alert alert-danger d-flex align-items-start" role="alert">
                    <i class="ri-error-warning-line me-2 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Authorization Failed</h6>
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
                        <ul class="list-unstyled mb-0">
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

                <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ url('/aliexpress-oauth-start') }}" class="btn btn-primary">
                        <i class="ri-refresh-line me-2"></i>
                        Try Again
                    </a>
                    <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">
                        <i class="ri-home-line me-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Help Section -->
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="card-title mb-3">
                <i class="ri-question-line me-2"></i>
                Need Help?
            </h6>
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h6 class="fw-medium mb-2">Common Issues:</h6>
                    <ul class="mb-0">
                        <li>Make sure your API credentials are correct in <code>.env</code></li>
                        <li>Check if your app is approved on AliExpress Open Platform</li>
                        <li>Verify your redirect URI matches the one configured</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-medium mb-2">Resources:</h6>
                    <ul class="mb-0">
                        <li>
                            <a href="https://openservice.aliexpress.com/" target="_blank">
                                AliExpress Open Platform <i class="ri-external-link-line"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/test-aliexpress-token') }}">
                                Manual Token Creation
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTokenVisibility(elementId, button) {
    const input = document.getElementById(elementId);
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'ri-eye-off-line';
        button.innerHTML = '<i class="ri-eye-off-line"></i> Hide';
    } else {
        input.type = 'password';
        icon.className = 'ri-eye-line';
        button.innerHTML = '<i class="ri-eye-line"></i> Show';
    }
}

function copyToken(elementId) {
    const input = document.getElementById(elementId);

    // Temporarily change to text to copy
    const wasPassword = input.type === 'password';
    if (wasPassword) {
        input.type = 'text';
    }

    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices

    navigator.clipboard.writeText(input.value).then(() => {
        // Restore password type if it was password
        if (wasPassword) {
            input.type = 'password';
        }

        // Show success feedback
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="ri-check-line"></i> Copied!';
        button.classList.remove('btn-primary', 'btn-outline-primary');
        button.classList.add('btn-success');

        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-primary');
        }, 2000);
    });
}
</script>
@endsection
