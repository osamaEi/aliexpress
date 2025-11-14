@extends('dashboard')

@section('title', 'Category Profit Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="ri-money-dollar-circle-line me-2"></i>
                        Category Profit Management
                    </h4>
                    <p class="mb-0 mt-2 small">
                        Set fixed profit amounts for categories. Subcategories inherit from parent if not set.
                    </p>
                </div>
                <div class="card-body">
                    <!-- Toast Container -->
                    <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="40%">Category</th>
                                    <th width="15%">Type</th>
                                    <th width="20%">Profit Amount</th>
                                    <th width="10%">Status</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr id="category-row-{{ $category->id }}">
                                    <td>
                                        @if($category->parent_id)
                                            <span class="text-muted ms-3">└─</span>
                                        @endif
                                        <strong>{{ $category->name }}</strong>
                                        @if($category->parent && $category->parent->adminProfit && !$category->adminProfit)
                                            <br>
                                            <small class="text-muted">
                                                <i class="ri-arrow-up-line"></i>
                                                Inherits {{ $category->parent->adminProfit->profit_amount }} {{ $category->parent->adminProfit->currency }} from {{ $category->parent->name }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($category->parent_id)
                                            <span class="badge bg-info">Subcategory</span>
                                        @else
                                            <span class="badge bg-primary">Main Category</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($category->adminProfit)
                                            <span class="profit-display">
                                                <strong>{{ $category->adminProfit->profit_amount }} {{ $category->adminProfit->currency }}</strong>
                                            </span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($category->adminProfit)
                                            <div class="form-check form-switch">
                                                <input
                                                    class="form-check-input status-toggle"
                                                    type="checkbox"
                                                    id="status-{{ $category->id }}"
                                                    data-category-id="{{ $category->id }}"
                                                    {{ $category->adminProfit->is_active ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="status-{{ $category->id }}">
                                                    <span class="status-text">
                                                        {{ $category->adminProfit->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </label>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-primary edit-btn"
                                            data-category-id="{{ $category->id }}"
                                            data-category-name="{{ $category->name }}"
                                            data-profit-amount="{{ $category->adminProfit->profit_amount ?? '' }}"
                                            data-currency="{{ $category->adminProfit->currency ?? 'AED' }}"
                                        >
                                            <i class="ri-edit-line"></i>
                                            {{ $category->adminProfit ? 'Edit' : 'Set' }}
                                        </button>

                                        @if($category->adminProfit)
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
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No categories found
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
                <h5 class="modal-title">Set Profit for <span id="modalCategoryName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="profitForm">
                    <input type="hidden" id="categoryId">

                    <div class="mb-3">
                        <label for="profitAmount" class="form-label">Profit Amount <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control"
                            id="profitAmount"
                            step="0.01"
                            min="0"
                            required
                            placeholder="Enter profit amount"
                        >
                        <small class="text-muted">Fixed amount to add to each product in this category</small>
                    </div>

                    <div class="mb-3">
                        <label for="currency" class="form-label">Currency</label>
                        <select class="form-select" id="currency">
                            <option value="AED">AED</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="isActive" checked>
                        <label class="form-check-label" for="isActive">
                            Active (apply this profit to products)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveProfit">
                    <i class="ri-save-line"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

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

    // Open modal for edit/create
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const categoryName = this.dataset.categoryName;
            const profitAmount = this.dataset.profitAmount;
            const currency = this.dataset.currency;

            document.getElementById('categoryId').value = categoryId;
            document.getElementById('modalCategoryName').textContent = categoryName;
            document.getElementById('profitAmount').value = profitAmount;
            document.getElementById('currency').value = currency;
            document.getElementById('isActive').checked = true;

            new bootstrap.Modal(document.getElementById('profitModal')).show();
        });
    });

    // Save profit
    document.getElementById('saveProfit').addEventListener('click', function() {
        const categoryId = document.getElementById('categoryId').value;
        const profitAmount = document.getElementById('profitAmount').value;
        const currency = document.getElementById('currency').value;
        const isActive = document.getElementById('isActive').checked;

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
                is_active: isActive ? 1 : 0
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

    // Toggle active status
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const categoryId = this.dataset.categoryId;
            const isActive = this.checked;

            fetch(`/admin/category-profits/${categoryId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.parentElement.querySelector('.status-text').textContent =
                        data.is_active ? 'Active' : 'Inactive';
                    showToast('success', data.message);
                } else {
                    this.checked = !isActive;
                    showToast('error', data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !isActive;
                showToast('error', 'An error occurred. Please try again.');
            });
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
