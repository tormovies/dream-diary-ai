<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite не поддерживает изменение колонок напрямую
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');
            DB::statement('CREATE TABLE dreams_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(255) NULL,
                description TEXT NOT NULL,
                dream_type VARCHAR(255) NOT NULL,
                "order" INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
            );');
            DB::statement('INSERT INTO dreams_new SELECT * FROM dreams;');
            DB::statement('DROP TABLE dreams;');
            DB::statement('ALTER TABLE dreams_new RENAME TO dreams;');
            DB::statement('CREATE INDEX dreams_report_id_index ON dreams(report_id);');
            DB::statement('CREATE INDEX dreams_dream_type_index ON dreams(dream_type);');
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            Schema::table('dreams', function (Blueprint $table) {
                $table->string('title')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'sqlite') {
            // Для SQLite откат сложен, оставляем как есть
        } else {
            Schema::table('dreams', function (Blueprint $table) {
                $table->string('title')->nullable(false)->change();
            });
        }
    }
};
