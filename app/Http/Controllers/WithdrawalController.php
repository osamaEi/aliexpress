<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /**
     * Display withdrawal form
     */
    public function create()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return redirect()->route('wallet.index')
                ->with('error', __('messages.wallet_not_found'));
        }

        return view('wallet.withdrawal', compact('wallet'));
    }

    /**
     * Store a new withdrawal request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return redirect()->route('wallet.index')
                ->with('error', __('messages.wallet_not_found'));
        }

        $validated = $request->validate([
            'paypal_email' => 'required|email|max:255',
            'amount' => 'required|numeric|min:10|max:' . $wallet->balance,
            'seller_note' => 'nullable|string|max:1000',
        ]);

        // Check if there's a pending withdrawal
        $pendingWithdrawal = WithdrawalRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingWithdrawal) {
            return redirect()->back()
                ->with('error', __('messages.pending_withdrawal_exists'));
        }

        DB::transaction(function () use ($user, $validated, $wallet) {
            // Create withdrawal request
            WithdrawalRequest::create([
                'user_id' => $user->id,
                'paypal_email' => $validated['paypal_email'],
                'amount' => $validated['amount'],
                'currency' => $wallet->currency ?? 'USD',
                'status' => 'pending',
                'seller_note' => $validated['seller_note'] ?? null,
            ]);

            // Deduct from wallet balance (hold the amount)
            $wallet->decrement('balance', $validated['amount']);
        });

        return redirect()->route('wallet.withdrawal.history')
            ->with('success', __('messages.withdrawal_request_submitted'));
    }

    /**
     * Display withdrawal history
     */
    public function history()
    {
        $withdrawals = WithdrawalRequest::where('user_id', Auth::id())
            ->with('approver')
            ->latest()
            ->paginate(20);

        return view('wallet.withdrawal-history', compact('withdrawals'));
    }

    /**
     * Cancel a pending withdrawal request
     */
    public function cancel(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$withdrawal->isPending()) {
            return redirect()->back()
                ->with('error', __('messages.cannot_cancel_withdrawal'));
        }

        DB::transaction(function () use ($withdrawal) {
            // Return amount to wallet
            $wallet = $withdrawal->user->wallet;
            $wallet->increment('balance', $withdrawal->amount);

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'rejected',
                'admin_note' => 'Cancelled by user',
                'rejected_at' => now(),
            ]);
        });

        return redirect()->back()
            ->with('success', __('messages.withdrawal_cancelled'));
    }
}
