<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'description',
        'aliexpress_category_id',
        'image',
        'photo',
        'parent_id',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the products in this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include root categories.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get seller profit settings for this category
     */
    public function sellerProfits(): HasMany
    {
        return $this->hasMany(SellerSubcategoryProfit::class);
    }

    /**
     * Get admin profit setting for this category
     */
    public function adminProfit()
    {
        return $this->hasOne(AdminCategoryProfit::class);
    }

    /**
     * Get the admin profit amount for this category (with parent inheritance)
     */
    public function getAdminProfitAmount(): float
    {
        return AdminCategoryProfit::getProfitForCategory($this->id);
    }

    /**
     * Check if this is a subcategory
     */
    public function isSubcategory(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get all subcategories (if this is a parent category)
     */
    public function getSubcategories()
    {
        return $this->children()->active()->get();
    }
}
