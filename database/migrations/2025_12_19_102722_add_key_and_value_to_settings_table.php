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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('key')->nullable();
            $table->text('value')->nullable();
        });
        
        // SQLite не поддерживает добавление UNIQUE колонок напрямую
        // Создаем индекс отдельно
        if (config('database.default') === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS settings_key_unique ON settings(key)');
        } else {
            Schema::table('settings', function (Blueprint $table) {
                $table->unique('key');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем индекс перед удалением колонок
        if (config('database.default') === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS settings_key_unique');
        }
        
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['key', 'value']);
        });
    }
};
