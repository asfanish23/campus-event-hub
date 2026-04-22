<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->unsignedInteger('quantity')->default(1);
                $table->timestamps();

                $table->unique(['user_id', 'product_id']);
            });
        }

        // Migrate any existing order items into cart only if the cart is empty.
        if (Schema::hasTable('orders') && Schema::hasTable('cart_items')) {
            DB::statement(
                'INSERT IGNORE INTO cart_items (user_id, product_id, quantity, created_at, updated_at) '
                . 'SELECT user_id, product_id, quantity, created_at, updated_at FROM orders'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
