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
        Schema::table('product_user', function (Blueprint $table) {
            $table->decimal('seller_amount', 10, 2)->nullable()->after('status');
            $table->decimal('admin_amount', 10, 2)->nullable()->after('seller_amount');
            $table->decimal('price', 10, 2)->nullable()->after('admin_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_user', function (Blueprint $table) {
            $table->dropColumn(['seller_amount', 'admin_amount', 'price']);
        });
    }
};
