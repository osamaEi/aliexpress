@extends('dashboard')

@section('content')
@php
    $sessionCurrency = $currentCurrency->code ?? 'AED';
@endphp
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="mb-2 mb-md-0">
                <h5 class="mb-0">{{ __('messages.profit_settings') }}</h5>
                <p class="text-muted mb-0 small">{{ __('messages.set_profit_for_each_subcategory') }}</p>
            </div>
            <button type="button" class="btn btn-primary" id="saveAllBtn">
                <i class="ri-save-line me-1"></i>
                {{ __('messages.save_all') }}
            </button>
        </div>

        <div class="card-body">
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

            <!-- Search and Filter Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="{{ __('messages.search_by_category') }}...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">{{ __('messages.all_status') }}</option>
                        <option value="active">{{ __('messages.active_only') }}</option>
                        <option value="inactive">{{ __('messages.inactive_only') }}</option>
                        <option value="configured">{{ __('messages.configured_only') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="typeFilter">
                        <option value="">{{ __('messages.all_types') }}</option>
                        <option value="percentage">{{ __('messages.percentage') }}</option>
                        <option value="fixed">{{ __('messages.fixed_amount') }}</option>
                    </select>
                </div>
            </div>

            <form id="profitForm" method="POST" action="{{ route('seller.profit-settings.bulk-update') }}">
                @csrf

                <div class="table-responsive">
                    <table class="table table-hover" id="profitTable">
                        <thead>
                            <tr>
                                <th>{{ __('messages.parent_category') }}</th>
                                <th>{{ __('messages.subcategory') }}</th>
                                <th>{{ __('messages.profit_type') }}</th>
                                <th>{{ __('messages.profit_value') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $currentParent = null;
                            @endphp

                            @foreach($subcategories as $index => $subcategory)
                                @php
                                    $existingProfit = $profitSettings->get($subcategory->id);
                                    $profitType = $existingProfit ? $existingProfit->profit_type : 'percentage';
                                    $profitValue = $existingProfit ? $existingProfit->profit_value : 0;
                                    $isActive = $existingProfit ? $existingProfit->is_active : true;
                                    $hasConfig = $existingProfit ? 'configured' : 'not-configured';
                                @endphp

                                <tr data-status="{{ $isActive ? 'active' : 'inactive' }}"
                                    data-type="{{ $profitType }}"
                                    data-config="{{ $hasConfig }}"
                                    data-parent="{{ app()->getLocale() == 'ar' && $subcategory->parent->name_ar ? $subcategory->parent->name_ar : $subcategory->parent->name }}"
                                    data-subcategory="{{ app()->getLocale() == 'ar' && $subcategory->name_ar ? $subcategory->name_ar : $subcategory->name }}">
                                    <!-- Parent Category -->
                                    <td>
                                        @if($currentParent != $subcategory->parent_id)
                                            @php
                                                $currentParent = $subcategory->parent_id;
                                            @endphp
                                            <strong>{{ app()->getLocale() == 'ar' && $subcategory->parent->name_ar ? $subcategory->parent->name_ar : $subcategory->parent->name }}</strong>
                                        @endif
                                    </td>

                                    <!-- Subcategory -->
                                    <td>
                                        {{ app()->getLocale() == 'ar' && $subcategory->name_ar ? $subcategory->name_ar : $subcategory->name }}
                                        <input type="hidden" name="profits[{{ $index }}][category_id]" value="{{ $subcategory->id }}">
                                    </td>

                                    <!-- Profit Type -->
                                    <td>
                                        <select name="profits[{{ $index }}][profit_type]" class="form-select form-select-sm profit-type" data-index="{{ $index }}" style="width: 150px;">
                                            <option value="percentage" {{ $profitType == 'percentage' ? 'selected' : '' }}>
                                                {{ __('messages.percentage') }}
                                            </option>
                                            <option value="fixed" {{ $profitType == 'fixed' ? 'selected' : '' }}>
                                                {{ __('messages.fixed_amount') }}
                                            </option>
                                        </select>
                                    </td>

                                    <!-- Profit Value -->
                                    <td>
                                        <div class="input-group input-group-sm" style="width: 200px;">
                                            <input type="number"
                                                   name="profits[{{ $index }}][profit_value]"
                                                   class="form-control"
                                                   value="{{ $profitValue }}"
                                                   min="0"
                                                   step="0.01"
                                                   required>
                                            <span class="input-group-text profit-unit" data-index="{{ $index }}">
                                                @if($profitType == 'percentage')
                                                    %
                                                @else
                                                    <x-session-currency-icon width="16" height="16" />
                                                @endif
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-checkbox"
                                                   type="checkbox"
                                                   name="profits[{{ $index }}][is_active]"
                                                   value="1"
                                                   {{ $isActive ? 'checked' : '' }}
                                                   data-row-index="{{ $index }}">
                                            <label class="form-check-label">
                                                {{ __('messages.active') }}
                                            </label>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button"
                                                    class="btn btn-outline-info preview-profit"
                                                    data-index="{{ $index }}"
                                                    title="{{ __('messages.preview_calculation') }}">
                                                <i class="ri-calculator-line"></i>
                                            </button>
                                            @if($existingProfit)
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm delete-profit"
                                                    data-profit-id="{{ $existingProfit->id }}"
                                                    data-delete-url="{{ route('seller.profit-settings.destroy', $existingProfit->id) }}"
                                                    title="{{ __('messages.delete') }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>

            <!-- Preview Modal -->
            <div class="modal fade" id="previewModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('messages.profit_calculation_preview') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.example_base_price') }} (<x-session-currency-icon width="16" height="16" />)</label>
                                <input type="number" class="form-control form-control-lg" id="previewBasePrice" value="100" min="0" step="0.01">
                            </div>
                            <div class="alert alert-light border">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">{{ __('messages.base_price') }}</small>
                                        <h6 class="mb-0 d-inline-flex align-items-center gap-1" id="previewBasePriceDisplay" style="direction: ltr;"><x-session-currency-icon width="16" height="16" /> 100.00</h6>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">{{ __('messages.profit_amount') }}</small>
                                        <h6 class="mb-0 text-success d-inline-flex align-items-center gap-1" id="previewProfitAmount" style="direction: ltr;"><x-session-currency-icon width="16" height="16" /> 0.00</h6>
                                    </div>
                                    <div class="col-12">
                                        <hr>
                                        <small class="text-muted d-block mb-2">{{ __('messages.final_selling_price') }}</small>
                                        <h4 class="mb-0 text-primary d-inline-flex align-items-center gap-1" id="previewFinalPrice" style="direction: ltr;"><x-session-currency-icon width="18" height="18" /> 100.00</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mb-0">
                                <small>
                                    <i class="ri-information-line me-1"></i>
                                    {{ __('messages.profit_calculation_note') }}
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const profitTable = document.getElementById('profitTable');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');

    // Search functionality
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    typeFilter.addEventListener('change', filterTable);

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const typeValue = typeFilter.value;
        const rows = profitTable.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const parent = row.dataset.parent.toLowerCase();
            const subcategory = row.dataset.subcategory.toLowerCase();
            const status = row.dataset.status;
            const type = row.dataset.type;
            const config = row.dataset.config;

            let showRow = true;

            // Search filter
            if (searchTerm && !parent.includes(searchTerm) && !subcategory.includes(searchTerm)) {
                showRow = false;
            }

            // Status filter
            if (statusValue) {
                if (statusValue === 'configured' && config !== 'configured') {
                    showRow = false;
                } else if (statusValue !== 'configured' && status !== statusValue) {
                    showRow = false;
                }
            }

            // Type filter
            if (typeValue && type !== typeValue) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });
    }

    // Handle status checkbox changes
    document.querySelectorAll('.status-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            row.dataset.status = this.checked ? 'active' : 'inactive';
        });
    });

    // Currency icons for dynamic switching
    const currencyIcons = {
        'AED': '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle;"><path d="M8 7V17H12C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7H8Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 11H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 13H12.5H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
        'SAR': '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle;"><text x="3" y="18" font-size="16" font-weight="bold" fill="currentColor">&#xFDFC;</text></svg>',
        'USD': '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle;"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
    };
    const sessionCurrency = '{{ session("currency_code", "USD") }}';
    const currencyIcon = currencyIcons[sessionCurrency] || sessionCurrency;

    // Handle profit type change
    document.querySelectorAll('.profit-type').forEach(function(select) {
        select.addEventListener('change', function() {
            const index = this.dataset.index;
            const unit = document.querySelector(`.profit-unit[data-index="${index}"]`);
            const row = this.closest('tr');

            if (this.value === 'percentage') {
                unit.textContent = '%';
            } else {
                unit.innerHTML = currencyIcon;
            }

            row.dataset.type = this.value;
        });
    });

    // Handle save all button
    document.getElementById('saveAllBtn').addEventListener('click', function() {
        if (confirm('{{ __("messages.confirm_save_all_profits") }}')) {
            const form = document.getElementById('profitForm');

            console.log('Form action:', form.action);
            console.log('Form method:', form.method);

            // Disable all inputs in hidden rows to prevent them from being submitted
            const rows = profitTable.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.style.display === 'none') {
                    // Disable all inputs in hidden row
                    row.querySelectorAll('input, select').forEach(input => {
                        input.disabled = true;
                    });
                }
            });

            // Log form data for debugging
            const formData = new FormData(form);
            console.log('Form data entries:');
            for (let [key, value] of formData.entries()) {
                console.log(key, '=', value);
            }

            form.submit();
        }
    });

    // Handle preview
    let currentPreviewIndex = null;

    document.querySelectorAll('.preview-profit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentPreviewIndex = this.dataset.index;
            updatePreview();

            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        });
    });

    document.getElementById('previewBasePrice').addEventListener('input', function() {
        if (currentPreviewIndex !== null) {
            updatePreview();
        }
    });

    function updatePreview() {
        const basePrice = parseFloat(document.getElementById('previewBasePrice').value) || 0;
        const profitTypeSelect = document.querySelector(`select[name="profits[${currentPreviewIndex}][profit_type]"]`);
        const profitValueInput = document.querySelector(`input[name="profits[${currentPreviewIndex}][profit_value]"]`);

        const profitType = profitTypeSelect.value;
        const profitValue = parseFloat(profitValueInput.value) || 0;

        let profitAmount = 0;
        if (profitType === 'percentage') {
            profitAmount = basePrice * (profitValue / 100);
        } else {
            profitAmount = profitValue;
        }

        const finalPrice = basePrice + profitAmount;

        document.getElementById('previewBasePriceDisplay').innerHTML = formatCurrency(basePrice);
        document.getElementById('previewProfitAmount').innerHTML = formatCurrency(profitAmount);
        document.getElementById('previewFinalPrice').innerHTML = formatCurrency(finalPrice);
    }

    function formatCurrency(amount) {
        return `${currencyIcon} ${amount.toFixed(2)}`;
    }

    // Handle delete profit setting
    document.querySelectorAll('.delete-profit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('{{ __("messages.confirm_delete") }}')) {
                return;
            }

            const deleteUrl = this.dataset.deleteUrl;
            const profitId = this.dataset.profitId;

            // Create a temporary form for DELETE request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteUrl;
            form.style.display = 'none';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            // Add DELETE method
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            // Append form to body and submit
            document.body.appendChild(form);
            form.submit();
        });
    });
});
</script>
@endpush
@endsection
