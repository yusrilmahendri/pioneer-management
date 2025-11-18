<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessStatus;

class BusinessStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'business_status' => 'Aktif',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'business_status' => 'Nonaktif',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'business_status' => 'Pending',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'business_status' => 'Suspended',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'business_status' => 'Under Review',
                'created_by' => 'system',
                'updated_by' => 'system'
            ],
            [
                'business_status' => 'Closed',
                'created_by' => 'system',
                'updated_by' => 'system'
            ]
        ];

        foreach ($statuses as $status) {
            BusinessStatus::firstOrCreate(
                ['business_status' => $status['business_status']],
                $status
            );
        }

        $this->command->info('Business statuses seeded successfully!');
    }
}
