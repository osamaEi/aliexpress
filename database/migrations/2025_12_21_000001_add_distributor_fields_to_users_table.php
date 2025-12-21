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
        Schema::table('users', function (Blueprint $table) {
            // Check and add only missing columns
            if (!Schema::hasColumn('users', 'store_name')) {
                $table->string('store_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'store_slug')) {
                $table->string('store_slug')->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'default_currency')) {
                $table->string('default_currency')->nullable();
            }
            if (!Schema::hasColumn('users', 'commercial_register')) {
                $table->string('commercial_register')->nullable();
            }
            if (!Schema::hasColumn('users', 'freelance_document')) {
                $table->string('freelance_document')->nullable();
            }
            if (!Schema::hasColumn('users', 'social_media_accounts')) {
                $table->json('social_media_accounts')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'store_name',
                'store_slug',
                'default_currency',
                'commercial_register',
                'freelance_document',
                'social_media_accounts',
            ]);
        });
    }
};
