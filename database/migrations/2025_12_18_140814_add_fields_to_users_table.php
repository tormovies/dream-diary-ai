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
            $table->enum('role', ['admin', 'user'])->default('user')->after('email_verified_at');
            $table->string('nickname')->unique()->after('name');
            $table->string('avatar')->nullable()->after('nickname');
            $table->text('bio')->nullable()->after('avatar');
            $table->enum('diary_privacy', ['public', 'private', 'friends'])->default('private')->after('bio');
            $table->string('public_link')->unique()->nullable()->after('diary_privacy');
            
            $table->index('role');
            $table->index('nickname');
            $table->index('public_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'nickname', 'avatar', 'bio', 'diary_privacy', 'public_link']);
        });
    }
};
