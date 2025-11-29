@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">{{ __('messages.seller_details') }}</h4>
                <p class="text-muted">{{ $user->name }} - {{ $user->email }}</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i>
                {{ __('messages.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-4">
            <!-- User Profile Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-4">
                        <div class="flex-grow-1 {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}">
                            <h4 class="mb-2">{{ $user->name }}</h4>
                            <p class="text-muted mb-2">
                                <i class="ri-mail-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                {{ $user->email }}
                            </p>
                            @if($user->is_blocked)
                                <span class="badge bg-danger mb-2">
                                    <i class="ri-lock-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                    {{ __('messages.blocked') }}
                                </span>
                            @else
                                <span class="badge bg-success mb-2">
                                    <i class="ri-checkbox-circle-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                    {{ __('messages.active') }}
                                </span>
                            @endif
                        </div>
                        <div class="position-relative">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}"
                                     alt="{{ $user->name }}"
                                     class="rounded-circle border border-3 border-primary"
                                     style="width: 120px; height: 120px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            @else
                                <div class="rounded-circle border border-3 border-primary d-flex align-items-center justify-content-center"
                                     style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                    <span class="text-white" style="font-size: 2.5rem; font-weight: 600;">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                            @endif
                            @if($user->email_verified_at)
                                <span class="position-absolute bottom-0 {{ app()->getLocale() == 'ar' ? 'start-0' : 'end-0' }} bg-success rounded-circle border border-3 border-white"
                                      style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                      title="{{ __('messages.verified') }}">
                                    <i class="ri-check-line text-white"></i>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center mb-3">
                        <form action="{{ route('admin.users.toggle-block', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @if($user->is_blocked)
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="ri-lock-unlock-line me-1"></i>
                                    {{ __('messages.unblock_user') }}
                                </button>
                            @else
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#blockUserModal">
                                    <i class="ri-lock-line me-1"></i>
                                    {{ __('messages.block_user') }}
                                </button>
                            @endif
                        </form>

                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                            <i class="ri-notification-line me-1"></i>
                            {{ __('messages.send_notification') }}
                        </button>
                    </div>

                    <hr>

                    <div>
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted text-uppercase">{{ __('messages.business_information') }}</small>
                            <div class="mt-2">
                                @if($user->company_name)
                                    <p class="mb-2">
                                        <i class="ri-building-line text-primary {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                                        <strong>{{ $user->company_name }}</strong>
                                    </p>
                                @endif
                                @if($user->country)
                                    <p class="mb-2">
                                        <i class="ri-map-pin-line text-danger {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                                        {{ $user->country }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if(!empty($mainCategories) || !empty($subCategories))
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted text-uppercase">{{ __('messages.categories') }}</small>
                            <div class="mt-2">
                                @if(!empty($mainCategories))
                                    <div class="mb-2">
                                        <p class="mb-1">
                                            <i class="ri-folder-line text-warning {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                            <small class="text-muted">{{ __('messages.main_categories') }}:</small>
                                        </p>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($mainCategories as $category)
                                                <span class="badge bg-primary">{{ $category }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($subCategories))
                                    <div>
                                        <p class="mb-1">
                                            <i class="ri-folder-2-line text-info {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                            <small class="text-muted">{{ __('messages.sub_categories') }}:</small>
                                        </p>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($subCategories as $category)
                                                <span class="badge bg-secondary">{{ $category }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted text-uppercase">{{ __('messages.account_details') }}</small>
                            <div class="mt-2">
                                <p class="mb-2">
                                    <i class="ri-user-line text-primary {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                                    {{ $user->full_name ?? $user->name }}
                                </p>
                                <p class="mb-2">
                                    <i class="ri-shield-user-line text-success {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                                    <span class="badge bg-primary">{{ ucfirst($user->user_type) }}</span>
                                </p>
                                <p class="mb-0">
                                    <i class="ri-calendar-line text-secondary {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                                    {{ __('messages.joined') }}: {{ $user->created_at->format('Y-m-d') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallet Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-wallet-line me-2"></i>
                        {{ __('messages.wallet') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($wallet)
                        <div class="mb-3">
                            <small class="text-muted">{{ __('messages.available_balance') }}</small>
                            <h3 class="text-success mb-0">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</h3>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">{{ __('messages.pending_balance') }}</small>
                            <h5 class="text-warning mb-0">{{ number_format($wallet->pending_balance, 2) }} {{ $wallet->currency }}</h5>
                        </div>
                        <div>
                            <small class="text-muted">{{ __('messages.status') }}</small>
                            <div>
                                @if($wallet->is_active)
                                    <span class="badge bg-success">{{ __('messages.active') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('messages.inactive') }}</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">{{ __('messages.no_wallet') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Current Subscription Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-vip-crown-line me-2"></i>
                        {{ __('messages.current_subscription') }}
                    </h5>
                    @if($activeSubscription)
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manageSubscriptionModal">
                            <i class="ri-settings-line me-1"></i>
                            {{ __('messages.manage') }}
                        </button>
                    @else
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#assignSubscriptionModal">
                            <i class="ri-add-line me-1"></i>
                            {{ __('messages.assign_subscription') }}
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($activeSubscription)
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>{{ __('messages.plan_name') }}:</strong>
                                    <span class="badge" style="background-color: {{ $activeSubscription->subscription->color }}">
                                        {{ $activeSubscription->subscription->localized_name }}
                                    </span>
                                </p>
                                <p class="mb-2"><strong>{{ __('messages.start_date') }}:</strong> {{ $activeSubscription->start_date }}</p>
                                <p class="mb-2"><strong>{{ __('messages.end_date') }}:</strong> {{ $activeSubscription->end_date }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>{{ __('messages.status') }}:</strong>
                                    <span class="badge bg-{{ $activeSubscription->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($activeSubscription->status) }}
                                    </span>
                                </p>
                                <p class="mb-2"><strong>{{ __('messages.amount_paid') }}:</strong> ${{ number_format($activeSubscription->amount_paid, 2) }}</p>
                                <p class="mb-0"><strong>{{ __('messages.payment_method') }}:</strong> {{ $activeSubscription->payment_method ?? '-' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">{{ __('messages.no_active_subscription') }}</p>
                    @endif
                </div>
            </div>

            <!-- Assigned Products Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-product-hunt-line me-2"></i>
                        {{ __('messages.assigned_products') }} ({{ $products->total() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.product_name') }}</th>
                                        <th>{{ __('messages.price') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.assigned_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                    <tr>
                                        <td>{{ $product->localized_name }}</td>
                                        <td>${{ number_format($product->price, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $product->pivot->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($product->pivot->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $product->pivot->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($products->hasPages())
                            <div class="mt-3">
                                {{ $products->links() }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">{{ __('messages.no_products_assigned') }}</p>
                    @endif
                </div>
            </div>

            <!-- Orders Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-shopping-cart-line me-2"></i>
                        {{ __('messages.recent_orders') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.order_id') }}</th>
                                        <th>{{ __('messages.product') }}</th>
                                        <th>{{ __('messages.total') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ $order->product->localized_name ?? '-' }}</td>
                                        <td>${{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">{{ __('messages.no_orders_found') }}</p>
                    @endif
                </div>
            </div>

            <!-- Withdrawal Requests Card -->
            @if($withdrawalRequests->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-money-dollar-circle-line me-2"></i>
                        {{ __('messages.withdrawal_requests') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.amount') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.requested_date') }}</th>
                                    <th>{{ __('messages.processed_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($withdrawalRequests as $request)
                                <tr>
                                    <td>{{ number_format($request->amount, 2) }} {{ $request->currency }}</td>
                                    <td>
                                        <span class="badge bg-{{ $request->status === 'completed' ? 'success' : ($request->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $request->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $request->processed_at ? $request->processed_at->format('Y-m-d') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Block User Modal -->
<div class="modal fade" id="blockUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.toggle-block', $user) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.block_user') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="block_reason" class="form-label">{{ __('messages.reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="block_reason" name="block_reason" rows="3" required></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="ri-alert-line me-2"></i>
                        {{ __('messages.block_user_warning') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('messages.block_user') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.send-notification', $user) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.send_notification') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="notification_title" class="form-label">{{ __('messages.title') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="notification_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="notification_message" class="form-label">{{ __('messages.message') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notification_message" name="message" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('messages.send') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Subscription Modal -->
@if($activeSubscription)
<div class="modal fade" id="manageSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.extend-subscription', $user) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.manage_subscription') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="extend_days" class="form-label">{{ __('messages.extend_by_days') }}</label>
                        <input type="number" class="form-control" id="extend_days" name="extend_days" min="1" placeholder="30">
                        <small class="text-muted">{{ __('messages.current_end_date') }}: {{ $activeSubscription->end_date }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('messages.extend_subscription') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Assign Subscription Modal -->
<div class="modal fade" id="assignSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.assign-subscription', $user) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.assign_subscription') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subscription_id" class="form-label">{{ __('messages.select_plan') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="subscription_id" name="subscription_id" required>
                            <option value="">{{ __('messages.choose') }}...</option>
                            @foreach($subscriptions as $subscription)
                                <option value="{{ $subscription->id }}">
                                    {{ $subscription->localized_name }} - {{ number_format($subscription->price * 3.67, 2) }} AED
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="duration_days" class="form-label">{{ __('messages.duration_days') }}</label>
                        <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" placeholder="30">
                        <small class="text-muted">{{ __('messages.leave_empty_default') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('messages.assign') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
