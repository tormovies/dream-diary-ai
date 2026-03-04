<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('entity_group_id')->nullable()->after('author_id')->constrained('entity_groups')->nullOnDelete();
        });

        DB::statement("ALTER TABLE articles MODIFY COLUMN type ENUM('guide', 'article', 'entity_group') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['entity_group_id']);
        });

        DB::statement("ALTER TABLE articles MODIFY COLUMN type ENUM('guide', 'article') NOT NULL");
    }
};
