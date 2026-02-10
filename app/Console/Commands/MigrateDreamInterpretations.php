<?php

namespace App\Console\Commands;

use App\Models\DreamInterpretation;
use App\Models\DreamInterpretationResult;
use App\Models\DreamInterpretationSeriesDream;
use App\Services\DreamAnalysisAdapters\DreamAnalysisAdapterFactory;
use Illuminate\Console\Command;

class MigrateDreamInterpretations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dream-interpretations:migrate {--force : Принудительно мигрировать все записи, даже если уже есть нормализованные данные}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Мигрировать существующие данные анализа снов в нормализованную структуру (одноразовая миграция, обычно уже выполнена)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начинаем миграцию данных анализа снов...');

        $query = DreamInterpretation::whereNotNull('analysis_data')
            ->whereNull('api_error');

        if (!$this->option('force')) {
            $query->doesntHave('result');
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('Нет записей для миграции.');
            return 0;
        }

        $this->info("Найдено записей для миграции: {$total}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $errors = 0;

        // Обрабатываем чанками, чтобы не грузить все записи в память
        $query->chunk(100, function ($interpretations) use (&$success, &$errors, $bar) {
            foreach ($interpretations as $interpretation) {
                try {
                    $this->migrateInterpretation($interpretation);
                    $success++;
                } catch (\Exception $e) {
                    $errors++;
                    $this->newLine();
                    $this->error("Ошибка при миграции ID {$interpretation->id}: {$e->getMessage()}");
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Миграция завершена!");
        $this->table(
            ['Статус', 'Количество'],
            [
                ['Успешно', $success],
                ['Ошибки', $errors],
                ['Всего', $total],
            ]
        );

        return 0;
    }

    /**
     * Мигрирует одну интерпретацию
     */
    private function migrateInterpretation(DreamInterpretation $interpretation): void
    {
        // Проверяем, не существует ли уже нормализованных данных
        if (!$this->option('force') && $interpretation->result) {
            return;
        }

        $rawAnalysisData = $interpretation->analysis_data;
        if (empty($rawAnalysisData)) {
            return;
        }

        // Определяем версию формата
        $version = DreamAnalysisAdapterFactory::detectVersion($rawAnalysisData);
        
        // Получаем адаптер и нормализуем данные
        $adapter = DreamAnalysisAdapterFactory::getAdapter($version);
        $normalized = $adapter->normalize($rawAnalysisData);

        // Сохраняем нормализованные данные
        $result = DreamInterpretationResult::updateOrCreate(
            ['dream_interpretation_id' => $interpretation->id],
            [
                'type' => $normalized['type'],
                'format_version' => $normalized['version'],
                'traditions' => $normalized['traditions'],
                'analysis_type' => $normalized['analysis_type'],
                'recommendations' => $normalized['recommendations'],
            ]
        );

        if ($normalized['type'] === 'single') {
            // Сохраняем данные для одиночного сна
            $singleAnalysis = $normalized['single_analysis'];
            $result->update([
                'dream_title' => $singleAnalysis['dream_title'] ?? null,
                'dream_detailed' => $singleAnalysis['dream_detailed'] ?? null,
                'dream_type' => $singleAnalysis['dream_type'] ?? null,
                'key_symbols' => $singleAnalysis['key_symbols'] ?? [],
                'unified_locations' => $singleAnalysis['unified_locations'] ?? [],
                'key_tags' => $singleAnalysis['key_tags'] ?? [],
                'summary_insight' => $singleAnalysis['summary_insight'] ?? null,
                'emotional_tone' => $singleAnalysis['emotional_tone'] ?? null,
            ]);
        } else {
            // Сохраняем данные для серии снов
            $seriesAnalysis = $normalized['series_analysis'];
            $result->update([
                'series_title' => $seriesAnalysis['series_title'] ?? null,
                'overall_theme' => $seriesAnalysis['overall_theme'] ?? null,
                'emotional_arc' => $seriesAnalysis['emotional_arc'] ?? null,
                'key_connections' => $seriesAnalysis['key_connections'] ?? [],
            ]);

            // Удаляем старые сны в серии (если есть)
            $result->seriesDreams()->delete();

            // Сохраняем отдельные сны в серии
            foreach ($seriesAnalysis['dreams'] ?? [] as $dreamData) {
                DreamInterpretationSeriesDream::create([
                    'dream_interpretation_result_id' => $result->id,
                    'dream_number' => $dreamData['dream_number'] ?? 1,
                    'dream_title' => $dreamData['dream_title'] ?? null,
                    'dream_detailed' => $dreamData['dream_detailed'] ?? null,
                    'dream_type' => $dreamData['dream_type'] ?? null,
                    'key_symbols' => $dreamData['key_symbols'] ?? [],
                    'unified_locations' => $dreamData['unified_locations'] ?? [],
                    'key_tags' => $dreamData['key_tags'] ?? [],
                    'summary_insight' => $dreamData['summary_insight'] ?? null,
                    'emotional_tone' => $dreamData['emotional_tone'] ?? null,
                    'order' => $dreamData['dream_number'] ?? 1,
                ]);
            }
        }
    }
}
