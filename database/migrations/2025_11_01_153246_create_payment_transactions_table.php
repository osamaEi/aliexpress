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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique()->nullable(); // Paymob transaction ID
            $table->string('paymob_order_id')->nullable(); // Paymob order ID
            $table->string('merchant_order_id'); // Our internal order/subscription ID
            $table->string('type'); // 'order' or 'subscription'
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('AED');
            $table->string('status')->default('pending'); // pending, success, failed, refunded
            $table->string('payment_method')->nullable(); // card, wallet, etc.
            $table->text('callback_data')->nullable(); // Full callback JSON
            $table->boolean('is_refunded')->default(false);
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['merchant_order_id', 'type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
