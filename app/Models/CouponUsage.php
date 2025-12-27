<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'order_amount',
        'discount_amount',
        'commission_amount',
        'commission_status',
    ];

    protected $casts = [
        'order_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    /**
     * Get the coupon.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the user who used the coupon.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope for pending commissions.
     */
    public function scopePending($query)
    {
        return $query->where('commission_status', 'pending');
    }

    /**
     * Scope for approved commissions.
     */
    public function scopeApproved($query)
    {
        return $query->where('commission_status', 'approved');
    }

    /**
     * Scope for paid commissions.
     */
    public function scopePaid($query)
    {
        return $query->where('commission_status', 'paid');
    }
}
