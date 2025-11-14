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
        // Seed the comprehensive data that matches our database schema
        $this->call([
            ComprehensiveSeeder::class,
            SuperAdminTableSeeder::class, // Add the super admin as separate seeder
            CashierTableSeeder::class,    // Add the cashier as separate seeder
        ]);
    }
}
