<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StatusBusines;

class StatusBusinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StatusBusines::insert([
            ['name_status_busines' => 'Active'],
            ['name_status_busines' => 'Inactive'],
        ]);   
    }
}
