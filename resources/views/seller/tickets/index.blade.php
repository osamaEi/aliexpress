@extends('dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('messages.my_tickets') }}</h5>
                    <a href="{{ route('seller.tickets.create') }}" class="btn btn-primary">
                        <i class="ri-add-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                        {{ __('messages.create_ticket') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($tickets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.ticket_id') }}</th>
                                        <th>{{ __('messages.subject') }}</th>
                                        <th>{{ __('messages.priority') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.replies') }}</th>
                                        <th>{{ __('messages.created_at') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tickets as $ticket)
                                        <tr>
                                            <td>#{{ $ticket->id }}</td>
                                            <td>{{ $ticket->subject }}</td>
                                            <td>
                                                <span class="badge bg-{{ $ticket->priority_color }}">
                                                    {{ __('messages.priority_' . $ticket->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $ticket->status_color }}">
                                                    {{ __('messages.status_' . $ticket->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $ticket->replies->count() }}
                                                </span>
                                            </td>
                                            <td>{{ $ticket->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="{{ route('seller.tickets.show', $ticket) }}"
                                                   class="btn btn-sm btn-info">
                                                    <i class="ri-eye-line"></i>
                                                    {{ __('messages.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $tickets->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri-ticket-line" style="font-size: 4rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">{{ __('messages.no_tickets_found') }}</p>
                            <a href="{{ route('seller.tickets.create') }}" class="btn btn-primary mt-2">
                                {{ __('messages.create_first_ticket') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
