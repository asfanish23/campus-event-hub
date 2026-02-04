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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // User and reference information
            $table->unsignedBigInteger('user_id');
            $table->string('bill_code')->nullable()->unique(); // ToyyibPay bill code
            $table->string('external_reference_no')->nullable(); // Your internal reference
            $table->enum('payment_type', ['merchandise', 'event_registration']); // Type of payment
            $table->unsignedBigInteger('related_id')->nullable(); // ID of product/event
            
            // Payment details
            $table->decimal('amount', 10, 2); // Amount in RM (stored in RM, converted to cents for API)
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            
            // ToyyibPay response data
            $table->string('billpl_code')->nullable();
            $table->string('bill_name')->nullable();
            $table->string('bill_description')->nullable();
            $table->string('bill_url')->nullable(); // The URL to redirect user for payment
            
            // Payment verification
            $table->timestamp('transaction_time')->nullable(); // When payment was completed
            $table->string('payment_reference')->nullable(); // ToyyibPay reference
            $table->json('callback_response')->nullable(); // Store full callback response
            
            // Metadata
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for faster queries
            $table->index('user_id');
            $table->index('status');
            $table->index('bill_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
