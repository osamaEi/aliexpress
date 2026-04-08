<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'store_type')) {
                $table->enum('store_type', ['online', 'physical', 'both'])->nullable()->after('website_url');
            }
            if (!Schema::hasColumn('users', 'store_region')) {
                $table->string('store_region')->nullable()->after('store_type');
            }
            if (!Schema::hasColumn('users', 'store_address')) {
                $table->string('store_address')->nullable()->after('store_region');
            }
            if (!Schema::hasColumn('users', 'store_map_url')) {
                $table->string('store_map_url')->nullable()->after('store_address');
            }
            if (!Schema::hasColumn('users', 'support_phone')) {
                $table->string('support_phone')->nullable()->after('store_map_url');
            }
            if (!Schema::hasColumn('users', 'support_email')) {
                $table->string('support_email')->nullable()->after('support_phone');
            }
            if (!Schema::hasColumn('users', 'working_hours')) {
                $table->json('working_hours')->nullable()->after('support_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'store_type', 'store_region', 'store_address',
                'store_map_url', 'support_phone', 'support_email', 'working_hours',
            ]);
        });
    }
};
