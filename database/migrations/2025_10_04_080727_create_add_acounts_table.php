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
            $table->unsignedBigInteger('provinsi_id');
            $table->unsignedBigInteger('kabupaten_id');
            $table->unsignedBigInteger('status_id');
            $table->string('nama_business')->nullable();
            $table->string('start_business')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('category_busines');
            $table->foreign('provinsi_id')->references('id')->on('provinsis');
            $table->foreign('kabupaten_id')->references('id')->on('kabupatens');
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
