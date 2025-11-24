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
        Schema::create('profits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Price breakdown
            $table->decimal('aliexpress_price', 10, 2)->default(0); // Original AliExpress price
            $table->decimal('admin_profit', 10, 2)->default(0);     // Admin's profit margin
            $table->decimal('seller_profit', 10, 2)->default(0);    // Seller's profit margin
            $table->decimal('shipping_price', 10, 2)->default(0);   // Shipping/freight cost

            // Total calculations
            $table->decimal('total_cost', 10, 2)->default(0);       // AliExpress + Shipping
            $table->decimal('total_profit', 10, 2)->default(0);     // Admin + Seller profit
            $table->decimal('final_price', 10, 2)->default(0);      // Total customer pays

            $table->string('currency', 3)->default('USD');
            $table->integer('quantity')->default(1);

            $table->timestamps();

            // Indexes
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profits');
    }
};
