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
        Schema::create('telegram_user_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_chat_id')->unique()->comment('Telegram chat ID (primary identifier)');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->comment('Optional: linked website user account');
            $table->json('category_preferences')->nullable()->comment('JSON array of event categories user is interested in');
            $table->boolean('notifications_enabled')->default(true);
            $table->string('notification_time')->default('09:00')->comment('Preferred time for weekly notifications (HH:MM format)');
            $table->integer('days_in_advance')->default(7)->comment('Show events for next N days');
            $table->boolean('send_event_updates')->default(true);
            $table->boolean('send_club_news')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_user_preferences');
    }
};
