<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductStatus;

class ProductStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'product_status' => 'Available',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'product_status' => 'Out of Stock',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'product_status' => 'Discontinued',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'product_status' => 'Coming Soon',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'product_status' => 'Limited Stock',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'product_status' => 'Pre-Order',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'product_status' => 'Seasonal',
                'created_by' => 'system',
                'updated_by' => 'system'
            ]
        ];

        foreach ($statuses as $status) {
            ProductStatus::firstOrCreate(
                ['product_status' => $status['product_status']],
                $status
            );
        }

        $this->command->info('Product statuses seeded successfully!');
    }
}
