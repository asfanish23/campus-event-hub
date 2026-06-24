<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_posts', 'permalink')) {
                $table->string('permalink', 1000)
                      ->nullable()
                      ->after('platform_post_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            if (Schema::hasColumn('social_posts', 'permalink')) {
                $table->dropColumn('permalink');
            }
        });
    }
};