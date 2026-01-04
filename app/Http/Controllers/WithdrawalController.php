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

        return view('wallet.withdrawal', compact('wallet', 'currentCurrency'));
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

        // Base validation rules (using converted balance for max)
        $rules = [
            'amount' => 'required|numeric|min:10|max:' . $balanceInCurrentCurrency,
            'withdrawal_method' => 'required|in:paypal,bank_transfer,mobile_wallet',
            'seller_note' => 'nullable|string|max:1000',
        ];

        // Add method-specific validation rules
        $withdrawalMethod = $request->withdrawal_method;

        if ($withdrawalMethod === 'paypal') {
            $rules['paypal_email'] = 'required|email|max:255';
        } elseif ($withdrawalMethod === 'bank_transfer') {
            $rules['iban'] = 'required|string|min:15|max:34';
            $rules['swift_code'] = 'nullable|string|max:11';
            $rules['bank_name'] = 'required|string|max:255';
            $rules['account_holder_name'] = 'required|string|max:255';
        } elseif ($withdrawalMethod === 'mobile_wallet') {
            $rules['wallet_provider'] = 'required|string';
            $rules['wallet_mobile_number'] = 'required|string';
            $rules['wallet_holder_name'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        // Check if there's a pending withdrawal
        $pendingWithdrawal = WithdrawalRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingWithdrawal) {
            return redirect()->back()
                ->with('error', __('messages.pending_withdrawal_exists'));
        }

        try {
            DB::transaction(function () use ($user, $validated, $wallet, $withdrawalMethod, $request, $amountInAED) {
                // Build withdrawal data (store amount in AED)
                $withdrawalData = [
                    'user_id' => $user->id,
                    'withdrawal_method' => $withdrawalMethod,
                    'amount' => $amountInAED,
                    'currency' => 'AED',
                    'status' => 'pending',
                    'seller_note' => $validated['seller_note'] ?? null,
                ];

                // Add method-specific data
                if ($withdrawalMethod === 'paypal') {
                    $withdrawalData['paypal_email'] = $validated['paypal_email'];
                } elseif ($withdrawalMethod === 'bank_transfer') {
                    $withdrawalData['iban'] = $validated['iban'];
                    $withdrawalData['swift_code'] = $validated['swift_code'] ?? null;
                    $withdrawalData['bank_name'] = $validated['bank_name'];
                    $withdrawalData['account_holder_name'] = $validated['account_holder_name'];
                } elseif ($withdrawalMethod === 'mobile_wallet') {
                    $withdrawalData['wallet_provider'] = $validated['wallet_provider'];
                    $withdrawalData['wallet_mobile_number'] = $validated['wallet_mobile_number'];
                    $withdrawalData['wallet_holder_name'] = $validated['wallet_holder_name'];
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
                'method' => $withdrawalMethod,
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
