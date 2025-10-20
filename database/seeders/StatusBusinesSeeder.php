<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusBusinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('status_busines')->insert([
            [
                'name_status_busines' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_status_busines' => 'Tidak Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_status_busines' => 'Dalam Pengembangan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_status_busines' => 'Dijual',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_status_busines' => 'Baru Didirikan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
