<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesTableSeeder::class);
        $this->call(PegawaiTableSeeder::class);
        $this->call(SuperAdminTableSeeder::class);
        $this->call(CategoryProductSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(PembayaranSeeder::class);
        $this->call(ProductsSeeder::class);
        $this->call(VoucherSeeder::class);

    }
}
