@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ __('messages.order_management') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.manage_orders') }}</p>
        </div>
        <form method="POST" action="{{ route('admin.orders.bulk-sync') }}" id="bulk-sync-form">
            @csrf
            <button type="submit" class="btn btn-primary" id="bulk-sync-btn" disabled>
                <i class="ri-refresh-line me-1"></i>
                {{ __('messages.sync_selected') }}
            </button>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.total_orders') }}: {{ $orders->total() }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th>{{ __('messages.order_number') }}</th>
                            <th>{{ __('messages.customer') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.total') }}</th>
                            <th>{{ __('messages.sync_status') }}</th>
                            <th>{{ __('messages.aliexpress_order_id') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>
                                @if($order->status === 'pending')
                                <input type="checkbox" class="form-check-input order-checkbox" name="order_ids[]" value="{{ $order->id }}" form="bulk-sync-form">
                                @endif
                            </td>
                            <td>
                                <strong>{{ $order->order_number }}</strong>
                                <div class="text-muted small">{{ $order->created_at->format('Y-m-d H:i') }}</div>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $order->user->name }}</strong>
                                    <div class="text-muted small">{{ $order->user->email }}</div>
                                </div>
                            </td>
                            <td>
                                @if($order->status === 'pending')
                                    <span class="badge bg-warning">{{ __('messages.pending') }}</span>
                                @elseif($order->status === 'processing')
                                    <span class="badge bg-info">{{ __('messages.processing') }}</span>
                                @elseif($order->status === 'shipped')
                                    <span class="badge bg-primary">{{ __('messages.shipped') }}</span>
                                @elseif($order->status === 'delivered')
                                    <span class="badge bg-success">{{ __('messages.delivered') }}</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge bg-danger">{{ __('messages.cancelled') }}</span>
                                @endif
                            </td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                @if($order->aliexpress_order_id)
                                    <span class="badge bg-success">
                                        <i class="ri-check-line me-1"></i>
                                        {{ __('messages.synced') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="ri-close-line me-1"></i>
                                        {{ __('messages.not_synced') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($order->aliexpress_order_id)
                                    <code>{{ $order->aliexpress_order_id }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(!$order->aliexpress_order_id && $order->status === 'pending')
                                <form method="POST" action="{{ route('admin.orders.sync', $order) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('messages.sync_with_aliexpress') }}">
                                        <i class="ri-refresh-line"></i>
                                        {{ __('messages.sync') }}
                                    </button>
                                </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                {{ __('messages.no_orders_yet') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Select All Checkbox
    document.getElementById('select-all')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkSyncButton();
    });

    // Individual Checkboxes
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkSyncButton();
        });
    });

    // Update Bulk Sync Button State
    function updateBulkSyncButton() {
        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        const bulkSyncBtn = document.getElementById('bulk-sync-btn');

        if (checkedBoxes.length > 0) {
            bulkSyncBtn.disabled = false;
            bulkSyncBtn.innerHTML = `<i class="ri-refresh-line me-1"></i> {{ __('messages.sync_selected') }} (${checkedBoxes.length})`;
        } else {
            bulkSyncBtn.disabled = true;
            bulkSyncBtn.innerHTML = '<i class="ri-refresh-line me-1"></i> {{ __('messages.sync_selected') }}';
        }
    }

    // Bulk Sync Form Submission
    document.getElementById('bulk-sync-form')?.addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('{{ __('messages.select_orders_to_sync') }}');
            return false;
        }

        return confirm('{{ __('messages.sync_selected') }} ' + checkedBoxes.length + ' orders?');
    });
</script>
@endpush
@endsection
