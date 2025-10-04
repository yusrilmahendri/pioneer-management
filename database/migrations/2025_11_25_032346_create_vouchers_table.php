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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_id');          // FK ke users.uuid     // FK ke status_products.uuid
            $table->unsignedBigInteger('status_product_id'); // FK ke status_products.id
            $table->string('kode_promo')->unique();
            $table->string('tipe_promo');     // e.g. percentage, nominal
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('kouta');
            $table->timestamps();

            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('status_product_id')->references('id')->on('status_products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
