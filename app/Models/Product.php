<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
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
        'is_active',
        'category_id',
        'aliexpress_id',
        'aliexpress_url',
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
     */
    public function isAliexpressProduct(): bool
    {
        return !empty($this->aliexpress_id);
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
}
