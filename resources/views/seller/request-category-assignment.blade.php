@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
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
            border-color: #3b82f6;
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
        .category-card.assigned .category-icon {
            opacity: 1;
        }

        .subcategory-item {
            transition: all 0.2s ease;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .subcategory-item:hover {
            background: #f9fafb;
        }

        .subcategory-item.selected {
            background: #eff6ff;
            border: 1px solid #3b82f6;
        }

        .assigned-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .section-header {
            background: linear-gradient(135deg, #561C04 0%, #7a2805 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
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

        .cursor-pointer {
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn {
            transition: all 0.2s ease;
        }

        textarea:focus {
            border-color: #561C04 !important;
            box-shadow: 0 0 0 0.25rem rgba(86, 28, 4, 0.15) !important;
        }
    </style>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-0 pt-4">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="ri-add-circle-line text-primary" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h4 class="mb-0">
                        {{ app()->getLocale() == 'ar' ? 'طلب تعيين فئات جديدة' : 'Request New Category Assignment' }}
                    </h4>
                    <p class="text-muted mb-0 small">
                        {{ app()->getLocale() == 'ar' ? 'اختر الفئات المناسبة لنشاطك التجاري' : 'Select categories that match your business activity' }}
                    </p>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-start">
                    <i class="ri-information-line me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>{{ app()->getLocale() == 'ar' ? 'نصيحة:' : 'Tip:' }}</strong>
                        {{ app()->getLocale() == 'ar'
                            ? 'اختر الفئات الرئيسية أولاً، ثم حدد الفئات الفرعية التي تريد العمل بها. الفئات المحددة حالياً تظهر بخلفية خضراء.'
                            : 'Select main categories first, then choose subcategories you want to work with. Currently assigned categories are shown with green background.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-4">

            <form action="{{ route('seller.submit-category-request') }}" method="POST">
                @csrf

                <div class="mb-5">
                    <div class="section-header">
                        <h5 class="mb-0">
                            <i class="ri-checkbox-multiple-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'الفئات الرئيسية' : 'Main Categories' }}
                            <span class="text-warning ms-2">*</span>
                        </h5>
                        <p class="mb-0 small opacity-75 mt-1">
                            {{ app()->getLocale() == 'ar' ? 'اختر فئة رئيسية أو أكثر' : 'Select one or more main categories' }}
                        </p>
                    </div>

                    <div class="row g-3">
                        @foreach($mainCategories as $mainCategory)
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
                                                <strong class="d-block">{{ $mainCategory->name }}</strong>
                                                @if($mainCategory->name_ar)
                                                    <small class="text-muted d-block mt-1" dir="rtl">{{ $mainCategory->name_ar }}</small>
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

                <div class="mb-5">
                    <div class="section-header">
                        <h5 class="mb-0">
                            <i class="ri-git-branch-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'الفئات الفرعية' : 'Subcategories' }}
                            <span class="badge bg-white bg-opacity-25 ms-2 small">
                                {{ app()->getLocale() == 'ar' ? 'اختياري' : 'Optional' }}
                            </span>
                        </h5>
                        <p class="mb-0 small opacity-75 mt-1">
                            {{ app()->getLocale() == 'ar' ? 'حدد الفئات الفرعية التي تريد العمل بها' : 'Select specific subcategories you want to work with' }}
                        </p>
                    </div>

                    @foreach($mainCategories as $mainCategory)
                        @if(isset($subCategories[$mainCategory->id]) && $subCategories[$mainCategory->id]->isNotEmpty())
                            <div class="subcategory-group" data-parent="{{ $mainCategory->id }}" style="display: {{ in_array($mainCategory->id, $assignedCategoryIds) ? 'block' : 'none' }}">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                        <i class="ri-folder-open-line text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">
                                            {{ app()->getLocale() == 'ar' ? 'الفئات الفرعية لـ' : 'Subcategories for' }}
                                            <strong class="text-primary">{{ $mainCategory->name }}</strong>
                                        </h6>
                                        @if($mainCategory->name_ar)
                                            <small class="text-muted" dir="rtl">{{ $mainCategory->name_ar }}</small>
                                        @endif
                                    </div>
                                </div>

                                <div class="row g-2">
                                    @foreach($subCategories[$mainCategory->id] as $subCategory)
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
                                                        <span>{{ $subCategory->name }}</span>
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

                <div class="mb-5">
                    <label for="reason" class="form-label fw-semibold mb-2">
                        <i class="ri-quill-pen-line me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'سبب الطلب' : 'Reason for Request' }}
                        <span class="text-muted fw-normal small ms-1">
                            ({{ app()->getLocale() == 'ar' ? 'اختياري' : 'Optional' }})
                        </span>
                    </label>
                    <textarea class="form-control shadow-sm"
                              id="reason"
                              name="reason"
                              rows="4"
                              style="border-radius: 10px; border-color: #e5e7eb;"
                              placeholder="{{ app()->getLocale() == 'ar' ? 'اشرح لنا لماذا تحتاج هذه الفئات لتطوير نشاطك التجاري...' : 'Tell us why you need these categories to grow your business...' }}"></textarea>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-information-line me-1"></i>
                        {{ app()->getLocale() == 'ar'
                            ? 'تقديم سبب مقنع يساعد في تسريع الموافقة على طلبك'
                            : 'Providing a compelling reason helps speed up the approval of your request' }}
                    </small>
                </div>

                <div class="d-flex gap-3 pt-3 border-top">
                    <button type="submit" class="btn btn-lg px-4" style="background: linear-gradient(135deg, #561C04 0%, #7a2805 100%); color: white; border: none; border-radius: 10px;">
                        <i class="ri-send-plane-fill me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'إرسال الطلب' : 'Submit Request' }}
                    </button>
                    <a href="{{ route('categories.index') }}" class="btn btn-lg btn-outline-secondary px-4" style="border-radius: 10px;">
                        <i class="ri-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}-line me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'رجوع' : 'Back' }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all main category checkboxes
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
                        // Uncheck all subcategories
                        const subcategoryCheckboxes = subcategoryGroup.querySelectorAll('input[type="checkbox"]');
                        subcategoryCheckboxes.forEach(sub => sub.checked = false);
                    }
                }
            });
        });
    });
</script>
@endsection
