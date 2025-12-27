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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('users')->onDelete('cascade'); // The distributor/store
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Admin who created it

            // Coupon titles and descriptions
            $table->string('title_ar');
            $table->string('title_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();

            // Coupon code
            $table->string('code', 10)->unique();

            // Valid for: website, store, both
            $table->enum('valid_for', ['website', 'store', 'both'])->default('both');

            // Discount type and value
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 10, 2);

            // Commission type and value (for affiliate)
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('commission_value', 10, 2);

            // Usage limits
            $table->integer('max_uses')->nullable(); // Total max uses
            $table->integer('max_uses_per_user')->nullable(); // Max uses per user
            $table->integer('used_count')->default(0); // Current usage count

            // Order requirements
            $table->decimal('min_order_amount', 10, 2)->nullable();

            // Additional options
            $table->boolean('free_shipping')->default(false);
            $table->boolean('exclude_discounted')->default(false); // Don't apply to discounted products

            // Features and terms (JSON arrays with ar/en)
            $table->json('features')->nullable(); // Discount features
            $table->json('terms')->nullable(); // Usage terms (shown to customer)
            $table->json('commission_terms')->nullable(); // Commission terms (shown to affiliate only)

            // Dates
            $table->date('start_date');
            $table->date('end_date');

            // Media
            $table->json('promo_images')->nullable(); // Up to 5 images
            $table->string('promo_video')->nullable(); // 1 video

            // Status
            $table->boolean('is_active')->default(true);

            // Statistics
            $table->decimal('total_discount_given', 12, 2)->default(0);
            $table->decimal('total_commission_earned', 12, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('store_id');
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });

        // Coupon usage tracking
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Customer who used it
            $table->foreignId('order_id')->nullable(); // Related order

            $table->decimal('order_amount', 12, 2); // Order amount before discount
            $table->decimal('discount_amount', 12, 2); // Discount applied
            $table->decimal('commission_amount', 12, 2); // Commission earned

            $table->enum('commission_status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');

            $table->timestamps();

            $table->index('coupon_id');
            $table->index('user_id');
            $table->index('commission_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
