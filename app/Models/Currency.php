<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'name_ar',
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
     * Get localized name attribute
     */
    public function getLocalizedNameAttribute()
    {
        if (app()->getLocale() == 'ar' && !empty($this->name_ar)) {
            return $this->name_ar;
        }
        return $this->name;
    }

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
        // AED is the system base/default currency; fall back to it if no default flag is set
        return self::where('is_default', true)->first() ?? self::where('code', 'AED')->first();
    }

    /**
     * Convert amount from another currency to this currency
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
     * Convert amount from this currency to another currency
     */
    public function convertTo($amount, $toCurrencyCode = 'USD')
    {
        if ($toCurrencyCode === $this->code) {
            return $amount;
        }

        $toCurrency = self::where('code', $toCurrencyCode)->first();
        if (!$toCurrency) {
            return $amount;
        }

        // Convert to USD first, then to target currency
        $usdAmount = $amount / $this->exchange_rate;
        return $usdAmount * $toCurrency->exchange_rate;
    }

    /**
     * Format amount with currency symbol
     */
    public function format($amount, $useIcon = true)
    {
        $formattedAmount = number_format($amount, 2);

        // Determine symbol/html for this currency
        if (in_array($this->code, ['AED', 'SAR', 'USD']) && $useIcon) {
            // Prefer rendering the Blade component for currency icons (safely)
            try {
                $iconHtml = view('components.currency-icon', [
                    'width' => 20,
                    'height' => 20,
                    'currency' => $this->code
                ])->render();
            } catch (\Throwable $e) {
                // Fallback to symbol
                $iconHtml = $this->symbol;
            }

            // Place icon before or after amount depending on locale
            if (app()->getLocale() == 'ar') {
                return new HtmlString($formattedAmount . ' ' . $iconHtml);
            }

            return new HtmlString($iconHtml . ' ' . $formattedAmount);
        }

        // Other currencies: place symbol based on locale
        if (app()->getLocale() == 'ar') {
            return $formattedAmount . ' ' . $this->symbol;
        }

        return $this->symbol . ' ' . $formattedAmount;
    }
}
