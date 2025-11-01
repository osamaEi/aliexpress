@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.all_users') }}</h4>
        <p class="text-muted">{{ __('messages.users') }}</p>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">{{ __('messages.search') }}</label>
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ __('messages.search') }}..."
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="user_type" class="form-label">{{ __('messages.user_type') }}</label>
                        <select class="form-select" id="user_type" name="user_type">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="admin" {{ request('user_type') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="seller" {{ request('user_type') === 'seller' ? 'selected' : '' }}>Seller</option>
                            <option value="customer" {{ request('user_type') === 'customer' ? 'selected' : '' }}>Customer</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-search-line me-1"></i>
                            {{ __('messages.filter') }}
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            {{ __('messages.reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.total_users') }}: {{ $users->total() }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.email') }}</th>
                            <th>{{ __('messages.user_type') }}</th>
                            <th>{{ __('messages.subscriptions') }}</th>
                            <th>{{ __('messages.registered') }}</th>
                            <th>{{ __('messages.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded-circle bg-label-{{ $user->user_type === 'admin' ? 'danger' : ($user->user_type === 'seller' ? 'primary' : 'secondary') }}">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->user_type === 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @elseif($user->user_type === 'seller')
                                    <span class="badge bg-primary">Seller</span>
                                @else
                                    <span class="badge bg-secondary">Customer</span>
                                @endif
                            </td>
                            <td>
                                @if($user->subscriptions_count > 0)
                                    <span class="badge bg-success">{{ $user->subscriptions_count }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('Y-m-d') }}</td>
                            <td>
                                @if($user->email_verified_at)
                                    <span class="badge bg-success">{{ __('messages.verified') }}</span>
                                @else
                                    <span class="badge bg-warning">{{ __('messages.unverified') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                {{ __('messages.no_users_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
