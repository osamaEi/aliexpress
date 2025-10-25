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
        Schema::create('product_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('aliexpress_product_id')->nullable(); // For products not yet imported
            $table->enum('status', ['assigned', 'imported', 'published'])->default('assigned');
            $table->timestamps();

            // Unique constraint: one seller per product
            $table->unique(['user_id', 'product_id']);
            $table->unique(['user_id', 'aliexpress_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_user');
    }
};
