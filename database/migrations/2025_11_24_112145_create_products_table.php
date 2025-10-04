<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
             $table->uuid('uuid')->primary();

            // Foreign keys (all UUID)
            $table->uuid('user_id');
            $table->uuid('category_id');
            $table->unsignedBigInteger('category_business_id');

            // Data fields
            $table->string('name_product');
            $table->text('deskripsi')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock');

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('uuid')->on('category_products');
            $table->foreign('category_business_id')->references('id')->on('category_busines');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
