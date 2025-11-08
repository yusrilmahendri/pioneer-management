# Database Seeders Documentation

## Overview

This document describes the comprehensive seeder system for the Pioneer Management application. All seeders have been updated to match the current database schema and provide realistic dummy data.

## Seeder Structure

### Main Seeders

#### 1. ComprehensiveSeeder
The main seeder that orchestrates all data seeding in the correct order:

```php
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
```

#### 2. SuperAdminTableSeeder
Creates the main system administrator account for project owner:
- **Email**: `pioneersolve@gmail.com`
- **Username**: `yusrilmahendri` 
- **Password**: `Bismillah@1`
- **Role**: `admin`

### Individual Seeders

#### BusinessCategorySeeder
Creates 8 business categories:
- Food & Beverage
- Retail & Trading
- Services
- Technology
- Healthcare
- Education
- Transportation
- Manufacturing

#### BusinessStatusSeeder
Creates 5 business statuses:
- Active
- Inactive
- Pending
- Suspended
- Closed

#### ProductCategorySeeder
Creates 8 product categories:
- Beverages
- Food
- Electronics
- Clothing
- Books & Stationery
- Health & Beauty
- Home & Garden
- Sports & Recreation

#### ProductStatusSeeder
Creates 5 product statuses:
- Available
- Out of Stock
- Discontinued
- Pre-order
- Coming Soon

#### TransactionStatusSeeder
Creates 7 transaction statuses:
- Pending
- Paid
- Processing
- Shipped
- Delivered
- Cancelled
- Refunded

#### UserSeeder
Creates sample users with different roles:
- **1 Admin**: System Administrator
- **2 Owners**: Business owners for different businesses
- **4 Employees**: Various employees assigned to different businesses

**Default Credentials:**
- Admin: `admin@pioneermanagement.com` / `admin123`
- Owner: `ahmad@kopisentral.com` / `owner123`
- Employee: `budi@kopisentral.com` / `employee123`

#### BusinessSeeder
Creates 5 sample businesses:
- Kopi Sentral Bandung (Food & Beverage)
- Bengkel Motor Jaya (Services)
- Toko Elektronik Modern (Retail)
- Digital Agency Nusantara (Technology)
- Klinik Sehat Sejahtera (Healthcare)

#### BusinessAccountSeeder
Links users to businesses (many-to-many relationship):
- Owners assigned to their respective businesses
- Employees assigned to appropriate businesses

#### ProductSeeder
Creates 10 sample products across different categories:
- Coffee shop items (Espresso, Cappuccino, Latte, Croissant)
- Auto repair services (Oil Change, Brake Service, Tune-up)
- Electronics (Smartphone, Headphones, Charger)

#### VoucherSeeder
Creates 4 sample vouchers with different types:
- Percentage discounts
- Fixed amount discounts
- Various promo codes and validity periods

#### PembayaranSeeder
Creates 7 sample payment transactions:
- Different users purchasing various products
- Some with voucher usage
- Realistic pricing and quantities

## Database Schema Alignment

All seeders have been updated to match the current database table structure:

### Table Name Mapping
- `users` - User accounts with UUID and role-based structure
- `business` - Business information
- `business_account` - User-business relationships (pivot table)
- `business_category` - Business categories
- `business_status` - Business statuses
- `product` - Products with proper relationships
- `category_product` - Product categories
- `status_product` - Product statuses
- `status_transaksi` - Transaction statuses
- `voucher` - Voucher/promo codes
- `pembayarans` - Payment transactions

### Key Features
- **UUID Generation**: Proper UUID handling for primary keys
- **Realistic Data**: All dummy data is contextually appropriate
- **Proper Relations**: Maintains referential integrity
- **Role Hierarchy**: Follows admin → owner → employee structure
- **Business Logic**: Realistic business scenarios and data

## Factory Integration

Updated factories to support the seeder system:

### UserFactory
- Supports role-based user creation
- Generates realistic Indonesian names and locations
- Proper salary ranges by role
- Gender-appropriate names

### BusinessFactory
- Industry-appropriate business names
- Realistic start dates and locations
- Proper category-business matching

### ProductFactory
- Category-appropriate products
- Realistic pricing by product type
- Proper stock levels and descriptions

### VoucherFactory
- Various promo code formats
- Realistic date ranges and quotas
- Different discount types

### PembayaranFactory
- Realistic transaction amounts
- Proper date distributions
- Voucher usage patterns

## Usage Instructions

### Running All Seeders
```bash
# Fresh migration and seed
php artisan migrate:fresh --seed

# Or run seeders separately
php artisan db:seed
```

### Running Specific Seeders
```bash
# Run comprehensive seeder only
php artisan db:seed --class=ComprehensiveSeeder

# Run super admin seeder only
php artisan db:seed --class=SuperAdminTableSeeder
```

### Development Testing
```bash
# Use factories for additional test data
php artisan tinker

# Create additional users
User::factory()->count(10)->create()

# Create additional businesses  
Business::factory()->count(5)->create()

# Create additional products
Product::factory()->count(20)->create()
```

## Data Relationships

The seeded data maintains proper relationships:

```
Admin (1)
├── Can manage all businesses
└── System-wide access

Owner (2)
├── Ahmad → Kopi Sentral Bandung
└── Sari → Bengkel Motor Jaya

Employees (4)
├── Budi, Dewi → Kopi Sentral Bandung
└── Eko, Fitri → Bengkel Motor Jaya

Products (10)
├── Coffee products → Kopi Sentral
├── Auto services → Bengkel Motor
└── Electronics → Toko Elektronik

Transactions (7)
├── Employee purchases
├── Cross-business transactions
└── Voucher usage examples
```

## File Cleanup

### Removed Files
The following unused seeders and factories were removed:
- `AddAcountSeeder.php`
- `AddBusinesSeeder.php` 
- `BusinessCategorySeeder.php` (old version)
- `CategoryBusinesSeeder.php`
- `CategoryProductSeeder.php` (old version)
- `OwnerTableSeeder.php`
- `PegawaiTableSeeder.php`
- `PembayaranSeeder.php` (old version)
- `ProductsSeeder.php`
- `RolesTableSeeder.php`
- `StatusBusinesSeeder.php`
- `StatusProductSeeder.php` (old version)
- `StatusTransaksiSeeder.php` (old version)
- `VoucherSeeder.php` (old version)

### Remaining Files
Clean, updated files that match current schema:
- `DatabaseSeeder.php` - Main orchestrator
- `ComprehensiveSeeder.php` - Complete data seeding
- `SuperAdminTableSeeder.php` - Owner's admin account
- Updated factories for all models

## Testing Data

After seeding, you can test the application with these credentials:

### Admin Access
- **Email**: `admin@pioneermanagement.com`
- **Password**: `admin123`
- **Capabilities**: Full system access, user management

### Owner Access  
- **Email**: `ahmad@kopisentral.com` 
- **Password**: `owner123`
- **Capabilities**: Business management, employee oversight

### Employee Access
- **Email**: `budi@kopisentral.com`
- **Password**: `employee123`  
- **Capabilities**: Product management, transaction processing

### Super Admin (Project Owner)
- **Email**: `pioneersolve@gmail.com`
- **Password**: `Bismillah@1`
- **Capabilities**: System administration

This seeder system provides a complete, realistic dataset for development and testing while maintaining data integrity and proper relationships throughout the application.