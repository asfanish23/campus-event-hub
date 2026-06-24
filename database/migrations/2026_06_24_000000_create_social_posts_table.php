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
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['instagram', 'facebook', 'threads']);
            $table->string('platform_post_id')->nullable();
            $table->enum('status', ['posted', 'failed', 'pending'])->default('pending');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'platform']);
            $table->index(['platform', 'status']);
            $table->index('posted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
