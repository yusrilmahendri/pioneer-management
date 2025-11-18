<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PioneerUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin - Edwin
        $admin = User::firstOrCreate(
            ['email' => 'edwin@pioneer.com'],
            [
                'id' => 1,
                'name' => 'Edwin',
                'username' => 'edwin_admin',
                'password' => Hash::make('password123'),
                'phone' => '081234567890',
                'birth_of_date' => '1990-01-15',
                'birth_of_place' => 'Jakarta',
                'gender' => 'male',
                'start_date' => '2023-01-01',
                'placement' => 'Head Office',
                'job_role' => 'System Administrator',
                'account_role' => 'admin',
                'salary' => 15000000.00,
                'uuid' => Str::uuid(),
                'created_by' => 'system',
                'updated_by' => 'system',
            ]
        );

        // Owner - Sulyadee
        $owner = User::firstOrCreate(
            ['email' => 'sulyadee@pioneer.com'],
            [
                'id' => 2,
                'name' => 'Sulyadee',
                'username' => 'sulyadee_owner',
                'password' => Hash::make('password123'),
                'phone' => '081234567891',
                'birth_of_date' => '1985-03-20',
                'birth_of_place' => 'Bandung',
                'gender' => 'male',
                'start_date' => '2023-01-01',
                'placement' => 'Pisantek Branch',
                'job_role' => 'Business Owner',
                'account_role' => 'owner',
                'salary' => 20000000.00,
                'uuid' => Str::uuid(),
                'created_by' => 'system',
                'updated_by' => 'system',
            ]
        );

        // Supervisor - Anton
        $supervisor = User::firstOrCreate(
            ['email' => 'anton@pioneer.com'],
            [
                'id' => 3,
                'name' => 'Anton',
                'username' => 'anton_supervisor',
                'password' => Hash::make('password123'),
                'phone' => '081234567892',
                'birth_of_date' => '1992-07-10',
                'birth_of_place' => 'Surabaya',
                'gender' => 'male',
                'start_date' => '2023-02-01',
                'placement' => 'Pisantek Branch',
                'job_role' => 'Operations Supervisor',
                'account_role' => 'supervisor',
                'salary' => 8000000.00,
                'uuid' => Str::uuid(),
                'created_by' => 'system',
                'updated_by' => 'system',
            ]
        );

        // Employee - Lelek
        $employee = User::firstOrCreate(
            ['email' => 'lelek@pioneer.com'],
            [
                'id' => 4,
                'name' => 'Lelek',
                'username' => 'lelek_employee',
                'password' => Hash::make('password123'),
                'phone' => '081234567893',
                'birth_of_date' => '1995-11-25',
                'birth_of_place' => 'Yogyakarta',
                'gender' => 'male',
                'start_date' => '2023-03-01',
                'placement' => 'Pisantek Branch',
                'job_role' => 'Sales Assistant',
                'account_role' => 'employee',
                'salary' => 5000000.00,
                'uuid' => Str::uuid(),
                'created_by' => 'system',
                'updated_by' => 'system',
            ]
        );

        $this->command->info('Pioneer users seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Admin Edwin: edwin@pioneer.com / password123');
        $this->command->info('Owner Sulyadee: sulyadee@pioneer.com / password123');
        $this->command->info('Supervisor Anton: anton@pioneer.com / password123');
        $this->command->info('Employee Lelek: lelek@pioneer.com / password123');
    }
}
