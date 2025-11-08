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
        Schema::create('voucher', function (Blueprint $table) {
            $table->char('uuid', 36)->primary();
            $table->char('user_id', 36);
            $table->unsignedBigInteger('status_product_id');
            $table->string('kode_promo');
            $table->string('tipe_promo');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('kouta');
            $table->timestamps();

            $table->index(['user_id'], 'vouchers_user_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher');
    }
};
