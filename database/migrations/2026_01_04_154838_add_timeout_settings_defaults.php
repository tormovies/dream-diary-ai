<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Добавляем настройки таймаутов для DeepSeek API
        DB::table('settings')->insert([
            [
                'key' => 'deepseek_http_timeout',
                'value' => '600', // 10 минут (в секундах)
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'deepseek_php_execution_timeout',
                'value' => '660', // 11 минут (в секундах)
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'deepseek_http_timeout',
            'deepseek_php_execution_timeout',
        ])->delete();
    }
};
