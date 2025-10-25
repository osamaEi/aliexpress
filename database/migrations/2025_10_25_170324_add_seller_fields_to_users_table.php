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
            $table->string('full_name')->nullable()->after('name');
            $table->string('company_name')->nullable()->after('full_name');
            $table->string('country')->nullable()->after('company_name');
            $table->string('user_type')->default('customer')->after('email'); // customer, seller, admin
            $table->string('main_activity')->nullable()->after('user_type');
            $table->string('sub_activity')->nullable()->after('main_activity');
            $table->string('otp_code')->nullable()->after('remember_token');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->boolean('is_verified')->default(false)->after('otp_expires_at');
            $table->timestamp('verified_at')->nullable()->after('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'company_name',
                'country',
                'user_type',
                'main_activity',
                'sub_activity',
                'otp_code',
                'otp_expires_at',
                'is_verified',
                'verified_at'
            ]);
        });
    }
};
