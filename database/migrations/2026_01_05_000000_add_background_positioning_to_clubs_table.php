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
            $table->integer('background_position_h')->default(0)->after('background_photo');
            $table->integer('background_position_v')->default(0)->after('background_position_h');
            $table->integer('background_zoom')->default(100)->after('background_position_v');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn(['background_position_h', 'background_position_v', 'background_zoom']);
        });
    }
};
