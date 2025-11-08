@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ __('messages.subcategory_profit_settings') }}</h5>
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

            <form id="profitForm" method="POST" action="{{ route('seller.profits.bulk-update') }}">
                @csrf

                <div class="table-responsive">
                    <table class="table table-hover">
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
                                @endphp

                                <tr>
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
                                                {{ $profitType == 'percentage' ? '%' : '$' }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="profits[{{ $index }}][is_active]" value="0">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="profits[{{ $index }}][is_active]"
                                                   value="1"
                                                   {{ $isActive ? 'checked' : '' }}>
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
                                                    title="{{ __('messages.preview') }}">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            @if($existingProfit)
                                            <form method="POST" action="{{ route('seller.profits.destroy', $existingProfit->id) }}" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ __('messages.delete') }}">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
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
                            <h5 class="modal-title">{{ __('messages.profit_preview') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.example_base_price') }}</label>
                                <input type="number" class="form-control" id="previewBasePrice" value="100" min="0" step="0.01">
                            </div>
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>{{ __('messages.base_price') }}:</strong>
                                        <p class="mb-0" id="previewBasePriceDisplay">$100.00</p>
                                    </div>
                                    <div class="col-6">
                                        <strong>{{ __('messages.profit_amount') }}:</strong>
                                        <p class="mb-0 text-success" id="previewProfitAmount">$0.00</p>
                                    </div>
                                    <div class="col-12 mt-2 pt-2 border-top">
                                        <strong>{{ __('messages.final_price') }}:</strong>
                                        <h4 class="mb-0 text-primary" id="previewFinalPrice">$100.00</h4>
                                    </div>
                                </div>
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
    // Handle profit type change
    document.querySelectorAll('.profit-type').forEach(function(select) {
        select.addEventListener('change', function() {
            const index = this.dataset.index;
            const unit = document.querySelector(`.profit-unit[data-index="${index}"]`);
            unit.textContent = this.value === 'percentage' ? '%' : '$';
        });
    });

    // Handle save all button
    document.getElementById('saveAllBtn').addEventListener('click', function() {
        document.getElementById('profitForm').submit();
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

        document.getElementById('previewBasePriceDisplay').textContent = '$' + basePrice.toFixed(2);
        document.getElementById('previewProfitAmount').textContent = '$' + profitAmount.toFixed(2);
        document.getElementById('previewFinalPrice').textContent = '$' + finalPrice.toFixed(2);
    }
});
</script>
@endpush
@endsection
