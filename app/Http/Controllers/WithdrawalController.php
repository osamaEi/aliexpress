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

        // Get current currency from session (shared by SetCurrency middleware)
        $currentCurrency = \App\Models\Currency::where('code', session('currency_code', 'AED'))->first();

        // Saved withdrawal methods (bank / paypal / mobile wallet) for the picker
        $withdrawalMethods = $user->withdrawalMethods()->orderByDesc('is_default')->get();

        return view('wallet.withdrawal', compact('wallet', 'currentCurrency', 'withdrawalMethods'));
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

        // Get current currency for conversion
        $currentCurrency = \App\Models\Currency::where('code', session('currency_code', 'AED'))->first();

        // Convert wallet balance to current currency for validation display
        $balanceInCurrentCurrency = $currentCurrency ? $currentCurrency->convertFrom($wallet->balance, 'AED') : $wallet->balance;

        // Convert entered amount back to AED for storage
        $amountInAED = $currentCurrency ? $currentCurrency->convertTo($request->amount, 'AED') : $request->amount;

        // Validate amount + the chosen saved method
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:' . $balanceInCurrentCurrency,
            'withdrawal_method_id' => 'required|exists:withdrawal_methods,id',
            'seller_note' => 'nullable|string|max:1000',
        ]);

        // Load the method and make sure it belongs to this user
        $method = $user->withdrawalMethods()->find($validated['withdrawal_method_id']);
        if (!$method) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('messages.withdrawal_method_not_found') ?? 'Selected withdrawal method not found.');
        }

        // Check if there's a pending withdrawal
        $pendingWithdrawal = WithdrawalRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingWithdrawal) {
            return redirect()->back()
                ->with('error', __('messages.pending_withdrawal_exists'));
        }

        try {
            DB::transaction(function () use ($user, $validated, $wallet, $method, $amountInAED) {
                // Snapshot the chosen method's details onto the request so historic
                // records stay accurate even if the method is later edited/deleted.
                $withdrawalData = [
                    'user_id' => $user->id,
                    'withdrawal_method' => $method->type,
                    'amount' => $amountInAED,
                    'currency' => 'AED',
                    'status' => 'pending',
                    'seller_note' => $validated['seller_note'] ?? null,
                ];

                if ($method->type === 'paypal') {
                    $withdrawalData['paypal_email'] = $method->paypal_email;
                } elseif ($method->type === 'bank_transfer') {
                    $withdrawalData['iban'] = $method->iban;
                    $withdrawalData['swift_code'] = $method->swift_code;
                    $withdrawalData['bank_name'] = $method->bank_name;
                    $withdrawalData['account_holder_name'] = $method->account_holder_name;
                } elseif ($method->type === 'mobile_wallet') {
                    $withdrawalData['wallet_provider'] = $method->wallet_provider;
                    $withdrawalData['wallet_mobile_number'] = $method->wallet_mobile_number;
                    $withdrawalData['wallet_holder_name'] = $method->wallet_holder_name;
                }

                // Create withdrawal request
                WithdrawalRequest::create($withdrawalData);

                // Deduct from wallet balance (hold the amount in AED)
                $wallet->decrement('balance', $amountInAED);
            });

            return redirect()->route('wallet.withdrawal.history')
                ->with('success', __('messages.withdrawal_request_submitted'));
        } catch (\Exception $e) {
            \Log::error('Withdrawal request error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'method' => $method->type ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
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
