@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4">
        <h4 class="mb-1">{{ __('messages.withdrawal_requests') }}</h4>
        <p class="text-muted">{{ __('messages.manage_withdrawal_requests') }}</p>
    </div>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="avatar flex-shrink-0 mb-3">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="ri-time-line ri-24px"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">{{ $stats['pending_count'] }}</h5>
                    <small class="text-muted">{{ __('messages.pending_requests') }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="avatar flex-shrink-0 mb-3">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="ri-money-dollar-circle-line ri-24px"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">AED {{ number_format($stats['pending_amount'], 2) }}</h5>
                    <small class="text-muted">{{ __('messages.pending_amount') }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="avatar flex-shrink-0 mb-3">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="ri-check-line ri-24px"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">{{ $stats['approved_today'] }}</h5>
                    <small class="text-muted">{{ __('messages.approved_today') }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="avatar flex-shrink-0 mb-3">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="ri-check-double-line ri-24px"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">{{ $stats['completed_today'] }}</h5>
                    <small class="text-muted">{{ __('messages.completed_today') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter by Status -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.wallets.withdrawals') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">{{ __('messages.status') }}</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">{{ __('messages.pending') }}</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('messages.approved') }}</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('messages.rejected') }}</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-filter-line me-1"></i>
                            {{ __('messages.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Withdrawal Requests Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.withdrawal_requests') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.request_id') }}</th>
                            <th>{{ __('messages.user') }}</th>
                            <th>{{ __('messages.amount') }}</th>
                            <th>{{ __('messages.bank_details') }}</th>
                            <th>{{ __('messages.date') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td><strong>#{{ $request->id }}</strong></td>
                            <td>
                                <div>
                                    <strong>{{ $request->user->name }}</strong>
                                    <div class="text-muted small">{{ $request->user->email }}</div>
                                </div>
                            </td>
                            <td>
                                <strong class="text-danger">AED {{ number_format($request->amount, 2) }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $request->bank_name }}</strong>
                                    <div class="text-muted small">{{ $request->account_number }}</div>
                                    <div class="text-muted small">{{ $request->account_name }}</div>
                                    @if($request->iban)
                                    <div class="text-muted small">IBAN: {{ $request->iban }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <small>{{ $request->created_at->format('Y-m-d H:i') }}</small>
                                <div class="text-muted small">{{ $request->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($request->status === 'pending')
                                    <span class="badge bg-warning">{{ __('messages.pending') }}</span>
                                @elseif($request->status === 'approved')
                                    <span class="badge bg-success">{{ __('messages.approved') }}</span>
                                @elseif($request->status === 'rejected')
                                    <span class="badge bg-danger">{{ __('messages.rejected') }}</span>
                                @else
                                    <span class="badge bg-info">{{ __('messages.completed') }}</span>
                                @endif

                                @if($request->processed_at)
                                <div class="text-muted small mt-1">
                                    {{ __('messages.by') }}: {{ $request->processor->name ?? 'N/A' }}
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($request->isPending())
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $request->id }}">
                                        <i class="ri-check-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>

                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.wallets.withdrawals.approve', $request) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('messages.approve_withdrawal') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>{{ __('messages.approve_withdrawal_confirm') }} <strong>AED {{ number_format($request->amount, 2) }}</strong> {{ __('messages.to') }} <strong>{{ $request->user->name }}</strong>?</p>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('messages.admin_notes') }}</label>
                                                        <textarea class="form-control" name="admin_notes" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                                    <button type="submit" class="btn btn-success">{{ __('messages.approve') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.wallets.withdrawals.reject', $request) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('messages.reject_withdrawal') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>{{ __('messages.reject_withdrawal_confirm') }} <strong>{{ $request->user->name }}</strong>?</p>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('messages.rejection_reason') }} <span class="text-danger">*</span></label>
                                                        <textarea class="form-control" name="admin_notes" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                                    <button type="submit" class="btn btn-danger">{{ __('messages.reject') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @elseif($request->isApproved())
                                <form method="POST" action="{{ route('admin.wallets.withdrawals.complete', $request) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('{{ __('messages.mark_as_completed_confirm') }}')">
                                        <i class="ri-check-double-line"></i>
                                        {{ __('messages.mark_completed') }}
                                    </button>
                                </form>
                                @else
                                <span class="text-muted">-</span>
                                @endif

                                @if($request->admin_notes)
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="{{ $request->admin_notes }}">
                                    <i class="ri-information-line"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                {{ __('messages.no_withdrawal_requests') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="card-footer">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
