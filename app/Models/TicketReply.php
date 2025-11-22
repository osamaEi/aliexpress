<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketReply extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_admin',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    /**
     * Get the ticket this reply belongs to
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user who made the reply
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
