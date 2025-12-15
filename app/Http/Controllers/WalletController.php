<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use App\Services\PayPalService;
use App\Services\ZiinaPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $walletService;
    protected $paypalService;
    protected $ziinaService;

    public function __construct(WalletService $walletService, PayPalService $paypalService, ZiinaPaymentService $ziinaService)
    {
        $this->walletService = $walletService;
        $this->paypalService = $paypalService;
        $this->ziinaService = $ziinaService;
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
     * Show transaction history with filters
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Build query with filters
        $query = $wallet->transactions()->with('reference');

        // Filter by type (credit/debit)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by transaction type (deposit, withdrawal, etc.)
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Get paginated results
        $transactions = $query->latest()->paginate(20)->withQueryString();

        // Calculate commission statistics for sellers
        $commissionStats = null;
        if ($user->user_type === 'seller') {
            $commissionStats = $this->getCommissionStatistics($user);
        }

        return view('wallet.transactions', compact('transactions', 'commissionStats'));
    }

    /**
     * Get commission statistics for seller
     */
    private function getCommissionStatistics($user)
    {
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Total commissions earned
        $totalCommissions = $wallet->transactions()
            ->where('transaction_type', 'commission')
            ->where('type', 'credit')
            ->sum('amount');

        // Paid commissions (completed and available)
        $paidCommissions = $wallet->transactions()
            ->where('transaction_type', 'commission')
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->sum('amount');

        // Unpaid commissions (pending)
        $unpaidCommissions = $wallet->transactions()
            ->where('transaction_type', 'commission')
            ->where('type', 'credit')
            ->where('status', 'pending')
            ->sum('amount');

        return [
            'total' => $totalCommissions,
            'paid' => $paidCommissions,
            'unpaid' => $unpaidCommissions,
        ];
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

    /**
     * Process PayPal deposit - Handle captured payment from frontend
     */
    public function depositPayPal(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:2|max:10000',
            'note' => 'nullable|string|max:500',
            'order_id' => 'required|string',
            'payer_id' => 'nullable|string',
            'details' => 'required|array',
        ]);

        try {
            $user = Auth::user();

            // Check if payment was already processed
            $existingTransaction = \App\Models\PaymentTransaction::where('paypal_order_id', $validated['order_id'])->first();

            if ($existingTransaction && $existingTransaction->status === 'success') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already processed'
                ], 400);
            }

            // Create payment transaction record
            $merchantOrderId = 'WALLET-' . $user->id . '-' . time();

            // Extract transaction ID and amounts from PayPal details
            $transactionId = $validated['details']['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

            // Get the gross amount (what customer paid) from PayPal response
            $grossAmount = $validated['details']['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? $validated['amount'];

            // Get PayPal fee if available
            $paypalFee = $validated['details']['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['value'] ?? 0;

            $paymentTransaction = \App\Models\PaymentTransaction::create([
                'user_id' => $user->id,
                'merchant_order_id' => $merchantOrderId,
                'type' => 'wallet_deposit',
                'amount' => $grossAmount, // Use gross amount from PayPal
                'currency' => config('paypal.currency'),
                'status' => 'success',
                'payment_method' => 'paypal',
                'paypal_order_id' => $validated['order_id'],
                'transaction_id' => $transactionId,
                'callback_data' => $validated['details'],
                'paid_at' => now(),
            ]);

            // Credit the wallet with the FULL amount customer paid (absorb PayPal fees)
            $wallet = $this->walletService->getOrCreateWallet($user);

            $this->walletService->deposit(
                $user,
                $grossAmount, // Credit full amount
                $validated['note'] ?: 'Wallet deposit via PayPal',
                [
                    'paypal_order_id' => $validated['order_id'],
                    'transaction_id' => $transactionId,
                    'payment_transaction_id' => $paymentTransaction->id,
                    'paypal_fee' => $paypalFee,
                    'gross_amount' => $grossAmount,
                ]
            );

            \Illuminate\Support\Facades\Log::info('Wallet Deposit Processed', [
                'user_id' => $user->id,
                'amount' => $grossAmount,
                'paypal_fee' => $paypalFee,
                'order_id' => $validated['order_id'],
                'transaction_id' => $transactionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wallet deposit successful',
                'new_balance' => $wallet->fresh()->balance
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Wallet Deposit Error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initiate Ziina wallet deposit
     */
    public function depositZiina(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:2|max:100000',
        ]);

        try {
            $user = Auth::user();
            $amount = $validated['amount'];

            // Calculate fees and gross amount
            $feeCalculation = $this->ziinaService->calculateFees($amount);

            // Create payment intent with Ziina
            $paymentIntent = $this->ziinaService->createWalletDepositPayment(
                $feeCalculation['gross_amount'],
                $user->id
            );

            // Store payment data in session
            session([
                'ziina_wallet_deposit' => [
                    'payment_intent_id' => $paymentIntent['id'],
                    'user_id' => $user->id,
                    'net_amount' => $feeCalculation['net_amount'],
                    'fee' => $feeCalculation['fee'],
                    'gross_amount' => $feeCalculation['gross_amount'],
                ]
            ]);

            // Redirect to Ziina payment page
            return redirect($paymentIntent['redirect_url']);

        } catch (\Exception $e) {
            \Log::error('Ziina wallet deposit initiation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', __('messages.payment_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Handle successful Ziina deposit payment
     */
    public function depositZiinaSuccess(Request $request)
    {
        $paymentIntentId = $request->query('payment_intent_id');

        if (!$paymentIntentId) {
            return redirect()->route('wallet.index')
                ->with('error', __('messages.invalid_payment_data'));
        }

        try {
            // Get payment session data
            $sessionData = session('ziina_wallet_deposit');

            if (!$sessionData || $sessionData['payment_intent_id'] !== $paymentIntentId) {
                throw new \Exception('Invalid payment session');
            }

            // Verify payment with Ziina
            $paymentIntent = $this->ziinaService->getPaymentIntent($paymentIntentId);

            // Check payment status
            if (!in_array($paymentIntent['status'], ['completed', 'succeeded'])) {
                throw new \Exception('Payment not completed');
            }

            $user = \App\Models\User::findOrFail($sessionData['user_id']);

            // Process wallet deposit
            \DB::transaction(function () use ($user, $sessionData, $paymentIntentId) {
                // Credit the wallet with the NET amount (customer's intended deposit)
                $this->walletService->deposit(
                    $user,
                    $sessionData['net_amount'],
                    'Wallet deposit via Ziina',
                    [
                        'payment_intent_id' => $paymentIntentId,
                        'payment_method' => 'ziina',
                        'gross_amount' => $sessionData['gross_amount'],
                        'gateway_fee' => $sessionData['fee'],
                    ]
                );
            });

            // Clear session data
            session()->forget('ziina_wallet_deposit');

            \Log::info('Ziina wallet deposit successful', [
                'user_id' => $user->id,
                'net_amount' => $sessionData['net_amount'],
                'gross_amount' => $sessionData['gross_amount'],
                'fee' => $sessionData['fee'],
                'payment_intent_id' => $paymentIntentId
            ]);

            return redirect()->route('wallet.index')
                ->with('success', __('messages.wallet_deposit_successful'));

        } catch (\Exception $e) {
            \Log::error('Ziina wallet deposit verification failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('wallet.index')
                ->with('error', __('messages.payment_verification_failed'));
        }
    }

    /**
     * Handle cancelled Ziina deposit payment
     */
    public function depositZiinaCancel(Request $request)
    {
        // Clear session data
        session()->forget('ziina_wallet_deposit');

        return redirect()->route('wallet.index')
            ->with('info', __('messages.payment_cancelled'));
    }

    /**
     * Calculate fees for wallet deposit (AJAX)
     */
    public function calculateDepositFees(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:2|max:100000',
        ]);

        try {
            $feeCalculation = $this->ziinaService->calculateFees($validated['amount']);

            return response()->json([
                'success' => true,
                'data' => $feeCalculation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
