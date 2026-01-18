@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.affiliate.stores') }}">{{ app()->getLocale() == 'ar' ? 'المتاجر' : 'Stores' }}</a></li>
                <li class="breadcrumb-item active">{{ app()->getLocale() == 'ar' ? 'إضافة متجر' : 'Add Store' }}</li>
            </ol>
        </nav>
        <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'إضافة متجر جديد' : 'Add New Store' }}</h4>
        <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'إضافة متجر موزع بدون بيانات دخول' : 'Add a distributor store without login credentials' }}</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.affiliate.stores.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">
                    <!-- Store Name -->
                    <div class="col-md-6">
                        <label for="store_name" class="form-label">
                            {{ app()->getLocale() == 'ar' ? 'اسم المتجر' : 'Store Name' }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('store_name') is-invalid @enderror"
                            id="store_name"
                            name="store_name"
                            value="{{ old('store_name') }}"
                            required
                        >
                        @error('store_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Owner Name -->
                    <div class="col-md-6">
                        <label for="name" class="form-label">
                            {{ app()->getLocale() == 'ar' ? 'اسم المالك' : 'Owner Name' }}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Country -->
                    <div class="col-md-6">
                        <label for="country" class="form-label">
                            {{ app()->getLocale() == 'ar' ? 'الدولة' : 'Country' }}
                            <span class="text-danger">*</span>
                        </label>
                        <select
                            class="form-select @error('country') is-invalid @enderror"
                            id="country"
                            name="country"
                            required
                        >
                            <option value="">{{ app()->getLocale() == 'ar' ? 'اختر الدولة' : 'Select Country' }}</option>
                            @foreach($countries as $code => $names)
                                <option value="{{ $code }}" {{ old('country') === $code ? 'selected' : '' }}>
                                    {{ app()->getLocale() == 'ar' ? $names['ar'] : $names['en'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <label for="phone" class="form-label">
                            {{ app()->getLocale() == 'ar' ? 'رقم الهاتف' : 'Phone Number' }}
                        </label>
                        <input
                            type="text"
                            class="form-control @error('phone') is-invalid @enderror"
                            id="phone"
                            name="phone"
                            value="{{ old('phone') }}"
                            dir="ltr"
                            style="text-align: left;"
                        >
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Avatar -->
                    <div class="col-md-6">
                        <label for="avatar" class="form-label">
                            {{ app()->getLocale() == 'ar' ? 'صورة المتجر' : 'Store Avatar' }}
                        </label>
                        <input
                            type="file"
                            class="form-control @error('avatar') is-invalid @enderror"
                            id="avatar"
                            name="avatar"
                            accept="image/*"
                        >
                        <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الحد الأقصى 2 ميجابايت' : 'Max 2MB' }}</small>
                        @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preview -->
                    <div class="col-md-6">
                        <div id="avatarPreview" class="d-none">
                            <label class="form-label">{{ app()->getLocale() == 'ar' ? 'معاينة' : 'Preview' }}</label>
                            <div>
                                <img id="previewImage" src="" alt="Preview" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="alert alert-info mt-4">
                    <i class="ri-information-line me-2"></i>
                    {{ app()->getLocale() == 'ar' ? 'سيتم إنشاء المتجر بدون بيانات دخول. يمكنك إضافة كوبونات لهذا المتجر مباشرة.' : 'The store will be created without login credentials. You can add coupons for this store directly.' }}
                </div>

                <!-- Submit -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>
                        {{ app()->getLocale() == 'ar' ? 'حفظ المتجر' : 'Save Store' }}
                    </button>
                    <a href="{{ route('admin.affiliate.stores') }}" class="btn btn-outline-secondary">
                        {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('avatarPreview').classList.remove('d-none');
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection
