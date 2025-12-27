<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'flag',
        'phone_code',
        'currency_code',
        'is_active',
        'is_source',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_source' => 'boolean',
    ];

    /**
     * Get localized name
     */
    public function getLocalizedNameAttribute()
    {
        if (app()->getLocale() == 'ar' && !empty($this->name_ar)) {
            return $this->name_ar;
        }
        return $this->name;
    }

    /**
     * Scope for active countries
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for source countries (UAE, Saudi, China, etc.)
     */
    public function scopeSource($query)
    {
        return $query->where('is_source', true);
    }

    /**
     * Get users from this country
     */
    public function users()
    {
        return $this->hasMany(User::class, 'country', 'code');
    }

    /**
     * Get flag emoji or image
     */
    public function getFlagDisplayAttribute()
    {
        if ($this->flag) {
            return $this->flag;
        }
        // Default flags for known countries
        $flags = [
            'AE' => '🇦🇪',
            'SA' => '🇸🇦',
            'CN' => '🇨🇳',
            'US' => '🇺🇸',
            'UK' => '🇬🇧',
            'EG' => '🇪🇬',
            'KW' => '🇰🇼',
            'BH' => '🇧🇭',
            'QA' => '🇶🇦',
            'OM' => '🇴🇲',
        ];
        return $flags[$this->code] ?? '🏳️';
    }
}
