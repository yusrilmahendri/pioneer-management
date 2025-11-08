<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration fixes table name mismatches between old migrations and actual database schema
     */
    public function up(): void
    {
        // Rename tables to match actual database schema if they exist with old names
        
        if (Schema::hasTable('status_products') && !Schema::hasTable('product_status')) {
            Schema::rename('status_products', 'product_status');
        }
        
        if (Schema::hasTable('status_transaksis') && !Schema::hasTable('transaction_status')) {
            Schema::rename('status_transaksis', 'transaction_status');
        }
        
        if (Schema::hasTable('category_products') && !Schema::hasTable('product_category')) {
            Schema::rename('category_products', 'product_category');
        }
        
        if (Schema::hasTable('products') && !Schema::hasTable('product')) {
            Schema::rename('products', 'product');
        }
        
        if (Schema::hasTable('vouchers') && !Schema::hasTable('voucher')) {
            Schema::rename('vouchers', 'voucher');
        }
        
        // Update table structures to match actual database schema
        if (Schema::hasTable('product_status')) {
            Schema::table('product_status', function (Blueprint $table) {
                // Check if column exists before trying to rename
                if (Schema::hasColumn('product_status', 'name_status')) {
                    $table->renameColumn('name_status', 'product_status');
                }
            });
        }
        
        if (Schema::hasTable('transaction_status')) {
            Schema::table('transaction_status', function (Blueprint $table) {
                // Check if column exists before trying to rename
                if (Schema::hasColumn('transaction_status', 'name_status_transaksi')) {
                    $table->renameColumn('name_status_transaksi', 'transaction_status');
                }
            });
        }
        
        if (Schema::hasTable('product_category')) {
            Schema::table('product_category', function (Blueprint $table) {
                // Check if column exists before trying to rename
                if (Schema::hasColumn('product_category', 'category_product')) {
                    $table->renameColumn('category_product', 'product_category');
                }
            });
        }
        
        if (Schema::hasTable('product')) {
            Schema::table('product', function (Blueprint $table) {
                // Update columns to match actual schema
                if (Schema::hasColumn('product', 'name_product')) {
                    $table->renameColumn('name_product', 'product');
                }
                if (Schema::hasColumn('product', 'deskripsi')) {
                    $table->renameColumn('deskripsi', 'description');
                }
                
                // Add missing columns if they don't exist
                if (!Schema::hasColumn('product', 'id_business')) {
                    $table->unsignedBigInteger('id_business')->nullable();
                }
                if (!Schema::hasColumn('product', 'id_product_category')) {
                    $table->unsignedBigInteger('id_product_category')->nullable();
                }
                if (!Schema::hasColumn('product', 'id_product_status')) {
                    $table->unsignedBigInteger('id_product_status')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the table renames
        if (Schema::hasTable('product_status')) {
            Schema::rename('product_status', 'status_products');
        }
        
        if (Schema::hasTable('transaction_status')) {
            Schema::rename('transaction_status', 'status_transaksis');
        }
        
        if (Schema::hasTable('product_category')) {
            Schema::rename('product_category', 'category_products');
        }
        
        if (Schema::hasTable('product')) {
            Schema::rename('product', 'products');
        }
        
        if (Schema::hasTable('voucher')) {
            Schema::rename('voucher', 'vouchers');
        }
    }
};