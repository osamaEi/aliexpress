<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if columns already exist to avoid errors
        if (!Schema::hasColumn('withdrawal_requests', 'withdrawal_method')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                // Add withdrawal method field
                $table->string('withdrawal_method')->default('paypal')->after('user_id');
            });
        }

        // Make paypal_email nullable using raw SQL (avoids doctrine/dbal dependency)
        DB::statement('ALTER TABLE withdrawal_requests MODIFY paypal_email VARCHAR(255) NULL');

        if (!Schema::hasColumn('withdrawal_requests', 'iban')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                // Bank Transfer (IBAN) fields
                $table->string('iban')->nullable()->after('paypal_email');
                $table->string('swift_code')->nullable()->after('iban');
                $table->string('bank_name')->nullable()->after('swift_code');
                $table->string('account_holder_name')->nullable()->after('bank_name');

                // Mobile Wallet fields
                $table->string('wallet_provider')->nullable()->after('account_holder_name');
                $table->string('wallet_mobile_number')->nullable()->after('wallet_provider');
                $table->string('wallet_holder_name')->nullable()->after('wallet_mobile_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $columns = ['withdrawal_method', 'iban', 'swift_code', 'bank_name', 'account_holder_name', 'wallet_provider', 'wallet_mobile_number', 'wallet_holder_name'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('withdrawal_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
