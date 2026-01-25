<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DreamInterpretationResult;
use App\Helpers\TraditionHelper;

class CheckTraditionsMismatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'traditions:check-mismatch 
                            {--fix : Нормализовать данные в базе (преобразовать русские названия в ключи)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверяет несоответствия ключей традиций в базе данных (русские названия vs ключи конфига)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Проверка несоответствий ключей традиций ===');
        $this->newLine();

        // Получаем все записи с traditions
        $results = DreamInterpretationResult::whereNotNull('traditions')->get();
        
        $totalRecords = $results->count();
        $singleType = 0;
        $seriesType = 0;
        $needsFix = [];
        $stats = [
            'total' => 0,
            'single' => 0,
            'series' => 0,
            'fixed' => 0,
        ];

        $this->info("Всего записей с traditions: {$totalRecords}");
        $this->newLine();

        foreach ($results as $result) {
            if (!$result->traditions || !is_array($result->traditions)) {
                continue;
            }

            $stats['total']++;
            
            if ($result->type === 'single') {
                $stats['single']++;
            } elseif ($result->type === 'series') {
                $stats['series']++;
            }

            $needsNormalization = false;
            $normalizedTraditions = [];
            $originalTraditions = $result->traditions;

            foreach ($result->traditions as $tradition) {
                $normalized = TraditionHelper::normalizeKey($tradition);
                
                // Проверяем, нужна ли нормализация
                if ($normalized !== mb_strtolower(trim($tradition))) {
                    $needsNormalization = true;
                }
                
                // Убираем дубликаты
                if (!in_array($normalized, $normalizedTraditions)) {
                    $normalizedTraditions[] = $normalized;
                }
            }

            if ($needsNormalization) {
                $needsFix[] = [
                    'id' => $result->id,
                    'type' => $result->type,
                    'interpretation_id' => $result->dream_interpretation_id,
                    'old' => $originalTraditions,
                    'new' => $normalizedTraditions,
                ];
            }
        }

        // Выводим статистику
        $this->info('=== Статистика ===');
        $this->line("Всего записей: {$stats['total']}");
        $this->line("  - Одиночные (single): {$stats['single']}");
        $this->line("  - Серии (series): {$stats['series']}");
        $this->newLine();

        $mismatchCount = count($needsFix);
        $this->info("Записей с несоответствиями: {$mismatchCount}");
        
        if ($mismatchCount > 0) {
            // Группируем по типам
            $singleMismatches = array_filter($needsFix, fn($item) => $item['type'] === 'single');
            $seriesMismatches = array_filter($needsFix, fn($item) => $item['type'] === 'series');
            
            $this->line("  - Одиночные (single): " . count($singleMismatches));
            $this->line("  - Серии (series): " . count($seriesMismatches));
            $this->newLine();

            // Показываем примеры
            $this->info('=== Примеры несоответствий (первые 10) ===');
            foreach (array_slice($needsFix, 0, 10) as $item) {
                $this->line("ID: {$item['id']} (тип: {$item['type']}, interpretation_id: {$item['interpretation_id']})");
                $this->line("  Было: " . json_encode($item['old'], JSON_UNESCAPED_UNICODE));
                $this->line("  Будет: " . json_encode($item['new'], JSON_UNESCAPED_UNICODE));
                $this->newLine();
            }

            if ($mismatchCount > 10) {
                $this->line("... и еще " . ($mismatchCount - 10) . " записей");
                $this->newLine();
            }

            // Если указан флаг --fix, нормализуем данные
            if ($this->option('fix')) {
                $this->newLine();
                $this->warn('=== Нормализация данных ===');
                
                if (!$this->confirm("Вы уверены, что хотите обновить {$mismatchCount} записей в базе данных?")) {
                    $this->info('Операция отменена.');
                    return 0;
                }

                $bar = $this->output->createProgressBar($mismatchCount);
                $bar->start();

                foreach ($needsFix as $item) {
                    $result = DreamInterpretationResult::find($item['id']);
                    if ($result) {
                        $result->traditions = $item['new'];
                        $result->save();
                        $stats['fixed']++;
                    }
                    $bar->advance();
                }

                $bar->finish();
                $this->newLine(2);
                $this->info("✓ Обновлено записей: {$stats['fixed']}");
            } else {
                $this->newLine();
                $this->comment('Для нормализации данных запустите команду с флагом --fix:');
                $this->line('  php artisan traditions:check-mismatch --fix');
            }
        } else {
            $this->info('✓ Все записи уже нормализованы! Несоответствий не найдено.');
        }

        return 0;
    }
}
