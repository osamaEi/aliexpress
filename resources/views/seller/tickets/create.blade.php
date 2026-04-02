@extends('dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.create_new_ticket') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('seller.tickets.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="subject" class="form-label">{{ __('messages.subject') }}</label>
                            <input type="text"
                                   class="form-control @error('subject') is-invalid @enderror"
                                   id="subject"
                                   name="subject"
                                   value="{{ old('subject') }}"
                                   required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">{{ __('messages.priority') }}</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <label class="priority-btn">
                                    <input type="radio" name="priority" value="low" {{ old('priority') == 'low' ? 'checked' : '' }} required>
                                    <span class="btn btn-outline-success">{{ __('messages.priority_low') }}</span>
                                </label>
                                <label class="priority-btn">
                                    <input type="radio" name="priority" value="medium" {{ old('priority') == 'medium' ? 'checked' : '' }}>
                                    <span class="btn btn-outline-warning">{{ __('messages.priority_medium') }}</span>
                                </label>
                                <label class="priority-btn">
                                    <input type="radio" name="priority" value="high" {{ old('priority') == 'high' ? 'checked' : '' }}>
                                    <span class="btn btn-outline-danger">{{ __('messages.priority_high') }}</span>
                                </label>
                            </div>
                            @error('priority')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <style>
                                .priority-btn input[type="radio"] { display: none; }
                                .priority-btn input[type="radio"]:checked + span { opacity: 1; filter: none; color: #fff !important; }
                                .priority-btn span { opacity: 0.55; cursor: pointer; transition: opacity .2s; min-width: 90px; }
                                .priority-btn input[type="radio"]:checked + span.btn-outline-success { background-color: var(--bs-success) !important; border-color: var(--bs-success) !important; }
                                .priority-btn input[type="radio"]:checked + span.btn-outline-warning { background-color: var(--bs-warning) !important; border-color: var(--bs-warning) !important; }
                                .priority-btn input[type="radio"]:checked + span.btn-outline-danger  { background-color: var(--bs-danger)  !important; border-color: var(--bs-danger)  !important; }
                            </style>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('messages.description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="8"
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('seller.tickets.index') }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                {{ __('messages.back') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-send-plane-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                {{ __('messages.submit_ticket') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
