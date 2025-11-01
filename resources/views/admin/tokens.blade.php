@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.token_management') }}</h4>
        <p class="text-muted">{{ __('messages.aliexpress_tokens') }}</p>
    </div>

    <!-- Token Management Form -->
    <div class="card">
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
                            value="{{ old('app_key', substr(config('aliexpress.app_key'), 0, 10) . '...' . substr(config('aliexpress.app_key'), -10)) }}"
                            placeholder="{{ __('messages.token_hint') }}"
                        >
                        @error('app_key')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">{{ __('messages.current_value') }}: {{ substr(config('aliexpress.app_key'), 0, 10) }}...{{ substr(config('aliexpress.app_key'), -10) }}</small>
                </div>

                <!-- App Secret -->
                <div class="mb-4">
                    <label for="app_secret" class="form-label">{{ __('messages.app_secret') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ri-lock-password-line"></i>
                        </span>
                        <input
                            type="password"
                            class="form-control @error('app_secret') is-invalid @enderror"
                            id="app_secret"
                            name="app_secret"
                            placeholder="{{ __('messages.token_hint') }}"
                        >
                        @error('app_secret')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">{{ __('messages.current_value') }}: ********{{ substr(config('aliexpress.app_secret'), -4) }}</small>
                </div>

                <!-- Access Token -->
                <div class="mb-4">
                    <label for="access_token" class="form-label">{{ __('messages.access_token') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ri-shield-keyhole-line"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control @error('access_token') is-invalid @enderror"
                            id="access_token"
                            name="access_token"
                            placeholder="{{ __('messages.token_hint') }}"
                        >
                        @error('access_token')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">{{ __('messages.current_value') }}: {{ substr(config('aliexpress.access_token'), 0, 15) }}...{{ substr(config('aliexpress.access_token'), -15) }}</small>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>
                        {{ __('messages.update_tokens') }}
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Information Card -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex align-items-start">
                <div class="badge rounded-pill bg-label-info me-3 p-2">
                    <i class="ri-information-line ri-24px"></i>
                </div>
                <div>
                    <h6 class="mb-2">{{ __('messages.info') }}</h6>
                    <ul class="mb-0">
                        <li>{{ __('messages.token_hint') }}</li>
                        <li>After updating tokens, the configuration cache will be cleared automatically</li>
                        <li>Make sure to backup your .env file before making changes</li>
                        <li>Invalid tokens may cause API calls to fail</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
