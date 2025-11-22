<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of user's tickets
     */
    public function index()
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->with('replies')
            ->latest()
            ->paginate(15);

        return view('seller.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        return view('seller.tickets.create');
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        return redirect()->route('seller.tickets.show', $ticket)
            ->with('success', __('messages.ticket_created_successfully'));
    }

    /**
     * Display the specified ticket
     */
    public function show(Ticket $ticket)
    {
        // Make sure user can only view their own tickets
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $ticket->load(['replies.user', 'assignedAdmin']);

        return view('seller.tickets.show', compact('ticket'));
    }

    /**
     * Store a reply to the ticket
     */
    public function reply(Request $request, Ticket $ticket)
    {
        // Make sure user can only reply to their own tickets
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_admin' => false,
        ]);

        // If ticket was closed, reopen it
        if ($ticket->status === 'closed') {
            $ticket->update([
                'status' => 'open',
                'closed_at' => null,
            ]);
        }

        return redirect()->route('seller.tickets.show', $ticket)
            ->with('success', __('messages.reply_sent_successfully'));
    }
}
