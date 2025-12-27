@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'تعديل الدولة' : 'Edit Country' }}</h4>
            <p class="text-muted">{{ $country->flag_display }} {{ $country->localized_name }}</p>
        </div>
        <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            {{ app()->getLocale() == 'ar' ? 'العودة' : 'Back' }}
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.countries.update', $country) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">{{ app()->getLocale() == 'ar' ? 'كود الدولة' : 'Country Code' }} *</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
                            value="{{ old('code', $country->code) }}" placeholder="AE" maxlength="3" style="text-transform: uppercase;" required>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'كود ISO من 2-3 أحرف (مثل: AE, SA, CN)' : 'ISO code 2-3 letters (e.g., AE, SA, CN)' }}</small>
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="flag" class="form-label">{{ app()->getLocale() == 'ar' ? 'العلم (إيموجي)' : 'Flag (Emoji)' }}</label>
                        <input type="text" class="form-control @error('flag') is-invalid @enderror" id="flag" name="flag"
                            value="{{ old('flag', $country->flag) }}" placeholder="🇦🇪" maxlength="10">
                        @error('flag')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">{{ app()->getLocale() == 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                            value="{{ old('name', $country->name) }}" placeholder="United Arab Emirates" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="name_ar" class="form-label">{{ app()->getLocale() == 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                        <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar"
                            value="{{ old('name_ar', $country->name_ar) }}" placeholder="الإمارات العربية المتحدة" dir="rtl">
                        @error('name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="phone_code" class="form-label">{{ app()->getLocale() == 'ar' ? 'كود الهاتف' : 'Phone Code' }}</label>
                        <input type="text" class="form-control @error('phone_code') is-invalid @enderror" id="phone_code" name="phone_code"
                            value="{{ old('phone_code', $country->phone_code) }}" placeholder="+971">
                        @error('phone_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="currency_code" class="form-label">{{ app()->getLocale() == 'ar' ? 'كود العملة' : 'Currency Code' }}</label>
                        <input type="text" class="form-control @error('currency_code') is-invalid @enderror" id="currency_code" name="currency_code"
                            value="{{ old('currency_code', $country->currency_code) }}" placeholder="AED" maxlength="3" style="text-transform: uppercase;">
                        @error('currency_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="sort_order" class="form-label">{{ app()->getLocale() == 'ar' ? 'ترتيب العرض' : 'Sort Order' }}</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order"
                            value="{{ old('sort_order', $country->sort_order) }}" min="0">
                        @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $country->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                {{ app()->getLocale() == 'ar' ? 'نشط' : 'Active' }}
                            </label>
                        </div>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'إظهار الدولة في القوائم' : 'Show country in dropdowns' }}</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_source" name="is_source" value="1" {{ old('is_source', $country->is_source) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_source">
                                {{ app()->getLocale() == 'ar' ? 'دولة مصدر للمنتجات' : 'Product Source Country' }}
                            </label>
                        </div>
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'هذه الدولة مصدر للمنتجات (مثل الإمارات، السعودية، الصين)' : 'This country is a product source (e.g., UAE, Saudi, China)' }}</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>
                        {{ app()->getLocale() == 'ar' ? 'تحديث' : 'Update' }}
                    </button>
                    <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary">
                        {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
