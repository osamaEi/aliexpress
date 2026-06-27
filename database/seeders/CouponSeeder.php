<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CouponSeeder extends Seeder
{
    /**
     * Seed demo coupons: a few admin "Global Store" coupons (code + direct link)
     * and a couple of regular distributor coupons. Idempotent by coupon title.
     */
    public function run(): void
    {
        // Admin account (creator) — fall back to first user if no admin exists.
        $admin = User::where('user_type', 'admin')->first() ?? User::first();
        $adminId = $admin?->id;

        // Ensure a couple of Global Stores exist to own the global coupons.
        $globalStores = $this->ensureGlobalStores();

        // A category to attach (optional) — first root category if any.
        $categoryId = \App\Models\Category::whereNull('parent_id')->value('id');

        $now = now();

        // ── Global Stores coupons (admin-owned) ──
        $globalCoupons = [
            [
                'title_ar' => 'خصم 20% - سوق الإلكترونيات',
                'title_en' => '20% Off - Electronics Market',
                'coupon_method' => 'code',
                'code' => 'ELEC20',
                'discount_type' => 'percentage', 'discount_value' => 20,
                'commission_type' => 'percentage', 'commission_value' => 8,
            ],
            [
                'title_ar' => 'خصم 50 ر.س - أزياء عالمية',
                'title_en' => 'SAR 50 Off - Global Fashion',
                'coupon_method' => 'code',
                'code' => 'FASHION50',
                'discount_type' => 'fixed', 'discount_value' => 50,
                'commission_type' => 'fixed', 'commission_value' => 10,
            ],
            [
                'title_ar' => 'عرض الرابط المباشر - متجر المنزل',
                'title_en' => 'Direct Link Deal - Home Store',
                'coupon_method' => 'link',
                'direct_link' => 'https://example.com/home-store-offer',
                'discount_type' => 'percentage', 'discount_value' => 15,
                'commission_type' => 'percentage', 'commission_value' => 6,
            ],
            [
                'title_ar' => 'خصم 30% - مستحضرات التجميل',
                'title_en' => '30% Off - Beauty & Care',
                'coupon_method' => 'code',
                'code' => 'BEAUTY30',
                'discount_type' => 'percentage', 'discount_value' => 30,
                'commission_type' => 'percentage', 'commission_value' => 12,
            ],
        ];

        foreach ($globalCoupons as $i => $c) {
            $store = $globalStores[$i % count($globalStores)];

            Coupon::firstOrCreate(
                ['title_en' => $c['title_en']],
                array_merge([
                    'store_id' => $store->id,
                    'created_by' => $adminId,
                    'description_ar' => 'كوبون تجريبي للمتاجر العالمية.',
                    'description_en' => 'Demo coupon for Global Stores.',
                    'is_global' => true,
                    'category_id' => $categoryId,
                    'valid_for' => 'both',
                    'max_uses' => 1000,
                    'max_uses_per_user' => 1,
                    'used_count' => 0,
                    'min_order_amount' => 0,
                    'free_shipping' => false,
                    'exclude_discounted' => false,
                    'start_date' => $now->toDateString(),
                    'end_date' => $now->copy()->addMonths(3)->toDateString(),
                    'is_active' => true,
                ], $c)
            );
        }

        // ── Regular distributor coupons (only if a real distributor exists) ──
        $distributor = User::where('user_type', 'distributor')
            ->where('is_global_store', false)
            ->first();

        if ($distributor) {
            $distCoupons = [
                [
                    'title_ar' => 'خصم 10% - كوبون المتجر',
                    'title_en' => '10% Off - Store Coupon',
                    'code' => 'STORE10',
                    'discount_type' => 'percentage', 'discount_value' => 10,
                    'commission_type' => 'percentage', 'commission_value' => 5,
                ],
                [
                    'title_ar' => 'شحن مجاني - كوبون المتجر',
                    'title_en' => 'Free Shipping - Store Coupon',
                    'code' => 'FREESHIP',
                    'discount_type' => 'fixed', 'discount_value' => 0,
                    'commission_type' => 'fixed', 'commission_value' => 3,
                ],
            ];

            foreach ($distCoupons as $c) {
                Coupon::firstOrCreate(
                    ['title_en' => $c['title_en']],
                    array_merge([
                        'store_id' => $distributor->id,
                        'created_by' => $distributor->id,
                        'description_ar' => 'كوبون تجريبي لمتجر موزع.',
                        'description_en' => 'Demo coupon for a distributor store.',
                        'coupon_method' => 'code',
                        'is_global' => false,
                        'category_id' => $categoryId,
                        'valid_for' => 'both',
                        'max_uses' => 500,
                        'max_uses_per_user' => 1,
                        'used_count' => 0,
                        'min_order_amount' => 0,
                        'free_shipping' => $c['code'] === 'FREESHIP',
                        'exclude_discounted' => false,
                        'start_date' => $now->toDateString(),
                        'end_date' => $now->copy()->addMonths(2)->toDateString(),
                        'is_active' => true,
                    ], $c)
                );
            }
        }

        $this->command->info('Coupons seeded successfully! (' . Coupon::count() . ' total)');
    }

    /**
     * Make sure at least two admin Global Stores exist; create them if needed.
     */
    private function ensureGlobalStores(): array
    {
        $defaults = [
            ['store_name' => 'Global Electronics Hub', 'country' => 'AE'],
            ['store_name' => 'Global Fashion House', 'country' => 'SA'],
        ];

        $stores = [];
        foreach ($defaults as $d) {
            $stores[] = User::firstOrCreate(
                ['store_name' => $d['store_name'], 'is_global_store' => true],
                [
                    'name' => $d['store_name'],
                    'country' => $d['country'],
                    'user_type' => 'distributor',
                    'is_global_store' => true,
                    'email' => 'store_' . Str::random(10) . '@placeholder.local',
                    'password' => '',
                    'is_verified' => true,
                ]
            );
        }

        return $stores;
    }
}
