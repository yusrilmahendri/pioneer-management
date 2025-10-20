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
        Schema::create('add_busines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_busines_id');
            $table->unsignedBigInteger('status_busines_id');
            $table->string('name_busines');
            $table->string('provinsi_id');   // ID dari API (misal: "19")
            $table->string('provinsi_nama'); // Nama provinsi (misal: "Sumatera Selatan")
            $table->string('kabupaten_id');  // ID dari API (misal: "1902")
            $table->string('kabupaten_nama'); // Nama kabupaten (misal: "Kota Palembang")
            $table->date('start_date'); 
            $table->timestamps();

            $table->foreign('category_busines_id')->references('id')->on('category_busines');
            $table->foreign('status_busines_id')->references('id')->on('status_busines');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_busines');
    }
};
