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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Add withdrawal method field
            $table->enum('withdrawal_method', ['paypal', 'bank_transfer', 'mobile_wallet'])
                  ->default('paypal')
                  ->after('user_id');

            // Make paypal_email nullable since other methods don't need it
            $table->string('paypal_email')->nullable()->change();

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn([
                'withdrawal_method',
                'iban',
                'swift_code',
                'bank_name',
                'account_holder_name',
                'wallet_provider',
                'wallet_mobile_number',
                'wallet_holder_name'
            ]);
        });
    }
};
