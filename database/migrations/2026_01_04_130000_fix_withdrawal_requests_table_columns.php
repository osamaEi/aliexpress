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
        // Make previously required fields nullable for new withdrawal methods
        // Using raw SQL to avoid doctrine/dbal dependency

        // Check if wallet_id column exists and drop it (not needed anymore)
        if (Schema::hasColumn('withdrawal_requests', 'wallet_id')) {
            // First drop the foreign key constraint
            try {
                Schema::table('withdrawal_requests', function (Blueprint $table) {
                    $table->dropForeign(['wallet_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist
            }

            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->dropColumn('wallet_id');
            });
        }

        // Make bank_name nullable
        if (Schema::hasColumn('withdrawal_requests', 'bank_name')) {
            DB::statement('ALTER TABLE withdrawal_requests MODIFY bank_name VARCHAR(255) NULL');
        }

        // Make account_number nullable
        if (Schema::hasColumn('withdrawal_requests', 'account_number')) {
            DB::statement('ALTER TABLE withdrawal_requests MODIFY account_number VARCHAR(255) NULL');
        }

        // Make account_name nullable
        if (Schema::hasColumn('withdrawal_requests', 'account_name')) {
            DB::statement('ALTER TABLE withdrawal_requests MODIFY account_name VARCHAR(255) NULL');
        }

        // Add paypal_email if it doesn't exist
        if (!Schema::hasColumn('withdrawal_requests', 'paypal_email')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->string('paypal_email')->nullable()->after('user_id');
            });
        } else {
            // Make paypal_email nullable if it exists
            DB::statement('ALTER TABLE withdrawal_requests MODIFY paypal_email VARCHAR(255) NULL');
        }

        // Add seller_note if it doesn't exist (rename notes to seller_note)
        if (Schema::hasColumn('withdrawal_requests', 'notes') && !Schema::hasColumn('withdrawal_requests', 'seller_note')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->renameColumn('notes', 'seller_note');
            });
        } elseif (!Schema::hasColumn('withdrawal_requests', 'seller_note')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->text('seller_note')->nullable();
            });
        }

        // Add admin_note if it doesn't exist (rename admin_notes to admin_note)
        if (Schema::hasColumn('withdrawal_requests', 'admin_notes') && !Schema::hasColumn('withdrawal_requests', 'admin_note')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->renameColumn('admin_notes', 'admin_note');
            });
        } elseif (!Schema::hasColumn('withdrawal_requests', 'admin_note')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->text('admin_note')->nullable();
            });
        }

        // Add approved_by if it doesn't exist (equivalent to processed_by)
        if (!Schema::hasColumn('withdrawal_requests', 'approved_by')) {
            if (Schema::hasColumn('withdrawal_requests', 'processed_by')) {
                Schema::table('withdrawal_requests', function (Blueprint $table) {
                    $table->renameColumn('processed_by', 'approved_by');
                });
            } else {
                Schema::table('withdrawal_requests', function (Blueprint $table) {
                    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                });
            }
        }

        // Add timestamp fields
        if (!Schema::hasColumn('withdrawal_requests', 'approved_at')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable();
            });
        }

        if (!Schema::hasColumn('withdrawal_requests', 'rejected_at')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->timestamp('rejected_at')->nullable();
            });
        }

        if (!Schema::hasColumn('withdrawal_requests', 'completed_at')) {
            Schema::table('withdrawal_requests', function (Blueprint $table) {
                $table->timestamp('completed_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is a fix, not easily reversible
    }
};
