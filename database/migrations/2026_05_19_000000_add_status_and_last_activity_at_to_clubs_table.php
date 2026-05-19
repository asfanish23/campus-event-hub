<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            if (! Schema::hasColumn('clubs', 'status')) {
                $table->string('status')->default('active')->after('background_position_v');
            }

            if (! Schema::hasColumn('clubs', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            if (Schema::hasColumn('clubs', 'last_activity_at')) {
                $table->dropColumn('last_activity_at');
            }

            if (Schema::hasColumn('clubs', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};