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
        Schema::table('events', function (Blueprint $table) {
            // Instagram auto-posting and scheduling
            $table->boolean('instagram_auto_post')->default(false)->after('instagram_engagement_rate');
            $table->timestamp('instagram_scheduled_at')->nullable()->after('instagram_auto_post');
            
            // Track if scheduled post has been processed
            $table->boolean('instagram_scheduled_posted')->default(false)->after('instagram_scheduled_at');
            
            // Index for querying scheduled posts
            $table->index(['instagram_auto_post', 'instagram_scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['instagram_auto_post', 'instagram_scheduled_at']);
            $table->dropColumn([
                'instagram_auto_post',
                'instagram_scheduled_at',
                'instagram_scheduled_posted',
            ]);
        });
    }
};
