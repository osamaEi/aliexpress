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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // credit, debit
            $table->string('transaction_type'); // deposit, withdrawal, payment, refund, commission, order_payment, subscription_payment
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('currency', 3)->default('AED');
            $table->string('status')->default('completed'); // pending, completed, failed, cancelled
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable(); // Order, UserSubscription, PaymentTransaction
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('payment_method')->nullable(); // card, bank_transfer, wallet
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('type');
            $table->index('transaction_type');
            $table->index('status');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
