<?php

namespace App\Console\Commands;

use App\Models\SeoMeta;
use App\Models\DreamInterpretation;
use App\Models\Report;
use Illuminate\Console\Command;

class CleanOrphanedSeoMeta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:clean-orphaned 
                            {--dry-run : Показать что будет удалено без фактического удаления}
                            {--force : Выполнить удаление без подтверждения}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удалить SEO параметры, для которых не существует связанных отчетов или толкований';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔍 Поиск "сиротских" SEO записей...');
        $this->newLine();

        $orphanedSeo = collect();

        // 1. Проверяем 'dream-analyzer-result' - толкования снов
        $this->info('📋 Проверка толкований снов (dream-analyzer-result)...');
        $dreamAnalyzerSeo = SeoMeta::where('page_type', 'dream-analyzer-result')
            ->whereNotNull('page_id')
            ->get();

        $dreamAnalyzerOrphaned = $dreamAnalyzerSeo->filter(function ($seo) {
            $interpretation = DreamInterpretation::find($seo->page_id);
            // Толкование не существует ИЛИ это анализ отчета (есть report_id)
            return !$interpretation || ($interpretation->report_id !== null);
        });

        $orphanedSeo = $orphanedSeo->merge($dreamAnalyzerOrphaned);
        $this->line("   Найдено: {$dreamAnalyzerSeo->count()} записей, из них сиротских: {$dreamAnalyzerOrphaned->count()}");

        // 2. Проверяем 'report-analysis' - анализы отчетов
        $this->info('📋 Проверка анализов отчетов (report-analysis)...');
        $reportAnalysisSeo = SeoMeta::where('page_type', 'report-analysis')
            ->whereNotNull('page_id')
            ->get();

        $reportAnalysisOrphaned = $reportAnalysisSeo->filter(function ($seo) {
            $interpretation = DreamInterpretation::with('report')->find($seo->page_id);
            // Толкование не существует ИЛИ нет report_id ИЛИ report удален
            return !$interpretation 
                || !$interpretation->report_id 
                || !$interpretation->report;
        });

        $orphanedSeo = $orphanedSeo->merge($reportAnalysisOrphaned);
        $this->line("   Найдено: {$reportAnalysisSeo->count()} записей, из них сиротских: {$reportAnalysisOrphaned->count()}");

        // 3. Проверяем 'report' - отчеты
        $this->info('📋 Проверка отчетов (report)...');
        $reportSeo = SeoMeta::where('page_type', 'report')
            ->whereNotNull('page_id')
            ->get();

        $reportOrphaned = $reportSeo->filter(function ($seo) {
            return !Report::find($seo->page_id);
        });

        $orphanedSeo = $orphanedSeo->merge($reportOrphaned);
        $this->line("   Найдено: {$reportSeo->count()} записей, из них сиротских: {$reportOrphaned->count()}");

        $this->newLine();
        $totalOrphaned = $orphanedSeo->unique('id')->count();

        if ($totalOrphaned === 0) {
            $this->info('✅ Сиротских SEO записей не найдено!');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  Найдено {$totalOrphaned} сиротских SEO записей:");
        $this->newLine();

        // Группируем по типу для вывода
        $grouped = $orphanedSeo->unique('id')->groupBy('page_type');
        
        foreach ($grouped as $pageType => $items) {
            $this->line("   <fg=yellow>{$pageType}:</> {$items->count()} записей");
            if ($this->output->isVerbose()) {
                foreach ($items->take(10) as $item) {
                    $this->line("      - ID: {$item->id}, page_id: {$item->page_id}, title: " . mb_substr($item->title ?? 'N/A', 0, 50));
                }
                if ($items->count() > 10) {
                    $this->line("      ... и еще " . ($items->count() - 10) . " записей");
                }
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('🔍 Режим проверки (--dry-run). Записи не будут удалены.');
            return Command::SUCCESS;
        }

        if (!$force) {
            if (!$this->confirm("Удалить эти {$totalOrphaned} записей?", true)) {
                $this->info('❌ Операция отменена.');
                return Command::SUCCESS;
            }
        }

        // Удаляем сиротские записи
        $idsToDelete = $orphanedSeo->unique('id')->pluck('id')->toArray();
        $deleted = SeoMeta::whereIn('id', $idsToDelete)->delete();

        $this->info("✅ Удалено {$deleted} сиротских SEO записей!");
        
        return Command::SUCCESS;
    }
}
