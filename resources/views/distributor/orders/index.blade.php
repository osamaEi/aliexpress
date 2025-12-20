@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.my_orders') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.order_number') }}</th>
                            <th>{{ __('messages.product') }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'العميل' : 'Customer' }}</th>
                            <th>{{ __('messages.quantity') }}</th>
                            <th>{{ __('messages.total') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'التاريخ' : 'Date' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->product->name ?? 'N/A' }}</td>
                            <td>{{ $order->user->name ?? 'N/A' }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td>{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{
                                    $order->status === 'pending' ? 'warning' :
                                    ($order->status === 'processing' ? 'info' :
                                    ($order->status === 'shipped' ? 'primary' :
                                    ($order->status === 'delivered' ? 'success' :
                                    ($order->status === 'cancelled' ? 'danger' : 'secondary'))))
                                }}">
                                    {{ __('messages.' . $order->status) }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                {{ __('messages.no_orders_yet') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
