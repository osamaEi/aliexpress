@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        @if($parentCategory)
                            {{ app()->getLocale() == 'ar' ? 'التصنيفات الفرعية لـ' : 'Subcategories of' }} "{{ app()->getLocale() == 'ar' && $parentCategory->name_ar ? $parentCategory->name_ar : $parentCategory->name }}"
                        @else
                            {{ app()->getLocale() == 'ar' ? 'التصنيفات الرئيسية' : 'Main Categories' }}
                        @endif
                    </h5>
                </div>
                @if($parentCategory)
                    <a href="{{ route('distributor.categories') }}" class="btn btn-sm btn-secondary">
                        <i class="ri-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}-line"></i>
                        {{ app()->getLocale() == 'ar' ? 'العودة للتصنيفات الرئيسية' : 'Back to Main Categories' }}
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 80px;">{{ app()->getLocale() == 'ar' ? 'الصورة' : 'Image' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الاسم بالإنجليزي' : 'English Name' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الاسم بالعربي' : 'Arabic Name' }}</th>
                            <th style="width: 120px;">{{ app()->getLocale() == 'ar' ? 'التصنيفات الفرعية' : 'Subcategories' }}</th>
                            <th style="width: 100px;">{{ __('messages.status') }}</th>
                            <th style="width: 120px;">{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td>
                                @if($category->image)
                                    <img src="{{ $category->image }}" alt="{{ $category->name }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @elseif($category->photo)
                                    <img src="{{ asset('storage/' . $category->photo) }}" alt="{{ $category->name }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="ri-image-line ri-2x text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $category->name }}</strong>
                            </td>
                            <td>
                                @if($category->name_ar)
                                    <strong>{{ $category->name_ar }}</strong>
                                @else
                                    <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'لا يوجد' : 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($category->children_count > 0)
                                    <span class="badge bg-primary">
                                        <i class="ri-folder-line me-1"></i>
                                        {{ $category->children_count }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">0</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                    {{ $category->is_active ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </td>
                            <td>
                                @if($category->children_count > 0)
                                    <a href="{{ route('distributor.categories', ['parent_id' => $category->id]) }}" class="btn btn-sm btn-info">
                                        <i class="ri-eye-line me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'عرض الفرعية' : 'View Subs' }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="ri-folder-open-line ri-3x text-muted mb-2"></i>
                                <p class="text-muted mb-0">
                                    {{ app()->getLocale() == 'ar' ? 'لا توجد تصنيفات' : 'No categories available' }}
                                </p>
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

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(86, 28, 4, 0.05);
    }

    .badge {
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: scale(1.05);
    }
</style>
@endsection
