<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // Coupon delivery method: a typed code, or a direct affiliate link
            $table->enum('coupon_method', ['code', 'link'])->default('code')->after('code');
            $table->string('direct_link')->nullable()->after('coupon_method');

            // Whether this coupon belongs to an admin "Global Store"
            $table->boolean('is_global')->default(false)->after('direct_link');

            // Main + sub category targeting
            $table->foreignId('category_id')->nullable()->after('is_global')
                  ->constrained('categories')->nullOnDelete();
            $table->foreignId('sub_category_id')->nullable()->after('category_id')
                  ->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('sub_category_id');
            $table->dropColumn(['coupon_method', 'direct_link', 'is_global']);
        });
    }
};
