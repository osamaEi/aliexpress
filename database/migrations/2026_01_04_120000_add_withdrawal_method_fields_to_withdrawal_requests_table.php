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

        // Add paypal_email if it doesn't exist
        if (!Schema::hasColumn('withdrawal_requests', 'paypal_email')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->string('paypal_email')->nullable()->after('withdrawal_method');
            });
        } else {
            // Make paypal_email nullable using raw SQL (avoids doctrine/dbal dependency)
            DB::statement('ALTER TABLE withdrawal_requests MODIFY paypal_email VARCHAR(255) NULL');
        }

        if (!Schema::hasColumn('withdrawal_requests', 'iban')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->string('iban')->nullable()->after('paypal_email');
            });
        }
        if (!Schema::hasColumn('withdrawal_requests', 'swift_code')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->string('swift_code')->nullable()->after('iban');
            });
        }
        if (!Schema::hasColumn('withdrawal_requests', 'account_holder_name')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->string('account_holder_name')->nullable()->after('swift_code');
            });
        }
        if (!Schema::hasColumn('withdrawal_requests', 'wallet_provider')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->string('wallet_provider')->nullable()->after('account_holder_name');
            });
        }
        if (!Schema::hasColumn('withdrawal_requests', 'wallet_mobile_number')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->string('wallet_mobile_number')->nullable()->after('wallet_provider');
            });
        }
        if (!Schema::hasColumn('withdrawal_requests', 'wallet_holder_name')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
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
