@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.seller_users') }}</h4>
        <p class="text-muted">{{ __('messages.manage_seller_users') }}</p>
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
            <form method="GET" action="{{ route('admin.sellers.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">{{ __('messages.search') }}</label>
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ __('messages.search_by_name_email_company') }}..."
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="is_verified" class="form-label">{{ __('messages.verification_status') }}</label>
                        <select class="form-select" id="is_verified" name="is_verified">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="1" {{ request('is_verified') === '1' ? 'selected' : '' }}>{{ __('messages.verified') }}</option>
                            <option value="0" {{ request('is_verified') === '0' ? 'selected' : '' }}>{{ __('messages.unverified') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="main_activity" class="form-label">{{ __('messages.main_activity') }}</label>
                        <select class="form-select" id="main_activity" name="main_activity">
                            <option value="">{{ __('messages.all') }}</option>
                            @foreach($mainActivities as $activity)
                                <option value="{{ $activity }}" {{ request('main_activity') === $activity ? 'selected' : '' }}>
                                    {{ $activity }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-search-line me-1"></i>
                            {{ __('messages.filter') }}
                        </button>
                        <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
                            {{ __('messages.reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sellers Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.total_sellers') }}: {{ $sellers->total() }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.company') }}</th>
                            <th>{{ __('messages.email') }}</th>
                            <th>{{ __('messages.activity') }}</th>
                            <th>{{ __('messages.subscription') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.registered') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sellers as $seller)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        @if($seller->avatar)
                                            <img src="{{ asset('storage/' . $seller->avatar) }}" alt="{{ $seller->name }}" class="rounded-circle">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($seller->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $seller->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $seller->company_name ?: '-' }}</td>
                            <td>{{ $seller->email }}</td>
                            <td>
                                @if($seller->main_activity)
                                    <span class="badge bg-secondary">{{ $seller->main_activity }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($seller->subscriptions->count() > 0)
                                    <span class="badge bg-success">
                                        {{ __('messages.active') }} ({{ $seller->subscriptions_count }})
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.no_subscription') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($seller->is_verified)
                                    <span class="badge bg-success">{{ __('messages.verified') }}</span>
                                @else
                                    <span class="badge bg-warning">{{ __('messages.pending_verification') }}</span>
                                @endif
                            </td>
                            <td>{{ $seller->created_at->format('Y-m-d') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.sellers.show', $seller) }}">
                                                <i class="ri-eye-line me-2"></i>{{ __('messages.view_details') }}
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.sellers.update-verification', $seller) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_verified" value="{{ $seller->is_verified ? 0 : 1 }}">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="ri-{{ $seller->is_verified ? 'close' : 'check' }}-line me-2"></i>
                                                    {{ $seller->is_verified ? __('messages.unverify') : __('messages.verify') }}
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.sellers.destroy', $seller) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="ri-delete-bin-line me-2"></i>{{ __('messages.delete') }}
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                {{ __('messages.no_sellers_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($sellers->hasPages())
        <div class="card-footer">
            {{ $sellers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
