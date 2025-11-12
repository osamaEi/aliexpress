@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.customer_users') }}</h4>
        <p class="text-muted">{{ __('messages.manage_customer_users') }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.customers.index') }}">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="search" class="form-label">{{ __('messages.search') }}</label>
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ __('messages.search_by_name_email') }}..."
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="email_verified" class="form-label">{{ __('messages.email_status') }}</label>
                        <select class="form-select" id="email_verified" name="email_verified">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="1" {{ request('email_verified') === '1' ? 'selected' : '' }}>{{ __('messages.verified') }}</option>
                            <option value="0" {{ request('email_verified') === '0' ? 'selected' : '' }}>{{ __('messages.unverified') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-search-line me-1"></i>
                            {{ __('messages.filter') }}
                        </button>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                            {{ __('messages.reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.total_customers') }}: {{ $customers->total() }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.email') }}</th>
                            <th>{{ __('messages.country') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.registered') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        @if($customer->avatar)
                                            <img src="{{ asset('storage/' . $customer->avatar) }}" alt="{{ $customer->name }}" class="rounded-circle">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $customer->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->country ?: '-' }}</td>
                            <td>
                                @if($customer->email_verified_at)
                                    <span class="badge bg-success">{{ __('messages.verified') }}</span>
                                @else
                                    <span class="badge bg-warning">{{ __('messages.unverified') }}</span>
                                @endif
                            </td>
                            <td>{{ $customer->created_at->format('Y-m-d') }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-info">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                {{ __('messages.no_customers_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="card-footer">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
