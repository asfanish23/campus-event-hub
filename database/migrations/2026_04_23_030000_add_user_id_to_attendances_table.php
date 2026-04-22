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
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            // Prevent duplicate attendance for the same user-event pair.
            $table->unique(['user_id', 'event_id'], 'attendances_user_event_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_user_event_unique');
            if (Schema::hasColumn('attendances', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
