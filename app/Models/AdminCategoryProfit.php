<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminCategoryProfit extends Model
{
    protected $fillable = [
        'category_id',
        'profit_amount',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'profit_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category for this profit setting
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get admin profit for a specific category (with inheritance from parent)
     *
     * @param int $categoryId
     * @param string|null $targetCurrency The currency to convert to
     * @return float
     */
    public static function getProfitForCategory($categoryId, ?string $targetCurrency = null): float
    {
        // Try to find profit for this specific category
        $profit = self::where('category_id', $categoryId)
            ->where('is_active', true)
            ->first();

        if (!$profit) {
            // If not found and this is a subcategory, check parent category
            $category = Category::find($categoryId);
            if ($category && $category->parent_id) {
                $profit = self::where('category_id', $category->parent_id)
                    ->where('is_active', true)
                    ->first();
            }
        }

        if (!$profit) {
            return 0;
        }

        // Convert currency if needed
        $profitValue = $profit->profit_amount;
        $savedCurrency = $profit->currency ?? 'AED';

        if ($targetCurrency && $savedCurrency !== $targetCurrency) {
            $targetCurrencyModel = Currency::where('code', $targetCurrency)->first();
            $savedCurrencyModel = Currency::where('code', $savedCurrency)->first();

            if ($targetCurrencyModel && $savedCurrencyModel) {
                // Convert from saved currency to target currency via USD
                $usdAmount = $profitValue / $savedCurrencyModel->exchange_rate;
                $profitValue = $usdAmount * $targetCurrencyModel->exchange_rate;
            }
        }

        return $profitValue;
    }

    /**
     * Calculate profit amount with currency conversion
     *
     * @param string|null $targetCurrency The currency to convert to
     * @return float
     */
    public function calculateProfit(?string $targetCurrency = null): float
    {
        if (!$this->is_active) {
            return 0;
        }

        $profitValue = $this->profit_amount;
        $savedCurrency = $this->currency ?? 'AED';

        if ($targetCurrency && $savedCurrency !== $targetCurrency) {
            $targetCurrencyModel = Currency::where('code', $targetCurrency)->first();
            $savedCurrencyModel = Currency::where('code', $savedCurrency)->first();

            if ($targetCurrencyModel && $savedCurrencyModel) {
                // Convert from saved currency to target currency via USD
                $usdAmount = $profitValue / $savedCurrencyModel->exchange_rate;
                $profitValue = $usdAmount * $targetCurrencyModel->exchange_rate;
            }
        }

        return $profitValue;
    }

    /**
     * Scope to filter active profit settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
