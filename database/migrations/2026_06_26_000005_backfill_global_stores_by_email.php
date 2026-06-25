<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The previous backfill matched on empty password, but the User model hashes
     * passwords (cast 'hashed'), so admin store passwords became bcrypt('') instead
     * of an empty string and were missed. Admin-created Global Stores use a generated
     * placeholder email (store_xxx@placeholder.local) — flag those reliably.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('user_type', 'distributor')
            ->where('email', 'like', 'store\_%@placeholder.local')
            ->update(['is_global_store' => true]);
    }

    public function down(): void
    {
        // No-op
    }
};
