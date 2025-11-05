@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.site_settings') }}</h4>
        <p class="text-muted">{{ __('messages.manage_site_settings') }}</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

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

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- General Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.general_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($settings->get('text', collect())->merge($settings->get('textarea', collect())) as $setting)
                            @if(!in_array($setting->key, ['admin_profit_type']))
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>
                                @if($setting->type === 'textarea')
                                <textarea
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    rows="3">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                @else
                                <input
                                    type="text"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}">
                                @endif
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.email_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($settings->get('email', collect()) as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>
                                <input
                                    type="email"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.image_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($settings->get('image', collect()) as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>

                                @if($setting->value)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $setting->value) }}"
                                         alt="{{ $setting->key }}"
                                         class="img-thumbnail"
                                         style="max-width: 200px; max-height: 200px;">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger ms-2 delete-image"
                                        data-key="{{ $setting->key }}">
                                        <i class="ri-delete-bin-line"></i> {{ __('messages.delete') }}
                                    </button>
                                </div>
                                @endif

                                <input
                                    type="file"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    accept="image/*">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Profit Settings -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.admin_profit_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $profitType = $settings->get('text', collect())->firstWhere('key', 'admin_profit_type');
                                $profitPercentage = $settings->get('number', collect())->firstWhere('key', 'admin_profit_percentage');
                                $profitFixed = $settings->get('number', collect())->firstWhere('key', 'admin_profit_fixed');
                            @endphp

                            <div class="col-md-12 mb-3">
                                <label for="admin_profit_type" class="form-label">
                                    {{ __('messages.profit_type') }}
                                </label>
                                <select name="settings[admin_profit_type]" id="admin_profit_type" class="form-control">
                                    <option value="percentage" {{ old('settings.admin_profit_type', $profitType?->value) === 'percentage' ? 'selected' : '' }}>
                                        {{ __('messages.percentage') }}
                                    </option>
                                    <option value="fixed" {{ old('settings.admin_profit_type', $profitType?->value) === 'fixed' ? 'selected' : '' }}>
                                        {{ __('messages.fixed_amount') }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3" id="percentage_field">
                                <label for="admin_profit_percentage" class="form-label">
                                    {{ __('messages.profit_percentage') }}
                                    @if($profitPercentage?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $profitPercentage->description }}"></i>
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="settings[admin_profit_percentage]"
                                        id="admin_profit_percentage"
                                        class="form-control"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        value="{{ old('settings.admin_profit_percentage', $profitPercentage?->value) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3" id="fixed_field">
                                <label for="admin_profit_fixed" class="form-label">
                                    {{ __('messages.fixed_profit_amount') }}
                                    @if($profitFixed?->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $profitFixed->description }}"></i>
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="settings[admin_profit_fixed]"
                                        id="admin_profit_fixed"
                                        class="form-control"
                                        step="0.01"
                                        min="0"
                                        value="{{ old('settings.admin_profit_fixed', $profitFixed?->value) }}">
                                    <span class="input-group-text">{{ $settings->get('text', collect())->firstWhere('key', 'currency')?->value ?? 'AED' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Numeric Settings (excluding profit settings) -->
            @php
                $numericSettings = $settings->get('number', collect())->filter(function($setting) {
                    return !in_array($setting->key, ['admin_profit_percentage', 'admin_profit_fixed']);
                });
            @endphp

            @if($numericSettings->count() > 0)
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.other_numeric_settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($numericSettings as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    @if($setting->description)
                                    <i class="ri-question-line" data-bs-toggle="tooltip" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>
                                <input
                                    type="number"
                                    name="settings[{{ $setting->key }}]"
                                    id="{{ $setting->key }}"
                                    class="form-control"
                                    step="0.01"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="ri-save-line me-1"></i> {{ __('messages.save_settings') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle profit type change
    const profitTypeSelect = document.getElementById('admin_profit_type');
    const percentageField = document.getElementById('percentage_field');
    const fixedField = document.getElementById('fixed_field');

    function toggleProfitFields() {
        if (profitTypeSelect.value === 'percentage') {
            percentageField.style.display = 'block';
            fixedField.style.display = 'none';
        } else {
            percentageField.style.display = 'none';
            fixedField.style.display = 'block';
        }
    }

    profitTypeSelect.addEventListener('change', toggleProfitFields);
    toggleProfitFields(); // Initial state

    // Handle image deletion
    document.querySelectorAll('.delete-image').forEach(button => {
        button.addEventListener('click', function() {
            const key = this.getAttribute('data-key');

            if (confirm('{{ __('messages.confirm_delete_image') }}')) {
                fetch('{{ route('admin.settings.delete-image') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ key: key })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __('messages.error_deleting_image') }}');
                });
            }
        });
    });
});
</script>
@endpush
@endsection
