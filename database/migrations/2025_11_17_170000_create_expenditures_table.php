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
        Schema::create('expenditures', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->unsignedBigInteger('id_business'); // Foreign key to business table
            $table->bigInteger('id_user'); // User who created the expenditure (match users table)
            $table->string('category'); // Free text category (operational, marketing, equipment, etc.)
            $table->text('description'); // Description of expenditure
            $table->decimal('amount', 15, 2); // Amount with 2 decimal places
            $table->string('receipt_image')->nullable(); // Optional receipt image path
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->bigInteger('id_user_approved')->nullable(); // ID of user who approved/rejected (match users table)
            $table->timestamp('approved_at')->nullable(); // When it was approved/rejected
            $table->text('notes')->nullable(); // Admin/Owner notes during approval/rejection
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('id_business')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_user_approved')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for better performance
            $table->index('id_business');
            $table->index('id_user');
            $table->index('status');
            $table->index('id_user_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenditures');
    }
};