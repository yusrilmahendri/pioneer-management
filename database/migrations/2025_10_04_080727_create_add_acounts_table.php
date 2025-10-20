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
        Schema::create('add_acounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('status_id');
            $table->string('provinsi_id');   // ID dari API (misal: "19")
            $table->string('provinsi_nama'); // Nama provinsi (misal: "Sumatera Selatan")
            $table->string('kabupaten_id');  // ID dari API (misal: "1902")
            $table->string('kabupaten_nama'); // Nama kabupaten (misal: "Kota Palembang")
            $table->string('nama_business')->nullable();
            $table->string('start_business')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('category_busines');
            $table->foreign('status_id')->references('id')->on('status_busines');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_acounts');
    }
};
