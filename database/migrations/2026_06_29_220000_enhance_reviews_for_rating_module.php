<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('event_id')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('reviews', 'comment')) {
                $table->text('comment')->nullable()->after('rating');
            }

            if (!Schema::hasColumn('reviews', 'is_reported')) {
                $table->boolean('is_reported')->default(false)->after('review_text');
            }

            if (!Schema::hasColumn('reviews', 'reported_by_admin_id')) {
                $table->foreignId('reported_by_admin_id')->nullable()->after('is_reported')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('reviews', 'reported_at')) {
                $table->timestamp('reported_at')->nullable()->after('reported_by_admin_id');
            }
        });

        if (Schema::hasColumn('reviews', 'comment') && Schema::hasColumn('reviews', 'review_text')) {
            DB::table('reviews')
                ->whereNull('comment')
                ->update(['comment' => DB::raw('review_text')]);
        }

        if (Schema::hasColumn('reviews', 'user_id')) {
            DB::statement('UPDATE reviews r JOIN users u ON u.name = r.reviewer_name SET r.user_id = u.id WHERE r.user_id IS NULL');
        }

        DB::statement('CREATE UNIQUE INDEX reviews_event_user_unique ON reviews (event_id, user_id)');
    }

    public function down(): void
    {
        if (Schema::hasTable('reviews')) {
            DB::statement('DROP INDEX reviews_event_user_unique ON reviews');
        }

        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'reported_at')) {
                $table->dropColumn('reported_at');
            }

            if (Schema::hasColumn('reviews', 'reported_by_admin_id')) {
                $table->dropConstrainedForeignId('reported_by_admin_id');
            }

            if (Schema::hasColumn('reviews', 'is_reported')) {
                $table->dropColumn('is_reported');
            }

            if (Schema::hasColumn('reviews', 'comment')) {
                $table->dropColumn('comment');
            }

            if (Schema::hasColumn('reviews', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
