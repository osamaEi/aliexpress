<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminWalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Display all user wallets
     */
    public function index(Request $request)
    {
        $query = Wallet::with('user');

        // Search by user name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by user type
        if ($request->filled('user_type')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('user_type', $request->user_type);
            });
        }

        $wallets = $query->latest('last_transaction_at')->paginate(20);

        // Statistics
        $stats = [
            'total_wallets' => Wallet::count(),
            'total_balance' => Wallet::sum('balance'),
            'total_pending' => Wallet::sum('pending_balance'),
            'active_wallets' => Wallet::where('is_active', true)->count(),
        ];

        return view('admin.wallets.index', compact('wallets', 'stats'));
    }

    /**
     * Show wallet details
     */
    public function show(Wallet $wallet)
    {
        $wallet->load('user', 'transactions');
        $transactions = $wallet->transactions()->paginate(20);

        return view('admin.wallets.show', compact('wallet', 'transactions'));
    }

    /**
     * Withdrawal requests
     */
    public function withdrawalRequests(Request $request)
    {
        $query = WithdrawalRequest::with(['user', 'wallet', 'processor']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to pending
            $query->pending();
        }

        $requests = $query->latest()->paginate(20);

        // Statistics
        $stats = [
            'pending_count' => WithdrawalRequest::pending()->count(),
            'pending_amount' => WithdrawalRequest::pending()->sum('amount'),
            'approved_today' => WithdrawalRequest::approved()->whereDate('processed_at', today())->count(),
            'completed_today' => WithdrawalRequest::completed()->whereDate('processed_at', today())->count(),
        ];

        return view('admin.wallets.withdrawals', compact('requests', 'stats'));
    }

    /**
     * Approve withdrawal request
     */
    public function approveWithdrawal(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        if (!$withdrawalRequest->isPending()) {
            return back()->with('error', __('messages.request_already_processed'));
        }

        try {
            DB::transaction(function () use ($withdrawalRequest, $request) {
                // Update request status
                $withdrawalRequest->update([
                    'status' => 'approved',
                    'admin_notes' => $request->admin_notes,
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                ]);

                // Process withdrawal (debit wallet and release hold)
                $wallet = $withdrawalRequest->wallet;
                $user = $withdrawalRequest->user;

                // Release hold
                $this->walletService->releaseHold($user, $withdrawalRequest->amount);

                // Debit wallet
                $wallet->debit(
                    $withdrawalRequest->amount,
                    'withdrawal',
                    "Withdrawal approved - {$withdrawalRequest->bank_name}",
                    [
                        'withdrawal_request_id' => $withdrawalRequest->id,
                        'bank_name' => $withdrawalRequest->bank_name,
                        'account_number' => $withdrawalRequest->account_number,
                    ]
                );
            });

            return back()->with('success', __('messages.withdrawal_approved'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject withdrawal request
     */
    public function rejectWithdrawal(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        if (!$withdrawalRequest->isPending()) {
            return back()->with('error', __('messages.request_already_processed'));
        }

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($withdrawalRequest, $request) {
                // Update request status
                $withdrawalRequest->update([
                    'status' => 'rejected',
                    'admin_notes' => $request->admin_notes,
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                ]);

                // Release hold
                $this->walletService->releaseHold($withdrawalRequest->user, $withdrawalRequest->amount);
            });

            return back()->with('success', __('messages.withdrawal_rejected'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark withdrawal as completed
     */
    public function completeWithdrawal(WithdrawalRequest $withdrawalRequest)
    {
        if (!$withdrawalRequest->isApproved()) {
            return back()->with('error', __('messages.request_not_approved'));
        }

        $withdrawalRequest->update([
            'status' => 'completed',
        ]);

        return back()->with('success', __('messages.withdrawal_completed'));
    }

    /**
     * Add balance to user wallet (admin credit)
     */
    public function creditWallet(Request $request, Wallet $wallet)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        try {
            $wallet->credit(
                $request->amount,
                'admin_credit',
                $request->description,
                ['admin_id' => Auth::id()]
            );

            return back()->with('success', __('messages.balance_added_successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Deduct balance from user wallet (admin debit)
     */
    public function debitWallet(Request $request, Wallet $wallet)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        try {
            $wallet->debit(
                $request->amount,
                'admin_debit',
                $request->description,
                ['admin_id' => Auth::id()]
            );

            return back()->with('success', __('messages.balance_deducted_successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * All wallet transactions
     */
    public function transactions(Request $request)
    {
        $query = WalletTransaction::with(['user', 'wallet']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by transaction type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search by description
        if ($request->filled('search')) {
            $query->where('description', 'like', "%{$request->search}%");
        }

        $transactions = $query->latest()->paginate(20);

        return view('admin.wallets.transactions', compact('transactions'));
    }
}
