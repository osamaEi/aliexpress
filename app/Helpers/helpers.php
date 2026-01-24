<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

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
     * @param string|null $currency Currency code (defaults to session currency)
     * @param bool $asHtml Return as HTML (with SVG icon for AED)
     * @return string
     */
    function currency_symbol(?string $currency = null, bool $asHtml = false, int $iconWidth = 20, int $iconHeight = 20, string $iconClass = ''): string
    {
        // Get currency from session if not provided
        $currency = $currency ?? session('currency_code', 'AED');

        // Try to get symbol from session first (faster)
        if ($currency === session('currency_code')) {
            $symbol = session('currency_symbol');
            if ($symbol) {
                // For AED, use SVG icon if requested
                if ($currency === 'AED' && $asHtml) {
                    try {
                        return view('components.currency-icon', [
                            'width' => $iconWidth,
                            'height' => $iconHeight,
                            'class' => $iconClass,
                        ])->render();
                    } catch (\Throwable $e) {
                        return $symbol;
                    }
                }
                return $symbol;
            }
        }

        // Fallback: get from database
        try {
            $currencyModel = \App\Models\Currency::where('code', $currency)->first();
            if ($currencyModel && $currencyModel->symbol) {
                return $currencyModel->symbol;
            }
        } catch (\Throwable $e) {
            // Continue to static fallback
        }

        // Last resort: static symbols
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

if (!function_exists('convert_price')) {
    /**
     * Convert price to current currency using exchange rate
     *
     * @param float $amount The amount in base currency (USD)
     * @param string|null $targetCurrency Target currency code (defaults to session currency)
     * @return float
     */
    function convert_price(float $amount, ?string $targetCurrency = null): float
    {
        // Get current currency from session or use provided target currency
        $currencyCode = $targetCurrency ?? session('currency_code', 'USD');

        // If currency is USD (base currency), no conversion needed
        if ($currencyCode === 'USD') {
            return $amount;
        }

        // Get exchange rate from session
        $exchangeRate = session('currency_rate', 1.0);

        // Convert: base amount * exchange rate
        return $amount * $exchangeRate;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format amount with currency symbol (with automatic conversion)
     *
     * @param float $amount The amount in base currency (AED)
     * @param string|null $currency Currency code (defaults to session currency)
     * @param int $decimals Number of decimal places
     * @param bool $useIcon Use SVG icon for AED currency
     * @param bool $convert Whether to convert the price (default: true)
     * @return string
     */
    function format_currency(float $amount, ?string $currency = null, int $decimals = 2, bool $useIcon = true, bool $convert = true): string
    {
        // Get currency from session if not provided
        $currency = $currency ?? session('currency_code', 'AED');

        // Convert price if needed
        if ($convert) {
            $amount = convert_price($amount, $currency);
        }

        $formattedAmount = number_format($amount, $decimals);
        $symbol = currency_symbol($currency, $useIcon);
        // If symbol contains HTML (SVG), return an HtmlString so Blade won't double-escape
        $containsHtml = is_string($symbol) && stripos($symbol, '<svg') !== false;

        if ($containsHtml) {
            if (app()->getLocale() == 'ar') {
                return new HtmlString($formattedAmount . ' ' . $symbol);
            }

            return new HtmlString($symbol . ' ' . $formattedAmount);
        }

        // For Arabic, put symbol after amount; for English, before
        return app()->getLocale() == 'ar'
            ? $formattedAmount . ' ' . $symbol
            : $symbol . ' ' . $formattedAmount;
    }
}
