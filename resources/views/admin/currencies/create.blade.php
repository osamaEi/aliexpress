@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.add_currency') }}</h4>
        <p class="text-muted">{{ __('messages.add_new_currency_description') }}</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.currencies.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">{{ __('messages.currency_code') }} *</label>
                        <input type="text"
                               class="form-control @error('code') is-invalid @enderror"
                               id="code"
                               name="code"
                               value="{{ old('code') }}"
                               placeholder="USD"
                               maxlength="3"
                               required
                               style="text-transform: uppercase;">
                        <small class="text-muted">{{ __('messages.currency_code_hint') }}</small>
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">{{ __('messages.currency_name') }} *</label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="{{ __('messages.currency_name_placeholder') }}"
                               required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="symbol" class="form-label">{{ __('messages.symbol') }} *</label>
                        <input type="text"
                               class="form-control @error('symbol') is-invalid @enderror"
                               id="symbol"
                               name="symbol"
                               value="{{ old('symbol') }}"
                               placeholder="$"
                               maxlength="10"
                               required>
                        <small class="text-muted">{{ __('messages.symbol_hint') }}</small>
                        @error('symbol')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="exchange_rate" class="form-label">{{ __('messages.exchange_rate') }} *</label>
                        <input type="number"
                               class="form-control @error('exchange_rate') is-invalid @enderror"
                               id="exchange_rate"
                               name="exchange_rate"
                               value="{{ old('exchange_rate', 1.0000) }}"
                               step="0.0001"
                               min="0.0001"
                               required>
                        <small class="text-muted">{{ __('messages.exchange_rate_hint') }}</small>
                        @error('exchange_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="sort_order" class="form-label">{{ __('messages.sort_order') }}</label>
                        <input type="number"
                               class="form-control @error('sort_order') is-invalid @enderror"
                               id="sort_order"
                               name="sort_order"
                               value="{{ old('sort_order', 0) }}"
                               min="0">
                        <small class="text-muted">{{ __('messages.sort_order_hint') }}</small>
                        @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                {{ __('messages.active') }}
                            </label>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="is_default"
                                   name="is_default"
                                   {{ old('is_default') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                {{ __('messages.set_as_default_currency') }}
                            </label>
                            <small class="text-muted d-block">{{ __('messages.default_currency_hint') }}</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> {{ __('messages.save') }}
                    </button>
                    <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line"></i> {{ __('messages.back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
