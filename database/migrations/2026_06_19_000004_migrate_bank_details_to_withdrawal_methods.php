<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Move each user's existing flat bank details (on the users table) into a
     * default row in withdrawal_methods, so nobody loses their saved data.
     */
    public function up(): void
    {
        $users = DB::table('users')
            ->where(function ($q) {
                $q->whereNotNull('bank_iban')
                  ->orWhereNotNull('bank_name')
                  ->orWhereNotNull('bank_account_number');
            })
            ->get();

        foreach ($users as $user) {
            // Skip if this user already has a bank method (idempotent re-runs)
            $exists = DB::table('withdrawal_methods')
                ->where('user_id', $user->id)
                ->where('type', 'bank_transfer')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('withdrawal_methods')->insert([
                'user_id'             => $user->id,
                'type'                => 'bank_transfer',
                'is_default'          => true,
                'label'               => $user->bank_name ?: 'Bank account',
                'bank_name'           => $user->bank_name ?? null,
                'account_holder_name' => $user->bank_account_name ?? null,
                'account_number'      => $user->bank_account_number ?? null,
                'iban'                => $user->bank_iban ?? null,
                'swift_code'          => $user->bank_swift_code ?? null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Only remove the rows that were generated from the flat columns.
     */
    public function down(): void
    {
        // Non-destructive: leave migrated rows in place. The flat columns on
        // users are untouched, so reverting create_withdrawal_methods_table
        // drops these rows anyway.
    }
};
