@extends('dashboard')

@section('title', 'Category Profit Management')

@php
    $sessionCurrency = session('currency_code', 'USD');
    $currentCurrency = \App\Models\Currency::where('code', $sessionCurrency)->first();
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="ri-money-dollar-circle-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'إدارة أرباح الفئات' : 'Category Profit Management' }}
                        </h4>
                        <p class="mb-0 mt-2 small">
                            {{ app()->getLocale() == 'ar' ? 'تعيين مبالغ ربح ثابتة للفئات. الفئات الفرعية ترث من الفئة الأم إذا لم يتم تعيينها.' : 'Set fixed profit amounts for categories. Subcategories inherit from parent if not set.' }}
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark">
                            <x-session-currency-icon width="16" height="16" />
                            {{ $sessionCurrency }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Toast Container -->
                    <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="45%">{{ app()->getLocale() == 'ar' ? 'الفئة' : 'Category' }}</th>
                                    <th width="15%">{{ app()->getLocale() == 'ar' ? 'النوع' : 'Type' }}</th>
                                    <th width="25%">{{ app()->getLocale() == 'ar' ? 'مبلغ الربح' : 'Profit Amount' }}</th>
                                    <th width="15%">{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr id="category-row-{{ $category->id }}">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <div>
                                                @if($category->parent_id)
                                                    <span class="text-muted ms-3">└─</span>
                                                @endif
                                                <strong>{{ $category->name }}</strong>
                                            </div>
                                            @if($category->parent && $category->parent->adminProfit && !$category->adminProfit)
                                                <div class="ms-4 mt-1">
                                                    <small class="text-muted">
                                                        <i class="ri-arrow-up-line"></i>
                                                        @php
                                                            $inheritedCurrency = $category->parent->adminProfit->currency ?? 'USD';
                                                            $inheritedAmount = $category->parent->adminProfit->profit_amount;
                                                            if ($currentCurrency && $inheritedCurrency !== $sessionCurrency) {
                                                                $inheritedCurrencyModel = \App\Models\Currency::where('code', $inheritedCurrency)->first();
                                                                if ($inheritedCurrencyModel) {
                                                                    $inheritedAmount = $currentCurrency->convertFrom($inheritedAmount, $inheritedCurrency);
                                                                }
                                                            }
                                                        @endphp
                                                        {{ app()->getLocale() == 'ar' ? 'يرث' : 'Inherits' }}
                                                        <span class="d-inline-flex align-items-center gap-1" style="direction: ltr;">
                                                            <x-session-currency-icon width="12" height="12" />
                                                            {{ number_format($inheritedAmount, 2) }}
                                                        </span>
                                                        {{ app()->getLocale() == 'ar' ? 'من' : 'from' }} {{ $category->parent->name }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($category->parent_id)
                                            <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? 'فئة فرعية' : 'Subcategory' }}</span>
                                        @else
                                            <span class="badge bg-primary">{{ app()->getLocale() == 'ar' ? 'فئة رئيسية' : 'Main Category' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($category->adminProfit)
                                            @php
                                                $profitCurrency = $category->adminProfit->currency ?? 'USD';
                                                $profitAmount = $category->adminProfit->profit_amount;
                                                if ($currentCurrency && $profitCurrency !== $sessionCurrency) {
                                                    $profitAmount = $currentCurrency->convertFrom($profitAmount, $profitCurrency);
                                                }
                                            @endphp
                                            <span class="profit-display d-inline-flex align-items-center gap-1" style="direction: ltr; color: #561C04;">
                                                <x-session-currency-icon width="16" height="16" />
                                                <strong>{{ number_format($profitAmount, 2) }}</strong>
                                            </span>
                                        @else
                                            <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'غير محدد' : 'Not set' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-primary edit-btn"
                                            data-category-id="{{ $category->id }}"
                                            data-category-name="{{ $category->name }}"
                                            data-profit-amount="{{ $category->adminProfit->profit_amount ?? '' }}"
                                            data-currency="{{ $category->adminProfit->currency ?? $sessionCurrency }}"
                                        >
                                            <i class="ri-edit-line"></i>
                                            {{ $category->adminProfit ? (app()->getLocale() == 'ar' ? 'تعديل' : 'Edit') : (app()->getLocale() == 'ar' ? 'تعيين' : 'Set') }}
                                        </button>

                                        @if($category->adminProfit && !$category->parent_id)
                                        <button
                                            class="btn btn-sm btn-danger delete-btn"
                                            data-category-id="{{ $category->id }}"
                                            data-category-name="{{ $category->name }}"
                                        >
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        {{ app()->getLocale() == 'ar' ? 'لا توجد فئات' : 'No categories found' }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit/Create Modal -->
<div class="modal fade" id="profitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ app()->getLocale() == 'ar' ? 'تعيين الربح لـ' : 'Set Profit for' }} <span id="modalCategoryName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="profitForm">
                    <input type="hidden" id="categoryId">

                    <div class="mb-3">
                        <label for="profitAmount" class="form-label">
                            {{ app()->getLocale() == 'ar' ? 'مبلغ الربح' : 'Profit Amount' }}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text currency-icon-display">
                                <x-session-currency-icon width="16" height="16" />
                            </span>
                            <input
                                type="number"
                                class="form-control"
                                id="profitAmount"
                                step="0.01"
                                min="0"
                                required
                                placeholder="{{ app()->getLocale() == 'ar' ? 'أدخل مبلغ الربح' : 'Enter profit amount' }}"
                            >
                        </div>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'مبلغ ثابت يضاف لكل منتج في هذه الفئة' : 'Fixed amount to add to each product in this category' }}</small>
                    </div>

                    <div class="mb-3">
                        <label for="currency" class="form-label">{{ app()->getLocale() == 'ar' ? 'العملة' : 'Currency' }}</label>
                        <select class="form-select" id="currency">
                            <option value="AED" {{ $sessionCurrency == 'AED' ? 'selected' : '' }}>
                                AED - {{ app()->getLocale() == 'ar' ? 'درهم إماراتي' : 'UAE Dirham' }}
                            </option>
                            <option value="USD" {{ $sessionCurrency == 'USD' ? 'selected' : '' }}>
                                USD - {{ app()->getLocale() == 'ar' ? 'دولار أمريكي' : 'US Dollar' }}
                            </option>
                            <option value="SAR" {{ $sessionCurrency == 'SAR' ? 'selected' : '' }}>
                                SAR - {{ app()->getLocale() == 'ar' ? 'ريال سعودي' : 'Saudi Riyal' }}
                            </option>
                            <option value="EUR" {{ $sessionCurrency == 'EUR' ? 'selected' : '' }}>
                                EUR - {{ app()->getLocale() == 'ar' ? 'يورو' : 'Euro' }}
                            </option>
                            <option value="GBP" {{ $sessionCurrency == 'GBP' ? 'selected' : '' }}>
                                GBP - {{ app()->getLocale() == 'ar' ? 'جنيه إسترليني' : 'British Pound' }}
                            </option>
                        </select>
                    </div>

                    <input type="hidden" id="isActive" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                <button type="button" class="btn btn-primary" id="saveProfit">
                    <i class="ri-save-line"></i> {{ app()->getLocale() == 'ar' ? 'حفظ' : 'Save' }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Currency icons for dynamic switching
    const currencyIcons = {
        'AED': '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle;"><path d="M8 7V17H12C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7H8Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 11H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 13H12.5H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
        'SAR': '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle;"><text x="3" y="18" font-size="16" font-weight="bold" fill="currentColor">&#xFDFC;</text></svg>',
        'USD': '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle;"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'EUR': '€',
        'GBP': '£'
    };
    const sessionCurrency = '{{ $sessionCurrency }}';

    // Show toast notification
    function showToast(type, message) {
        const toastContainer = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        toast.innerHTML = `
            <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-line me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Update currency icon in input group
    function updateCurrencyIcon(currency) {
        const iconDisplay = document.querySelector('.currency-icon-display');
        if (iconDisplay) {
            iconDisplay.innerHTML = currencyIcons[currency] || currency;
        }
    }

    // Open modal for edit/create
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const categoryName = this.dataset.categoryName;
            const profitAmount = this.dataset.profitAmount;
            const currency = this.dataset.currency || sessionCurrency;

            document.getElementById('categoryId').value = categoryId;
            document.getElementById('modalCategoryName').textContent = categoryName;
            document.getElementById('profitAmount').value = profitAmount;
            document.getElementById('currency').value = currency;

            // Update currency icon
            updateCurrencyIcon(currency);

            new bootstrap.Modal(document.getElementById('profitModal')).show();
        });
    });

    // Update currency icon when currency changes
    document.getElementById('currency').addEventListener('change', function() {
        updateCurrencyIcon(this.value);
    });

    // Save profit
    document.getElementById('saveProfit').addEventListener('click', function() {
        const categoryId = document.getElementById('categoryId').value;
        const profitAmount = document.getElementById('profitAmount').value;
        const currency = document.getElementById('currency').value;
        const isActive = 1;

        if (!profitAmount || profitAmount < 0) {
            showToast('error', 'Please enter a valid profit amount');
            return;
        }

        this.disabled = true;
        this.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm"></i> Saving...';

        fetch(`/admin/category-profits/${categoryId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                profit_amount: profitAmount,
                currency: currency,
                is_active: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('profitModal')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('error', data.message || 'Failed to save profit');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'An error occurred. Please try again.');
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="ri-save-line"></i> Save';
        });
    });

    // Delete profit
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const categoryName = this.dataset.categoryName;

            if (!confirm(`Are you sure you want to remove profit setting for "${categoryName}"?`)) {
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm"></i>';

            fetch(`/admin/category-profits/${categoryId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('error', data.message || 'Failed to delete profit');
                    this.disabled = false;
                    this.innerHTML = '<i class="ri-delete-bin-line"></i>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'An error occurred. Please try again.');
                this.disabled = false;
                this.innerHTML = '<i class="ri-delete-bin-line"></i>';
            });
        });
    });
</script>
@endpush

<style>
    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.2em;
    }
</style>
@endsection
