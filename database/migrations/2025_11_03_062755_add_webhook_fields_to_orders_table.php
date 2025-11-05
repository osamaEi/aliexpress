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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('webhook_last_received_at')->nullable()->after('aliexpress_response');
            $table->string('webhook_last_event')->nullable()->after('webhook_last_received_at');
            $table->unsignedInteger('webhook_received_count')->default(0)->after('webhook_last_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['webhook_last_received_at', 'webhook_last_event', 'webhook_received_count']);
        });
    }
};
