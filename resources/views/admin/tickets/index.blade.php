@extends('dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.support_tickets') }}</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.tickets.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('messages.all') }}</option>
                                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>
                                        {{ __('messages.status_open') }}
                                    </option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                        {{ __('messages.status_in_progress') }}
                                    </option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>
                                        {{ __('messages.status_closed') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.priority') }}</label>
                                <select name="priority" class="form-select">
                                    <option value="">{{ __('messages.all') }}</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>
                                        {{ __('messages.priority_low') }}
                                    </option>
                                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>
                                        {{ __('messages.priority_medium') }}
                                    </option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>
                                        {{ __('messages.priority_high') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('messages.search') }}</label>
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="{{ __('messages.search_tickets') }}"
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-search-line"></i>
                                    {{ __('messages.filter') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($tickets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.ticket_id') }}</th>
                                        <th>{{ __('messages.user') }}</th>
                                        <th>{{ __('messages.subject') }}</th>
                                        <th>{{ __('messages.priority') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.assigned_to') }}</th>
                                        <th>{{ __('messages.replies') }}</th>
                                        <th>{{ __('messages.created_at') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tickets as $ticket)
                                        <tr>
                                            <td>#{{ $ticket->id }}</td>
                                            <td>{{ $ticket->user->name }}</td>
                                            <td>{{ Str::limit($ticket->subject, 40) }}</td>
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
                                                @if($ticket->assignedAdmin)
                                                    {{ $ticket->assignedAdmin->name }}
                                                @else
                                                    <span class="text-muted">{{ __('messages.unassigned') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $ticket->replies->count() }}
                                                </span>
                                            </td>
                                            <td>{{ $ticket->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="{{ route('admin.tickets.show', $ticket) }}"
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
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
