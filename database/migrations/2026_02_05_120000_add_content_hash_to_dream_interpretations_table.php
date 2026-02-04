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
        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->char('content_hash', 64)->nullable()->after('dream_description');
            $table->index(['content_hash', 'user_id']);
            $table->index(['content_hash', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->dropIndex(['content_hash', 'user_id']);
            $table->dropIndex(['content_hash', 'ip_address']);
            $table->dropColumn('content_hash');
        });
    }
};
