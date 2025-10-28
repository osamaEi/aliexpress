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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // User information
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();

            // AliExpress order information
            $table->string('aliexpress_order_id')->nullable();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);

            // Pricing
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 3)->default('AED');

            // Customer shipping information
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            $table->string('phone_country', 5)->default('971');
            $table->text('shipping_address');
            $table->string('shipping_address2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_province')->nullable();
            $table->string('shipping_country');
            $table->string('shipping_zip')->nullable();

            // Order status
            $table->enum('status', [
                'pending',           // Order created, not yet sent to AliExpress
                'processing',        // Being sent to AliExpress
                'placed',           // Successfully placed on AliExpress
                'paid',             // Payment confirmed
                'shipped',          // Order shipped
                'delivered',        // Order delivered
                'cancelled',        // Order cancelled
                'failed'            // Order failed
            ])->default('pending');

            // Tracking
            $table->string('tracking_number')->nullable();
            $table->string('shipping_method')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();

            // AliExpress response data
            $table->json('aliexpress_response')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('order_number');
            $table->index('aliexpress_order_id');
            $table->index('status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
