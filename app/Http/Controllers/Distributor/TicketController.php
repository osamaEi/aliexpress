<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Mail\NewTicketNotification;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    /**
     * Display a listing of user's tickets
     */
    public function index()
    {
        // Tickets the distributor opened, plus coupon-activation tickets routed to them.
        $tickets = Ticket::where(function ($q) {
                $q->where('user_id', Auth::id())
                  ->orWhere(function ($q2) {
                      $q2->where('recipient_type', 'distributor')
                         ->where('recipient_id', Auth::id());
                  });
            })
            ->with(['replies', 'coupon', 'user'])
            ->latest()
            ->paginate(15);

        return view('distributor.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        return view('distributor.tickets.create');
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

        // Load the user relationship for the email
        $ticket->load('user');

        // Send email notification to admin
        $adminEmail = env('MAIL_USERNAME');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new NewTicketNotification($ticket));
        }

        return redirect()->route('distributor.tickets.show', $ticket)
            ->with('success', __('messages.ticket_created_successfully'));
    }

    /**
     * Display the specified ticket
     */
    public function show(Ticket $ticket)
    {
        // Allow the ticket owner OR the distributor it was routed to (coupon activation)
        $isOwner = $ticket->user_id === Auth::id();
        $isRecipient = $ticket->recipient_type === 'distributor' && $ticket->recipient_id === Auth::id();
        if (!$isOwner && !$isRecipient) {
            abort(403);
        }

        $ticket->load(['replies.user', 'assignedAdmin', 'coupon', 'user']);

        return view('distributor.tickets.show', compact('ticket'));
    }

    /**
     * Approve (or reject) a coupon-activation request tied to this ticket.
     * Activates the marketer's coupon_marketer row and generates a tracking code.
     */
    public function couponDecision(Request $request, Ticket $ticket)
    {
        // Only the distributor the ticket was routed to may decide
        abort_unless(
            $ticket->recipient_type === 'distributor' && $ticket->recipient_id === Auth::id(),
            403
        );
        abort_if(empty($ticket->coupon_id), 404);

        $decision = $request->validate(['decision' => 'required|in:approve,reject'])['decision'];

        \App\Http\Controllers\MarketerController::applyCouponDecision($ticket, $decision);

        $ar = app()->getLocale() === 'ar';
        return redirect()->route('distributor.tickets.show', $ticket)->with('success', $decision === 'approve'
            ? ($ar ? 'تم تفعيل الكوبون للمسوّق.' : 'Coupon activated for the marketer.')
            : ($ar ? 'تم رفض طلب التفعيل.' : 'Activation request rejected.'));
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
            'attachments.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB per image
        ]);

        // Handle image uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('ticket-attachments', $filename, 'public');
                $attachmentPaths[] = $path;
            }
        }

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_admin' => false,
            'attachments' => !empty($attachmentPaths) ? $attachmentPaths : null,
        ]);

        // If ticket was closed, reopen it
        if ($ticket->status === 'closed') {
            $ticket->update([
                'status' => 'open',
                'closed_at' => null,
            ]);
        }

        return redirect()->route('distributor.tickets.show', $ticket)
            ->with('success', __('messages.reply_sent_successfully'));
    }
}
