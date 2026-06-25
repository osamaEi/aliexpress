<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tickets can now be about a coupon (marketer activation request) and routed to
     * either the admin (global coupon) or a specific distributor (their own coupon).
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('user_id')
                  ->constrained('coupons')->nullOnDelete();
            $table->enum('recipient_type', ['admin', 'distributor'])->default('admin')->after('coupon_id');
            $table->foreignId('recipient_id')->nullable()->after('recipient_type')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropConstrainedForeignId('recipient_id');
            $table->dropColumn('recipient_type');
        });
    }
};
