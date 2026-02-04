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
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
            $table->string('president_name')->nullable()->change();
            $table->string('president_contact')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->string('president_name')->nullable(false)->change();
            $table->string('president_contact')->nullable(false)->change();
        });
    }
};
