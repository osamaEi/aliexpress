<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_city_id')->nullable()->after('shipping_city')->constrained('cities')->nullOnDelete();
            $table->foreignId('shipping_district_id')->nullable()->after('shipping_city_id')->constrained('districts')->nullOnDelete();
            $table->foreignId('distributor_shipping_rate_id')->nullable()->after('shipping_district_id')->constrained('distributor_shipping_rates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_city_id');
            $table->dropConstrainedForeignId('shipping_district_id');
            $table->dropConstrainedForeignId('distributor_shipping_rate_id');
        });
    }
};
