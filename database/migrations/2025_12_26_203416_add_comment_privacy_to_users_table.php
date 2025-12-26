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
            $table->enum('comment_privacy', ['all', 'friends', 'only_me', 'none'])
                  ->default('all')
                  ->after('diary_privacy')
                  ->comment('Кто может комментировать отчёты: all - все, friends - только друзья, only_me - только я, none - никто');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('comment_privacy');
        });
    }
};
