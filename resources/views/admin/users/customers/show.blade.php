@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.customer_details') }}</h4>
        <p class="text-muted">{{ __('messages.view_customer_information') }}</p>
    </div>

    <!-- Customer Info Card -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.customer_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.name') }}:</div>
                        <div class="col-md-8">{{ $customer->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.email') }}:</div>
                        <div class="col-md-8">{{ $customer->email }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.country') }}:</div>
                        <div class="col-md-8">{{ $customer->country ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.email_verification') }}:</div>
                        <div class="col-md-8">
                            @if($customer->email_verified_at)
                                <span class="badge bg-success">{{ __('messages.verified') }}</span>
                                <small class="text-muted ms-2">({{ $customer->email_verified_at->format('Y-m-d') }})</small>
                            @else
                                <span class="badge bg-warning">{{ __('messages.unverified') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.registered_date') }}:</div>
                        <div class="col-md-8">{{ $customer->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">{{ __('messages.last_updated') }}:</div>
                        <div class="col-md-8">{{ $customer->updated_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.quick_actions') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="ri-delete-bin-line me-1"></i>
                            {{ __('messages.delete_customer') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.statistics') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">{{ __('messages.total_orders') }}</small>
                        <h4>0</h4>
                        <small class="text-muted">{{ __('messages.coming_soon') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            {{ __('messages.back_to_customers') }}
        </a>
    </div>
</div>
@endsection
