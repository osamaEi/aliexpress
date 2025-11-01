<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'pending_balance',
        'currency',
        'is_active',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime',
    ];

    /**
     * Get the user that owns the wallet
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for the wallet
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    /**
     * Get available balance (total - pending)
     */
    public function getAvailableBalanceAttribute(): float
    {
        return (float) ($this->balance - $this->pending_balance);
    }

    /**
     * Check if wallet has sufficient balance
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }

    /**
     * Credit the wallet
     */
    public function credit(float $amount, string $transactionType, ?string $description = null, ?array $metadata = []): WalletTransaction
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->last_transaction_at = now();
        $this->save();

        return $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'credit',
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'currency' => $this->currency,
            'status' => 'completed',
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Debit the wallet
     */
    public function debit(float $amount, string $transactionType, ?string $description = null, ?array $metadata = []): WalletTransaction
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient wallet balance');
        }

        $balanceBefore = $this->balance;
        $this->balance -= $amount;
        $this->last_transaction_at = now();
        $this->save();

        return $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'debit',
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'currency' => $this->currency,
            'status' => 'completed',
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Hold amount (add to pending)
     */
    public function hold(float $amount): void
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient wallet balance to hold');
        }

        $this->pending_balance += $amount;
        $this->save();
    }

    /**
     * Release hold amount
     */
    public function releaseHold(float $amount): void
    {
        $this->pending_balance -= $amount;
        if ($this->pending_balance < 0) {
            $this->pending_balance = 0;
        }
        $this->save();
    }
}
