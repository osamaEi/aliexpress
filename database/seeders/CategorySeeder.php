<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic devices and accessories',
                'is_active' => true,
                'children' => [
                    ['name' => 'Smartphones & Tablets', 'slug' => 'smartphones-tablets'],
                    ['name' => 'Computers & Laptops', 'slug' => 'computers-laptops'],
                    ['name' => 'Audio & Headphones', 'slug' => 'audio-headphones'],
                    ['name' => 'Cameras & Photography', 'slug' => 'cameras-photography'],
                    ['name' => 'Smart Home', 'slug' => 'smart-home'],
                    ['name' => 'Wearable Technology', 'slug' => 'wearable-technology'],
                ]
            ],
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'description' => 'Clothing, shoes, and accessories',
                'is_active' => true,
                'children' => [
                    ['name' => 'Men\'s Clothing', 'slug' => 'mens-clothing'],
                    ['name' => 'Women\'s Clothing', 'slug' => 'womens-clothing'],
                    ['name' => 'Shoes', 'slug' => 'shoes'],
                    ['name' => 'Bags & Accessories', 'slug' => 'bags-accessories'],
                    ['name' => 'Watches', 'slug' => 'watches'],
                    ['name' => 'Jewelry', 'slug' => 'jewelry'],
                ]
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Home improvement, furniture, and garden supplies',
                'is_active' => true,
                'children' => [
                    ['name' => 'Furniture', 'slug' => 'furniture'],
                    ['name' => 'Kitchen & Dining', 'slug' => 'kitchen-dining'],
                    ['name' => 'Bedding & Bath', 'slug' => 'bedding-bath'],
                    ['name' => 'Home Decor', 'slug' => 'home-decor'],
                    ['name' => 'Garden & Outdoor', 'slug' => 'garden-outdoor'],
                    ['name' => 'Tools & Hardware', 'slug' => 'tools-hardware'],
                ]
            ],
            [
                'name' => 'Beauty & Health',
                'slug' => 'beauty-health',
                'description' => 'Beauty products, skincare, and health items',
                'is_active' => true,
                'children' => [
                    ['name' => 'Skincare', 'slug' => 'skincare'],
                    ['name' => 'Makeup', 'slug' => 'makeup'],
                    ['name' => 'Hair Care', 'slug' => 'hair-care'],
                    ['name' => 'Fragrances', 'slug' => 'fragrances'],
                    ['name' => 'Personal Care', 'slug' => 'personal-care'],
                    ['name' => 'Health & Wellness', 'slug' => 'health-wellness'],
                ]
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'is_active' => true,
                'children' => [
                    ['name' => 'Exercise & Fitness', 'slug' => 'exercise-fitness'],
                    ['name' => 'Outdoor Recreation', 'slug' => 'outdoor-recreation'],
                    ['name' => 'Sports Apparel', 'slug' => 'sports-apparel'],
                    ['name' => 'Cycling', 'slug' => 'cycling'],
                    ['name' => 'Team Sports', 'slug' => 'team-sports'],
                    ['name' => 'Water Sports', 'slug' => 'water-sports'],
                ]
            ],
            [
                'name' => 'Toys & Kids',
                'slug' => 'toys-kids',
                'description' => 'Toys, games, and kids products',
                'is_active' => true,
                'children' => [
                    ['name' => 'Action Figures & Dolls', 'slug' => 'action-figures-dolls'],
                    ['name' => 'Building Toys', 'slug' => 'building-toys'],
                    ['name' => 'Educational Toys', 'slug' => 'educational-toys'],
                    ['name' => 'Baby & Toddler Toys', 'slug' => 'baby-toddler-toys'],
                    ['name' => 'Games & Puzzles', 'slug' => 'games-puzzles'],
                    ['name' => 'Kids\' Fashion', 'slug' => 'kids-fashion'],
                ]
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'description' => 'Car accessories and motorcycle parts',
                'is_active' => true,
                'children' => [
                    ['name' => 'Car Electronics', 'slug' => 'car-electronics'],
                    ['name' => 'Car Accessories', 'slug' => 'car-accessories'],
                    ['name' => 'Motorcycle Accessories', 'slug' => 'motorcycle-accessories'],
                    ['name' => 'Tools & Equipment', 'slug' => 'automotive-tools-equipment'],
                    ['name' => 'Car Care', 'slug' => 'car-care'],
                ]
            ],
            [
                'name' => 'Pet Supplies',
                'slug' => 'pet-supplies',
                'description' => 'Products for pets',
                'is_active' => true,
                'children' => [
                    ['name' => 'Dog Supplies', 'slug' => 'dog-supplies'],
                    ['name' => 'Cat Supplies', 'slug' => 'cat-supplies'],
                    ['name' => 'Pet Food', 'slug' => 'pet-food'],
                    ['name' => 'Pet Toys', 'slug' => 'pet-toys'],
                    ['name' => 'Pet Grooming', 'slug' => 'pet-grooming'],
                ]
            ],
            [
                'name' => 'Books & Media',
                'slug' => 'books-media',
                'description' => 'Books, movies, music, and digital media',
                'is_active' => true,
                'children' => [
                    ['name' => 'Books', 'slug' => 'books'],
                    ['name' => 'Movies & TV', 'slug' => 'movies-tv'],
                    ['name' => 'Music', 'slug' => 'music'],
                    ['name' => 'Video Games', 'slug' => 'video-games'],
                ]
            ],
            [
                'name' => 'Office & Stationery',
                'slug' => 'office-stationery',
                'description' => 'Office supplies and stationery',
                'is_active' => true,
                'children' => [
                    ['name' => 'Office Electronics', 'slug' => 'office-electronics'],
                    ['name' => 'Stationery', 'slug' => 'stationery'],
                    ['name' => 'Office Furniture', 'slug' => 'office-furniture'],
                    ['name' => 'School Supplies', 'slug' => 'school-supplies'],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            // Create parent category
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $parent = Category::create($categoryData);

            // Create child categories
            foreach ($children as $childData) {
                Category::create([
                    'name' => $childData['name'],
                    'slug' => $childData['slug'],
                    'description' => $childData['description'] ?? null,
                    'parent_id' => $parent->id,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Categories seeded successfully!');
    }
}
