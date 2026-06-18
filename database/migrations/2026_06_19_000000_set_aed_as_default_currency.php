<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Make AED the system default currency.
 *
 * AED is the base currency for all backend operations (product prices, profits,
 * wallet, orders). USD remains in the table with exchange_rate = 1.0 because it
 * is used as the mathematical pivot inside Currency::convertFrom(), but it is no
 * longer flagged as the default.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Clear any existing default flag
        DB::table('currencies')->update(['is_default' => false]);

        // Set AED as the default currency
        DB::table('currencies')->where('code', 'AED')->update(['is_default' => true]);
    }

    public function down(): void
    {
        DB::table('currencies')->update(['is_default' => false]);
        DB::table('currencies')->where('code', 'USD')->update(['is_default' => true]);
    }
};
