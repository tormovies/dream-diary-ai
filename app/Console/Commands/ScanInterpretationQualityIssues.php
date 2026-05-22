<?php

namespace App\Console\Commands;

use App\Models\DreamInterpretation;
use App\Models\DreamInterpretationStat;
use App\Support\InterpretationQualityAnalyzer;
use Illuminate\Console\Command;

class ScanInterpretationQualityIssues extends Command
{
    protected $signature = 'interpretations:scan-quality-issues
                            {--chunk=200 : Размер чанка}
                            {--only-completed : Только processing_status=completed}
                            {--rescan : Перепроверить все (в т.ч. с уже проставленной меткой)}
                            {--dry-run : Только показать, без записи в БД}';

    protected $description = 'Найти и проставить analysis_issue у существующих толкований (обрезка, ошибка JSON)';

    public function handle(): int
    {
        $query = DreamInterpretation::query()
            ->select(['id', 'analysis_data', 'raw_api_response', 'processing_status']);

        if (! $this->option('rescan')) {
            $query->whereNull('analysis_issue');
        }

        if ($this->option('only-completed')) {
            $query->where('processing_status', 'completed');
        }

        $total = (clone $query)->count();
        $this->info("Кандидатов для проверки: {$total}");

        if ($total === 0) {
            return 0;
        }

        if ($this->option('dry-run')) {
            $this->warn('Режим dry-run: записи в БД не обновляются.');

            return 0;
        }

        $chunk = (int) $this->option('chunk');
        $found = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->orderBy('id')->chunk($chunk, function ($interpretations) use (&$found, $bar) {
            foreach ($interpretations as $interpretation) {
                $issue = InterpretationQualityAnalyzer::detect(
                    is_array($interpretation->analysis_data) ? $interpretation->analysis_data : null,
                    $interpretation->raw_api_response,
                    false
                );

                if ($issue !== null) {
                    $found++;
                }

                DreamInterpretation::where('id', $interpretation->id)->update(['analysis_issue' => $issue]);
                $stat = DreamInterpretationStat::where('dream_interpretation_id', $interpretation->id)->first();
                if ($stat) {
                    $stat->update(['analysis_issue' => $issue]);
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Готово. Найдено проблемных: {$found}");

        return 0;
    }
}
