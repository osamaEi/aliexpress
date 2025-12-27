@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ app()->getLocale() == 'ar' ? 'تفاصيل المحفظة' : 'Wallet Details' }}</h4>
            <p class="text-muted">{{ $wallet->user->name }} - {{ $wallet->user->email }}</p>
        </div>
        <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            {{ app()->getLocale() == 'ar' ? 'العودة' : 'Back' }}
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Wallet Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                            <i class="ri-wallet-3-line ri-24px"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{!! format_currency($wallet->balance, 'AED', 2, true) !!}</h5>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الرصيد الإجمالي' : 'Total Balance' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-warning me-3 p-2">
                            <i class="ri-time-line ri-24px"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{!! format_currency($wallet->pending_balance, 'AED', 2, true) !!}</h5>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الرصيد المعلق' : 'Pending Balance' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-primary me-3 p-2">
                            <i class="ri-money-dollar-circle-line ri-24px"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{!! format_currency($wallet->available_balance, 'AED', 2, true) !!}</h5>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الرصيد المتاح' : 'Available Balance' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-info me-3 p-2">
                            <i class="ri-user-line ri-24px"></i>
                        </div>
                        <div>
                            @if($wallet->user->user_type === 'seller')
                                <span class="badge bg-primary">{{ app()->getLocale() == 'ar' ? 'بائع' : 'Seller' }}</span>
                            @elseif($wallet->user->user_type === 'distributor')
                                <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'تاجر' : 'Distributor' }}</span>
                            @elseif($wallet->user->user_type === 'buyer')
                                <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? 'مشتري' : 'Buyer' }}</span>
                            @else
                                <span class="badge bg-danger">{{ app()->getLocale() == 'ar' ? 'مدير' : 'Admin' }}</span>
                            @endif
                            <small class="text-muted d-block">{{ app()->getLocale() == 'ar' ? 'نوع المستخدم' : 'User Type' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="ri-add-line me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'إضافة رصيد' : 'Add Balance (Credit)' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.wallets.credit', $wallet) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="credit_amount" class="form-label">{{ app()->getLocale() == 'ar' ? 'المبلغ' : 'Amount' }} *</label>
                            <div class="input-group">
                                <span class="input-group-text">AED</span>
                                <input type="number" class="form-control" id="credit_amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="credit_description" class="form-label">{{ app()->getLocale() == 'ar' ? 'الوصف' : 'Description' }} *</label>
                            <input type="text" class="form-control" id="credit_description" name="description" required placeholder="{{ app()->getLocale() == 'ar' ? 'سبب إضافة الرصيد' : 'Reason for adding balance' }}">
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-add-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'إضافة رصيد' : 'Add Balance' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="ri-subtract-line me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'خصم رصيد' : 'Deduct Balance (Debit)' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.wallets.debit', $wallet) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="debit_amount" class="form-label">{{ app()->getLocale() == 'ar' ? 'المبلغ' : 'Amount' }} *</label>
                            <div class="input-group">
                                <span class="input-group-text">AED</span>
                                <input type="number" class="form-control" id="debit_amount" name="amount" step="0.01" min="0.01" max="{{ $wallet->available_balance }}" required>
                            </div>
                            <small class="text-muted">{{ app()->getLocale() == 'ar' ? 'الحد الأقصى' : 'Max' }}: AED {{ number_format($wallet->available_balance, 2) }}</small>
                        </div>
                        <div class="mb-3">
                            <label for="debit_description" class="form-label">{{ app()->getLocale() == 'ar' ? 'الوصف' : 'Description' }} *</label>
                            <input type="text" class="form-control" id="debit_description" name="description" required placeholder="{{ app()->getLocale() == 'ar' ? 'سبب خصم الرصيد' : 'Reason for deducting balance' }}">
                        </div>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد من خصم هذا المبلغ؟' : 'Are you sure you want to deduct this amount?' }}')">
                            <i class="ri-subtract-line me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'خصم رصيد' : 'Deduct Balance' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ri-user-line me-2"></i>
                {{ app()->getLocale() == 'ar' ? 'معلومات المستخدم' : 'User Information' }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-1"><strong>{{ app()->getLocale() == 'ar' ? 'الاسم' : 'Name' }}:</strong></p>
                    <p class="text-muted">{{ $wallet->user->name }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>{{ app()->getLocale() == 'ar' ? 'البريد الإلكتروني' : 'Email' }}:</strong></p>
                    <p class="text-muted">{{ $wallet->user->email }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>{{ app()->getLocale() == 'ar' ? 'رقم الهاتف' : 'Phone' }}:</strong></p>
                    <p class="text-muted">{{ $wallet->user->phone ?? '-' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>{{ app()->getLocale() == 'ar' ? 'تاريخ التسجيل' : 'Registered' }}:</strong></p>
                    <p class="text-muted">{{ $wallet->user->created_at->format('Y-m-d') }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>{{ app()->getLocale() == 'ar' ? 'آخر نشاط' : 'Last Activity' }}:</strong></p>
                    <p class="text-muted">{{ $wallet->last_transaction_at ? $wallet->last_transaction_at->diffForHumans() : '-' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>{{ app()->getLocale() == 'ar' ? 'حالة المحفظة' : 'Wallet Status' }}:</strong></p>
                    @if($wallet->is_active)
                        <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'نشط' : 'Active' }}</span>
                    @else
                        <span class="badge bg-danger">{{ app()->getLocale() == 'ar' ? 'معطل' : 'Inactive' }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ri-history-line me-2"></i>
                {{ app()->getLocale() == 'ar' ? 'سجل المعاملات' : 'Transaction History' }}
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() == 'ar' ? 'التاريخ' : 'Date' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'النوع' : 'Type' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'نوع المعاملة' : 'Transaction Type' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الوصف' : 'Description' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'المبلغ' : 'Amount' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الرصيد بعد' : 'Balance After' }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <small>{{ $transaction->created_at->format('Y-m-d H:i') }}</small>
                            </td>
                            <td>
                                @if($transaction->type === 'credit')
                                    <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'إضافة' : 'Credit' }}</span>
                                @else
                                    <span class="badge bg-danger">{{ app()->getLocale() == 'ar' ? 'خصم' : 'Debit' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</span>
                            </td>
                            <td>{{ $transaction->description ?? '-' }}</td>
                            <td>
                                <span class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type === 'credit' ? '+' : '-' }} AED {{ number_format(abs($transaction->amount), 2) }}
                                </span>
                            </td>
                            <td>AED {{ number_format($transaction->balance_after, 2) }}</td>
                            <td>
                                @if($transaction->status === 'completed')
                                    <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? 'مكتمل' : 'Completed' }}</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge bg-warning">{{ app()->getLocale() == 'ar' ? 'قيد الانتظار' : 'Pending' }}</span>
                                @else
                                    <span class="badge bg-danger">{{ app()->getLocale() == 'ar' ? 'فشل' : 'Failed' }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                {{ app()->getLocale() == 'ar' ? 'لا توجد معاملات' : 'No transactions found' }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transactions->hasPages())
        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
