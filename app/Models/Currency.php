<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get active currencies
     */
    public static function active()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get default currency
     */
    public static function default()
    {
        return self::where('is_default', true)->first() ?? self::where('code', 'USD')->first();
    }

    /**
     * Convert amount from USD to this currency
     */
    public function convertFrom($amount, $fromCurrencyCode = 'USD')
    {
        if ($fromCurrencyCode === $this->code) {
            return $amount;
        }

        $fromCurrency = self::where('code', $fromCurrencyCode)->first();
        if (!$fromCurrency) {
            return $amount;
        }

        // Convert to USD first, then to target currency
        $usdAmount = $amount / $fromCurrency->exchange_rate;
        return $usdAmount * $this->exchange_rate;
    }

    /**
     * Format amount with currency symbol
     */
    public function format($amount)
    {
        return $this->symbol . ' ' . number_format($amount, 2);
    }
}
