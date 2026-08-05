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
    protected $signature = 'dream-interpretations:migrate
                            {--force : Принудительно мигрировать все записи, даже если уже есть нормализованные данные}
                            {--only-empty : Только записи с пустым dream_detailed / overall_theme (восстановление из analysis_data/raw)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Мигрировать / пересобрать нормализованные данные анализа снов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начинаем миграцию данных анализа снов...');

        $query = DreamInterpretation::query()
            ->whereNull('api_error')
            ->where(function ($q) {
                $q->whereNotNull('analysis_data')->orWhereNotNull('raw_api_response');
            });

        if ($this->option('only-empty')) {
            $query->whereHas('result', function ($q) {
                $q->where(function ($inner) {
                    $inner->where(function ($s) {
                        $s->where('type', 'single')
                            ->where(function ($e) {
                                $e->whereNull('dream_detailed')->orWhere('dream_detailed', '');
                            });
                    })->orWhere(function ($s) {
                        $s->where('type', 'series')
                            ->where(function ($e) {
                                $e->whereNull('overall_theme')->orWhere('overall_theme', '');
                            });
                    });
                });
            });
        } elseif (! $this->option('force')) {
            $query->whereNotNull('analysis_data')->doesntHave('result');
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
        $stillEmpty = 0;

        $query->chunk(100, function ($interpretations) use (&$success, &$errors, &$stillEmpty, $bar) {
            foreach ($interpretations as $interpretation) {
                try {
                    $filled = $this->migrateInterpretation($interpretation);
                    if ($filled) {
                        $success++;
                    } else {
                        $stillEmpty++;
                    }
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

        $this->info('Миграция завершена!');
        $this->table(
            ['Статус', 'Количество'],
            [
                ['Заполнено', $success],
                ['Всё ещё пусто (нужен повтор API)', $stillEmpty],
                ['Ошибки', $errors],
                ['Всего', $total],
            ]
        );

        return 0;
    }

    /**
     * @return bool true если после миграции есть usable content
     */
    private function migrateInterpretation(DreamInterpretation $interpretation): bool
    {
        if (! $this->option('force') && ! $this->option('only-empty') && $interpretation->result) {
            return false;
        }

        $rawAnalysisData = is_array($interpretation->analysis_data) ? $interpretation->analysis_data : null;

        // Если в analysis_data нет usable-полей — пробуем перепарсить raw_api_response
        if (! $rawAnalysisData || ! \App\Support\InterpretationQualityAnalyzer::hasUsableAnalysisContent($rawAnalysisData)) {
            $payload = json_decode((string) $interpretation->raw_api_response, true);
            $content = $payload['choices'][0]['message']['content'] ?? '';
            if ($content !== '') {
                $parsed = \App\Support\DeepSeekJsonParser::parseFromAssistantContent($content);
                if (is_array($parsed['data'] ?? null)) {
                    $rawAnalysisData = $parsed['data'];
                    // Сохраняем восстановленный analysis_data
                    $interpretation->update(['analysis_data' => $rawAnalysisData]);
                }
            }
        }

        if (empty($rawAnalysisData)) {
            return false;
        }

        $version = DreamAnalysisAdapterFactory::detectVersion($rawAnalysisData);
        $adapter = DreamAnalysisAdapterFactory::getAdapter($version);
        $normalized = $adapter->normalize($rawAnalysisData);

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
            $singleAnalysis = $normalized['single_analysis'];
            $result->update([
                'dream_title' => $this->truncateVarchar($singleAnalysis['dream_title'] ?? null),
                'dream_detailed' => $singleAnalysis['dream_detailed'] ?? null,
                'dream_type' => $this->truncateVarchar($singleAnalysis['dream_type'] ?? null),
                'key_symbols' => $singleAnalysis['key_symbols'] ?? [],
                'unified_locations' => $singleAnalysis['unified_locations'] ?? [],
                'key_tags' => $singleAnalysis['key_tags'] ?? [],
                'summary_insight' => $singleAnalysis['summary_insight'] ?? null,
                'emotional_tone' => $this->truncateVarchar($singleAnalysis['emotional_tone'] ?? null),
            ]);
            $filled = ! empty($singleAnalysis['dream_detailed']);
        } else {
            $seriesAnalysis = $normalized['series_analysis'];
            $result->update([
                'series_title' => $this->truncateVarchar($seriesAnalysis['series_title'] ?? null),
                'overall_theme' => $seriesAnalysis['overall_theme'] ?? null,
                'emotional_arc' => $seriesAnalysis['emotional_arc'] ?? null,
                'key_connections' => $seriesAnalysis['key_connections'] ?? [],
            ]);

            $result->seriesDreams()->delete();

            foreach ($seriesAnalysis['dreams'] ?? [] as $dreamData) {
                DreamInterpretationSeriesDream::create([
                    'dream_interpretation_result_id' => $result->id,
                    'dream_number' => $dreamData['dream_number'] ?? 1,
                    'dream_title' => $this->truncateVarchar($dreamData['dream_title'] ?? null),
                    'dream_detailed' => $dreamData['dream_detailed'] ?? null,
                    'dream_type' => $this->truncateVarchar($dreamData['dream_type'] ?? null),
                    'key_symbols' => $dreamData['key_symbols'] ?? [],
                    'unified_locations' => $dreamData['unified_locations'] ?? [],
                    'key_tags' => $dreamData['key_tags'] ?? [],
                    'summary_insight' => $dreamData['summary_insight'] ?? null,
                    'emotional_tone' => $this->truncateVarchar($dreamData['emotional_tone'] ?? null),
                    'order' => $dreamData['dream_number'] ?? 1,
                ]);
            }
            $filled = ! empty($seriesAnalysis['overall_theme']);
        }

        if ($filled) {
            $interpretation->update(['analysis_issue' => null]);
            $stat = \App\Models\DreamInterpretationStat::where('dream_interpretation_id', $interpretation->id)->first();
            if ($stat) {
                $stat->update(['analysis_issue' => null]);
            }

            // Восстановить context_for_next_analysis в связанный отчёт
            if ($interpretation->report_id) {
                $ctx = \App\Support\AnalysisContextExtractor::extract(
                    is_array($rawAnalysisData) ? $rawAnalysisData : null,
                    $interpretation->raw_api_response
                );
                if (is_array($ctx) && $ctx !== []) {
                    \App\Models\Report::where('id', $interpretation->report_id)->update([
                        'current_context' => json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                }
            }
        }

        return $filled;
    }

    private function truncateVarchar(?string $value, int $max = 255): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
    }
}
