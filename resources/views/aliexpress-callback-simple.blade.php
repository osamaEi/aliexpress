<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Callback - AliExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .token-display {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .token-value {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
            background: white;
            padding: 15px;
            border-radius: 5px;
            border: 2px solid #dee2e6;
        }
        .btn-copy {
            width: 100%;
            margin-top: 10px;
            padding: 15px;
            font-size: 18px;
            font-weight: 600;
        }
        .btn-show {
            width: 100%;
            margin-top: 10px;
            padding: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-{{ $status === 'success' ? 'success' : 'danger' }} text-white">
                <h4 class="mb-0">
                    <i class="ri-{{ $status === 'success' ? 'checkbox-circle' : 'error-warning' }}-line me-2"></i>
                    OAuth Callback Result
                </h4>
            </div>
            <div class="card-body">
                @if($status === 'success')
                    <div class="alert alert-success">
                        <h5><i class="ri-checkbox-circle-line me-2"></i>{{ $message }}</h5>
                    </div>

                    @if(isset($tokenData['access_token']))
                    <div class="token-display">
                        <h5 class="mb-3">
                            <i class="ri-key-line me-2"></i>
                            Your Access Token
                        </h5>

                        <!-- Show/Hide Token -->
                        <div class="mb-3">
                            <div id="tokenHidden" class="token-value" style="display: block;">
                                <span style="letter-spacing: 3px;">••••••••••••••••••••••••••••••••••••••••••••</span>
                            </div>
                            <div id="tokenVisible" class="token-value" style="display: none;">
                                {{ $tokenData['access_token'] }}
                            </div>
                        </div>

                        <!-- Show Button -->
                        <button class="btn btn-outline-primary btn-show" onclick="toggleToken()">
                            <i class="ri-eye-line" id="eyeIcon"></i>
                            <span id="toggleText">Show Token</span>
                        </button>

                        <!-- Copy Button -->
                        <button class="btn btn-primary btn-copy" onclick="copyToClipboard()">
                            <i class="ri-file-copy-line me-2"></i>
                            <span id="copyText">Copy Access Token</span>
                        </button>

                        <!-- Hidden input for copying -->
                        <input type="hidden" id="tokenInput" value="{{ $tokenData['access_token'] }}">
                    </div>
                    @endif

                    @if(isset($tokenData['expires_in']))
                    <div class="alert alert-info">
                        <i class="ri-time-line me-2"></i>
                        <strong>Expires in:</strong> {{ $tokenData['expires_in'] }} seconds ({{ round($tokenData['expires_in'] / 3600, 2) }} hours)
                    </div>
                    @endif

                    <div class="alert alert-warning">
                        <h6><i class="ri-footprint-line me-2"></i>Next Steps:</h6>
                        <ol class="mb-0">
                            <li>Click <strong>"Copy Access Token"</strong> above</li>
                            <li>Open your <code>.env</code> file</li>
                            <li>Find: <code>ALIEXPRESS_ACCESS_TOKEN=</code></li>
                            <li>Paste the token after the <code>=</code> sign</li>
                            <li>Save the file</li>
                            <li>Run: <code>php artisan config:clear</code></li>
                        </ol>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ url('/test-aliexpress') }}" class="btn btn-success btn-lg">
                            <i class="ri-test-tube-line me-2"></i>
                            Test API Connection
                        </a>
                        <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">
                            <i class="ri-home-line me-2"></i>
                            Back to Dashboard
                        </a>
                    </div>

                    <!-- Debug: Show Full Response -->
                    <div class="mt-4">
                        <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#fullResponse">
                            <i class="ri-code-line me-2"></i>
                            Show Full API Response (Debug)
                        </button>
                        <div class="collapse mt-2" id="fullResponse">
                            <div class="alert alert-dark">
                                <pre style="max-height: 400px; overflow: auto; margin: 0;">{{ json_encode($tokenData, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>

                    @if(isset($tokenData['refresh_token']))
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#refreshToken">
                            Show Refresh Token
                        </button>
                        <div class="collapse mt-2" id="refreshToken">
                            <div class="alert alert-secondary">
                                <strong>Refresh Token:</strong>
                                <code class="d-block mt-2">{{ $tokenData['refresh_token'] }}</code>
                            </div>
                        </div>
                    </div>
                    @endif

                @else
                    <div class="alert alert-danger">
                        <h5><i class="ri-error-warning-line me-2"></i>{{ $message }}</h5>
                        @if(isset($error))
                        <hr>
                        <p class="mb-0"><strong>Error:</strong> {{ $error }}</p>
                        @endif
                    </div>

                    @if(isset($debugInfo))
                    <div class="alert alert-secondary">
                        <h6>Debug Info:</h6>
                        <ul class="mb-0">
                            @foreach($debugInfo as $key => $value)
                            <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="d-grid gap-2">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let tokenVisible = false;

        function toggleToken() {
            tokenVisible = !tokenVisible;
            const hiddenDiv = document.getElementById('tokenHidden');
            const visibleDiv = document.getElementById('tokenVisible');
            const eyeIcon = document.getElementById('eyeIcon');
            const toggleText = document.getElementById('toggleText');

            if (tokenVisible) {
                hiddenDiv.style.display = 'none';
                visibleDiv.style.display = 'block';
                eyeIcon.className = 'ri-eye-off-line';
                toggleText.textContent = 'Hide Token';
            } else {
                hiddenDiv.style.display = 'block';
                visibleDiv.style.display = 'none';
                eyeIcon.className = 'ri-eye-line';
                toggleText.textContent = 'Show Token';
            }
        }

        function copyToClipboard() {
            const tokenInput = document.getElementById('tokenInput');
            const copyBtn = document.querySelector('.btn-copy');
            const copyText = document.getElementById('copyText');

            // Create temporary textarea
            const textarea = document.createElement('textarea');
            textarea.value = tokenInput.value;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                document.execCommand('copy');

                // Success feedback
                copyBtn.classList.remove('btn-primary');
                copyBtn.classList.add('btn-success');
                copyText.innerHTML = '<i class="ri-check-line me-2"></i>Copied Successfully!';

                // Reset after 2 seconds
                setTimeout(() => {
                    copyBtn.classList.remove('btn-success');
                    copyBtn.classList.add('btn-primary');
                    copyText.innerHTML = 'Copy Access Token';
                }, 2000);
            } catch (err) {
                alert('Failed to copy. Please copy manually.');
            }

            document.body.removeChild(textarea);
        }
    </script>
</body>
</html>
