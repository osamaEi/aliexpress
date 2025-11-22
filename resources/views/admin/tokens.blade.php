@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.token_management') }}</h4>
        <p class="text-muted">{{ __('messages.aliexpress_tokens') }}</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-check-line me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Token Status Card -->
    @if($tokenStatus)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ri-shield-check-line me-2"></i>{{ __('messages.access_token') }} {{ __('messages.status') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge {{ $tokenStatus['authorized'] ? 'bg-success' : 'bg-danger' }} me-2">
                            {{ $tokenStatus['authorized'] ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </div>
                    <p class="mb-2">
                        <strong>{{ __('messages.expires_at') }}:</strong>
                        {{ $tokenStatus['expires_at']->format('Y-m-d H:i:s') }}
                        <span class="text-muted">({{ $tokenStatus['expires_in'] }})</span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong>{{ __('messages.can_refresh') }}:</strong>
                        <span class="badge {{ $tokenStatus['can_refresh'] ? 'bg-success' : 'bg-danger' }}">
                            {{ $tokenStatus['can_refresh'] ? __('messages.yes') : __('messages.no') }}
                        </span>
                    </p>
                    <p class="mb-0">
                        <strong>{{ __('messages.refresh_expires_at') }}:</strong>
                        {{ $tokenStatus['refresh_expires_at']->format('Y-m-d H:i:s') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- API Credentials Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.update_tokens') }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.tokens.update') }}">
                @csrf

                <!-- App Key -->
                <div class="mb-4">
                    <label for="app_key" class="form-label">{{ __('messages.app_key') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ri-key-2-line"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control @error('app_key') is-invalid @enderror"
                            id="app_key"
                            name="app_key"
                            value="{{ old('app_key', $tokens['app_key']) }}"
                            placeholder="Enter your AliExpress App Key"
                            required
                        >
                        @error('app_key')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @if($tokens['app_key'])
                    <small class="text-muted">
                        {{ __('messages.current_value') }}: {{ substr($tokens['app_key'], 0, 10) }}...{{ substr($tokens['app_key'], -10) }}
                    </small>
                    @endif
                </div>

                <!-- App Secret -->
                <div class="mb-4">
                    <label for="app_secret" class="form-label">{{ __('messages.app_secret') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ri-lock-password-line"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control @error('app_secret') is-invalid @enderror"
                            id="app_secret"
                            name="app_secret"
                            value="{{ old('app_secret', $tokens['app_secret']) }}"
                            placeholder="Enter your AliExpress App Secret"
                            required
                        >
                        @error('app_secret')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @if($tokens['app_secret'])
                    <small class="text-muted">
                        {{ __('messages.current_value') }}: ********{{ substr($tokens['app_secret'], -4) }}
                    </small>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>
                        {{ __('messages.save_changes') }}
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Generate Access Token Card -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ri-refresh-line me-2"></i>Generate Access Token
            </h5>
        </div>
        <div class="card-body">
            <p class="mb-3">
                After saving your App Key and App Secret above, click the button below to authorize and generate an access token.
                You will be redirected to AliExpress to authorize the application.
            </p>
            <a href="{{ route('admin.tokens.generate') }}" class="btn btn-success">
                <i class="ri-key-line me-1"></i>
                Generate Access Token
            </a>
        </div>
    </div>

    <!-- Information Card -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex align-items-start">
                <div class="badge rounded-pill bg-label-warning me-3 p-2">
                    <i class="ri-error-warning-line ri-24px"></i>
                </div>
                <div>
                    <h6 class="mb-2">
                        <i class="ri-information-line me-2"></i>Important Instructions
                    </h6>
                    <ol class="mb-3">
                        <li class="mb-2">
                            <strong>Get Your AliExpress API Credentials:</strong>
                            <ul>
                                <li>Visit <a href="https://portals.aliexpress.com" target="_blank">AliExpress Open Platform</a></li>
                                <li>Login with your AliExpress seller account</li>
                                <li>Go to "App Management" → "My Apps"</li>
                                <li>Create a new app or select an existing one</li>
                                <li>Copy your <strong>App Key</strong> and <strong>App Secret</strong></li>
                            </ul>
                        </li>
                        <li class="mb-2">
                            <strong>Configure Redirect URI:</strong>
                            <ul>
                                <li>In your AliExpress app settings, add this redirect URI:</li>
                                <li><code>{{ route('admin.tokens.callback') }}</code></li>
                                <li>Save the configuration in AliExpress</li>
                            </ul>
                        </li>
                        <li class="mb-2">
                            <strong>Enter Credentials Above:</strong>
                            <ul>
                                <li>Paste your App Key and App Secret into the form above</li>
                                <li>Click "Save Changes" to store them in your .env file</li>
                            </ul>
                        </li>
                        <li class="mb-2">
                            <strong>Generate Access Token:</strong>
                            <ul>
                                <li>After saving, click "Generate Access Token"</li>
                                <li>You will be redirected to AliExpress to authorize</li>
                                <li>After authorization, you'll be redirected back with a valid token</li>
                            </ul>
                        </li>
                    </ol>
                    <div class="alert alert-danger mb-0">
                        <strong><i class="ri-alert-line me-2"></i>Common Errors:</strong>
                        <ul class="mb-0 mt-2">
                            <li><code>appkey不存在 (appkey does not exist)</code> - Your App Key is invalid or doesn't exist</li>
                            <li><code>Invalid redirect_uri</code> - The redirect URI is not configured in your AliExpress app</li>
                            <li><code>Invalid app_secret</code> - Your App Secret is incorrect</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
