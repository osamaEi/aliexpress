<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Saved withdrawal methods per user (one-to-many). A user (seller/distributor)
     * can store multiple methods — bank transfer, PayPal, mobile wallet — and pick
     * one when requesting a withdrawal.
     */
    public function up(): void
    {
        if (Schema::hasTable('withdrawal_methods')) {
            return;
        }

        Schema::create('withdrawal_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // bank_transfer | paypal | mobile_wallet
            $table->string('type');
            $table->boolean('is_default')->default(false);
            $table->string('label')->nullable();

            // Bank transfer
            $table->string('bank_name')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift_code')->nullable();

            // PayPal
            $table->string('paypal_email')->nullable();

            // Mobile wallet
            $table->string('wallet_provider')->nullable();
            $table->string('wallet_mobile_number')->nullable();
            $table->string('wallet_holder_name')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_methods');
    }
};
