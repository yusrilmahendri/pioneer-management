<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StatusProduct;

class StatusProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StatusProduct::insert([
            ['name_status' => 'Available'],
            ['name_status' => 'Out of Stock'],
        ]);
    }
}
