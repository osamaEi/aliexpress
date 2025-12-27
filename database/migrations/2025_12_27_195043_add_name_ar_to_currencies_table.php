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
        Schema::table('currencies', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
        });

        // Update existing currencies with Arabic names
        DB::table('currencies')->where('code', 'USD')->update(['name_ar' => 'دولار أمريكي']);
        DB::table('currencies')->where('code', 'AED')->update(['name_ar' => 'درهم إماراتي']);
        DB::table('currencies')->where('code', 'SAR')->update(['name_ar' => 'ريال سعودي']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('name_ar');
        });
    }
};
