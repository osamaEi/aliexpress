<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            value="{{ old('name', $category->name ?? '') }}"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="slug" class="form-label">Slug</label>
        <input
            type="text"
            class="form-control @error('slug') is-invalid @enderror"
            id="slug"
            name="slug"
            value="{{ old('slug', $category->slug ?? '') }}"
            placeholder="Leave empty to auto-generate from name"
        >
        @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">URL-friendly version of the name</small>
    </div>

    <div class="col-md-6">
        <label for="aliexpress_category_id" class="form-label">AliExpress Category ID</label>
        <input
            type="text"
            class="form-control @error('aliexpress_category_id') is-invalid @enderror"
            id="aliexpress_category_id"
            name="aliexpress_category_id"
            value="{{ old('aliexpress_category_id', $category->aliexpress_category_id ?? '') }}"
        >
        @error('aliexpress_category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">AliExpress category identifier for API filtering</small>
    </div>

    <div class="col-md-6">
        <label for="image" class="form-label">Image URL</label>
        <input
            type="url"
            class="form-control @error('image') is-invalid @enderror"
            id="image"
            name="image"
            value="{{ old('image', $category->image ?? '') }}"
        >
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Direct URL to category image</small>
    </div>

    @if(isset($category->image) && $category->image)
    <div class="col-12">
        <label class="form-label">Current Image</label>
        <div>
            <img src="{{ $category->image }}" alt="{{ $category->name ?? 'Category' }}" style="max-width: 100px; height: auto; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
        </div>
    </div>
    @endif

    <div class="col-md-6">
        <label for="parent_id" class="form-label">Parent Category</label>
        <select
            class="form-select @error('parent_id') is-invalid @enderror"
            id="parent_id"
            name="parent_id"
        >
            <option value="">None (Root Category)</option>
            @foreach($parentCategories as $parent)
                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                    {{ $parent->name }}
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="order" class="form-label">Display Order</label>
        <input
            type="number"
            class="form-control @error('order') is-invalid @enderror"
            id="order"
            name="order"
            value="{{ old('order', $category->order ?? 0) }}"
            min="0"
        >
        @error('order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label d-block">Status</label>
        <div class="form-check form-switch mt-2">
            <input
                class="form-check-input"
                type="checkbox"
                id="is_active"
                name="is_active"
                value="1"
                {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_active">
                Active
            </label>
        </div>
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea
            class="form-control @error('description') is-invalid @enderror"
            id="description"
            name="description"
            rows="4"
        >{{ old('description', $category->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">
        <i class="ri-save-line me-1"></i> {{ isset($category) && $category->exists ? 'Update Category' : 'Create Category' }}
    </button>
    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
        <i class="ri-close-line me-1"></i> Cancel
    </a>
</div>

@push('scripts')
<script>
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.getElementById('slug').value = slug;
    });
</script>
@endpush
