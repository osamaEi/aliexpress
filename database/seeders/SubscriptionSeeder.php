<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subscriptions = [
            [
                'name' => 'Bronze',
                'name_ar' => 'برونزي',
                'description' => 'Perfect for beginners starting their dropshipping journey',
                'description_ar' => 'مثالي للمبتدئين الذين يبدؤون رحلتهم في الدروبشيبينغ',
                'price' => 29.99,
                'duration_days' => 30,
                'color' => '#CD7F32',
                'max_products' => 50,
                'max_orders_per_month' => 100,
                'priority_support' => false,
                'analytics_access' => false,
                'bulk_import' => false,
                'api_access' => false,
                'commission_rate' => 15.00,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Silver',
                'name_ar' => 'فضي',
                'description' => 'Great for growing businesses with more products',
                'description_ar' => 'رائع للأعمال النامية مع المزيد من المنتجات',
                'price' => 79.99,
                'duration_days' => 30,
                'color' => '#C0C0C0',
                'max_products' => 200,
                'max_orders_per_month' => 500,
                'priority_support' => true,
                'analytics_access' => true,
                'bulk_import' => true,
                'api_access' => false,
                'commission_rate' => 10.00,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Gold',
                'name_ar' => 'ذهبي',
                'description' => 'Premium plan for serious entrepreneurs with unlimited potential',
                'description_ar' => 'خطة متميزة لرواد الأعمال الجادين مع إمكانات غير محدودة',
                'price' => 149.99,
                'duration_days' => 30,
                'color' => '#FFD700',
                'max_products' => 1000,
                'max_orders_per_month' => 2000,
                'priority_support' => true,
                'analytics_access' => true,
                'bulk_import' => true,
                'api_access' => true,
                'commission_rate' => 5.00,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($subscriptions as $subscription) {
            Subscription::create($subscription);
        }
    }
}
