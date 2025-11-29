@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.edit_subscription') }}</h4>
        <p class="text-muted">{{ $subscription->localized_name }}</p>
    </div>

    <!-- Edit Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.subscription_details') }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="mb-3">{{ __('messages.subscription_details') }}</h6>
                    </div>

                    <!-- Plan Name (English) -->
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">{{ __('messages.product_name') }} (English) <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name', $subscription->name) }}"
                            required
                        >
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Plan Name (Arabic) -->
                    <div class="col-md-6 mb-3">
                        <label for="name_ar" class="form-label">{{ __('messages.product_name') }} (العربية)</label>
                        <input
                            type="text"
                            class="form-control @error('name_ar') is-invalid @enderror"
                            id="name_ar"
                            name="name_ar"
                            value="{{ old('name_ar', $subscription->name_ar) }}"
                            dir="rtl"
                        >
                        @error('name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description (English) -->
                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">{{ __('messages.description') }} (English)</label>
                        <textarea
                            class="form-control @error('description') is-invalid @enderror"
                            id="description"
                            name="description"
                            rows="3"
                        >{{ old('description', $subscription->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description (Arabic) -->
                    <div class="col-md-6 mb-3">
                        <label for="description_ar" class="form-label">{{ __('messages.description') }} (العربية)</label>
                        <textarea
                            class="form-control @error('description_ar') is-invalid @enderror"
                            id="description_ar"
                            name="description_ar"
                            rows="3"
                            dir="rtl"
                        >{{ old('description_ar', $subscription->description_ar) }}</textarea>
                        @error('description_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Price -->
                    <div class="col-md-4 mb-3">
                        <label for="price" class="form-label">{{ __('messages.price') }} (AED) <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            step="0.01"
                            class="form-control @error('price') is-invalid @enderror"
                            id="price"
                            name="price"
                            value="{{ old('price', $subscription->price) }}"
                            required
                        >
                        @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Duration -->
                    <div class="col-md-4 mb-3">
                        <label for="duration_days" class="form-label">{{ __('messages.duration_days') }} <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control @error('duration_days') is-invalid @enderror"
                            id="duration_days"
                            name="duration_days"
                            value="{{ old('duration_days', $subscription->duration_days) }}"
                            required
                        >
                        @error('duration_days')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Sort Order -->
                    <div class="col-md-4 mb-3">
                        <label for="sort_order" class="form-label">{{ __('messages.sort_order') }}</label>
                        <input
                            type="number"
                            class="form-control @error('sort_order') is-invalid @enderror"
                            id="sort_order"
                            name="sort_order"
                            value="{{ old('sort_order', $subscription->sort_order) }}"
                        >
                        @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Color -->
                    <div class="col-md-12 mb-3">
                        <label for="color" class="form-label">{{ __('messages.plan_color') }}</label>
                        <input
                            type="color"
                            class="form-control form-control-color @error('color') is-invalid @enderror"
                            id="color"
                            name="color"
                            value="{{ old('color', $subscription->color) }}"
                        >
                        @error('color')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Plan Limits -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="mb-3">{{ __('messages.plan_limits') }}</h6>
                    </div>

                    <!-- Max Products -->
                    <div class="col-md-4 mb-3">
                        <label for="max_products" class="form-label">{{ __('messages.max_products') }} <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control @error('max_products') is-invalid @enderror"
                            id="max_products"
                            name="max_products"
                            value="{{ old('max_products', $subscription->max_products) }}"
                            required
                        >
                        @error('max_products')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Max Orders Per Month -->
                    <div class="col-md-4 mb-3">
                        <label for="max_orders_per_month" class="form-label">{{ __('messages.orders_per_month') }} <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control @error('max_orders_per_month') is-invalid @enderror"
                            id="max_orders_per_month"
                            name="max_orders_per_month"
                            value="{{ old('max_orders_per_month', $subscription->max_orders_per_month) }}"
                            required
                        >
                        @error('max_orders_per_month')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Commission Rate -->
                    <div class="col-md-4 mb-3">
                        <label for="commission_rate" class="form-label">{{ __('messages.commission_rate') }} (%) <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            step="0.01"
                            class="form-control @error('commission_rate') is-invalid @enderror"
                            id="commission_rate"
                            name="commission_rate"
                            value="{{ old('commission_rate', $subscription->commission_rate) }}"
                            required
                        >
                        @error('commission_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Plan Features -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="mb-3">{{ __('messages.plan_features') }}</h6>
                    </div>

                    <!-- Priority Support -->
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="priority_support"
                                name="priority_support"
                                value="1"
                                {{ old('priority_support', $subscription->priority_support) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="priority_support">
                                {{ __('messages.priority_support') }}
                            </label>
                        </div>
                    </div>

                    <!-- Analytics Access -->
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="analytics_access"
                                name="analytics_access"
                                value="1"
                                {{ old('analytics_access', $subscription->analytics_access) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="analytics_access">
                                {{ __('messages.analytics_access') }}
                            </label>
                        </div>
                    </div>

                    <!-- Bulk Import -->
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="bulk_import"
                                name="bulk_import"
                                value="1"
                                {{ old('bulk_import', $subscription->bulk_import) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="bulk_import">
                                {{ __('messages.bulk_import') }}
                            </label>
                        </div>
                    </div>

                    <!-- API Access -->
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="api_access"
                                name="api_access"
                                value="1"
                                {{ old('api_access', $subscription->api_access) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="api_access">
                                <img src="{{ asset('vector.png') }}" alt="Taif" style="height: 20px; vertical-align: middle;" class="{{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}">
                                {{ __('messages.api_access') }}
                            </label>
                        </div>
                    </div>

                    <!-- Is Active -->
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="is_active"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $subscription->is_active) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="is_active">
                                {{ __('messages.active') }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>
                        {{ __('messages.update_category') }}
                    </button>
                    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
