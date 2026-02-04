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
            // Add repost scheduling columns
            $table->boolean('instagram_auto_repost')->default(false)->after('instagram_scheduled_posted');
            $table->timestamp('instagram_repost_at')->nullable()->after('instagram_auto_repost');
            $table->boolean('instagram_reposted')->default(false)->after('instagram_repost_at');
            
            // Index for efficient querying
            $table->index(['instagram_auto_repost', 'instagram_repost_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['instagram_auto_repost', 'instagram_repost_at']);
            $table->dropColumn(['instagram_auto_repost', 'instagram_repost_at', 'instagram_reposted']);
        });
    }
};
