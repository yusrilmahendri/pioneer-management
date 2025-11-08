<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuperAdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $adminId = DB::selectOne('SELECT UUID_SHORT() as id')->id;
        
        DB::table('users')->insertOrIgnore([
            'id' => $adminId,
            'uuid' => (string) Str::uuid(),
            'name' => 'Yusril Mahendri',
            'email' => 'pioneersolve@gmail.com', 
            'username' => 'yusrilmahendri',
            'password' => bcrypt('Bismillah@1'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2020-01-01',
            'placement' => 'Head Office',
            'job_role' => 'System Administrator',
            'account_role' => 'admin',
            'salary' => 10000000,
            'created_at' => now(),
            'created_by' => 'system',
        ]);

        // Note: Role assignment would be handled by Spatie Permission package if still using it
        // $user->assignRole('admin');
    }
}
