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
            // Add phone field if it doesn't exist
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            // Add withdrawal_method field if it doesn't exist
            if (!Schema::hasColumn('users', 'withdrawal_method')) {
                $table->string('withdrawal_method')->nullable()->after('sub_activity');
            }

            // Add marketing_code field if it doesn't exist
            if (!Schema::hasColumn('users', 'marketing_code')) {
                $table->string('marketing_code', 100)->nullable()->after('withdrawal_method');
            }

            // Add marketing_code_approved field if it doesn't exist
            if (!Schema::hasColumn('users', 'marketing_code_approved')) {
                $table->boolean('marketing_code_approved')->default(false)->after('marketing_code');
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
                'phone',
                'withdrawal_method',
                'marketing_code',
                'marketing_code_approved'
            ]);
        });
    }
};
