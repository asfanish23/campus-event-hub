<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('twitter_url');
            $table->string('threads_url')->nullable()->after('youtube_url');
            $table->string('tiktok_url')->nullable()->after('threads_url');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn(['youtube_url', 'threads_url', 'tiktok_url']);
        });
    }
};
