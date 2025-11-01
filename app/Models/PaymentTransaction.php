<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'paymob_order_id',
        'merchant_order_id',
        'type',
        'amount',
        'currency',
        'status',
        'payment_method',
        'callback_data',
        'is_refunded',
        'refund_amount',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'is_refunded' => 'boolean',
        'paid_at' => 'datetime',
        'callback_data' => 'array',
    ];

    /**
     * Get the user that owns the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for successful transactions
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for order payments
     */
    public function scopeOrders($query)
    {
        return $query->where('type', 'order');
    }

    /**
     * Scope for subscription payments
     */
    public function scopeSubscriptions($query)
    {
        return $query->where('type', 'subscription');
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Mark transaction as success
     */
    public function markAsSuccess($transactionId = null, $paymentMethod = null)
    {
        $this->update([
            'status' => 'success',
            'transaction_id' => $transactionId ?? $this->transaction_id,
            'payment_method' => $paymentMethod ?? $this->payment_method,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
        ]);
    }
}
