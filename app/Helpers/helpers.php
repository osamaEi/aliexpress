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
