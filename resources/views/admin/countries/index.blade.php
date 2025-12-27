@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'إدارة الدول' : 'Countries Management' }}</h4>
            <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'إدارة دول المصادر والعملاء' : 'Manage source and customer countries' }}</p>
        </div>
        <a href="{{ route('admin.countries.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i>
            {{ app()->getLocale() == 'ar' ? 'إضافة دولة' : 'Add Country' }}
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-primary me-3 p-2">
                            <i class="ri-global-line ri-24px"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'إجمالي الدول' : 'Total Countries' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                            <i class="ri-check-line ri-24px"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['active'] }}</h5>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'دول نشطة' : 'Active Countries' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-warning me-3 p-2">
                            <i class="ri-store-2-line ri-24px"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['source'] }}</h5>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'دول المصدر' : 'Source Countries' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.countries.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">{{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                            placeholder="{{ app()->getLocale() == 'ar' ? 'اسم الدولة أو الكود' : 'Country name or code' }}">
                    </div>
                    <div class="col-md-3">
                        <label for="is_source" class="form-label">{{ app()->getLocale() == 'ar' ? 'دول المصدر' : 'Source Countries' }}</label>
                        <select class="form-select" id="is_source" name="is_source">
                            <option value="">{{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}</option>
                            <option value="1" {{ request('is_source') === '1' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'دول مصدر' : 'Source' }}</option>
                            <option value="0" {{ request('is_source') === '0' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'غير مصدر' : 'Non-Source' }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="is_active" class="form-label">{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</label>
                        <select class="form-select" id="is_active" name="is_active">
                            <option value="">{{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'نشط' : 'Active' }}</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'معطل' : 'Inactive' }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-search-line"></i>
                        </button>
                        <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-refresh-line"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Countries Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() == 'ar' ? 'العلم' : 'Flag' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الكود' : 'Code' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الاسم' : 'Name' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'كود الهاتف' : 'Phone Code' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'العملة' : 'Currency' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'دولة مصدر' : 'Source' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($countries as $country)
                        <tr>
                            <td>
                                <span style="font-size: 1.5rem;">{{ $country->flag_display }}</span>
                            </td>
                            <td><strong>{{ $country->code }}</strong></td>
                            <td>
                                {{ $country->localized_name }}
                                @if($country->name_ar && app()->getLocale() != 'ar')
                                <small class="text-muted d-block" dir="rtl">{{ $country->name_ar }}</small>
                                @elseif(app()->getLocale() == 'ar')
                                <small class="text-muted d-block">{{ $country->name }}</small>
                                @endif
                            </td>
                            <td>{{ $country->phone_code ?? '-' }}</td>
                            <td>{{ $country->currency_code ?? '-' }}</td>
                            <td>
                                @if($country->is_source)
                                    <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'مصدر' : 'Source' }}</span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input toggle-status" type="checkbox"
                                        data-id="{{ $country->id }}"
                                        {{ $country->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.countries.edit', $country) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    <form action="{{ route('admin.countries.destroy', $country) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد من حذف هذه الدولة؟' : 'Are you sure you want to delete this country?' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                {{ app()->getLocale() == 'ar' ? 'لا توجد دول' : 'No countries found' }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($countries->hasPages())
        <div class="card-footer">
            {{ $countries->links() }}
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle status
    document.querySelectorAll('.toggle-status').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const countryId = this.dataset.id;
            fetch(`{{ url('admin/countries') }}/${countryId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success notification
                }
            });
        });
    });
});
</script>
@endsection
