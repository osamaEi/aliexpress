<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember("setting_{$key}", 3600, function () use ($key) {
            return self::where('key', $key)->first();
        });

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'text', string $description = null): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );

        Cache::forget("setting_{$key}");

        return $setting;
    }

    /**
     * Remove a setting
     */
    public static function remove(string $key): bool
    {
        Cache::forget("setting_{$key}");
        return self::where('key', $key)->delete();
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('all_settings', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('all_settings');
        self::chunk(100, function ($settings) {
            foreach ($settings as $setting) {
                Cache::forget("setting_{$setting->key}");
            }
        });
    }
}
