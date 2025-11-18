<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid')->unique();
            $table->string('name_product');
            $table->text('description')->nullable();
            $table->decimal('price_product', 10, 2);
            $table->integer('stock');
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('updated_by')->nullable();
            $table->unsignedBigInteger('id_business');
            $table->unsignedBigInteger('id_product_category');
            $table->unsignedBigInteger('id_product_status');
            
            // Add foreign key constraints
            $table->foreign('id_business')->references('id')->on('business');
            $table->foreign('id_product_category')->references('id')->on('product_category');
            $table->foreign('id_product_status')->references('id')->on('product_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
