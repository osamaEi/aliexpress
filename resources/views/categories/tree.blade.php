@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ __('messages.category_tree') }}</h5>
                <small class="text-muted">{{ __('messages.total') }}: {{ $rootCount ?? 0 }} {{ __('messages.root_categories') }}, {{ $childCount ?? 0 }} {{ __('messages.subcategories') }}</small>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('categories.import-all') }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.import_all_categories_confirm', ['count' => count($allCategories ?? [])]) }}');">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="ri-download-line me-1"></i> {{ __('messages.import_all') }} ({{ count($allCategories ?? []) }})
                    </button>
                </form>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                    <i class="ri-arrow-left-line me-1"></i> {{ __('messages.back') }}
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                {{ __('messages.category_tree_import_info', ['count' => count($allCategories ?? [])]) }}
            </div>

            <form action="{{ route('categories.save-tree') }}" method="POST" id="categoryTreeForm">
                @csrf

                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                        <i class="ri-checkbox-multiple-line me-1"></i> {{ __('messages.select_all') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                        <i class="ri-checkbox-blank-line me-1"></i> {{ __('messages.deselect_all') }}
                    </button>
                    <span class="ms-3 text-muted">
                        <strong id="selectedCount">0</strong> {{ __('messages.categories_selected') }}
                    </span>
                </div>

                <div class="row g-3">
                    @forelse($categoryTree as $index => $category)
                        @php
                            // Handle different possible response structures
                            $catId = $category['id'] ?? $category['category_id'] ?? $category['cate_id'] ?? '';
                            $catName = $category['name'] ?? $category['category_name'] ?? $category['title'] ?? 'Unknown';
                            $isLeaf = $category['is_leaf_category'] ?? $category['isLeaf'] ?? false;
                            $hasChildren = $category['has_children'] ?? !$isLeaf ?? false;
                        @endphp

                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 category-card">
                                <div class="card-body">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input category-checkbox"
                                               type="checkbox"
                                               name="selected[]"
                                               value="{{ $index }}"
                                               id="cat{{ $index }}">
                                        <label class="form-check-label fw-semibold" for="cat{{ $index }}">
                                            {{ $catName }}
                                        </label>
                                    </div>

                                    <div class="small text-muted mb-2">
                                        <i class="ri-price-tag-3-line me-1"></i>
                                        <code>{{ $catId }}</code>
                                    </div>

                                    <div class="d-flex gap-1 flex-wrap">
                                        @if($hasChildren)
                                            <span class="badge bg-info">{{ __('messages.has_children') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('messages.leaf') }}</span>
                                        @endif
                                    </div>

                                    <!-- Hidden inputs for form submission -->
                                    <input type="hidden" name="categories[{{ $index }}][id]" value="{{ $catId }}">
                                    <input type="hidden" name="categories[{{ $index }}][name]" value="{{ $catName }}">
                                    <input type="hidden" name="categories[{{ $index }}][order]" value="{{ $index }}">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="ri-inbox-line" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">{{ __('messages.no_categories_found_in_tree') }}</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if(count($categoryTree) > 0)
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                        <i class="ri-close-line me-1"></i> {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> {{ __('messages.save_selected_categories') }}
                    </button>
                </div>
                @endif
            </form>

            <!-- Debug Info (collapsible) -->
            <div class="mt-4">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#debugInfo">
                    <i class="ri-bug-line me-1"></i> {{ __('messages.show_raw_api_response') }}
                </button>
                <div class="collapse mt-2" id="debugInfo">
                    <div class="card">
                        <div class="card-body">
                            <pre class="mb-0" style="max-height: 400px; overflow: auto; font-size: 12px;">{{ json_encode($categoryTree, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .category-card {
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    .category-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .category-checkbox:checked ~ .form-check-label {
        color: #0d6efd;
    }
</style>
@endpush

@push('scripts')
<script>
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.category-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = checked;
    }

    function selectAll() {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(cb => cb.checked = true);
        updateSelectedCount();
    }

    function deselectAll() {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
        updateSelectedCount();
    }

    // Update count when individual checkboxes change
    document.querySelectorAll('.category-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Validate form before submission
    document.getElementById('categoryTreeForm').addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.category-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('{{ __('messages.select_at_least_one_category') }}');
            return false;
        }
    });

    // Initial count
    updateSelectedCount();
</script>
@endpush
@endsection
