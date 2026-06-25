<?php

namespace App\Http\Controllers\Marketer;

use App\Http\Controllers\Controller;
use App\Mail\NewTicketNotification;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->with('replies')
            ->latest()
            ->paginate(15);

        return view('marketer.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('marketer.tickets.create');
    }

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
            'recipient_type' => 'admin',
        ]);

        $ticket->load('user');

        $adminEmail = env('MAIL_USERNAME');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new NewTicketNotification($ticket));
        }

        return redirect()->route('marketer.tickets.show', $ticket)
            ->with('success', __('messages.ticket_created_successfully'));
    }

    public function show(Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $ticket->load(['replies.user', 'assignedAdmin', 'coupon']);

        return view('marketer.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $attachmentPaths[] = $file->storeAs('ticket-attachments', $filename, 'public');
            }
        }

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_admin' => false,
            'attachments' => !empty($attachmentPaths) ? $attachmentPaths : null,
        ]);

        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open', 'closed_at' => null]);
        }

        return redirect()->route('marketer.tickets.show', $ticket)
            ->with('success', __('messages.reply_sent_successfully'));
    }
}
