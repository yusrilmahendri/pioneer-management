<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expenditure;
use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Str;

class ExpenditureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users and businesses for testing
        $admin = User::where('account_role', 'admin')->first();
        $owner = User::where('account_role', 'owner')->first();
        $supervisor = User::where('account_role', 'supervisor')->first();
        $employee = User::where('account_role', 'employee')->first();
        
        $business1 = Business::first();
        $business2 = Business::skip(1)->first();
        
        if (!$admin || !$owner || !$business1) {
            $this->command->info('Required users or businesses not found. Please seed users and businesses first.');
            return;
        }

        // Admin expenditures (auto-approved)
        Expenditure::create([
            'uuid' => Str::uuid(),
            'id_business' => $business1->id,
            'id_user' => $admin->id,
            'category' => 'Office Supplies',
            'description' => 'Purchase of office chairs and desks',
            'amount' => 2500000.00,
            'status' => 'approved', // Admin expenditures are auto-approved
            'id_user_approved' => $admin->id,
            'approved_at' => now(),
        ]);

        // Owner expenditure (pending)
        Expenditure::create([
            'uuid' => Str::uuid(),
            'id_business' => $business1->id,
            'id_user' => $owner->id,
            'category' => 'Marketing',
            'description' => 'Social media advertising campaign',
            'amount' => 1500000.00,
            'status' => 'pending',
        ]);

        // Owner expenditure (approved by admin)
        Expenditure::create([
            'uuid' => Str::uuid(),
            'id_business' => $business1->id,
            'id_user' => $owner->id,
            'category' => 'Equipment',
            'description' => 'New coffee machine for the cafe',
            'amount' => 8500000.00,
            'status' => 'approved',
            'id_user_approved' => $admin->id,
            'approved_at' => now()->subDays(2),
            'notes' => 'Approved by admin - necessary equipment upgrade'
        ]);

        if ($supervisor) {
            // Supervisor expenditure (pending)
            Expenditure::create([
                'uuid' => Str::uuid(),
                'id_business' => $business1->id,
                'id_user' => $supervisor->id,
                'category' => 'Operational',
                'description' => 'Monthly inventory restocking',
                'amount' => 750000.00,
                'status' => 'pending',
            ]);
        }

        if ($employee) {
            // Employee expenditure (pending)
            Expenditure::create([
                'uuid' => Str::uuid(),
                'id_business' => $business1->id,
                'id_user' => $employee->id,
                'category' => 'Transportation',
                'description' => 'Fuel for delivery motorcycle',
                'amount' => 200000.00,
                'status' => 'pending',
            ]);

            // Employee expenditure (rejected)
            Expenditure::create([
                'uuid' => Str::uuid(),
                'id_business' => $business1->id,
                'id_user' => $employee->id,
                'category' => 'Personal',
                'description' => 'Personal lunch expense',
                'amount' => 150000.00,
                'status' => 'rejected',
                'id_user_approved' => $owner->id,
                'approved_at' => now()->subDays(1),
                'notes' => 'Personal expenses are not covered by company policy'
            ]);
        }

        if ($business2) {
            // Different business expenditure (for testing cross-business access)
            Expenditure::create([
                'uuid' => Str::uuid(),
                'id_business' => $business2->id,
                'id_user' => $admin->id,
                'category' => 'Setup',
                'description' => 'Initial business setup costs',
                'amount' => 5000000.00,
                'status' => 'approved',
                'id_user_approved' => $admin->id,
                'approved_at' => now(),
            ]);
        }

        $this->command->info('Expenditures seeded successfully!');
    }
}