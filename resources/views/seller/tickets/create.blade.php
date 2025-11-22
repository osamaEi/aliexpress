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
                            <label for="priority" class="form-label">{{ __('messages.priority') }}</label>
                            <select class="form-select @error('priority') is-invalid @enderror"
                                    id="priority"
                                    name="priority"
                                    required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                    {{ __('messages.priority_low') }}
                                </option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>
                                    {{ __('messages.priority_medium') }}
                                </option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                    {{ __('messages.priority_high') }}
                                </option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
