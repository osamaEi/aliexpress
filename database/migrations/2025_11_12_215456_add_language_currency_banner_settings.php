<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new settings
        $settings = [
            [
                'key' => 'site_language',
                'value' => 'ar',
                'type' => 'select',
                'description' => 'Default language for the website (ar = Arabic, en = English)',
            ],
            [
                'key' => 'site_currency',
                'value' => 'AED',
                'type' => 'select',
                'description' => 'Default currency for the website',
            ],
            [
                'key' => 'site_banner',
                'value' => null,
                'type' => 'image',
                'description' => 'Main banner image for the home page',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the settings
        Setting::whereIn('key', ['site_language', 'site_currency', 'site_banner'])->delete();
    }
};
