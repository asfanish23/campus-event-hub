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
            // Instagram posting tracking
            $table->string('instagram_media_id')->nullable()->after('qr_active');
            $table->timestamp('instagram_posted_at')->nullable()->after('instagram_media_id');
            $table->timestamp('instagram_last_synced_at')->nullable()->after('instagram_posted_at');

            // Instagram engagement metrics
            $table->integer('instagram_likes_count')->default(0)->after('instagram_last_synced_at');
            $table->integer('instagram_comments_count')->default(0)->after('instagram_likes_count');
            $table->integer('instagram_reach')->default(0)->after('instagram_comments_count');
            $table->integer('instagram_impressions')->default(0)->after('instagram_reach');
            $table->decimal('instagram_engagement_rate', 5, 2)->default(0)->after('instagram_impressions');
            
            // Index for querying Instagram-posted events
            $table->index('instagram_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['instagram_media_id']);
            $table->dropColumn([
                'instagram_media_id',
                'instagram_posted_at',
                'instagram_last_synced_at',
                'instagram_likes_count',
                'instagram_comments_count',
                'instagram_reach',
                'instagram_impressions',
                'instagram_engagement_rate',
            ]);
        });
    }
};
