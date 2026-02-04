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
        Schema::table('instagram_accounts', function (Blueprint $table) {
            // OAuth fields
            $table->longText('refresh_token')->nullable()->after('access_token'); // Encrypted
            $table->string('oauth_state')->nullable()->after('refresh_token'); // For OAuth flow security
            $table->string('connection_method')->default('manual')->after('oauth_state'); // 'oauth' or 'manual'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instagram_accounts', function (Blueprint $table) {
            $table->dropColumn(['refresh_token', 'oauth_state', 'connection_method']);
        });
    }
};
