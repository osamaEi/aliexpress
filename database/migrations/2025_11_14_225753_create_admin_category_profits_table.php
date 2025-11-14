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
        Schema::create('admin_category_profits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->decimal('profit_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('AED');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure one profit setting per category
            $table->unique('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_category_profits');
    }
};
