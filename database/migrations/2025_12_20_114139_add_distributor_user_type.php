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
        // Update existing users with user_type enum if needed
        // Since user_type is a string field, no schema change needed
        // The 'distributor' type will be handled at application level

        // Optional: You can add a check constraint if using PostgreSQL
        // For MySQL, we'll handle validation in the application layer
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No changes needed to revert
        // The user_type field remains as string
    }
};
