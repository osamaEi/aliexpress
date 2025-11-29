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
                'value' => '#561C04',
                'type' => 'color',
                'description' => 'Primary color for the website theme',
            ],
            [
                'key' => 'primary_light_color',
                'value' => '#F5E6D3',
                'type' => 'color',
                'description' => 'Light version of primary color for backgrounds and subtle elements',
            ],
            [
                'key' => 'btn_primary_hover_color',
                'value' => '#4a1603',
                'type' => 'color',
                'description' => 'Hover color for primary buttons',
            ],
            [
                'key' => 'btn_primary_active_color',
                'value' => '#3d1202',
                'type' => 'color',
                'description' => 'Active color for primary buttons',
            ],
            // Secondary colors
            [
                'key' => 'secondary_color',
                'value' => '#6d788d',
                'type' => 'color',
                'description' => 'Secondary color for the website theme',
            ],
            [
                'key' => 'btn_secondary_hover_color',
                'value' => '#5a6376',
                'type' => 'color',
                'description' => 'Hover color for secondary buttons',
            ],
            [
                'key' => 'btn_secondary_active_color',
                'value' => '#4a5365',
                'type' => 'color',
                'description' => 'Active color for secondary buttons',
            ],
            // Success colors (already in migration, adding to seeder for completeness)
            [
                'key' => 'btn_success_color',
                'value' => '#ff6f00',
                'type' => 'color',
                'description' => 'Default color for success buttons',
            ],
            [
                'key' => 'btn_success_hover_color',
                'value' => '#e56300',
                'type' => 'color',
                'description' => 'Hover color for success buttons',
            ],
            [
                'key' => 'btn_success_active_color',
                'value' => '#cc5700',
                'type' => 'color',
                'description' => 'Active color for success buttons',
            ],
            // Info colors
            [
                'key' => 'btn_info_color',
                'value' => '#000000',
                'type' => 'color',
                'description' => 'Default color for info buttons',
            ],
            [
                'key' => 'btn_info_hover_color',
                'value' => '#333333',
                'type' => 'color',
                'description' => 'Hover color for info buttons',
            ],
            [
                'key' => 'btn_info_active_color',
                'value' => '#1a1a1a',
                'type' => 'color',
                'description' => 'Active color for info buttons',
            ],
            // Warning colors (already in migration, adding to seeder for completeness)
            [
                'key' => 'btn_warning_color',
                'value' => '#fdb528',
                'type' => 'color',
                'description' => 'Default color for warning buttons',
            ],
            [
                'key' => 'btn_warning_hover_color',
                'value' => '#e0a800',
                'type' => 'color',
                'description' => 'Hover color for warning buttons',
            ],
            [
                'key' => 'btn_warning_active_color',
                'value' => '#d39e00',
                'type' => 'color',
                'description' => 'Active color for warning buttons',
            ],
            // Danger colors (already in migration, adding to seeder for completeness)
            [
                'key' => 'btn_danger_color',
                'value' => '#ff4d49',
                'type' => 'color',
                'description' => 'Default color for danger buttons',
            ],
            [
                'key' => 'btn_danger_hover_color',
                'value' => '#e63946',
                'type' => 'color',
                'description' => 'Hover color for danger buttons',
            ],
            [
                'key' => 'btn_danger_active_color',
                'value' => '#cc2936',
                'type' => 'color',
                'description' => 'Active color for danger buttons',
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
