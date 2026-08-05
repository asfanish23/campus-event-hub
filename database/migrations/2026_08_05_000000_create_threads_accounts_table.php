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
        Schema::create('threads_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->onDelete('cascade');
            $table->string('threads_username');
            $table->string('threads_user_id');
            $table->longText('access_token'); // Encrypted
            $table->boolean('is_active')->default(true);
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('last_post_at')->nullable();
            $table->longText('refresh_token')->nullable(); // Encrypted
            $table->string('oauth_state')->nullable(); // For OAuth flow security
            $table->string('connection_method')->default('manual'); // 'oauth' or 'manual'
            $table->timestamps();

            $table->unique(['club_id']); // One Threads account per club
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threads_accounts');
    }
};
