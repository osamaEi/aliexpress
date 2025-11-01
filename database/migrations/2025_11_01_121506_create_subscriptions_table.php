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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bronze, Silver, Gold
            $table->string('name_ar')->nullable(); // Arabic name
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->decimal('price', 10, 2); // Monthly price
            $table->integer('duration_days')->default(30); // Duration in days
            $table->string('color')->default('#6c757d'); // Badge color

            // Features/Limits
            $table->integer('max_products')->default(10); // Max products allowed
            $table->integer('max_orders_per_month')->default(50); // Max orders per month
            $table->boolean('priority_support')->default(false);
            $table->boolean('analytics_access')->default(false);
            $table->boolean('bulk_import')->default(false);
            $table->boolean('api_access')->default(false);
            $table->decimal('commission_rate', 5, 2)->default(10.00); // Commission percentage

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
