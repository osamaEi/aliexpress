<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'description',
        'description_ar',
        'short_description',
        'short_description_ar',
        'price',
        'currency',
        'original_price',
        'seller_amount',
        'admin_amount',
        'compare_price',
        'cost',
        'sku',
        'stock_quantity',
        'track_inventory',
        'has_variations',
        'is_active',
        'category_id',
        'photo',
        'video',
        'aliexpress_id',
        'aliexpress_url',
        'country_code',
        'aliexpress_price',
        'aliexpress_product_status',
        'aliexpress_variants',
        'aliexpress_data',
        'images',
        'specifications',
        'shipping_cost',
        'processing_time_days',
        'supplier_profit_margin',
        'last_synced_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'admin_amount' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'aliexpress_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'supplier_profit_margin' => 'decimal:2',
        'stock_quantity' => 'integer',
        'processing_time_days' => 'integer',
        'track_inventory' => 'boolean',
        'has_variations' => 'boolean',
        'is_active' => 'boolean',
        'aliexpress_variants' => 'array',
        'aliexpress_data' => 'array',
        'images' => 'array',
        'specifications' => 'array',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the additional images for the product.
     */
    public function additionalImages()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the variations for the product.
     */
    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    /**
     * Get total stock from variations.
     */
    public function getTotalVariationStockAttribute()
    {
        return $this->variations()->sum('quantity');
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include products from AliExpress.
     */
    public function scopeFromAliexpress($query)
    {
        return $query->whereNotNull('aliexpress_id');
    }

    /**
     * Scope a query to only include products in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Check if product is from AliExpress.
     * Real AliExpress IDs are long numeric strings (typically 13+ digits)
     */
    public function isAliexpressProduct(): bool
    {
        return !empty($this->aliexpress_id) && strlen((string)$this->aliexpress_id) >= 10;
    }

    /**
     * Get the profit margin.
     */
    public function getProfitMargin(): float
    {
        if ($this->cost && $this->price) {
            return (($this->price - $this->cost) / $this->price) * 100;
        }
        return 0;
    }

    /**
     * Calculate selling price based on AliExpress price and margin.
     */
    public function calculateSellingPrice(): float
    {
        if ($this->aliexpress_price && $this->supplier_profit_margin) {
            $cost = $this->aliexpress_price + $this->shipping_cost;
            return $cost * (1 + ($this->supplier_profit_margin / 100));
        }
        return $this->price ?? 0;
    }

    /**
     * Get the primary image URL.
     */
    public function getPrimaryImage(): ?string
    {
        return $this->images[0] ?? null;
    }

    /**
     * Get the localized name based on current app locale.
     */
    public function getLocalizedNameAttribute(): string
    {
        return app()->getLocale() === 'ar' && !empty($this->name_ar)
            ? $this->name_ar
            : $this->name;
    }

    /**
     * Get the localized description based on current app locale.
     */
    public function getLocalizedDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'ar' && !empty($this->description_ar)
            ? $this->description_ar
            : $this->description;
    }

    /**
     * Get the localized short description based on current app locale.
     */
    public function getLocalizedShortDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'ar' && !empty($this->short_description_ar)
            ? $this->short_description_ar
            : $this->short_description;
    }

    /**
     * Get the users (distributors/sellers) who have this product assigned.
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'product_user')
            ->withPivot(['aliexpress_product_id', 'status', 'is_choice'])
            ->withTimestamps();
    }

    /**
     * Scope a query to only include products from distributors in a specific country.
     */
    public function scopeFromCountry($query, string $countryCode)
    {
        return $query->whereHas('assignedUsers', function ($q) use ($countryCode) {
            $q->where('country', $countryCode)
              ->where('user_type', 'distributor');
        });
    }

    /**
     * Get the distributor that owns this product (first assigned distributor user).
     * Returns null for AliExpress/China products with no distributor.
     */
    public function distributorOwner(): ?User
    {
        return $this->assignedUsers()
            ->where('user_type', 'distributor')
            ->orderBy('product_user.created_at')
            ->first();
    }
}
