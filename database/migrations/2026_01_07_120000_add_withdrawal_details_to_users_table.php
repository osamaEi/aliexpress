<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Withdrawal method type: paypal, bank_transfer, mobile_wallet
            if (!Schema::hasColumn('users', 'withdrawal_method')) {
                $table->string('withdrawal_method')->nullable()->after('sub_activity');
            }

            // PayPal details
            if (!Schema::hasColumn('users', 'paypal_email')) {
                $table->string('paypal_email')->nullable()->after('withdrawal_method');
            }

            // Bank transfer details
            if (!Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('paypal_email');
            }
            if (!Schema::hasColumn('users', 'bank_account_name')) {
                $table->string('bank_account_name')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('users', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_account_name');
            }
            if (!Schema::hasColumn('users', 'bank_iban')) {
                $table->string('bank_iban')->nullable()->after('bank_account_number');
            }
            if (!Schema::hasColumn('users', 'bank_swift_code')) {
                $table->string('bank_swift_code')->nullable()->after('bank_iban');
            }

            // Mobile wallet details
            if (!Schema::hasColumn('users', 'wallet_provider')) {
                $table->string('wallet_provider')->nullable()->after('bank_swift_code');
            }
            if (!Schema::hasColumn('users', 'wallet_phone_number')) {
                $table->string('wallet_phone_number')->nullable()->after('wallet_provider');
            }
            if (!Schema::hasColumn('users', 'wallet_holder_name')) {
                $table->string('wallet_holder_name')->nullable()->after('wallet_phone_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'paypal_email',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_iban',
                'bank_swift_code',
                'wallet_provider',
                'wallet_phone_number',
                'wallet_holder_name',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
