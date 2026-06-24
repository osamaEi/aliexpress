@extends('dashboard')

@section('content')
@php $isAr = app()->getLocale() == 'ar'; @endphp
<div class="col-12" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <style>
        .category-card {
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
            border: 2px solid #e5e7eb;
        }
        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #561C04;
        }
        .category-card.selected {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-color: #561C04;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }
        .category-card.assigned {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-color: #10b981;
        }
        .category-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.7;
        }
        .category-card.selected .category-icon,
        .category-card.assigned .category-icon { opacity: 1; }
        .subcategory-item {
            transition: all 0.2s ease;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        .subcategory-item:hover { background: #f9fafb; }
        .subcategory-item.selected {
            background: #eff6ff;
            border: 1px solid #561C04;
        }
        .section-header {
            background: linear-gradient(135deg, #561C04 0%, #7a2805 100%);
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 14px rgba(86, 28, 4, 0.22);
        }
        .section-header h5, .section-header i, .section-header p { color: #fff !important; }
        /* Clear, readable "optional" pill on the brown header */
        .section-header .badge-optional {
            background: #ffffff;
            color: #561C04;
            font-weight: 600;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .section-header .badge-required {
            background: #ffc107;
            color: #4a2600;
            font-weight: 700;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .subcategory-group {
            background: #f9fafb;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
        }
        .form-check-input:checked {
            background-color: #561C04;
            border-color: #561C04;
        }
        .form-check-input:focus {
            border-color: #7a2805;
            box-shadow: 0 0 0 0.25rem rgba(86, 28, 4, 0.25);
        }
        .cursor-pointer { cursor: pointer; }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .btn { transition: all 0.2s ease; }
        textarea:focus {
            border-color: #561C04 !important;
            box-shadow: 0 0 0 0.25rem rgba(86, 28, 4, 0.15) !important;
        }
    </style>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-0 pt-4">
            <div class="d-flex align-items-center mb-3">
                <div>
                    <h4 class="mb-0">{{ __('messages.request_category_assignment') }}</h4>
                    <p class="text-muted mb-0 small">{{ __('messages.request_category_subtitle') }}</p>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-start">
                    <i class="ri-information-line me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>{{ __('messages.tip') }}:</strong>
                        {{ __('messages.tip_categories') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-4">
            <form action="{{ route('seller.submit-category-request') }}" method="POST">
                @csrf

                {{-- Main Categories --}}
                <div class="mb-5">
                    <div class="section-header">
                        <h5 class="mb-0 d-flex align-items-center flex-wrap gap-2">
                            <i class="ri-checkbox-multiple-line"></i>
                            <span>{{ __('messages.main_categories') }}</span>
                            <span class="badge badge-required">{{ __('messages.required') ?? ($isAr ? 'مطلوب' : 'Required') }}</span>
                        </h5>
                        <p class="mb-0 small mt-1" style="opacity:.85;">{{ __('messages.select_one_or_more_main') }}</p>
                    </div>

                    <div class="row g-3">
                        @foreach($mainCategories as $mainCategory)
                            @php
                                $catName = $isAr && $mainCategory->name_ar ? $mainCategory->name_ar : $mainCategory->name;
                                $catNameAlt = $isAr ? $mainCategory->name : $mainCategory->name_ar;
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <div class="category-card p-3 rounded position-relative {{ in_array($mainCategory->id, $assignedCategoryIds) ? 'assigned' : '' }}">
                                    <div class="form-check">
                                        <input class="form-check-input main-category-checkbox"
                                               type="checkbox"
                                               name="main_categories[]"
                                               value="{{ $mainCategory->id }}"
                                               id="main_{{ $mainCategory->id }}"
                                               data-category-id="{{ $mainCategory->id }}"
                                               {{ in_array($mainCategory->id, $assignedCategoryIds) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 cursor-pointer" for="main_{{ $mainCategory->id }}">
                                            <div>
                                                <strong class="d-block">{{ $catName }}</strong>
                                                @if($catNameAlt)
                                                    <small class="text-muted d-block mt-1">{{ $catNameAlt }}</small>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @error('main_categories')
                        <div class="alert alert-danger mt-3 mb-0">
                            <i class="ri-error-warning-line me-2"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Subcategories --}}
                <div class="mb-5">
                    <div class="section-header">
                        <h5 class="mb-0 d-flex align-items-center flex-wrap gap-2">
                            <i class="ri-git-branch-line"></i>
                            <span>{{ __('messages.subcategories') }}</span>
                            <span class="badge badge-optional">{{ __('messages.optional') }}</span>
                        </h5>
                        <p class="mb-0 small mt-1" style="opacity:.85;">{{ __('messages.select_subcategories_subtitle') }}</p>
                    </div>

                    @foreach($mainCategories as $mainCategory)
                        @if(isset($subCategories[$mainCategory->id]) && $subCategories[$mainCategory->id]->isNotEmpty())
                            @php
                                $catName = $isAr && $mainCategory->name_ar ? $mainCategory->name_ar : $mainCategory->name;
                            @endphp
                            <div class="subcategory-group" data-parent="{{ $mainCategory->id }}" style="display: {{ in_array($mainCategory->id, $assignedCategoryIds) ? 'block' : 'none' }}">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                        <i class="ri-folder-open-line text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">
                                            {{ __('messages.subcategories_for') }}
                                            <strong class="text-primary">{{ $catName }}</strong>
                                        </h6>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    @foreach($subCategories[$mainCategory->id] as $subCategory)
                                        @php
                                            $subName = $isAr && $subCategory->name_ar ? $subCategory->name_ar : $subCategory->name;
                                        @endphp
                                        <div class="col-md-6 col-lg-4">
                                            <div class="subcategory-item {{ in_array($subCategory->id, $assignedCategoryIds) ? 'selected' : '' }}">
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           name="sub_categories[]"
                                                           value="{{ $subCategory->id }}"
                                                           id="sub_{{ $subCategory->id }}"
                                                           {{ in_array($subCategory->id, $assignedCategoryIds) ? 'checked' : '' }}>
                                                    <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="sub_{{ $subCategory->id }}">
                                                        <span>{{ $subName }}</span>
                                                        @if(in_array($subCategory->id, $assignedCategoryIds))
                                                            <i class="ri-check-line text-success ms-2"></i>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @error('sub_categories')
                        <div class="alert alert-danger mt-3 mb-0">
                            <i class="ri-error-warning-line me-2"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Reason --}}
                <div class="mb-5">
                    <label for="reason" class="form-label fw-semibold mb-2">
                        <i class="ri-quill-pen-line me-2"></i>
                        {{ __('messages.reason_for_request') }}
                        <span class="text-muted fw-normal small ms-1">({{ __('messages.optional') }})</span>
                    </label>
                    <textarea class="form-control shadow-sm"
                              id="reason"
                              name="reason"
                              rows="4"
                              style="border-radius: 10px; border-color: #e5e7eb;"
                              placeholder="{{ __('messages.reason_placeholder') }}"></textarea>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-information-line me-1"></i>
                        {{ __('messages.reason_hint') }}
                    </small>
                </div>

                <div class="d-flex gap-3 pt-3 border-top">
                    <button type="submit" class="btn btn-lg px-4" style="background: linear-gradient(135deg, #561C04 0%, #7a2805 100%); color: white; border: none; border-radius: 10px;">
                        <i class="ri-send-plane-fill me-2"></i>
                        {{ __('messages.submit_request') }}
                    </button>
                    <a href="{{ route('categories.index') }}" class="btn btn-lg btn-outline-secondary px-4" style="border-radius: 10px;">
                        <i class="ri-arrow-{{ $isAr ? 'right' : 'left' }}-line me-2"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mainCheckboxes = document.querySelectorAll('.main-category-checkbox');
        mainCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const categoryId = this.dataset.categoryId;
                const subcategoryGroup = document.querySelector(`[data-parent="${categoryId}"]`);
                if (subcategoryGroup) {
                    if (this.checked) {
                        subcategoryGroup.style.display = 'block';
                    } else {
                        subcategoryGroup.style.display = 'none';
                        subcategoryGroup.querySelectorAll('input[type="checkbox"]').forEach(sub => sub.checked = false);
                    }
                }
            });
        });
    });
</script>
@endsection
