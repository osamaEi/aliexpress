<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A marketer "activates" a coupon to promote it. Each activation gets a unique
     * tracking code/link; usage through it credits the marketer. Mirrors product_user.
     */
    public function up(): void
    {
        Schema::create('coupon_marketer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // the marketer
            $table->string('tracking_code')->nullable()->unique(); // generated on approval
            $table->enum('status', ['pending', 'active', 'rejected'])->default('pending');
            $table->decimal('earnings', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['coupon_id', 'user_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_marketer');
    }
};
