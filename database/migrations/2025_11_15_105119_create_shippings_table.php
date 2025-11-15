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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('tracking_number')->nullable();
            $table->string('carrier_name')->nullable(); // Logistics company name
            $table->string('carrier_code')->nullable(); // Logistics company code
            $table->string('shipping_method')->nullable(); // Shipping service type
            $table->string('status')->default('pending'); // pending, in_transit, delivered, exception, etc.
            $table->string('origin_country')->nullable();
            $table->string('destination_country')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('tracking_events')->nullable(); // Array of tracking events/history
            $table->json('raw_response')->nullable(); // Full API response for debugging
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('tracking_number');
            $table->index('status');
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};
