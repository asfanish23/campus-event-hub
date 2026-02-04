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
        Schema::table('products', function (Blueprint $table) {
            // Add club_id if it doesn't exist
            if (!Schema::hasColumn('products', 'club_id')) {
                $table->unsignedBigInteger('club_id')->nullable()->after('id');
                $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeignIdFor('Club');
            $table->dropColumn('club_id');
        });
    }
};
