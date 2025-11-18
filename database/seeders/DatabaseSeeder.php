<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed master data first (categories and statuses)
        $this->call([
            BusinessCategorySeeder::class,
            BusinessStatusSeeder::class,
            ProductCategorySeeder::class,
            ProductStatusSeeder::class,
        ]);

        // Seed users
        $this->call([
            PioneerUsersSeeder::class,
        ]);

        // Seed business and products (depends on categories, statuses, and users)
        $this->call([
            PisantekBusinessSeeder::class,
            PisantekProductSeeder::class,
        ]);

        // Keep existing seeders if still needed
        $this->call([
            SuperAdminTableSeeder::class,
            CashierTableSeeder::class,
        ]);
    }
}
