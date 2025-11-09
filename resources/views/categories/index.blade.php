@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">
                    @if($parentCategory)
                        {{ __('messages.subcategories_of') }} "{{ $parentCategory->name }}"
                    @else
                        {{ __('messages.main_categories') }}
                    @endif
                </h5>
            </div>

            @if($parentCategory)
                <div class="mb-2">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('categories.index') }}">{{ __('messages.main_categories') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $parentCategory->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> {{ __('messages.back_to_main_categories') }}
                    </a>
                </div>
            @endif
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-line me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 60px;">{{ __('messages.image') }}</th>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.arabic_name') }}</th>
                            <th>{{ __('messages.slug') }}</th>
                            <th>{{ __('messages.aliexpress_id') }}</th>
                            <th>{{ __('messages.products') }}</th>
                            <th>{{ __('messages.subcategories') }}</th>
                            <th>{{ __('messages.order') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th style="width: 150px;">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    @if($category->photo)
                                        <img src="{{ asset('storage/' . $category->photo) }}" alt="{{ $category->name }}" style="width: 40px; height: 40px; object-fit: contain;">
                                    @elseif($category->image)
                                        <img src="{{ $category->image }}" alt="{{ $category->name }}" style="width: 40px; height: 40px; object-fit: contain;">
                                    @else
                                        <div style="width: 40px; height: 40px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ri-shopping-bag-line" style="font-size: 20px; color: #999;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $category->name }}</strong>
                                </td>
                                <td>
                                    @if($category->name_ar)
                                        <span dir="rtl">{{ $category->name_ar }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>
                                    @if($category->aliexpress_category_id)
                                        <span class="badge bg-info">{{ $category->aliexpress_category_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $category->products_count }}</span>
                                </td>
                                <td>
                                    @if($category->children_count > 0)
                                        <a href="{{ route('categories.index', ['parent_id' => $category->id]) }}" class="badge bg-primary">
                                            {{ $category->children_count }}
                                        </a>
                                        @if(!$parentCategory && $category->children->count() > 0)
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    {{ $category->children->pluck('name')->join(', ') }}
                                                </small>
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-dark">0</span>
                                    @endif
                                </td>
                                <td>{{ $category->order }}</td>
                                <td>
                                    <form action="{{ route('categories.toggle-status', $category) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $category->is_active ? 'btn-success' : 'btn-secondary' }}" title="Click to {{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                            @if($category->is_active)
                                                <i class="ri-checkbox-circle-line me-1"></i> Active
                                            @else
                                                <i class="ri-close-circle-line me-1"></i> Inactive
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($category->children_count > 0)
                                        <a href="{{ route('categories.index', ['parent_id' => $category->id]) }}" class="btn btn-sm btn-icon btn-info" title="View Subcategories">
                                            <i class="ri-folder-open-line"></i>
                                        </a>
                                        @endif
                                        @if($category->aliexpress_category_id)
                                        <a href="{{ route('categories.fetch-subcategories', $category) }}" class="btn btn-sm btn-icon btn-success" title="Fetch Subcategories">
                                            <i class="ri-download-cloud-line"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-icon btn-primary" title="Edit">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-danger" title="Delete">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="ri-inbox-line" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">
                                        @if($parentCategory)
                                            {{ __('messages.no_subcategories_found') }}
                                        @else
                                            {{ __('messages.no_categories_found') }}
                                        @endif
                                    </p>
                                    @if(!$parentCategory)
                                        <a href="{{ route('categories.create') }}" class="btn btn-primary">
                                            <i class="ri-add-line me-1"></i> {{ __('messages.create_first_category') }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
