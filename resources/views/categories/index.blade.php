@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <style>
        .assigned-category {
            background-color: #f0f9ff !important;
        }
        [dir="ltr"] .assigned-category {
            border-left: 4px solid #3b82f6;
        }
        [dir="rtl"] .assigned-category {
            border-right: 4px solid #3b82f6;
        }
        .assigned-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
    </style>
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

            @if(auth()->user() && auth()->user()->user_type === 'seller')
                <div class="d-flex gap-2 align-items-center mb-3">
                    @if(!empty($assignedCategoryIds))
                        <div class="alert alert-info mb-0 flex-grow-1">
                            <i class="ri-information-line me-2"></i>
                            {{ app()->getLocale() == 'ar'
                                ? 'الفئات المميزة باللون الأزرق هي الفئات المخصصة لك'
                                : 'Categories highlighted in blue are assigned to you' }}
                        </div>
                    @endif
                    <a href="{{ route('seller.request-category-assignment') }}" class="btn btn-primary">
                        <i class="ri-add-circle-line me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'طلب تعيين فئة جديدة' : 'Request New Category Assignment' }}
                    </a>
                </div>
            @endif

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
                            <th>{{ __('messages.products') }}</th>
                            <th>{{ __('messages.subcategories') }}</th>
                            <th>{{ __('messages.order') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th style="width: 150px;">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr class="{{ in_array($category->id, $assignedCategoryIds) ? 'assigned-category' : '' }}">
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
                                    @if(in_array($category->id, $assignedCategoryIds))
                                        <span class="assigned-badge ms-2">
                                            <i class="ri-check-line"></i>
                                            {{ app()->getLocale() == 'ar' ? 'مخصصة' : 'Assigned' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($category->name_ar)
                                        <span dir="rtl">{{ $category->name_ar }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $category->products_count }}</span>
                                </td>
                                <td>
                                    @if($category->children_count > 0)
                                        <span class="badge bg-primary">{{ $category->children_count }}</span>
                                    @else
                                        <span class="badge bg-light text-dark">0</span>
                                    @endif
                                </td>
                                <td>{{ $category->order }}</td>
                                <td>
                                    @if(auth()->user()->user_type === 'admin')
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
                                    @else
                                        <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            @if($category->is_active)
                                                <i class="ri-checkbox-circle-line me-1"></i> Active
                                            @else
                                                <i class="ri-close-circle-line me-1"></i> Inactive
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($category->children_count > 0)
                                        <a href="{{ route('categories.index', ['parent_id' => $category->id]) }}" class="btn btn-sm btn-icon btn-info" title="{{ app()->getLocale() == 'ar' ? 'عرض الفئات الفرعية' : 'View Subcategories' }}">
                                            <i class="ri-folder-open-line"></i>
                                        </a>
                                        @endif

                                        @if(auth()->user() && auth()->user()->user_type !== 'seller')
                                            @if($category->aliexpress_category_id)
                                            <a href="{{ route('categories.fetch-subcategories', $category) }}" class="btn btn-sm btn-icon btn-success" title="Fetch Subcategories">
                                                <i class="ri-download-cloud-line"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-icon btn-primary" title="{{ app()->getLocale() == 'ar' ? 'تعديل' : 'Edit' }}">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد من حذف هذه الفئة؟' : 'Are you sure you want to delete this category?' }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-danger" title="{{ app()->getLocale() == 'ar' ? 'حذف' : 'Delete' }}">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        @else
                                            @if(in_array($category->id, $assignedCategoryIds))
                                                <span class="badge bg-success">
                                                    <i class="ri-check-double-line"></i>
                                                    {{ app()->getLocale() == 'ar' ? 'مخصصة لك' : 'Assigned to you' }}
                                                </span>
                                            @else
                                                <span class="text-muted small">
                                                    {{ app()->getLocale() == 'ar' ? 'غير مخصصة' : 'Not assigned' }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
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
