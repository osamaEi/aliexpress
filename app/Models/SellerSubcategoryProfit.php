<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerSubcategoryProfit extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'profit_type',
        'profit_value',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'profit_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the seller (user) that owns this profit setting
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category (subcategory) for this profit setting
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calculate the profit amount for a given base price
     *
     * @param float $basePrice
     * @return float
     */
    public function calculateProfit(float $basePrice): float
    {
        if (!$this->is_active) {
            return 0;
        }

        if ($this->profit_type === 'percentage') {
            return $basePrice * ($this->profit_value / 100);
        }

        return $this->profit_value;
    }

    /**
     * Calculate the final price including profit
     *
     * @param float $basePrice
     * @return float
     */
    public function calculateFinalPrice(float $basePrice): float
    {
        return $basePrice + $this->calculateProfit($basePrice);
    }

    /**
     * Scope to filter active profit settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by seller
     */
    public function scopeForSeller($query, $sellerId)
    {
        return $query->where('user_id', $sellerId);
    }

    /**
     * Scope to filter by category
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
