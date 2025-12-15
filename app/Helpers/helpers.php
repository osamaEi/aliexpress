<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

if (!function_exists('setting')) {
    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('setting_image')) {
    /**
     * Get a setting image URL
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    function setting_image(string $key, ?string $default = null): ?string
    {
        $value = Setting::get($key);

        if (!$value) {
            return $default;
        }

        return Storage::disk('public')->exists($value)
            ? asset('storage/' . $value)
            : $default;
    }
}

if (!function_exists('admin_profit')) {
    /**
     * Calculate admin profit for a given amount
     *
     * @param float $amount
     * @return float
     */
    function admin_profit(float $amount): float
    {
        $profitType = setting('admin_profit_type', 'percentage');

        if ($profitType === 'percentage') {
            $percentage = (float) setting('admin_profit_percentage', 10);
            return $amount * ($percentage / 100);
        } else {
            return (float) setting('admin_profit_fixed', 0);
        }
    }
}

if (!function_exists('calculate_price_with_profit')) {
    /**
     * Calculate price including admin profit
     *
     * @param float $basePrice
     * @return float
     */
    function calculate_price_with_profit(float $basePrice): float
    {
        return $basePrice + admin_profit($basePrice);
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get currency symbol based on locale
     *
     * @param string $currency
     * @param bool $asHtml Return as HTML (with SVG icon for AED)
     * @return string
     */
    function currency_symbol(string $currency = 'AED', bool $asHtml = false): string
    {
        // For AED, use SVG icon
        if ($currency === 'AED' && $asHtml) {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" class="inline-block" style="vertical-align: middle;"><path d="M8 7V17H12C14.8 17 17 14.8 17 12C17 9.2 14.8 7 12 7H8Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 11H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.5 13H12.5H18.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
        }

        $symbols = [
            'AED' => app()->getLocale() == 'ar' ? 'د.إ' : 'AED',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'SAR' => app()->getLocale() == 'ar' ? 'ر.س' : 'SAR',
        ];

        return $symbols[$currency] ?? $currency;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format amount with currency symbol
     *
     * @param float $amount
     * @param string $currency
     * @param int $decimals
     * @param bool $useIcon Use SVG icon for AED currency
     * @return string
     */
    function format_currency(float $amount, string $currency = 'AED', int $decimals = 2, bool $useIcon = true): string
    {
        $formattedAmount = number_format($amount, $decimals);
        $symbol = currency_symbol($currency, $useIcon);

        // For Arabic, put symbol after amount; for English, before
        return app()->getLocale() == 'ar'
            ? $formattedAmount . ' ' . $symbol
            : $symbol . ' ' . $formattedAmount;
    }
}
