<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Allow subscription plans to target marketers too.
     * Widen the role enum: seller | distributor | marketer | both.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN role ENUM('seller','distributor','marketer','both') NOT NULL DEFAULT 'both'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN role ENUM('seller','distributor','both') NOT NULL DEFAULT 'both'");
    }
};
