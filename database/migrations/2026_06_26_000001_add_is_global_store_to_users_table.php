<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Marks distributor users that are "Global Stores" created by the admin
            // (no real login). Used to scope admin/affiliate coupons to these stores only.
            $table->boolean('is_global_store')->default(false)->after('user_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_global_store');
        });
    }
};
