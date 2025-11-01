<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    /**
     * Get or create wallet for user
     */
    public function getOrCreateWallet(User $user): Wallet
    {
        return $user->getOrCreateWallet();
    }

    /**
     * Deposit money into wallet
     */
    public function deposit(User $user, float $amount, string $description = null, array $metadata = []): WalletTransaction
    {
        $wallet = $this->getOrCreateWallet($user);

        return DB::transaction(function () use ($wallet, $amount, $description, $metadata) {
            return $wallet->credit($amount, 'deposit', $description ?? 'Wallet deposit', $metadata);
        });
    }

    /**
     * Withdraw money from wallet
     */
    public function withdraw(User $user, float $amount, string $description = null, array $metadata = []): WalletTransaction
    {
        $wallet = $this->getOrCreateWallet($user);

        return DB::transaction(function () use ($wallet, $amount, $description, $metadata) {
            return $wallet->debit($amount, 'withdrawal', $description ?? 'Wallet withdrawal', $metadata);
        });
    }

    /**
     * Transfer money between wallets
     */
    public function transfer(User $from, User $to, float $amount, string $description = null): array
    {
        return DB::transaction(function () use ($from, $to, $amount, $description) {
            $fromWallet = $this->getOrCreateWallet($from);
            $toWallet = $this->getOrCreateWallet($to);

            $debitTransaction = $fromWallet->debit(
                $amount,
                'transfer_out',
                $description ?? "Transfer to {$to->name}",
                ['recipient_id' => $to->id]
            );

            $creditTransaction = $toWallet->credit(
                $amount,
                'transfer_in',
                $description ?? "Transfer from {$from->name}",
                ['sender_id' => $from->id]
            );

            return [
                'debit' => $debitTransaction,
                'credit' => $creditTransaction
            ];
        });
    }

    /**
     * Pay for order using wallet
     */
    public function payForOrder(User $user, $order, float $amount): WalletTransaction
    {
        $wallet = $this->getOrCreateWallet($user);

        return DB::transaction(function () use ($wallet, $order, $amount, $user) {
            $transaction = $wallet->debit(
                $amount,
                'order_payment',
                "Payment for order #{$order->id}",
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number ?? null,
                ]
            );

            $transaction->update([
                'reference_type' => get_class($order),
                'reference_id' => $order->id,
                'payment_method' => 'wallet',
            ]);

            return $transaction;
        });
    }

    /**
     * Pay for subscription using wallet
     */
    public function payForSubscription(User $user, $subscription, float $amount): WalletTransaction
    {
        $wallet = $this->getOrCreateWallet($user);

        return DB::transaction(function () use ($wallet, $subscription, $amount, $user) {
            $transaction = $wallet->debit(
                $amount,
                'subscription_payment',
                "Payment for {$subscription->localized_name} subscription",
                [
                    'subscription_id' => $subscription->id,
                    'subscription_name' => $subscription->name,
                ]
            );

            $transaction->update([
                'reference_type' => get_class($subscription),
                'reference_id' => $subscription->id,
                'payment_method' => 'wallet',
            ]);

            return $transaction;
        });
    }

    /**
     * Refund to wallet
     */
    public function refund(User $user, float $amount, string $description, array $metadata = []): WalletTransaction
    {
        $wallet = $this->getOrCreateWallet($user);

        return DB::transaction(function () use ($wallet, $amount, $description, $metadata) {
            return $wallet->credit($amount, 'refund', $description, $metadata);
        });
    }

    /**
     * Add commission to seller wallet
     */
    public function addCommission(User $seller, float $amount, $order): WalletTransaction
    {
        $wallet = $this->getOrCreateWallet($seller);

        return DB::transaction(function () use ($wallet, $amount, $order) {
            $transaction = $wallet->credit(
                $amount,
                'commission',
                "Commission for order #{$order->id}",
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number ?? null,
                ]
            );

            $transaction->update([
                'reference_type' => get_class($order),
                'reference_id' => $order->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Get wallet balance
     */
    public function getBalance(User $user): float
    {
        $wallet = $this->getOrCreateWallet($user);
        return (float) $wallet->balance;
    }

    /**
     * Get available balance
     */
    public function getAvailableBalance(User $user): float
    {
        $wallet = $this->getOrCreateWallet($user);
        return $wallet->available_balance;
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance(User $user, float $amount): bool
    {
        $wallet = $this->getOrCreateWallet($user);
        return $wallet->hasSufficientBalance($amount);
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(User $user, int $perPage = 20)
    {
        $wallet = $this->getOrCreateWallet($user);
        return $wallet->transactions()->with('reference')->paginate($perPage);
    }

    /**
     * Hold amount in wallet (for pending transactions)
     */
    public function hold(User $user, float $amount): void
    {
        $wallet = $this->getOrCreateWallet($user);
        $wallet->hold($amount);
    }

    /**
     * Release hold amount
     */
    public function releaseHold(User $user, float $amount): void
    {
        $wallet = $this->getOrCreateWallet($user);
        $wallet->releaseHold($amount);
    }
}
