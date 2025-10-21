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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('sku')->nullable()->unique();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');

            // AliExpress specific fields
            $table->string('aliexpress_id')->nullable()->unique();
            $table->string('aliexpress_url')->nullable();
            $table->decimal('aliexpress_price', 10, 2)->nullable();
            $table->string('aliexpress_product_status')->nullable();
            $table->json('aliexpress_variants')->nullable();
            $table->json('images')->nullable();
            $table->json('specifications')->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->integer('processing_time_days')->nullable();
            $table->decimal('supplier_profit_margin', 5, 2)->default(30.00);
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
