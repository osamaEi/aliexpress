<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'created_by',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'code',
        'coupon_method',
        'direct_link',
        'is_global',
        'category_id',
        'sub_category_id',
        'valid_for',
        'discount_type',
        'discount_value',
        'commission_type',
        'commission_value',
        'max_uses',
        'max_uses_per_user',
        'used_count',
        'min_order_amount',
        'free_shipping',
        'exclude_discounted',
        'features',
        'terms',
        'commission_terms',
        'start_date',
        'end_date',
        'promo_images',
        'promo_video',
        'image',
        'is_active',
        'total_discount_given',
        'total_commission_earned',
    ];

    protected $casts = [
        'features' => 'array',
        'terms' => 'array',
        'commission_terms' => 'array',
        'promo_images' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'free_shipping' => 'boolean',
        'exclude_discounted' => 'boolean',
        'is_active' => 'boolean',
        'is_global' => 'boolean',
        'discount_value' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'total_discount_given' => 'decimal:2',
        'total_commission_earned' => 'decimal:2',
    ];

    /**
     * Get the store (distributor) that owns the coupon.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }

    /**
     * Get the admin who created the coupon.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all usages of this coupon.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Marketers who have activated this coupon.
     */
    public function marketers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_marketer', 'coupon_id', 'user_id')
            ->withPivot('tracking_code', 'status', 'earnings')
            ->withTimestamps();
    }

    /**
     * Specific products this coupon is scoped to. Empty = applies store-wide.
     */
    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product')->withTimestamps();
    }

    /**
     * Whether this coupon may be applied to the given product.
     * If the coupon is scoped to specific products, the product must be one of them.
     */
    public function appliesToProduct($productId): bool
    {
        if ($this->products()->count() === 0) {
            return true; // not scoped -> store-wide
        }
        return $this->products()->where('products.id', $productId)->exists();
    }

    /**
     * Main category this coupon targets.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Sub category this coupon targets.
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    /**
     * Check if coupon is currently valid (active and within date range).
     */
    public function isValid(): bool
    {
        $now = Carbon::now();
        return $this->is_active
            && $now->gte($this->start_date)
            && $now->lte($this->end_date)
            && ($this->max_uses === null || $this->used_count < $this->max_uses);
    }

    /**
     * Check if coupon is expired.
     */
    public function isExpired(): bool
    {
        return Carbon::now()->gt($this->end_date);
    }

    /**
     * Get remaining days until expiry.
     */
    public function getRemainingDaysAttribute(): int
    {
        $now = Carbon::now();
        if ($now->gt($this->end_date)) {
            return 0;
        }
        return $now->diffInDays($this->end_date);
    }

    /**
     * Get remaining uses.
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->max_uses === null) {
            return null;
        }
        return max(0, $this->max_uses - $this->used_count);
    }

    /**
     * Get title based on current locale.
     */
    public function getTitleAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
    }

    /**
     * Get description based on current locale.
     */
    public function getDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }

    /**
     * Calculate discount amount for a given order total.
     */
    public function calculateDiscount(float $orderTotal): float
    {
        if ($this->discount_type === 'percentage') {
            return ($orderTotal * $this->discount_value) / 100;
        }
        return min($this->discount_value, $orderTotal);
    }

    /**
     * Calculate commission amount for a given order total.
     */
    public function calculateCommission(float $orderTotal): float
    {
        if ($this->commission_type === 'percentage') {
            return ($orderTotal * $this->commission_value) / 100;
        }
        return $this->commission_value;
    }

    /**
     * Generate a unique coupon code.
     */
    public static function generateCode(int $length = 5): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Scope for active coupons.
     */
    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    /**
     * Scope for expired coupons.
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', Carbon::now());
    }

    /**
     * Scope for coupons by store.
     */
    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }
}
