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
            $table->string('name_busines');
            $table->unsignedBigInteger('category_busines_id');
            $table->unsignedBigInteger('status_busines_id');
            $table->unsignedBigInteger("province_id");
            $table->unsignedBigInteger('kabupaten_id');
            $table->timestamps();

            $table->foreign('category_busines_id')->references('id')->on('category_busines');
            $table->foreign('status_busines_id')->references('id')->on('status_busines');
            $table->foreign('province_id')->references('id')->on('provinsis');
            $table->foreign('kabupaten_id')->references('id')->on('kabupatens');
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
