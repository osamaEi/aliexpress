<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Display wallet dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getOrCreateWallet($user);
        $transactions = $this->walletService->getTransactionHistory($user, 10);

        return view('wallet.index', compact('wallet', 'transactions'));
    }

    /**
     * Show deposit form
     */
    public function depositForm()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return view('wallet.deposit', compact('wallet'));
    }

    /**
     * Process deposit
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:100000',
            'payment_method' => 'required|in:card,bank_transfer',
        ]);

        $user = Auth::user();
        $amount = $request->amount;

        // If payment method is card, redirect to Paymob
        if ($request->payment_method === 'card') {
            // Create a wallet deposit payment transaction
            // This will be handled similar to subscription/order payments
            return redirect()->route('payment.wallet-deposit', ['amount' => $amount]);
        }

        // For bank transfer, create pending transaction
        // Admin will approve later
        return redirect()->route('wallet.index')
            ->with('success', __('messages.deposit_request_submitted'));
    }

    /**
     * Show withdrawal form
     */
    public function withdrawalForm()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return view('wallet.withdrawal', compact('wallet'));
    }

    /**
     * Process withdrawal request
     */
    public function withdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $user = Auth::user();
        $amount = $request->amount;

        // Check if user has sufficient balance
        if (!$this->walletService->hasSufficientBalance($user, $amount)) {
            return back()->with('error', __('messages.insufficient_wallet_balance'));
        }

        try {
            $wallet = $this->walletService->getOrCreateWallet($user);

            // Hold the amount
            $this->walletService->hold($user, $amount);

            // Create withdrawal request (pending admin approval)
            \App\Models\WithdrawalRequest::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'currency' => 'AED',
                'status' => 'pending',
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'iban' => $request->iban,
                'swift_code' => $request->swift_code,
                'notes' => $request->notes,
            ]);

            return redirect()->route('wallet.index')
                ->with('success', __('messages.withdrawal_request_submitted'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show transaction history
     */
    public function transactions()
    {
        $user = Auth::user();
        $transactions = $this->walletService->getTransactionHistory($user, 20);

        return view('wallet.transactions', compact('transactions'));
    }

    /**
     * Show transfer form
     */
    public function transferForm()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return view('wallet.transfer', compact('wallet'));
    }

    /**
     * Process transfer
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'recipient_email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Check if transferring to self
        if ($request->recipient_email === $user->email) {
            return back()->with('error', __('messages.cannot_transfer_to_self'));
        }

        // Get recipient
        $recipient = \App\Models\User::where('email', $request->recipient_email)->first();

        // Check balance
        if (!$this->walletService->hasSufficientBalance($user, $request->amount)) {
            return back()->with('error', __('messages.insufficient_wallet_balance'));
        }

        try {
            $this->walletService->transfer($user, $recipient, $request->amount, $request->description);

            return redirect()->route('wallet.index')
                ->with('success', __('messages.transfer_successful'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
