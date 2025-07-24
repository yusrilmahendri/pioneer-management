<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class PegawaiTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $user = User::create([
            'name' => 'Nova Pratiwi Dewi',
            'email' => 'nopa@gmail.com', 
            'password' => bcrypt('Bismillah@1'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
        ]);

        $user->assignRole('pegawai');

        return $user;
    }
}
