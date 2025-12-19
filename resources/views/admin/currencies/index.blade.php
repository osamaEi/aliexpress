@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ __('messages.currency_management') }}</h4>
            <p class="text-muted">{{ __('messages.manage_currencies_and_rates') }}</p>
        </div>
        <a href="{{ route('admin.currencies.create') }}" class="btn btn-primary">
            <i class="ri-add-line"></i> {{ __('messages.add_currency') }}
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Currencies Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.sort_order') }}</th>
                            <th>{{ __('messages.currency_code') }}</th>
                            <th>{{ __('messages.currency_name') }}</th>
                            <th>{{ __('messages.symbol') }}</th>
                            <th>{{ __('messages.exchange_rate') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.default') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $currency)
                        <tr>
                            <td>{{ $currency->sort_order }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $currency->code }}</span>
                            </td>
                            <td>{{ $currency->name }}</td>
                            <td>
                                <x-currency-symbol :currency="$currency->code" width="28" height="28" class="fs-5" />
                            </td>
                            <td>
                                <code>{{ number_format($currency->exchange_rate, 4) }}</code>
                                <small class="text-muted d-block">1 USD = {{ number_format($currency->exchange_rate, 4) }} {{ $currency->code }}</small>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input toggle-active"
                                           type="checkbox"
                                           data-currency-id="{{ $currency->id }}"
                                           {{ $currency->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        <span class="badge bg-{{ $currency->is_active ? 'success' : 'secondary' }}">
                                            {{ $currency->is_active ? __('messages.active') : __('messages.inactive') }}
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                @if($currency->is_default)
                                    <span class="badge bg-warning">
                                        <i class="ri-star-fill"></i> {{ __('messages.default') }}
                                    </span>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary set-default"
                                            data-currency-id="{{ $currency->id }}">
                                        {{ __('messages.set_as_default') }}
                                    </button>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.currencies.edit', $currency) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="{{ __('messages.edit') }}">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    @if(!$currency->is_default)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger delete-currency"
                                            data-currency-id="{{ $currency->id }}"
                                            title="{{ __('messages.delete') }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                {{ __('messages.no_currencies_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Form (hidden) -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Active Status
    document.querySelectorAll('.toggle-active').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const currencyId = this.dataset.currencyId;
            const isActive = this.checked;

            fetch(`/admin/currencies/${currencyId}/toggle-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const label = this.nextElementSibling.querySelector('.badge');
                    if (data.is_active) {
                        label.classList.remove('bg-secondary');
                        label.classList.add('bg-success');
                        label.textContent = '{{ __("messages.active") }}';
                    } else {
                        label.classList.remove('bg-success');
                        label.classList.add('bg-secondary');
                        label.textContent = '{{ __("messages.inactive") }}';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !isActive;
            });
        });
    });

    // Set Default Currency
    document.querySelectorAll('.set-default').forEach(function(button) {
        button.addEventListener('click', function() {
            const currencyId = this.dataset.currencyId;

            if (confirm('{{ __("messages.confirm_set_default_currency") }}')) {
                fetch(`/admin/currencies/${currencyId}/set-default`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    });

    // Delete Currency
    document.querySelectorAll('.delete-currency').forEach(function(button) {
        button.addEventListener('click', function() {
            const currencyId = this.dataset.currencyId;

            if (confirm('{{ __("messages.confirm_delete_currency") }}')) {
                const form = document.getElementById('delete-form');
                form.action = `/admin/currencies/${currencyId}`;
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection
