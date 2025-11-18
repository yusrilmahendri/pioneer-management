<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessStatus;
use App\Models\User;

class PisantekBusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required IDs
        $foodCategory = BusinessCategory::where('name_category_business', 'Penjualan Makanan')->first();
        $activeStatus = BusinessStatus::where('business_status', 'Aktif')->first();
        $sulyadee = User::where('email', 'sulyadee@pioneer.com')->first();

        if (!$foodCategory || !$activeStatus || !$sulyadee) {
            $this->command->error('Required data not found. Please run other seeders first.');
            return;
        }

        // Create Pisantek Business
        $pisantek = Business::firstOrCreate(
            ['name_business' => 'Pisantek'],
            [
                'id' => 1,
                'name_business' => 'Pisantek',
                'id_business_category' => $foodCategory->id,
                'id_business_status' => $activeStatus->id,
                'created_by' => $sulyadee->id,
                'updated_by' => $sulyadee->id,
            ]
        );

        // Assign users to Pisantek business
        $supervisor = User::where('email', 'anton@pioneer.com')->first();
        $employee = User::where('email', 'lelek@pioneer.com')->first();

        if ($supervisor) {
            $supervisor->update(['id_business' => $pisantek->id]);
        }

        if ($employee) {
            $employee->update(['id_business' => $pisantek->id]);
        }

        // Update Sulyadee to be assigned to Pisantek as well
        $sulyadee->update(['id_business' => $pisantek->id]);

        $this->command->info('Pisantek business created successfully!');
        $this->command->info('Users assigned to Pisantek:');
        $this->command->info('- Owner: Sulyadee');
        $this->command->info('- Supervisor: Anton');
        $this->command->info('- Employee: Lelek');
    }
}
