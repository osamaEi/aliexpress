<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Admin profit is now driven solely by the global `admin_profit` setting,
     * so the per-category admin profit feature is removed entirely:
     *   - the `admin_category_profits` table
     *   - the `admin_category_profit` column on `orders`
     */
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'admin_category_profit')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('admin_category_profit');
            });
        }

        Schema::dropIfExists('admin_category_profits');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('orders', 'admin_category_profit')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('admin_category_profit', 10, 2)->default(0)->after('aliexpress_profit');
            });
        }

        if (!Schema::hasTable('admin_category_profits')) {
            Schema::create('admin_category_profits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
                $table->decimal('profit_amount', 10, 2)->default(0);
                $table->string('currency', 3)->default('AED');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique('category_id');
            });
        }
    }
};
