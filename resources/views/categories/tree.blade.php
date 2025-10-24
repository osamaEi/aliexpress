@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">AliExpress Category Tree</h5>
                <small class="text-muted">Total: {{ $rootCount ?? 0 }} root categories, {{ $childCount ?? 0 }} subcategories</small>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('categories.import-all') }}" method="POST" class="d-inline" onsubmit="return confirm('Import ALL {{ count($allCategories ?? []) }} categories from AliExpress? This may take a while.');">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="ri-download-line me-1"></i> Import All ({{ count($allCategories ?? []) }})
                    </button>
                </form>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                You can either select specific root categories to import manually, OR click "Import All" to import all {{ count($allCategories ?? []) }} categories with their parent-child relationships automatically.
            </div>

            <form action="{{ route('categories.save-tree') }}" method="POST" id="categoryTreeForm">
                @csrf

                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                        <i class="ri-checkbox-multiple-line me-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                        <i class="ri-checkbox-blank-line me-1"></i> Deselect All
                    </button>
                    <span class="ms-3 text-muted">
                        <strong id="selectedCount">0</strong> categories selected
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
                                            <span class="badge bg-info">Has Children</span>
                                        @else
                                            <span class="badge bg-success">Leaf</span>
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
                                <p class="text-muted mt-2">No categories found in the tree</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if(count($categoryTree) > 0)
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                        <i class="ri-close-line me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Selected Categories
                    </button>
                </div>
                @endif
            </form>

            <!-- Debug Info (collapsible) -->
            <div class="mt-4">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#debugInfo">
                    <i class="ri-bug-line me-1"></i> Show Raw API Response
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
            alert('Please select at least one category to save.');
            return false;
        }
    });

    // Initial count
    updateSelectedCount();
</script>
@endpush
@endsection
