<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->string('product_type')->default('simple')->after('club_id');
            }
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            }
            if (!Schema::hasColumn('cart_items', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('cart_items', 'variant_size')) {
                $table->string('variant_size')->nullable()->after('unit_price');
            }
            if (!Schema::hasColumn('cart_items', 'variant_color')) {
                $table->string('variant_color')->nullable()->after('variant_size');
            }

            try {
                $table->dropUnique(['user_id', 'product_id']);
            } catch (\Throwable $e) {
                // Ignore if the index does not exist in this environment.
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'product_variant_id')) {
                $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('orders', 'variant_size')) {
                $table->string('variant_size')->nullable()->after('unit_price');
            }
            if (!Schema::hasColumn('orders', 'variant_color')) {
                $table->string('variant_color')->nullable()->after('variant_size');
            }
        });

        if (Schema::hasTable('products')) {
            DB::table('products')->whereNull('product_type')->update(['product_type' => 'simple']);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'product_variant_id')) {
                $table->dropConstrainedForeignId('product_variant_id');
            }
            if (Schema::hasColumn('orders', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
            if (Schema::hasColumn('orders', 'variant_size')) {
                $table->dropColumn('variant_size');
            }
            if (Schema::hasColumn('orders', 'variant_color')) {
                $table->dropColumn('variant_color');
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->dropConstrainedForeignId('product_variant_id');
            }
            if (Schema::hasColumn('cart_items', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
            if (Schema::hasColumn('cart_items', 'variant_size')) {
                $table->dropColumn('variant_size');
            }
            if (Schema::hasColumn('cart_items', 'variant_color')) {
                $table->dropColumn('variant_color');
            }
        });

        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'product_type')) {
                $table->dropColumn('product_type');
            }
        });
    }
};