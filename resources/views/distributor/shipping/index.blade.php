@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">

    <div class="mb-4">
        <h4 class="mb-1">{{ $ar ? 'أسعار الشحن' : 'Shipping Rates' }}</h4>
        <p class="text-muted">{{ $ar ? 'حدد سعر الشحن لكل دولة / مدينة / حي. عند الطلب يُحسب الشحن تلقائياً حسب عنوان العميل.' : 'Set shipping cost per country / city / district. The most specific matching rate is applied at checkout.' }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row">
        {{-- Add rate form --}}
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ $ar ? 'إضافة سعر شحن' : 'Add Shipping Rate' }}</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('distributor.shipping.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ $ar ? 'الدولة' : 'Country' }} <span class="text-danger">*</span></label>
                            <select name="country_code" id="countrySelect" class="form-select" required>
                                <option value="">{{ $ar ? 'اختر الدولة' : 'Select country' }}</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->code }}">{{ $c->flag }} {{ $ar ? ($c->name_ar ?: $c->name) : $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ $ar ? 'المدينة' : 'City' }}
                                <small class="text-muted">({{ $ar ? 'اختياري - اتركه للدولة كاملة' : 'optional - leave for whole country' }})</small>
                            </label>
                            <select name="city_id" id="citySelect" class="form-select" disabled>
                                <option value="">{{ $ar ? 'كل الدولة' : 'Whole country' }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ $ar ? 'الحي' : 'District' }}
                                <small class="text-muted">({{ $ar ? 'اختياري' : 'optional' }})</small>
                            </label>
                            <select name="district_id" id="districtSelect" class="form-select" disabled>
                                <option value="">{{ $ar ? 'كل المدينة' : 'All districts' }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ $ar ? 'سعر الشحن (د.إ)' : 'Shipping Cost (AED)' }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="shipping_cost" class="form-control" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">{{ $ar ? 'أيام التوصيل (من)' : 'Delivery (min days)' }}</label>
                                <input type="number" min="0" name="delivery_days_min" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ $ar ? 'أيام التوصيل (إلى)' : 'Delivery (max days)' }}</label>
                                <input type="number" min="0" name="delivery_days_max" class="form-control">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-add-line me-1"></i>{{ $ar ? 'إضافة' : 'Add' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Existing rates --}}
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{ $ar ? 'الأسعار الحالية' : 'Current Rates' }} ({{ $rates->count() }})</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ $ar ? 'الدولة' : 'Country' }}</th>
                                    <th>{{ $ar ? 'النطاق' : 'Scope' }}</th>
                                    <th>{{ $ar ? 'السعر' : 'Cost' }}</th>
                                    <th>{{ $ar ? 'التوصيل' : 'Delivery' }}</th>
                                    <th>{{ $ar ? 'الحالة' : 'Status' }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rates as $rate)
                                <tr>
                                    <td>{{ optional($rate->country)->flag }} {{ $rate->country_code }}</td>
                                    <td>{{ $rate->scope_label }}</td>
                                    <td><strong>{{ number_format($rate->shipping_cost, 2) }}</strong> <small class="text-muted">د.إ</small></td>
                                    <td>
                                        @if($rate->delivery_days_min || $rate->delivery_days_max)
                                            {{ $rate->delivery_days_min }}–{{ $rate->delivery_days_max }} {{ $ar ? 'يوم' : 'days' }}
                                        @else <span class="text-muted">—</span> @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $rate->is_active ? 'success' : 'secondary' }}">
                                            {{ $rate->is_active ? ($ar ? 'نشط' : 'Active') : ($ar ? 'معطّل' : 'Inactive') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-secondary edit-rate"
                                                data-action="{{ route('distributor.shipping.update', $rate) }}"
                                                data-cost="{{ $rate->shipping_cost }}"
                                                data-min="{{ $rate->delivery_days_min }}"
                                                data-max="{{ $rate->delivery_days_max }}"
                                                title="{{ $ar ? 'تعديل' : 'Edit' }}"><i class="ri-edit-line"></i></button>
                                            <form method="POST" action="{{ route('distributor.shipping.destroy', $rate) }}" onsubmit="return confirm('{{ $ar ? 'حذف سعر الشحن؟' : 'Delete rate?' }}')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger" title="{{ $ar ? 'حذف' : 'Delete' }}"><i class="ri-delete-bin-line"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">{{ $ar ? 'لا توجد أسعار شحن بعد' : 'No shipping rates yet' }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit rate modal --}}
<div class="modal fade" id="editRateModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" id="editRateForm">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">{{ $ar ? 'تعديل سعر الشحن' : 'Edit Shipping Rate' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">{{ $ar ? 'سعر الشحن (د.إ)' : 'Shipping Cost (AED)' }}</label>
                    <input type="number" step="0.01" min="0" name="shipping_cost" id="editCost" class="form-control" required></div>
                <div class="row g-2">
                    <div class="col-6"><label class="form-label">{{ $ar ? 'أيام (من)' : 'Min days' }}</label>
                        <input type="number" min="0" name="delivery_days_min" id="editMin" class="form-control"></div>
                    <div class="col-6"><label class="form-label">{{ $ar ? 'أيام (إلى)' : 'Max days' }}</label>
                        <input type="number" min="0" name="delivery_days_max" id="editMax" class="form-control"></div>
                </div>
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
    const countrySel = document.getElementById('countrySelect');
    const citySel = document.getElementById('citySelect');
    const districtSel = document.getElementById('districtSelect');
    const isAr = {{ $ar ? 'true' : 'false' }};

    countrySel.addEventListener('change', function () {
        citySel.innerHTML = `<option value="">${isAr ? 'كل الدولة' : 'Whole country'}</option>`;
        districtSel.innerHTML = `<option value="">${isAr ? 'كل المدينة' : 'All districts'}</option>`;
        districtSel.disabled = true;
        if (!this.value) { citySel.disabled = true; return; }
        fetch(`{{ url('distributor/shipping/cities') }}/${this.value}`)
            .then(r => r.json())
            .then(d => {
                d.cities.forEach(c => {
                    const o = document.createElement('option');
                    o.value = c.id;
                    o.textContent = isAr ? (c.name_ar || c.name) : c.name;
                    citySel.appendChild(o);
                });
                citySel.disabled = false;
            });
    });

    citySel.addEventListener('change', function () {
        districtSel.innerHTML = `<option value="">${isAr ? 'كل المدينة' : 'All districts'}</option>`;
        if (!this.value) { districtSel.disabled = true; return; }
        fetch(`{{ url('distributor/shipping/districts') }}/${this.value}`)
            .then(r => r.json())
            .then(d => {
                d.districts.forEach(dist => {
                    const o = document.createElement('option');
                    o.value = dist.id;
                    o.textContent = isAr ? (dist.name_ar || dist.name) : dist.name;
                    districtSel.appendChild(o);
                });
                districtSel.disabled = false;
            });
    });

    // Edit modal
    document.querySelectorAll('.edit-rate').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('editRateForm').action = this.dataset.action;
            document.getElementById('editCost').value = this.dataset.cost;
            document.getElementById('editMin').value = this.dataset.min;
            document.getElementById('editMax').value = this.dataset.max;
            new bootstrap.Modal(document.getElementById('editRateModal')).show();
        });
    });
})();
</script>
@endpush
@endsection
