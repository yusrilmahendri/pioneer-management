<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class SuperAdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
         $user = User::create([
            'name' => 'Yusril Mahendri',
            'email' => 'pioneersolve@gmail.com', 
            'password' => bcrypt('Bismillah@1'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2020-01-01',
            'placement' => 'Head Office',
            'job_role' => 'Administrator',
            'account_role' => 'super-admin',
            'salary' => 10000000,
        ]);

        $user->assignRole('super-admin');

        return $user;
    }
}
