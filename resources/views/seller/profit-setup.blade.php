@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'إعداد مبالغ الربح' : 'Profit Amount Setup' }}</h4>
        <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'يرجى تحديد مبلغ الربح الثابت لكل فئة فرعية قمت باختيارها' : 'Please set fixed profit amount for each subcategory you selected' }}</p>
    </div>

    <!-- Alert Notice -->
    <div class="alert alert-info mb-4" role="alert">
        <i class="ri-information-line me-2"></i>
        {{ app()->getLocale() == 'ar'
            ? 'يجب عليك إكمال إعدادات الربح قبل البدء في استخدام النظام'
            : 'You must complete profit settings before accessing the system' }}
    </div>

    <!-- Save Notice -->
    <div class="alert alert-warning mb-4" role="alert">
        <i class="ri-save-line me-2"></i>
        {{ app()->getLocale() == 'ar'
            ? 'سوف يتم حفظ جميع القيم حتى لو كانت صفر'
            : 'All values will be saved even if they are zero' }}
    </div>

    <!-- Profit Settings Form -->
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('seller.profit.store') }}">
                @csrf

                @if($subcategories->isEmpty())
                    <div class="alert alert-warning">
                        {{ app()->getLocale() == 'ar'
                            ? 'لم يتم العثور على فئات فرعية. يرجى الاتصال بالدعم.'
                            : 'No subcategories found. Please contact support.' }}
                    </div>
                @else
                    @foreach($subcategories as $parentId => $subs)
                        @php
                            $parent = $subs->first()->parent;
                        @endphp

                        <div class="mb-4">
                            <h5 class="mb-3" style="color: #000; font-weight: 600;">
                                {{ app()->getLocale() == 'ar' ? $parent->name_ar : $parent->name_en }}
                            </h5>

                            <div class="row">
                                @foreach($subs as $subcategory)
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded" style="background: #f8f9fa;">
                                            <label for="profit_{{ $subcategory->id }}" class="form-label fw-semibold">
                                                {{ app()->getLocale() == 'ar' ? $subcategory->name_ar : $subcategory->name_en }}
                                            </label>
                                            <div class="row g-2">
                                                <div class="col-5">
                                                    <select class="form-select profit-type-select" name="profit_type_{{ $subcategory->id }}" data-id="{{ $subcategory->id }}">
                                                        <option value="fixed">{{ app()->getLocale() == 'ar' ? 'مبلغ ثابت' : 'Fixed' }}</option>
                                                        <option value="percentage">{{ app()->getLocale() == 'ar' ? 'نسبة %' : 'Percentage' }}</option>
                                                    </select>
                                                </div>
                                                <div class="col-7">
                                                    <div class="input-group">
                                                        <input
                                                            type="number"
                                                            class="form-control profit-value-input @error('profit_' . $subcategory->id) is-invalid @enderror"
                                                            id="profit_{{ $subcategory->id }}"
                                                            name="profit_{{ $subcategory->id }}"
                                                            data-id="{{ $subcategory->id }}"
                                                            min="0"
                                                            step="0.01"
                                                            placeholder="{{ app()->getLocale() == 'ar' ? 'القيمة' : 'Value' }}"
                                                            value="{{ old('profit_' . $subcategory->id, '0') }}"
                                                            required
                                                        >
                                                        <span class="input-group-text profit-unit" data-id="{{ $subcategory->id }}">{{ session('currency_code', 'AED') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            @error('profit_' . $subcategory->id)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <hr class="my-4">
                    @endforeach

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="ri-check-line me-2"></i>
                            {{ app()->getLocale() == 'ar' ? 'حفظ والمتابعة' : 'Save and Continue' }}
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Help Text -->
    <div class="card mt-4" style="background: #fff3cd; border-color: #ffc107;">
        <div class="card-body">
            <h6 class="mb-2" style="color: #856404;">
                <i class="ri-lightbulb-line me-2"></i>
                {{ app()->getLocale() == 'ar' ? 'نصيحة' : 'Tip' }}
            </h6>
            <p class="mb-0 small" style="color: #856404;">
                {{ app()->getLocale() == 'ar'
                    ? 'مبلغ الربح هو المبلغ الثابت الذي سيضاف على سعر المنتج الأساسي. على سبيل المثال، إذا كان سعر المنتج 100 درهم ومبلغ الربح 10 درهم، سيكون السعر النهائي 110 درهم.'
                    : 'The profit amount is the fixed amount added to the base product price. For example, if a product costs 100 AED and the profit is 10 AED, the final price will be 110 AED.' }}
            </p>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }

    .form-control:focus {
        border-color: #000;
        box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
    }

    .btn-primary {
        background-color: #000;
        border-color: #000;
    }

    .btn-primary:hover {
        background-color: #333;
        border-color: #333;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currencyCode = '{{ session("currency_code", "AED") }}';

    document.querySelectorAll('.profit-type-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const id = this.dataset.id;
            const type = this.value;
            const unitSpan = document.querySelector(`.profit-unit[data-id="${id}"]`);
            const valueInput = document.querySelector(`.profit-value-input[data-id="${id}"]`);

            if (type === 'percentage') {
                unitSpan.textContent = '%';
                valueInput.setAttribute('max', '100');
                if (parseFloat(valueInput.value) > 100) {
                    valueInput.value = 100;
                }
            } else {
                unitSpan.textContent = currencyCode;
                valueInput.removeAttribute('max');
            }
        });
    });
});
</script>
@endsection
