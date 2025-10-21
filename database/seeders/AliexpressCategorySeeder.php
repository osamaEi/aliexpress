<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class AliexpressCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Home Improvement', 'aliexpress_category_id' => '13', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01qneri71vFLxPJGTIa_!!6000000006141-2-tps-48-48.png'],
            ['name' => 'Home & Garden', 'aliexpress_category_id' => '15', 'image' => 'https://img.alicdn.com/imgextra/i4/O1CN01ZQl8jj1YRfGPl9s2k_!!6000000003054-2-tps-48-48.png'],
            ['name' => 'Sports & Entertainment', 'aliexpress_category_id' => '18', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01Gh0g6i1nE6xJl8yLD_!!6000000005055-2-tps-48-48.png'],
            ['name' => 'Office & School Supplies', 'aliexpress_category_id' => '21', 'image' => 'https://img.alicdn.com/imgextra/i2/O1CN01EjVQb21kZRGKq8N7N_!!6000000004698-2-tps-48-48.png'],
            ['name' => 'Toys & Hobbies', 'aliexpress_category_id' => '26', 'image' => 'https://img.alicdn.com/imgextra/i1/O1CN01JL9CsZ1h9mSPxGRVc_!!6000000004234-2-tps-48-48.png'],
            ['name' => 'Security & Protection', 'aliexpress_category_id' => '30', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01WyQxKF1XvGD7nIYOa_!!6000000002989-2-tps-48-48.png'],
            ['name' => 'Automobiles, Parts & Accessories', 'aliexpress_category_id' => '34', 'image' => 'https://img.alicdn.com/imgextra/i4/O1CN01m9HTLP1dBmJqkJaOB_!!6000000003698-2-tps-48-48.png'],
            ['name' => 'Jewelry & Accessories', 'aliexpress_category_id' => '36', 'image' => 'https://img.alicdn.com/imgextra/i2/O1CN01YyTsOu1w8JxQKVNgC_!!6000000006263-2-tps-48-48.png'],
            ['name' => 'Lights & Lighting', 'aliexpress_category_id' => '39', 'image' => 'https://img.alicdn.com/imgextra/i1/O1CN01SfN5F21YRfGPnL2HN_!!6000000003054-2-tps-48-48.png'],
            ['name' => 'Consumer Electronics', 'aliexpress_category_id' => '44', 'image' => 'https://img.alicdn.com/imgextra/i1/O1CN01D8JDMF1XvGD7nJyHE_!!6000000002989-2-tps-48-48.png'],
            ['name' => 'Beauty & Health', 'aliexpress_category_id' => '66', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01mHg8Kx1h9mSPxK3Yp_!!6000000004234-2-tps-48-48.png'],
            ['name' => 'Weddings & Events', 'aliexpress_category_id' => '320', 'image' => 'https://img.alicdn.com/imgextra/i1/O1CN01kZRGKq1nE6xJl9OJL_!!6000000005055-2-tps-48-48.png'],
            ['name' => 'Shoes', 'aliexpress_category_id' => '322', 'image' => 'https://img.alicdn.com/imgextra/i4/O1CN01m9HTLP1dBmJqkJaOB_!!6000000003698-2-tps-48-48.png'],
            ['name' => 'Electronic Components & Supplies', 'aliexpress_category_id' => '502', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01qneri71vFLxPJGTIa_!!6000000006141-2-tps-48-48.png'],
            ['name' => 'Phones & Telecommunications', 'aliexpress_category_id' => '509', 'image' => 'https://img.alicdn.com/imgextra/i1/O1CN01D8JDMF1XvGD7nJyHE_!!6000000002989-2-tps-48-48.png'],
            ['name' => 'Tools', 'aliexpress_category_id' => '1420', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01Gh0g6i1nE6xJl8yLD_!!6000000005055-2-tps-48-48.png'],
            ['name' => 'Mother & Kids', 'aliexpress_category_id' => '1501', 'image' => 'https://img.alicdn.com/imgextra/i1/O1CN01JL9CsZ1h9mSPxGRVc_!!6000000004234-2-tps-48-48.png'],
            ['name' => 'Furniture', 'aliexpress_category_id' => '1503', 'image' => 'https://img.alicdn.com/imgextra/i4/O1CN01ZQl8jj1YRfGPl9s2k_!!6000000003054-2-tps-48-48.png'],
            ['name' => 'Watches', 'aliexpress_category_id' => '1511', 'image' => 'https://img.alicdn.com/imgextra/i2/O1CN01YyTsOu1w8JxQKVNgC_!!6000000006263-2-tps-48-48.png'],
            ['name' => 'Luggage & Bags', 'aliexpress_category_id' => '1524', 'image' => 'https://img.alicdn.com/imgextra/i2/O1CN01EjVQb21kZRGKq8N7N_!!6000000004698-2-tps-48-48.png'],
            ['name' => "Women's Clothing", 'aliexpress_category_id' => '200000345', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01mHg8Kx1h9mSPxK3Yp_!!6000000004234-2-tps-48-48.png'],
            ['name' => "Men's Clothing", 'aliexpress_category_id' => '200000343', 'image' => 'https://img.alicdn.com/imgextra/i4/O1CN01m9HTLP1dBmJqkJaOB_!!6000000003698-2-tps-48-48.png'],
            ['name' => 'Hair Extensions & Wigs', 'aliexpress_category_id' => '200165144', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01mHg8Kx1h9mSPxK3Yp_!!6000000004234-2-tps-48-48.png'],
            ['name' => 'Special Category', 'aliexpress_category_id' => '200001075', 'image' => 'https://img.alicdn.com/imgextra/i1/O1CN01kZRGKq1nE6xJl9OJL_!!6000000005055-2-tps-48-48.png'],
            ['name' => 'Underwear', 'aliexpress_category_id' => '200574005', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01mHg8Kx1h9mSPxK3Yp_!!6000000004234-2-tps-48-48.png'],
            ['name' => 'Novelty & Special Use', 'aliexpress_category_id' => '200000532', 'image' => 'https://img.alicdn.com/imgextra/i1/O1CN01kZRGKq1nE6xJl9OJL_!!6000000005055-2-tps-48-48.png'],
            ['name' => 'Virtual Products', 'aliexpress_category_id' => '201169612', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01qneri71vFLxPJGTIa_!!6000000006141-2-tps-48-48.png'],
            ['name' => 'Sports Shoes, Clothing & Accessories', 'aliexpress_category_id' => '201768104', 'image' => 'https://img.alicdn.com/imgextra/i3/O1CN01Gh0g6i1nE6xJl8yLD_!!6000000005055-2-tps-48-48.png'],
            ['name' => 'Second-Hand', 'aliexpress_category_id' => '201520802', 'image' => 'https://img.alicdn.com/imgextra/i1/O1CN01kZRGKq1nE6xJl9OJL_!!6000000005055-2-tps-48-48.png'],
            ['name' => 'Motorcycle Equipments & Parts', 'aliexpress_category_id' => '201355758', 'image' => 'https://img.alicdn.com/imgextra/i4/O1CN01m9HTLP1dBmJqkJaOB_!!6000000003698-2-tps-48-48.png'],
            ['name' => 'Apparel Accessories', 'aliexpress_category_id' => '200000297', 'image' => 'https://img.alicdn.com/imgextra/i2/O1CN01YyTsOu1w8JxQKVNgC_!!6000000006263-2-tps-48-48.png'],
        ];

        foreach ($categories as $index => $category) {
            $slug = Str::slug($category['name']);

            // Try to find existing category by slug or aliexpress_category_id
            $existingCategory = Category::where('slug', $slug)
                ->orWhere('aliexpress_category_id', $category['aliexpress_category_id'])
                ->first();

            if ($existingCategory) {
                // Update existing category
                $existingCategory->update([
                    'name' => $category['name'],
                    'aliexpress_category_id' => $category['aliexpress_category_id'],
                    'image' => $category['image'],
                    'is_active' => true,
                    'order' => $index,
                ]);
            } else {
                // Create new category
                Category::create([
                    'name' => $category['name'],
                    'slug' => $slug,
                    'aliexpress_category_id' => $category['aliexpress_category_id'],
                    'image' => $category['image'],
                    'is_active' => true,
                    'order' => $index,
                ]);
            }
        }

        $this->command->info('AliExpress categories seeded successfully!');
    }
}
