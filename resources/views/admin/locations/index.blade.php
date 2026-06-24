@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">

    <div class="mb-4">
        <h4 class="mb-1">{{ $ar ? 'إدارة المدن والأحياء' : 'Cities & Districts' }}</h4>
        <p class="text-muted">{{ $ar ? 'تُستخدم في حساب الشحن لكل موزع' : 'Used for distributor shipping calculation' }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Country selector --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.locations.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ $ar ? 'الدولة' : 'Country' }}</label>
                    <select name="country" class="form-select" onchange="this.form.submit()">
                        @foreach($countries as $c)
                            <option value="{{ $c->code }}" {{ $selectedCode === $c->code ? 'selected' : '' }}>
                                {{ $c->flag }} {{ $ar ? ($c->name_ar ?: $c->name) : $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addCityModal">
                        <i class="ri-add-line me-1"></i>{{ $ar ? 'إضافة مدينة' : 'Add City' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        {{-- Cities list --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ $ar ? 'المدن' : 'Cities' }} ({{ $cities->count() }})</h6></div>
                <div class="list-group list-group-flush" id="citiesList">
                    @forelse($cities as $city)
                        <div class="list-group-item d-flex justify-content-between align-items-center city-row"
                             data-city-id="{{ $city->id }}"
                             data-city-name="{{ $city->name }}"
                             data-city-name-ar="{{ $city->name_ar }}">
                            <button type="button" class="btn btn-link text-decoration-none p-0 flex-grow-1 text-start select-city" style="color:inherit;">
                                <strong>{{ $ar ? ($city->name_ar ?: $city->name) : $city->name }}</strong>
                                <span class="badge bg-label-secondary ms-2">{{ $city->districts_count }} {{ $ar ? 'حي' : 'districts' }}</span>
                                @unless($city->is_active)<span class="badge bg-label-danger ms-1">{{ $ar ? 'معطّل' : 'inactive' }}</span>@endunless
                            </button>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary edit-city-btn" title="{{ $ar ? 'تعديل' : 'Edit' }}"><i class="ri-edit-line"></i></button>
                                <form method="POST" action="{{ route('admin.locations.cities.destroy', $city) }}" onsubmit="return confirm('{{ $ar ? 'حذف المدينة وكل أحيائها؟' : 'Delete city and its districts?' }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger" title="{{ $ar ? 'حذف' : 'Delete' }}"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">{{ $ar ? 'لا توجد مدن' : 'No cities yet' }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Districts panel --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0" id="districtsTitle">{{ $ar ? 'الأحياء' : 'Districts' }}</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="addDistrictBtn" disabled>
                        <i class="ri-add-line me-1"></i>{{ $ar ? 'إضافة حي' : 'Add District' }}
                    </button>
                </div>
                <div class="card-body">
                    <div id="districtsEmpty" class="text-center text-muted py-4">
                        {{ $ar ? 'اختر مدينة لعرض أحيائها' : 'Select a city to view its districts' }}
                    </div>
                    <div id="districtsList" class="d-none"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add City Modal --}}
<div class="modal fade" id="addCityModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="{{ route('admin.locations.cities.store') }}">
            @csrf
            <input type="hidden" name="country_code" value="{{ $selectedCode }}">
            <div class="modal-header"><h5 class="modal-title">{{ $ar ? 'إضافة مدينة' : 'Add City' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">{{ $ar ? 'الاسم (إنجليزي)' : 'Name (English)' }}</label>
                    <input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">{{ $ar ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                    <input type="text" name="name_ar" class="form-control"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ $ar ? 'إلغاء' : 'Cancel' }}</button>
                <button type="submit" class="btn btn-primary">{{ $ar ? 'حفظ' : 'Save' }}</button>
            </div>
        </form>
    </div></div>
</div>

{{-- Edit City Modal --}}
<div class="modal fade" id="editCityModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" id="editCityForm">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">{{ $ar ? 'تعديل المدينة' : 'Edit City' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">{{ $ar ? 'الاسم (إنجليزي)' : 'Name (English)' }}</label>
                    <input type="text" name="name" id="editCityName" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">{{ $ar ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                    <input type="text" name="name_ar" id="editCityNameAr" class="form-control"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ $ar ? 'إلغاء' : 'Cancel' }}</button>
                <button type="submit" class="btn btn-primary">{{ $ar ? 'حفظ' : 'Save' }}</button>
            </div>
        </form>
    </div></div>
</div>

@push('scripts')
<script>
(function () {
    const isAr = {{ $ar ? 'true' : 'false' }};
    const csrf = '{{ csrf_token() }}';
    let currentCityId = null;

    const districtsList = document.getElementById('districtsList');
    const districtsEmpty = document.getElementById('districtsEmpty');
    const districtsTitle = document.getElementById('districtsTitle');
    const addDistrictBtn = document.getElementById('addDistrictBtn');

    // Select a city -> load its districts
    document.querySelectorAll('.city-row .select-city').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.city-row');
            currentCityId = row.dataset.cityId;
            document.querySelectorAll('.city-row').forEach(r => r.classList.remove('active'));
            row.classList.add('active');
            districtsTitle.textContent = (isAr ? 'أحياء: ' : 'Districts: ') + (row.dataset.cityNameAr || row.dataset.cityName);
            addDistrictBtn.disabled = false;
            loadDistricts(currentCityId);
        });
    });

    function loadDistricts(cityId) {
        fetch(`{{ url('admin/locations/cities') }}/${cityId}/districts`)
            .then(r => r.json())
            .then(d => renderDistricts(d.districts));
    }

    function renderDistricts(districts) {
        districtsEmpty.classList.add('d-none');
        districtsList.classList.remove('d-none');
        if (!districts.length) {
            districtsList.innerHTML = `<div class="text-center text-muted py-3">${isAr ? 'لا توجد أحياء' : 'No districts'}</div>`;
            return;
        }
        districtsList.innerHTML = districts.map(d => `
            <div class="d-flex justify-content-between align-items-center border-bottom py-2" data-district-id="${d.id}">
                <span>${isAr ? (d.name_ar || d.name) : d.name} ${d.is_active ? '' : `<span class="badge bg-label-danger ms-1">${isAr?'معطّل':'inactive'}</span>`}</span>
                <button class="btn btn-sm btn-outline-danger del-district"><i class="ri-delete-bin-line"></i></button>
            </div>`).join('');
        districtsList.querySelectorAll('.del-district').forEach(b => {
            b.addEventListener('click', function () {
                const id = this.closest('[data-district-id]').dataset.districtId;
                if (!confirm(isAr ? 'حذف الحي؟' : 'Delete district?')) return;
                fetch(`{{ url('admin/locations/districts') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                }).then(() => loadDistricts(currentCityId));
            });
        });
    }

    addDistrictBtn.addEventListener('click', function () {
        if (!currentCityId) return;
        const name = prompt(isAr ? 'اسم الحي (إنجليزي):' : 'District name (English):');
        if (!name) return;
        const nameAr = prompt(isAr ? 'اسم الحي (عربي):' : 'District name (Arabic):') || '';
        fetch(`{{ route('admin.locations.districts.store') }}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ city_id: currentCityId, name: name, name_ar: nameAr })
        }).then(r => r.json()).then(() => loadDistricts(currentCityId));
    });

    // Edit city
    document.querySelectorAll('.edit-city-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.city-row');
            const form = document.getElementById('editCityForm');
            form.action = `{{ url('admin/locations/cities') }}/${row.dataset.cityId}`;
            document.getElementById('editCityName').value = row.dataset.cityName;
            document.getElementById('editCityNameAr').value = row.dataset.cityNameAr;
            new bootstrap.Modal(document.getElementById('editCityModal')).show();
        });
    });
})();
</script>
@endpush
@endsection
