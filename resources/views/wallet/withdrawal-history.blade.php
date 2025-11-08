@extends('dashboard')

@section('title', __('messages.withdrawal_history'))

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>
                    <i class="ri-history-line me-2"></i>
                    {{ __('messages.withdrawal_history') }}
                </h3>
                <a href="{{ route('wallet.withdrawal.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i>
                    {{ __('messages.new_withdrawal_request') }}
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    @if($withdrawals->isEmpty())
                        <div class="text-center py-5">
                            <i class="ri-inbox-line" style="font-size: 4rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">{{ __('messages.no_withdrawal_requests') }}</p>
                            <a href="{{ route('wallet.withdrawal.create') }}" class="btn btn-primary">
                                {{ __('messages.create_first_withdrawal') }}
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.request_id') }}</th>
                                        <th>{{ __('messages.amount') }}</th>
                                        <th>{{ __('messages.paypal_email') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.date') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawals as $withdrawal)
                                        <tr>
                                            <td class="fw-bold">#{{ $withdrawal->id }}</td>
                                            <td>
                                                <span class="fw-bold">
                                                    {{ number_format($withdrawal->amount, 2) }} {{ $withdrawal->currency }}
                                                </span>
                                            </td>
                                            <td>
                                                <i class="ri-paypal-line text-primary"></i>
                                                {{ $withdrawal->paypal_email }}
                                            </td>
                                            <td>
                                                @if($withdrawal->status === 'pending')
                                                    <span class="badge bg-warning">
                                                        <i class="ri-time-line me-1"></i>
                                                        {{ __('messages.pending') }}
                                                    </span>
                                                @elseif($withdrawal->status === 'approved')
                                                    <span class="badge bg-info">
                                                        <i class="ri-check-line me-1"></i>
                                                        {{ __('messages.approved') }}
                                                    </span>
                                                @elseif($withdrawal->status === 'completed')
                                                    <span class="badge bg-success">
                                                        <i class="ri-check-double-line me-1"></i>
                                                        {{ __('messages.completed') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="ri-close-line me-1"></i>
                                                        {{ __('messages.rejected') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $withdrawal->created_at->format('Y-m-d H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#detailModal{{ $withdrawal->id }}">
                                                    <i class="ri-eye-line"></i>
                                                    {{ __('messages.details') }}
                                                </button>

                                                @if($withdrawal->isPending())
                                                    <form action="{{ route('wallet.withdrawal.cancel', $withdrawal) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('{{ __('messages.confirm_cancel_withdrawal') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="ri-close-line"></i>
                                                            {{ __('messages.cancel') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Detail Modal -->
                                        <div class="modal fade" id="detailModal{{ $withdrawal->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            {{ __('messages.withdrawal_details') }} #{{ $withdrawal->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <strong>{{ __('messages.amount') }}:</strong>
                                                            <p>{{ number_format($withdrawal->amount, 2) }} {{ $withdrawal->currency }}</p>
                                                        </div>

                                                        <div class="mb-3">
                                                            <strong>{{ __('messages.paypal_email') }}:</strong>
                                                            <p>{{ $withdrawal->paypal_email }}</p>
                                                        </div>

                                                        <div class="mb-3">
                                                            <strong>{{ __('messages.status') }}:</strong>
                                                            <p>
                                                                @if($withdrawal->status === 'pending')
                                                                    <span class="badge bg-warning">{{ __('messages.pending') }}</span>
                                                                @elseif($withdrawal->status === 'approved')
                                                                    <span class="badge bg-info">{{ __('messages.approved') }}</span>
                                                                @elseif($withdrawal->status === 'completed')
                                                                    <span class="badge bg-success">{{ __('messages.completed') }}</span>
                                                                @else
                                                                    <span class="badge bg-danger">{{ __('messages.rejected') }}</span>
                                                                @endif
                                                            </p>
                                                        </div>

                                                        @if($withdrawal->seller_note)
                                                            <div class="mb-3">
                                                                <strong>{{ __('messages.your_note') }}:</strong>
                                                                <p class="text-muted">{{ $withdrawal->seller_note }}</p>
                                                            </div>
                                                        @endif

                                                        @if($withdrawal->admin_note)
                                                            <div class="mb-3">
                                                                <strong>{{ __('messages.admin_note') }}:</strong>
                                                                <p class="text-info">{{ $withdrawal->admin_note }}</p>
                                                            </div>
                                                        @endif

                                                        <div class="mb-3">
                                                            <strong>{{ __('messages.request_date') }}:</strong>
                                                            <p>{{ $withdrawal->created_at->format('Y-m-d H:i') }}</p>
                                                        </div>

                                                        @if($withdrawal->approved_at)
                                                            <div class="mb-3">
                                                                <strong>{{ __('messages.approved_date') }}:</strong>
                                                                <p>{{ $withdrawal->approved_at->format('Y-m-d H:i') }}</p>
                                                            </div>
                                                        @endif

                                                        @if($withdrawal->rejected_at)
                                                            <div class="mb-3">
                                                                <strong>{{ __('messages.rejected_date') }}:</strong>
                                                                <p>{{ $withdrawal->rejected_at->format('Y-m-d H:i') }}</p>
                                                            </div>
                                                        @endif

                                                        @if($withdrawal->completed_at)
                                                            <div class="mb-3">
                                                                <strong>{{ __('messages.completed_date') }}:</strong>
                                                                <p>{{ $withdrawal->completed_at->format('Y-m-d H:i') }}</p>
                                                            </div>
                                                        @endif

                                                        @if($withdrawal->approver)
                                                            <div class="mb-3">
                                                                <strong>{{ __('messages.processed_by') }}:</strong>
                                                                <p>{{ $withdrawal->approver->name }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            {{ __('messages.close') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $withdrawals->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
