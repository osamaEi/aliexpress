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
     * @return float
     */
    public static function getProfitForCategory($categoryId): float
    {
        // Try to find profit for this specific category
        $profit = self::where('category_id', $categoryId)
            ->where('is_active', true)
            ->first();

        if ($profit) {
            return $profit->profit_amount;
        }

        // If not found and this is a subcategory, check parent category
        $category = Category::find($categoryId);
        if ($category && $category->parent_id) {
            $parentProfit = self::where('category_id', $category->parent_id)
                ->where('is_active', true)
                ->first();

            if ($parentProfit) {
                return $parentProfit->profit_amount;
            }
        }

        // No profit configured
        return 0;
    }

    /**
     * Scope to filter active profit settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
