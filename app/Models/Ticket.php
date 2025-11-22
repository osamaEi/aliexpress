<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'status',
        'priority',
        'assigned_to',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    /**
     * Get the user who created the ticket
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin assigned to the ticket
     */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all replies for this ticket
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'primary',
            'in_progress' => 'warning',
            'closed' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get priority badge color
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'info',
            'medium' => 'warning',
            'high' => 'danger',
            default => 'secondary',
        };
    }
}
