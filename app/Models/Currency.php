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
    public function format($amount, $useIcon = true)
    {
        $formattedAmount = number_format($amount, 2);

        // For AED, use SVG icon
        if ($this->code === 'AED' && $useIcon) {
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" class="inline-block" style="vertical-align: middle;"><path d="M8 7V17H12C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7H8Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 11H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 13H12.5H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
            return $icon . ' ' . $formattedAmount;
        }

        return $this->symbol . ' ' . $formattedAmount;
    }
}
