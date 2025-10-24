@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Subcategories for: {{ $category->name }}</h5>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                <i class="ri-arrow-left-line me-1"></i> Back to Categories
            </a>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                Select the subcategories you want to save to your database. Subcategories will be created as children of "<strong>{{ $category->name }}</strong>".
            </div>

            <form action="{{ route('categories.save-subcategories', $category) }}" method="POST" id="subcategoriesForm">
                @csrf

                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                        <i class="ri-checkbox-multiple-line me-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                        <i class="ri-checkbox-blank-line me-1"></i> Deselect All
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)">
                                </th>
                                <th>Category ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subcategories as $index => $subcategory)
                                @php
                                    // Handle different possible response structures
                                    $catId = $subcategory['id'] ?? $subcategory['category_id'] ?? $subcategory['cate_id'] ?? '';
                                    $catName = $subcategory['name'] ?? $subcategory['category_name'] ?? $subcategory['title'] ?? 'Unknown';
                                    $isLeaf = $subcategory['is_leaf_category'] ?? $subcategory['isLeaf'] ?? false;
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox"
                                               class="subcategory-checkbox"
                                               name="selected[]"
                                               value="{{ $index }}"
                                               checked>
                                    </td>
                                    <td>
                                        <code>{{ $catId }}</code>
                                        @if($isLeaf)
                                            <span class="badge bg-success ms-2">Leaf</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $catName }}</strong>

                                        <!-- Hidden inputs for form submission -->
                                        <input type="hidden" name="subcategories[{{ $index }}][id]" value="{{ $catId }}">
                                        <input type="hidden" name="subcategories[{{ $index }}][name]" value="{{ $catName }}">
                                        <input type="hidden" name="subcategories[{{ $index }}][order]" value="{{ $index }}">
                                    </td>
                                    <td>
                                        @if(!$isLeaf)
                                            <span class="badge bg-info">Has Children</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="ri-inbox-line" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">No subcategories found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(count($subcategories) > 0)
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Total: <strong id="selectedCount">{{ count($subcategories) }}</strong> / {{ count($subcategories) }} selected</span>
                    </div>
                    <div>
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Save Selected Subcategories
                        </button>
                    </div>
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
                            <pre class="mb-0" style="max-height: 400px; overflow: auto; font-size: 12px;">{{ json_encode($subcategories, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.subcategory-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = checked;
    }

    function toggleAll(checkbox) {
        const checkboxes = document.querySelectorAll('.subcategory-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateSelectedCount();
    }

    function selectAll() {
        const checkboxes = document.querySelectorAll('.subcategory-checkbox');
        checkboxes.forEach(cb => cb.checked = true);
        document.getElementById('selectAllCheckbox').checked = true;
        updateSelectedCount();
    }

    function deselectAll() {
        const checkboxes = document.querySelectorAll('.subcategory-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
        document.getElementById('selectAllCheckbox').checked = false;
        updateSelectedCount();
    }

    // Update count when individual checkboxes change
    document.querySelectorAll('.subcategory-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Validate form before submission
    document.getElementById('subcategoriesForm').addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.subcategory-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('Please select at least one subcategory to save.');
            return false;
        }
    });
</script>
@endpush
@endsection
