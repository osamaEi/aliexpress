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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g. "Red - Large"
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(0);
            $table->string('sku')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add video and has_variations column to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'video')) {
                $table->string('video')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('products', 'has_variations')) {
                $table->boolean('has_variations')->default(false)->after('track_inventory');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'video')) {
                $table->dropColumn('video');
            }
            if (Schema::hasColumn('products', 'has_variations')) {
                $table->dropColumn('has_variations');
            }
        });
    }
};
