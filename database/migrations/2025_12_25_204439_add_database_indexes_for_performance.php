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
        // Индексы для таблицы reports
        Schema::table('reports', function (Blueprint $table) {
            $table->index('user_id', 'idx_reports_user_id');
            $table->index('report_date', 'idx_reports_report_date');
            $table->index('status', 'idx_reports_status');
            $table->index('access_level', 'idx_reports_access_level');
            $table->index('created_at', 'idx_reports_created_at');
            $table->index(['status', 'access_level'], 'idx_reports_status_access');
        });

        // Индексы для таблицы dreams
        Schema::table('dreams', function (Blueprint $table) {
            $table->index('report_id', 'idx_dreams_report_id');
            $table->index('dream_type', 'idx_dreams_dream_type');
        });

        // Индексы для таблицы comments
        Schema::table('comments', function (Blueprint $table) {
            $table->index('report_id', 'idx_comments_report_id');
            $table->index('user_id', 'idx_comments_user_id');
            $table->index('parent_id', 'idx_comments_parent_id');
            $table->index('created_at', 'idx_comments_created_at');
        });

        // Индексы для таблицы friendships
        Schema::table('friendships', function (Blueprint $table) {
            $table->index('user_id', 'idx_friendships_user_id');
            $table->index('friend_id', 'idx_friendships_friend_id');
            $table->index('status', 'idx_friendships_status');
            $table->index(['user_id', 'status'], 'idx_friendships_user_status');
            $table->index(['friend_id', 'status'], 'idx_friendships_friend_status');
        });

        // Индексы для таблицы report_tag
        Schema::table('report_tag', function (Blueprint $table) {
            $table->index('report_id', 'idx_report_tag_report_id');
            $table->index('tag_id', 'idx_report_tag_tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаление индексов для reports
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('idx_reports_user_id');
            $table->dropIndex('idx_reports_report_date');
            $table->dropIndex('idx_reports_status');
            $table->dropIndex('idx_reports_access_level');
            $table->dropIndex('idx_reports_created_at');
            $table->dropIndex('idx_reports_status_access');
        });

        // Удаление индексов для dreams
        Schema::table('dreams', function (Blueprint $table) {
            $table->dropIndex('idx_dreams_report_id');
            $table->dropIndex('idx_dreams_dream_type');
        });

        // Удаление индексов для comments
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('idx_comments_report_id');
            $table->dropIndex('idx_comments_user_id');
            $table->dropIndex('idx_comments_parent_id');
            $table->dropIndex('idx_comments_created_at');
        });

        // Удаление индексов для friendships
        Schema::table('friendships', function (Blueprint $table) {
            $table->dropIndex('idx_friendships_user_id');
            $table->dropIndex('idx_friendships_friend_id');
            $table->dropIndex('idx_friendships_status');
            $table->dropIndex('idx_friendships_user_status');
            $table->dropIndex('idx_friendships_friend_status');
        });

        // Удаление индексов для report_tag
        Schema::table('report_tag', function (Blueprint $table) {
            $table->dropIndex('idx_report_tag_report_id');
            $table->dropIndex('idx_report_tag_tag_id');
        });
    }
};
