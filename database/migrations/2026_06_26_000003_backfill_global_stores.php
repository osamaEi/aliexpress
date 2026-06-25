<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Existing admin-created stores (distributors without a usable login) predate the
     * is_global_store column, so they default to false and disappear from the Global
     * Stores list. Backfill them: any distributor with an empty/null password is an
     * admin-owned Global Store.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('user_type', 'distributor')
            ->where(function ($q) {
                $q->whereNull('password')->orWhere('password', '');
            })
            ->update(['is_global_store' => true]);
    }

    public function down(): void
    {
        // No-op: we don't want to un-flag global stores on rollback.
    }
};
