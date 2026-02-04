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
        Schema::create('instagram_activity_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('club_id')->constrained()->onDelete('cascade');
            $table->enum('activity_type', [
                'post_created',
                'engagement_milestone',
                'reach_milestone',
                'sync_complete'
            ]);
            $table->integer('milestone_value')->nullable(); // e.g., 100 likes
            $table->string('milestone_label')->nullable(); // e.g., "100 Likes"
            $table->text('message');
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('club_id');
            $table->index('event_id');
            $table->index(['club_id', 'read']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_activity_notifications');
    }
};
