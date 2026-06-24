<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorShippingRate extends Model
{
    protected $fillable = [
        'distributor_id',
        'country_code',
        'city_id',
        'district_id',
        'shipping_cost',
        'currency',
        'delivery_days_min',
        'delivery_days_max',
        'is_active',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope label: how specific this rate is (district > city > country).
     * Higher = more specific = preferred when resolving the shipping cost.
     */
    public function getSpecificityAttribute(): int
    {
        if ($this->district_id) return 3;
        if ($this->city_id) return 2;
        return 1;
    }

    /**
     * Human-readable scope label of where this rate applies.
     */
    public function getScopeLabelAttribute(): string
    {
        $ar = app()->getLocale() === 'ar';
        if ($this->district_id && $this->district) {
            return $this->district->localized_name;
        }
        if ($this->city_id && $this->city) {
            return $this->city->localized_name . ($ar ? ' (كل الأحياء)' : ' (all districts)');
        }
        return $ar ? 'كل الدولة' : 'Whole country';
    }

    /**
     * Resolve the best-matching active shipping rate for a distributor at a given
     * country / city / district. Most specific match wins:
     *   exact district  >  city (any district)  >  country (any city).
     */
    public static function resolveFor(
        int $distributorId,
        string $countryCode,
        ?int $cityId = null,
        ?int $districtId = null
    ): ?self {
        $rates = static::query()
            ->where('distributor_id', $distributorId)
            ->where('country_code', $countryCode)
            ->where('is_active', true)
            ->where(function ($q) use ($cityId, $districtId) {
                // country-level (no city)
                $q->whereNull('city_id');
                // city-level matching the requested city, any district
                if ($cityId) {
                    $q->orWhere(function ($q2) use ($cityId) {
                        $q2->where('city_id', $cityId)->whereNull('district_id');
                    });
                }
                // exact district match
                if ($districtId) {
                    $q->orWhere(function ($q2) use ($cityId, $districtId) {
                        $q2->where('city_id', $cityId)->where('district_id', $districtId);
                    });
                }
            })
            ->get();

        if ($rates->isEmpty()) {
            return null;
        }

        // Pick the most specific match
        return $rates->sortByDesc('specificity')->first();
    }
}
