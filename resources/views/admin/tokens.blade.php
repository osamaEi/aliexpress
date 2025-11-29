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

    <!-- Diagnostic Information -->
    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="ri-information-line me-2"></i>{{ __('messages.configuration_diagnostics') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <strong>{{ __('messages.token_error_message') }}</strong>
                <ol class="mb-0 mt-2">
                    <li>{{ __('messages.verify_app_key') }} <a href="https://openservice.aliexpress.com/myapp/index.htm" target="_blank">AliExpress Open Platform</a></li>
                    <li>{{ __('messages.ensure_app_active') }}</li>
                    <li>{{ __('messages.check_redirect_uri') }}</li>
                    <li>{{ __('messages.app_key_must_be_valid') }}</li>
                </ol>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <p><strong>{{ __('messages.current_app_key') }}:</strong> <code>{{ $tokens['app_key'] ?: __('messages.not_configured') }}</code></p>
                    <p><strong>{{ __('messages.callback_url') }}:</strong> <code>{{ route('admin.tokens.callback') }}</code></p>
                </div>
                <div class="col-md-6">
                    <p><strong>{{ __('messages.oauth_endpoint') }}:</strong> <code>https://oauth.aliexpress.com/authorize</code></p>
                    <p><strong>{{ __('messages.app_secret_configured') }}:</strong>
                        <span class="badge {{ $tokens['app_secret'] ? 'bg-success' : 'bg-danger' }}">
                            {{ $tokens['app_secret'] ? __('messages.yes') : __('messages.no') }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="mt-3">
                <p class="text-muted mb-0">
                    <i class="ri-lightbulb-line"></i>
                    <strong>{{ __('messages.important') }}:</strong> {{ __('messages.check_redirect_uri') }} <code>{{ route('admin.tokens.callback') }}</code>
                </p>
            </div>
        </div>
    </div>

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
                            placeholder="{{ __('messages.app_key') }}"
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
                            placeholder="{{ __('messages.app_secret') }}"
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
                <i class="ri-refresh-line me-2"></i>{{ __('messages.generate_access_token') }}
            </h5>
        </div>
        <div class="card-body">
            <p class="mb-3">
                {{ __('messages.after_saving_credentials') }}.
                {{ __('messages.redirected_to_aliexpress') }}.
            </p>
            <a href="{{ route('admin.tokens.generate') }}" class="btn btn-success">
                <i class="ri-key-line me-1"></i>
                {{ __('messages.generate_access_token') }}
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
                        <i class="ri-information-line me-2"></i>{{ __('messages.important_instructions') }}
                    </h6>
                    <ol class="mb-3">
                        <li class="mb-2">
                            <strong>{{ __('messages.get_aliexpress_credentials') }}:</strong>
                            <ul>
                                <li>{{ __('messages.visit') }} <a href="https://portals.aliexpress.com" target="_blank">AliExpress Open Platform</a></li>
                                <li>{{ __('messages.login_with_seller_account') }}</li>
                                <li>{{ __('messages.go_to_app_management') }}</li>
                                <li>{{ __('messages.create_or_select_app') }}</li>
                                <li>{{ __('messages.copy_app_key_secret') }}</li>
                            </ul>
                        </li>
                        <li class="mb-2">
                            <strong>{{ __('messages.configure_redirect_uri') }}:</strong>
                            <ul>
                                <li>{{ __('messages.add_redirect_uri') }}:</li>
                                <li><code>{{ route('admin.tokens.callback') }}</code></li>
                                <li>{{ __('messages.save_configuration') }}</li>
                            </ul>
                        </li>
                        <li class="mb-2">
                            <strong>{{ __('messages.enter_credentials') }}:</strong>
                            <ul>
                                <li>{{ __('messages.paste_credentials') }}</li>
                                <li>{{ __('messages.click_save_changes') }}</li>
                            </ul>
                        </li>
                        <li class="mb-2">
                            <strong>{{ __('messages.generate_access_token') }}:</strong>
                            <ul>
                                <li>{{ __('messages.after_saving_click_generate') }}</li>
                                <li>{{ __('messages.redirected_to_aliexpress') }}</li>
                                <li>{{ __('messages.after_auth_redirect_back') }}</li>
                            </ul>
                        </li>
                    </ol>
                    <div class="alert alert-danger mb-0">
                        <strong><i class="ri-alert-line me-2"></i>{{ __('messages.common_errors') }}:</strong>
                        <ul class="mb-0 mt-2">
                            <li><code>appkey不存在 (appkey does not exist)</code> - {{ __('messages.invalid_app_key') }}</li>
                            <li><code>Invalid redirect_uri</code> - {{ __('messages.invalid_redirect_uri') }}</li>
                            <li><code>Invalid app_secret</code> - {{ __('messages.invalid_app_secret') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
