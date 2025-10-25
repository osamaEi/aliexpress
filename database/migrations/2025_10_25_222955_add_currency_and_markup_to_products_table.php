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
        Schema::table('products', function (Blueprint $table) {
            $table->string('currency', 3)->default('AED')->after('price');
            $table->decimal('original_price', 10, 2)->nullable()->after('currency')->comment('Original price from AliExpress');
            $table->decimal('markup_amount', 10, 2)->default(0)->after('original_price')->comment('Amount added to original price');
            $table->decimal('markup_percentage', 5, 2)->default(0)->after('markup_amount')->comment('Percentage markup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['currency', 'original_price', 'markup_amount', 'markup_percentage']);
        });
    }
};
