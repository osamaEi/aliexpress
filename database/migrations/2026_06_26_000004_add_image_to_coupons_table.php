<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // Main coupon image (shown to admin & distributor). The code becomes optional
            // because it is generated later when the coupon is activated for a marketer.
            $table->string('image')->nullable()->after('promo_video');
        });

        // Make code nullable (generated at activation time, not creation)
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
