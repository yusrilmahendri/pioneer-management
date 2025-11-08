<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class ComprehensiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BusinessCategorySeeder::class,
            BusinessStatusSeeder::class,
            ProductCategorySeeder::class,
            ProductStatusSeeder::class,
            TransactionStatusSeeder::class,
            UserSeeder::class,
            BusinessSeeder::class,
            BusinessAccountSeeder::class,
            ProductSeeder::class,
            VoucherSeeder::class,
            PembayaranSeeder::class,
        ]);
    }
}

class BusinessCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'business_category' => 'Food & Beverage'],
            ['id' => 2, 'business_category' => 'Retail & Trading'],
            ['id' => 3, 'business_category' => 'Services'],
            ['id' => 4, 'business_category' => 'Technology'],
            ['id' => 5, 'business_category' => 'Healthcare'],
            ['id' => 6, 'business_category' => 'Education'],
            ['id' => 7, 'business_category' => 'Transportation'],
            ['id' => 8, 'business_category' => 'Manufacturing']
        ];

        foreach ($categories as $category) {
            DB::table('business_category')->insertOrIgnore([
                'id' => $category['id'],
                'business_category' => $category['business_category'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

class BusinessStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'business_status' => 'Active'],
            ['id' => 2, 'business_status' => 'Inactive'],
            ['id' => 3, 'business_status' => 'Pending'],
            ['id' => 4, 'business_status' => 'Suspended'],
            ['id' => 5, 'business_status' => 'Closed']
        ];

        foreach ($statuses as $status) {
            DB::table('business_status')->insertOrIgnore([
                'id' => $status['id'],
                'business_status' => $status['business_status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'product_category' => 'Beverages'],
            ['id' => 2, 'product_category' => 'Food'],
            ['id' => 3, 'product_category' => 'Electronics'],
            ['id' => 4, 'product_category' => 'Clothing'],
            ['id' => 5, 'product_category' => 'Books & Stationery'],
            ['id' => 6, 'product_category' => 'Health & Beauty'],
            ['id' => 7, 'product_category' => 'Home & Garden'],
            ['id' => 8, 'product_category' => 'Sports & Recreation']
        ];

        foreach ($categories as $category) {
            DB::table('product_category')->insertOrIgnore([
                'id' => $category['id'],
                'product_category' => $category['product_category'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

class ProductStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'product_status' => 'Available'],
            ['id' => 2, 'product_status' => 'Out of Stock'],
            ['id' => 3, 'product_status' => 'Discontinued'],
            ['id' => 4, 'product_status' => 'Pre-order'],
            ['id' => 5, 'product_status' => 'Coming Soon']
        ];

        foreach ($statuses as $status) {
            DB::table('product_status')->insertOrIgnore([
                'id' => $status['id'],
                'product_status' => $status['product_status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

class TransactionStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'transaction_status' => 'Pending'],
            ['id' => 2, 'transaction_status' => 'Paid'],
            ['id' => 3, 'transaction_status' => 'Processing'],
            ['id' => 4, 'transaction_status' => 'Shipped'],
            ['id' => 5, 'transaction_status' => 'Delivered'],
            ['id' => 6, 'transaction_status' => 'Cancelled'],
            ['id' => 7, 'transaction_status' => 'Refunded']
        ];

        foreach ($statuses as $status) {
            DB::table('transaction_status')->insertOrIgnore([
                'id' => $status['id'],
                'transaction_status' => $status['transaction_status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Super Admin
            [
                'id' => $this->generateId(),
                'uuid' => Str::uuid(),
                'name' => 'System Administrator',
                'email' => 'admin@pioneermanagement.com',
                'username' => 'sysadmin',
                'password' => bcrypt('admin123'),
                'phone' => '081234567890',
                'birth_of_date' => '1985-01-15',
                'birth_of_place' => 'Jakarta',
                'gender' => 'male',
                'start_date' => '2020-01-01',
                'placement' => 'Head Office',
                'job_role' => 'System Administrator',
                'account_role' => 'admin',
                'salary' => 15000000,
                'email_verified_at' => now(),
            ],
            // Business Owners
            [
                'id' => $this->generateId(),
                'uuid' => Str::uuid(),
                'name' => 'Ahmad Santoso',
                'email' => 'ahmad@kopisentral.com',
                'username' => 'ahmadsantoso',
                'password' => bcrypt('owner123'),
                'phone' => '081234567891',
                'birth_of_date' => '1980-05-20',
                'birth_of_place' => 'Bandung',
                'gender' => 'male',
                'start_date' => '2021-03-15',
                'placement' => 'Bandung Office',
                'job_role' => 'Business Owner',
                'account_role' => 'owner',
                'salary' => 12000000,
                'email_verified_at' => now(),
            ],
            [
                'id' => $this->generateId(),
                'uuid' => Str::uuid(),
                'name' => 'Sari Indahwati',
                'email' => 'sari@bengkeljaya.com',
                'username' => 'sariindah',
                'password' => bcrypt('owner123'),
                'phone' => '081234567892',
                'birth_of_date' => '1983-08-10',
                'birth_of_place' => 'Surabaya',
                'gender' => 'female',
                'start_date' => '2020-06-20',
                'placement' => 'Surabaya Office',
                'job_role' => 'Business Owner',
                'account_role' => 'owner',
                'salary' => 10000000,
                'email_verified_at' => now(),
            ],
            // Employees
            [
                'id' => $this->generateId(),
                'uuid' => Str::uuid(),
                'name' => 'Budi Prasetyo',
                'email' => 'budi@kopisentral.com',
                'username' => 'budiprasetyo',
                'password' => bcrypt('employee123'),
                'phone' => '081234567893',
                'birth_of_date' => '1992-11-25',
                'birth_of_place' => 'Bandung',
                'gender' => 'male',
                'start_date' => '2021-06-01',
                'placement' => 'Bandung Cafe',
                'job_role' => 'Barista',
                'account_role' => 'employee',
                'salary' => 4500000,
                'email_verified_at' => now(),
            ],
            [
                'id' => $this->generateId(),
                'uuid' => Str::uuid(),
                'name' => 'Dewi Lestari',
                'email' => 'dewi@kopisentral.com',
                'username' => 'dewilestari',
                'password' => bcrypt('employee123'),
                'phone' => '081234567894',
                'birth_of_date' => '1995-02-14',
                'birth_of_place' => 'Jakarta',
                'gender' => 'female',
                'start_date' => '2022-01-15',
                'placement' => 'Bandung Cafe',
                'job_role' => 'Cashier',
                'account_role' => 'employee',
                'salary' => 4000000,
                'email_verified_at' => now(),
            ],
            [
                'id' => $this->generateId(),
                'uuid' => Str::uuid(),
                'name' => 'Eko Kurniawan',
                'email' => 'eko@bengkeljaya.com',
                'username' => 'ekokurniawan',
                'password' => bcrypt('employee123'),
                'phone' => '081234567895',
                'birth_of_date' => '1988-07-08',
                'birth_of_place' => 'Surabaya',
                'gender' => 'male',
                'start_date' => '2020-09-01',
                'placement' => 'Surabaya Workshop',
                'job_role' => 'Mechanic',
                'account_role' => 'employee',
                'salary' => 5500000,
                'email_verified_at' => now(),
            ],
            [
                'id' => $this->generateId(),
                'uuid' => Str::uuid(),
                'name' => 'Fitri Handayani',
                'email' => 'fitri@bengkeljaya.com',
                'username' => 'fitrihandayani',
                'password' => bcrypt('employee123'),
                'phone' => '081234567896',
                'birth_of_date' => '1990-12-03',
                'birth_of_place' => 'Malang',
                'gender' => 'female',
                'start_date' => '2021-02-01',
                'placement' => 'Surabaya Workshop',
                'job_role' => 'Service Advisor',
                'account_role' => 'employee',
                'salary' => 4800000,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insertOrIgnore($user);
        }
    }

    private function generateId(): string
    {
        return (string) DB::selectOne('SELECT UUID_SHORT() as id')->id;
    }
}

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $businesses = [
            [
                'id' => 1,
                'business' => 'Kopi Sentral Bandung',
                'start_date' => '2021-03-15',
                'id_business_status' => 1, // Active
                'id_business_category' => 1, // Food & Beverage
                'id_provinsi' => 32, // Jawa Barat
                'id_kabupaten' => 3273, // Kota Bandung
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => 2,
                'business' => 'Bengkel Motor Jaya',
                'start_date' => '2020-06-20',
                'id_business_status' => 1, // Active
                'id_business_category' => 3, // Services
                'id_provinsi' => 35, // Jawa Timur
                'id_kabupaten' => 3578, // Kota Surabaya
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => 3,
                'business' => 'Toko Elektronik Modern',
                'start_date' => '2022-01-10',
                'id_business_status' => 1, // Active
                'id_business_category' => 2, // Retail & Trading
                'id_provinsi' => 31, // DKI Jakarta
                'id_kabupaten' => 3171, // Jakarta Pusat
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => 4,
                'business' => 'Digital Agency Nusantara',
                'start_date' => '2023-05-01',
                'id_business_status' => 1, // Active
                'id_business_category' => 4, // Technology
                'id_provinsi' => 51, // Bali
                'id_kabupaten' => 5171, // Kota Denpasar
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => 5,
                'business' => 'Klinik Sehat Sejahtera',
                'start_date' => '2021-09-15',
                'id_business_status' => 1, // Active
                'id_business_category' => 5, // Healthcare
                'id_provinsi' => 33, // Jawa Tengah
                'id_kabupaten' => 3374, // Kota Semarang
                'created_at' => now(),
                'created_by' => 'system',
            ],
        ];

        foreach ($businesses as $business) {
            DB::table('business')->insertOrIgnore($business);
        }
    }
}

class BusinessAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Get user IDs
        $adminId = DB::table('users')->where('account_role', 'admin')->first()->id;
        $ownerIds = DB::table('users')->where('account_role', 'owner')->pluck('id')->toArray();
        $employeeIds = DB::table('users')->where('account_role', 'employee')->pluck('id')->toArray();

        $businessAccounts = [
            // Owner-Business relationships
            ['id_user' => $ownerIds[0], 'id_business' => 1], // Ahmad -> Kopi Sentral
            ['id_user' => $ownerIds[1], 'id_business' => 2], // Sari -> Bengkel Jaya
            
            // Employee-Business relationships
            ['id_user' => $employeeIds[0], 'id_business' => 1], // Budi -> Kopi Sentral
            ['id_user' => $employeeIds[1], 'id_business' => 1], // Dewi -> Kopi Sentral
            ['id_user' => $employeeIds[2], 'id_business' => 2], // Eko -> Bengkel Jaya
            ['id_user' => $employeeIds[3], 'id_business' => 2], // Fitri -> Bengkel Jaya
        ];

        foreach ($businessAccounts as $account) {
            DB::table('business_account')->insertOrIgnore($account);
        }
    }
}

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Coffee shop products
            [
                'id' => $this->generateId(),
                'product' => 'Espresso',
                'description' => 'Strong Italian coffee made by forcing hot water through ground coffee beans',
                'price' => 18000.00,
                'stock' => 100,
                'id_business' => 1,
                'id_product_category' => 1, // Beverages
                'id_product_status' => 1, // Available
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => $this->generateId(),
                'product' => 'Cappuccino',
                'description' => 'Italian coffee drink prepared with espresso, hot milk, and steamed milk foam',
                'price' => 25000.00,
                'stock' => 80,
                'id_business' => 1,
                'id_product_category' => 1, // Beverages
                'id_product_status' => 1, // Available
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => $this->generateId(),
                'product' => 'Croissant',
                'description' => 'Buttery, flaky, viennoiserie pastry of Austrian origin',
                'price' => 15000.00,
                'stock' => 50,
                'id_business' => 1,
                'id_product_category' => 2, // Food
                'id_product_status' => 1, // Available
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => $this->generateId(),
                'product' => 'Latte',
                'description' => 'Coffee drink made with espresso and steamed milk',
                'price' => 28000.00,
                'stock' => 90,
                'id_business' => 1,
                'id_product_category' => 1, // Beverages
                'id_product_status' => 1, // Available
                'created_at' => now(),
                'created_by' => 'system',
            ],
            
            // Workshop services
            [
                'id' => $this->generateId(),
                'product' => 'Oil Change Service',
                'description' => 'Complete motorcycle oil change with quality oil and filter replacement',
                'price' => 75000.00,
                'stock' => 20,
                'id_business' => 2,
                'id_product_category' => 3, // Services (repurposed as automotive services)
                'id_product_status' => 1, // Available
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => $this->generateId(),
                'product' => 'Brake Pad Replacement',
                'description' => 'Professional brake pad replacement service for motorcycles',
                'price' => 150000.00,
                'stock' => 15,
                'id_business' => 2,
                'id_product_category' => 3, // Services
                'id_product_status' => 1, // Available
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => $this->generateId(),
                'product' => 'Engine Tune-up',
                'description' => 'Complete engine maintenance and performance optimization',
                'price' => 250000.00,
                'stock' => 10,
                'id_business' => 2,
                'id_product_category' => 3, // Services
                'id_product_status' => 1, // Available
                'created_at' => now(),
                'created_by' => 'system',
            ],
            
            // Electronics store products
            [
                'id' => $this->generateId(),
                'product' => 'Smartphone Android',
                'description' => 'Latest Android smartphone with high-end specifications',
                'price' => 3500000.00,
                'stock' => 25,
                'id_business' => 3,
                'id_product_category' => 3, // Electronics
                'id_product_status' => 1, // Available
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => $this->generateId(),
                'product' => 'Wireless Headphones',
                'description' => 'Bluetooth wireless headphones with noise cancellation',
                'price' => 750000.00,
                'stock' => 40,
                'id_business' => 3,
                'id_product_category' => 3, // Electronics
                'id_product_status' => 1, // Available
                'created_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => $this->generateId(),
                'product' => 'Laptop Charger',
                'description' => 'Universal laptop charger compatible with multiple brands',
                'price' => 350000.00,
                'stock' => 30,
                'id_business' => 3,
                'id_product_category' => 3, // Electronics
                'id_product_status' => 1, // Available
                'created_at' => now(),
                'created_by' => 'system',
            ],
        ];

        foreach ($products as $product) {
            DB::table('product')->insertOrIgnore($product);
        }
    }

    private function generateId(): string
    {
        return (string) DB::selectOne('SELECT UUID_SHORT() as id')->id;
    }
}

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->where('account_role', '!=', 'admin')->pluck('id')->toArray();
        
        $vouchers = [
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[0],
                'status_product_id' => 1, // Active status
                'kode_promo' => 'COFFEE50',
                'tipe_promo' => 'Percentage Discount',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'kouta' => 100,
                'created_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[1],
                'status_product_id' => 1,
                'kode_promo' => 'NEWCUST20',
                'tipe_promo' => 'Fixed Amount Discount',
                'start_date' => '2025-01-01',
                'end_date' => '2025-06-30',
                'kouta' => 50,
                'created_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[2],
                'status_product_id' => 1,
                'kode_promo' => 'WORKSHOP25',
                'tipe_promo' => 'Percentage Discount',
                'start_date' => '2025-02-01',
                'end_date' => '2025-08-31',
                'kouta' => 75,
                'created_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[3],
                'status_product_id' => 1,
                'kode_promo' => 'LOYAL100',
                'tipe_promo' => 'Fixed Amount Discount',
                'start_date' => '2025-01-15',
                'end_date' => '2025-12-15',
                'kouta' => 200,
                'created_at' => now(),
            ],
        ];

        foreach ($vouchers as $voucher) {
            DB::table('voucher')->insertOrIgnore($voucher);
        }
    }
}

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->where('account_role', 'employee')->pluck('id')->toArray();
        $productIds = DB::table('product')->pluck('id')->toArray();
        $voucherIds = DB::table('voucher')->pluck('uuid')->toArray();

        $payments = [
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[0],
                'product_id' => $productIds[0], // Espresso
                'kode_voucher' => null,
                'count' => 2,
                'price' => 36000.00,
                'date_order' => '2025-11-01',
                'created_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[1],
                'product_id' => $productIds[1], // Cappuccino
                'kode_voucher' => $voucherIds[0] ?? null,
                'count' => 1,
                'price' => 25000.00,
                'date_order' => '2025-11-02',
                'created_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[2],
                'product_id' => $productIds[4], // Oil Change Service
                'kode_voucher' => null,
                'count' => 1,
                'price' => 75000.00,
                'date_order' => '2025-11-03',
                'created_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[0],
                'product_id' => $productIds[2], // Croissant
                'kode_voucher' => null,
                'count' => 3,
                'price' => 45000.00,
                'date_order' => '2025-11-04',
                'created_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[3],
                'product_id' => $productIds[5], // Brake Pad Replacement
                'kode_voucher' => $voucherIds[2] ?? null,
                'count' => 1,
                'price' => 150000.00,
                'date_order' => '2025-11-05',
                'created_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[1],
                'product_id' => $productIds[7], // Smartphone
                'kode_voucher' => null,
                'count' => 1,
                'price' => 3500000.00,
                'date_order' => '2025-11-06',
                'created_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => $userIds[2],
                'product_id' => $productIds[8], // Wireless Headphones
                'kode_voucher' => $voucherIds[1] ?? null,
                'count' => 1,
                'price' => 750000.00,
                'date_order' => '2025-11-07',
                'created_at' => now(),
            ],
        ];

        foreach ($payments as $payment) {
            DB::table('pembayarans')->insertOrIgnore($payment);
        }
    }
}