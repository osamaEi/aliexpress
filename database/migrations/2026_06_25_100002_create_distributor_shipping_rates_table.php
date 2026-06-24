<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained('users')->cascadeOnDelete();
            $table->string('country_code', 3)->index();
            // Nullable city/district => flexible scope:
            //   country-only  : city_id null, district_id null
            //   city-level    : city_id set, district_id null
            //   district-level: city_id set, district_id set
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->string('currency', 3)->default('AED');
            $table->unsignedInteger('delivery_days_min')->nullable();
            $table->unsignedInteger('delivery_days_max')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['distributor_id', 'country_code', 'city_id', 'district_id'], 'dsr_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_shipping_rates');
    }
};
