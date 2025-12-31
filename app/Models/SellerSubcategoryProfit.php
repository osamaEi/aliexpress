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
     * @param string|null $targetCurrency The currency of the product/price
     * @return float
     */
    public function calculateProfit(float $basePrice, ?string $targetCurrency = null): float
    {
        if (!$this->is_active) {
            return 0;
        }

        if ($this->profit_type === 'percentage') {
            return $basePrice * ($this->profit_value / 100);
        }

        // For fixed amount, convert from session currency to target currency if needed
        $profitValue = $this->profit_value;
        $sessionCurrency = session('currency_code', 'USD');

        if ($targetCurrency && $sessionCurrency !== $targetCurrency) {
            $targetCurrencyModel = Currency::where('code', $targetCurrency)->first();
            $sessionCurrencyModel = Currency::where('code', $sessionCurrency)->first();

            if ($targetCurrencyModel && $sessionCurrencyModel) {
                // Convert fixed amount from session currency to target currency
                // First convert to USD, then to target currency
                $usdAmount = $profitValue / $sessionCurrencyModel->exchange_rate;
                $profitValue = $usdAmount * $targetCurrencyModel->exchange_rate;
            }
        }

        return $profitValue;
    }

    /**
     * Calculate the final price including profit
     *
     * @param float $basePrice
     * @param string|null $targetCurrency The currency of the product/price
     * @return float
     */
    public function calculateFinalPrice(float $basePrice, ?string $targetCurrency = null): float
    {
        return $basePrice + $this->calculateProfit($basePrice, $targetCurrency);
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
