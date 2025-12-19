<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if SAR currency already exists
        $sarExists = DB::table('currencies')->where('code', 'SAR')->exists();

        if (!$sarExists) {
            DB::table('currencies')->insert([
                'code' => 'SAR',
                'name' => 'Saudi Riyal',
                'symbol' => 'ر.س',
                'exchange_rate' => 3.7500,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove SAR currency
        DB::table('currencies')->where('code', 'SAR')->delete();
    }
};
