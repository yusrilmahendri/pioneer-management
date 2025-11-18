<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessCategory;

class BusinessCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name_category_business' => 'Penjualan Makanan',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_business' => 'Penjualan Minuman',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_business' => 'Retail & Fashion',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_business' => 'Jasa & Layanan',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_business' => 'Teknologi & Digital',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_business' => 'Kesehatan & Wellness',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_business' => 'Pendidikan & Training',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_business' => 'Otomotif',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_business' => 'Properti & Real Estate',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'name_category_business' => 'Agribisnis & Pertanian',
                'created_by' => 'system',
                'updated_by' => 'system'
            ]
        ];

        foreach ($categories as $category) {
            BusinessCategory::firstOrCreate(
                ['name_category_business' => $category['name_category_business']],
                $category
            );
        }

        $this->command->info('Business categories seeded successfully!');
    }
}
