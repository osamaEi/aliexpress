<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'E-Commerce Store',
                'type' => 'text',
                'description' => 'The name of the website',
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
                'description' => 'Site logo image',
            ],
            [
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'image',
                'description' => 'Site favicon',
            ],
            [
                'key' => 'admin_email',
                'value' => 'admin@example.com',
                'type' => 'email',
                'description' => 'Administrator email address',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@example.com',
                'type' => 'email',
                'description' => 'Support email address',
            ],
            [
                'key' => 'admin_profit_percentage',
                'value' => '10',
                'type' => 'number',
                'description' => 'Admin profit percentage on each order',
            ],
            [
                'key' => 'admin_profit_type',
                'value' => 'percentage',
                'type' => 'text',
                'description' => 'Admin profit type: percentage or fixed',
            ],
            [
                'key' => 'admin_profit_fixed',
                'value' => '0',
                'type' => 'number',
                'description' => 'Admin fixed profit amount on each order',
            ],
            [
                'key' => 'currency',
                'value' => 'AED',
                'type' => 'text',
                'description' => 'Default currency',
            ],
            [
                'key' => 'site_phone',
                'value' => null,
                'type' => 'text',
                'description' => 'Site phone number',
            ],
            [
                'key' => 'site_address',
                'value' => null,
                'type' => 'text',
                'description' => 'Site address',
            ],
            [
                'key' => 'site_description',
                'value' => 'Your one-stop shop for all your needs',
                'type' => 'textarea',
                'description' => 'Site description for SEO',
            ],
            [
                'key' => 'site_keywords',
                'value' => 'ecommerce, shopping, online store',
                'type' => 'textarea',
                'description' => 'Site keywords for SEO',
            ],
            [
                'key' => 'primary_color',
                'value' => '#666cff',
                'type' => 'color',
                'description' => 'Primary color for the website theme',
            ],
            [
                'key' => 'primary_light_color',
                'value' => '#e7e7ff',
                'type' => 'color',
                'description' => 'Light version of primary color for backgrounds and subtle elements',
            ],
            [
                'key' => 'theme_style',
                'value' => 'light',
                'type' => 'select',
                'description' => 'Theme style: light or dark',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
