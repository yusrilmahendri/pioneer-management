<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cashierId = DB::selectOne('SELECT UUID_SHORT() as id')->id;
        
        DB::table('users')->insertOrIgnore([
            'id' => $cashierId,
            'uuid' => (string) Str::uuid(),
            'name' => 'Aqsal Martha',
            'email' => 'pionee@gmail.com', 
            'username' => 'Aqsal',
            'password' => bcrypt('Bismillah@1'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2020-01-01',
            'placement' => 'Head Office',
            'job_role' => 'System Administrator',
            'account_role' => 'employee',
            'salary' => 10000000,
            'created_at' => now(),
            'created_by' => 'system',
        ]);
    }
}
