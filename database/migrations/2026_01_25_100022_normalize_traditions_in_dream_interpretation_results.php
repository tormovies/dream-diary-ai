<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Нормализует значения traditions в dream_interpretation_results:
     * преобразует русские названия (например, "эклектический") в ключи конфига (например, "eclectic")
     */
    public function up(): void
    {
        // Получаем все записи с traditions
        $results = DB::table('dream_interpretation_results')
            ->whereNotNull('traditions')
            ->get();

        foreach ($results as $result) {
            $traditions = json_decode($result->traditions, true);
            
            if (!is_array($traditions) || empty($traditions)) {
                continue;
            }

            // Нормализуем каждую традицию
            $normalizedTraditions = [];
            foreach ($traditions as $tradition) {
                $normalized = \App\Helpers\TraditionHelper::normalizeKey($tradition);
                // Убираем дубликаты
                if (!in_array($normalized, $normalizedTraditions)) {
                    $normalizedTraditions[] = $normalized;
                }
            }

            // Обновляем только если что-то изменилось
            if (json_encode($normalizedTraditions, JSON_UNESCAPED_UNICODE) !== json_encode($traditions, JSON_UNESCAPED_UNICODE)) {
                DB::table('dream_interpretation_results')
                    ->where('id', $result->id)
                    ->update([
                        'traditions' => json_encode($normalizedTraditions, JSON_UNESCAPED_UNICODE)
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * Откат не выполняется, так как мы не можем восстановить оригинальные русские названия
     */
    public function down(): void
    {
        // Откат не выполняется - нормализованные данные остаются
    }
};
