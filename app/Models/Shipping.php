<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipping extends Model
{
    protected $fillable = [
        'order_id',
        'tracking_number',
        'carrier_name',
        'carrier_code',
        'shipping_method',
        'status',
        'origin_country',
        'destination_country',
        'shipped_at',
        'estimated_delivery_at',
        'delivered_at',
        'tracking_events',
        'raw_response',
        'last_synced_at',
    ];

    protected $casts = [
        'tracking_events' => 'array',
        'raw_response' => 'array',
        'shipped_at' => 'datetime',
        'estimated_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the order that owns the shipping
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the latest tracking event
     */
    public function getLatestEvent(): ?array
    {
        if (empty($this->tracking_events)) {
            return null;
        }

        return end($this->tracking_events);
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'in_transit', 'shipped' => 'info',
            'out_for_delivery' => 'primary',
            'delivered' => 'success',
            'exception', 'failed' => 'danger',
            'returned' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get status display name
     */
    public function getStatusName(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'in_transit' => 'In Transit',
            'shipped' => 'Shipped',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'exception' => 'Exception',
            'failed' => 'Failed',
            'returned' => 'Returned',
            default => ucfirst($this->status),
        };
    }

    /**
     * Check if tracking is still active (not delivered or failed)
     */
    public function isActive(): bool
    {
        return !in_array($this->status, ['delivered', 'failed', 'returned']);
    }

    /**
     * Add a new tracking event
     */
    public function addTrackingEvent(array $event): void
    {
        $events = $this->tracking_events ?? [];
        $events[] = array_merge($event, ['recorded_at' => now()->toDateTimeString()]);
        $this->tracking_events = $events;
    }
}
