<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name_category_product' => 'Makanan & Minuman',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_product' => 'Snack & Camilan',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_product' => 'Buah & Sayuran',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_product' => 'Produk Olahan',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_product' => 'Minuman Segar',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_product' => 'Kue & Dessert',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_product' => 'Frozen Food',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_product' => 'Produk Kemasan',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_product' => 'Roti & Bakery',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_product' => 'Produk Tradisional',
                'created_by' => 'system',
                'updated_by' => 'system'
            ]
        ];

        foreach ($categories as $category) {
            ProductCategory::firstOrCreate(
                ['name_category_product' => $category['name_category_product']],
                $category
            );
        }

        $this->command->info('Product categories seeded successfully!');
    }
}
