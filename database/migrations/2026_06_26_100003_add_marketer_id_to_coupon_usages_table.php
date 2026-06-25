<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When a coupon is used through a marketer's tracking code, attribute the
     * commission to that marketer.
     */
    public function up(): void
    {
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->foreignId('marketer_id')->nullable()->after('user_id')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marketer_id');
        });
    }
};
