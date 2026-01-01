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
        // Убираем UNIQUE constraint с dream_interpretation_id (если существует)
        // Проверяем наличие уникального индекса
        $indexes = DB::select(
            "SHOW INDEX FROM dream_interpretation_results WHERE Column_name = 'dream_interpretation_id' AND Non_unique = 0"
        );
        
        if (!empty($indexes)) {
            // Находим имя индекса
            $indexName = $indexes[0]->Key_name;
            DB::statement("ALTER TABLE dream_interpretation_results DROP INDEX `{$indexName}`");
        }
        
        // Проверяем существующие колонки
        $columns = collect(DB::select('SHOW COLUMNS FROM dream_interpretation_results'))
            ->pluck('Field')
            ->toArray();
        
        Schema::table('dream_interpretation_results', function (Blueprint $table) use ($columns) {
            // Добавляем новые поля для новой системы (только если их еще нет)
            if (!in_array('tradition_name', $columns)) {
                $table->string('tradition_name', 50)->nullable()->after('dream_interpretation_id')
                    ->comment('Название традиции (freudian, jungian, etc) или comparison/synthesis/integrated');
            }
            if (!in_array('result_type', $columns)) {
                $table->string('result_type', 20)->default('tradition')->after('tradition_name')
                    ->comment('Тип результата: tradition, comparison, synthesis, integrated');
            }
            if (!in_array('analysis_data', $columns)) {
                // Добавляем JSON поле для хранения полного анализа
                // (будет содержать все секции: dream_metadata, core_analysis, symbolic_elements и т.д.)
                $table->json('analysis_data')->nullable()->after('key_connections')
                    ->comment('Полные данные анализа в формате JSON (новая унифицированная система)');
            }
        });
        
        // Проверяем существующие индексы
        $existingIndexes = collect(DB::select('SHOW INDEX FROM dream_interpretation_results'))
            ->pluck('Key_name')
            ->unique()
            ->toArray();
        
        // Добавляем индексы для новых полей (только если их еще нет)
        Schema::table('dream_interpretation_results', function (Blueprint $table) use ($existingIndexes) {
            if (!in_array('dream_interpretation_results_tradition_name_index', $existingIndexes)) {
                $table->index('tradition_name');
            }
            if (!in_array('dream_interpretation_results_result_type_index', $existingIndexes)) {
                $table->index('result_type');
            }
            if (!in_array('dir_id_tradition_idx', $existingIndexes)) {
                $table->index(['dream_interpretation_id', 'tradition_name'], 'dir_id_tradition_idx');
            }
            if (!in_array('dir_id_type_idx', $existingIndexes)) {
                $table->index(['dream_interpretation_id', 'result_type'], 'dir_id_type_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dream_interpretation_results', function (Blueprint $table) {
            // Удаляем индексы
            $table->dropIndex('dir_id_type_idx');
            $table->dropIndex('dir_id_tradition_idx');
            $table->dropIndex(['result_type']);
            $table->dropIndex(['tradition_name']);
            
            // Удаляем новые поля
            $table->dropColumn(['analysis_data', 'result_type', 'tradition_name']);
            
            // Восстанавливаем UNIQUE constraint
            $table->unique('dream_interpretation_id');
        });
    }
};
