<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Admin profit (the global `admin_profit` setting amount that is baked into the
     * product price) is now recorded per order so it shows up in the order-profits
     * report as a distinct line, sourced from product_user.admin_amount.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'admin_profit')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('admin_profit', 10, 2)->default(0)->after('aliexpress_profit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('orders', 'admin_profit')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('admin_profit');
            });
        }
    }
};
