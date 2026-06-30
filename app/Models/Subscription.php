<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price',
        'duration_days',
        'color',
        'max_products',
        'max_orders_per_month',
        'priority_support',
        'analytics_access',
        'bulk_import',
        'api_access',
        'commission_rate',
        'is_active',
        'sort_order',
        'role',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'priority_support' => 'boolean',
        'analytics_access' => 'boolean',
        'bulk_import' => 'boolean',
        'api_access' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get user subscriptions
     */
    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get active user subscriptions
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class)->where('status', 'active');
    }

    /**
     * Get localized name
     */
    public function getLocalizedNameAttribute(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar
            ? $this->name_ar
            : $this->name;
    }

    /**
     * Get localized description
     */
    public function getLocalizedDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'ar' && $this->description_ar
            ? $this->description_ar
            : $this->description;
    }

    /**
     * Scope to get active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Scope to subscriptions visible to a given user type
     */
    public function scopeVisibleTo($query, string $userType)
    {
        return $query->where(fn($q) => $q->where('role', $userType)->orWhere('role', 'both'));
    }

    /**
     * Get role badge color class
     */
    public function getRoleBadgeClassAttribute(): string
    {
        return match($this->role) {
            'seller'      => 'bg-label-primary',
            'distributor' => 'bg-label-success',
            'marketer'    => 'bg-label-info',
            default       => 'bg-label-secondary',
        };
    }

    /**
     * Get localized role label
     */
    public function getLocalizedRoleAttribute(): string
    {
        $locale = app()->getLocale();
        return match($this->role) {
            'seller'      => $locale === 'ar' ? 'بائع'    : 'Sellers',
            'distributor' => $locale === 'ar' ? 'متجر'    : 'Stores',
            'marketer'    => $locale === 'ar' ? 'مسوّق'   : 'Marketers',
            default       => $locale === 'ar' ? 'الجميع'  : 'Both',
        };
    }
}
