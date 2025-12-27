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
        Schema::table('product_user', function (Blueprint $table) {
            // Add rejection reason column if not exists
            if (!Schema::hasColumn('product_user', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }

            // Add approved_at column if not exists
            if (!Schema::hasColumn('product_user', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            }

            // Add approved_by column if not exists
            if (!Schema::hasColumn('product_user', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
        });

        // Update status enum to include pending, approved, rejected
        // Note: MySQL enum modification - first change column to string, then update values
        DB::statement("ALTER TABLE product_user MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");

        // Update existing statuses
        DB::table('product_user')->where('status', 'assigned')->update(['status' => 'pending']);
        DB::table('product_user')->where('status', 'imported')->update(['status' => 'approved']);
        DB::table('product_user')->where('status', 'published')->update(['status' => 'approved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_user', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['rejection_reason', 'approved_at', 'approved_by']);
        });
    }
};
