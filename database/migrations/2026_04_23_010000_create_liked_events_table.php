<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('liked_events')) {
            Schema::create('liked_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('event_id');
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->unique(['user_id', 'event_id']);
            });
        }

        // Migrate legacy data if old table exists.
        if (Schema::hasTable('event_likes')) {
            DB::statement('INSERT IGNORE INTO liked_events (user_id, event_id, created_at) SELECT user_id, event_id, created_at FROM event_likes');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liked_events');
    }
};
