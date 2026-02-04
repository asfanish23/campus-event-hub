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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable()->after('role');
            $table->enum('admin_status', ['pending', 'approved', 'rejected', 'not_admin'])->default('not_admin')->after('club_id');
            $table->text('admin_application_reason')->nullable()->after('admin_status');
            $table->timestamp('admin_submitted_at')->nullable()->after('admin_application_reason');
            
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
            $table->dropColumn(['club_id', 'admin_status', 'admin_application_reason', 'admin_submitted_at']);
        });
    }
};
