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
        Schema::table('products', function (Blueprint $table) {
            // Drop old markup fields
            $table->dropColumn(['markup_amount', 'markup_percentage']);

            // Add new seller and admin amount fields (nullable)
            $table->decimal('seller_amount', 10, 2)->nullable()->after('original_price')->comment('Amount added by seller');
            $table->decimal('admin_amount', 10, 2)->nullable()->after('seller_amount')->comment('Amount added by admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restore old fields
            $table->decimal('markup_amount', 10, 2)->default(0)->after('original_price');
            $table->decimal('markup_percentage', 5, 2)->default(0)->after('markup_amount');

            // Drop new fields
            $table->dropColumn(['seller_amount', 'admin_amount']);
        });
    }
};
