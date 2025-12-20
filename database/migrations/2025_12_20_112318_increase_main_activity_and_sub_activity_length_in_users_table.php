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
            // Change main_activity and sub_activity to TEXT to store JSON arrays
            $table->text('main_activity')->nullable()->change();
            $table->text('sub_activity')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to VARCHAR(255)
            $table->string('main_activity', 255)->nullable()->change();
            $table->string('sub_activity', 255)->nullable()->change();
        });
    }
};
