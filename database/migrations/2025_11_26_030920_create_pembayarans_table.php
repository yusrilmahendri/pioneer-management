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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->char('uuid', 36)->primary();
            $table->char('user_id', 36);
            $table->char('product_id', 36);
            $table->char('kode_voucher', 36)->nullable();
            $table->unsignedInteger('count');
            $table->decimal('price', 15, 2);
            $table->date('date_order');
            $table->timestamps();

            $table->index(['kode_voucher'], 'fk_pembayarans_kode_voucher');
            $table->index(['user_id'], 'idx_pembayarans_user_id');
            $table->index(['product_id'], 'idx_pembayarans_product_id');
            $table->index(['date_order'], 'idx_pembayarans_date_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
