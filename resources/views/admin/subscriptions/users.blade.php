@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">
                @if($role === 'seller')
                    {{ app()->getLocale() == 'ar' ? 'اشتراكات التجار' : 'Seller Subscriptions' }}
                @elseif($role === 'distributor')
                    {{ app()->getLocale() == 'ar' ? 'اشتراكات الموزعين' : 'Distributor Subscriptions' }}
                @else
                    {{ app()->getLocale() == 'ar' ? 'جميع الاشتراكات' : 'All Subscriptions' }}
                @endif
            </h4>
            <p class="text-muted">{{ __('messages.manage_subscriptions') }}</p>
        </div>
        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            {{ __('messages.back') }}
        </a>
    </div>

    <!-- Role Tabs -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $role === 'all' ? 'active' : '' }}" href="{{ route('admin.subscriptions.users') }}">
                <i class="ri-group-line me-1"></i>
                {{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}
                <span class="badge bg-secondary ms-1">{{ $role === 'all' ? $userSubscriptions->total() : '' }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $role === 'seller' ? 'active' : '' }}" href="{{ route('admin.subscriptions.sellers') }}">
                <i class="ri-store-line me-1"></i>
                {{ app()->getLocale() == 'ar' ? 'التجار' : 'Sellers' }}
                @if($role === 'seller')<span class="badge bg-white text-primary ms-1">{{ $userSubscriptions->total() }}</span>@endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $role === 'distributor' ? 'active' : '' }}" href="{{ route('admin.subscriptions.distributors') }}">
                <i class="ri-truck-line me-1"></i>
                {{ app()->getLocale() == 'ar' ? 'الموزعون' : 'Distributors' }}
                @if($role === 'distributor')<span class="badge bg-white text-success ms-1">{{ $userSubscriptions->total() }}</span>@endif
            </a>
        </li>
    </ul>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ request()->url() }}">
                <div class="row g-3">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                               placeholder="{{ app()->getLocale() == 'ar' ? 'بحث بالاسم أو البريد الإلكتروني...' : 'Search by name or email...' }}">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">{{ app()->getLocale() == 'ar' ? 'جميع الحالات' : 'All Statuses' }}</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'ar' ? 'فعال' : 'Active' }}
                            </option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'ar' ? 'منتهي' : 'Expired' }}
                            </option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'ar' ? 'ملغي' : 'Cancelled' }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-search-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'بحث' : 'Search' }}
                        </button>
                        <a href="{{ request()->url() }}" class="btn btn-outline-secondary">
                            {{ app()->getLocale() == 'ar' ? 'إعادة تعيين' : 'Reset' }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Subscriptions Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                {{ app()->getLocale() == 'ar' ? 'قائمة الاشتراكات' : 'Subscriptions List' }}
                <span class="badge bg-label-secondary ms-2">{{ $userSubscriptions->total() }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() == 'ar' ? 'المشترك' : 'Subscriber' }}</th>
                            @if($role === 'all')
                            <th>{{ app()->getLocale() == 'ar' ? 'النوع' : 'Type' }}</th>
                            @endif
                            <th>{{ __('messages.plan_name') }}</th>
                            <th>{{ __('messages.start_date') }}</th>
                            <th>{{ __('messages.end_date') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.amount_paid') }}</th>
                            <th>{{ __('messages.payment_method') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userSubscriptions as $userSubscription)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <strong>{{ $userSubscription->user->name }}</strong>
                                        <div class="text-muted small">{{ $userSubscription->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            @if($role === 'all')
                            <td>
                                @if($userSubscription->user->user_type === 'seller')
                                    <span class="badge bg-label-primary">
                                        <i class="ri-store-line me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'تاجر' : 'Seller' }}
                                    </span>
                                @elseif($userSubscription->user->user_type === 'distributor')
                                    <span class="badge bg-label-success">
                                        <i class="ri-truck-line me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'موزع' : 'Distributor' }}
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary">{{ $userSubscription->user->user_type }}</span>
                                @endif
                            </td>
                            @endif
                            <td>
                                <span class="badge" style="background-color: {{ $userSubscription->subscription->color }}">
                                    {{ $userSubscription->subscription->localized_name }}
                                </span>
                            </td>
                            <td>{{ $userSubscription->start_date?->format('Y-m-d') }}</td>
                            <td>
                                {{ $userSubscription->end_date?->format('Y-m-d') }}
                                @if($userSubscription->status === 'active')
                                <div class="text-muted small">
                                    {{ $userSubscription->days_remaining }} {{ __('messages.days_remaining') }}
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($userSubscription->status === 'active')
                                    <span class="badge bg-success">{{ __('messages.active') }}</span>
                                @elseif($userSubscription->status === 'expired')
                                    <span class="badge bg-secondary">{{ __('messages.expired') }}</span>
                                @elseif($userSubscription->status === 'cancelled')
                                    <span class="badge bg-danger">{{ __('messages.cancelled') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1" style="direction: ltr;">
                                    <x-session-currency-icon width="16" height="16" />
                                    {{ number_format(convert_price($userSubscription->amount_paid), 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-label-info">
                                    {{ ucfirst($userSubscription->payment_method) }}
                                </span>
                            </td>
                            <td>
                                @if($userSubscription->status === 'active')
                                    <form method="POST" action="{{ route('admin.subscriptions.close', $userSubscription->id) }}" style="display: inline;"
                                          onsubmit="return confirm('{{ __('messages.confirm_close_subscription') }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="ri-close-line me-1"></i>
                                            {{ __('messages.close') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $role === 'all' ? 9 : 8 }}" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ri-inbox-line" style="font-size:3rem;"></i>
                                    <p class="mt-2">{{ app()->getLocale() == 'ar' ? 'لا توجد اشتراكات' : 'No subscriptions found' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($userSubscriptions->hasPages())
        <div class="card-footer">
            {{ $userSubscriptions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
