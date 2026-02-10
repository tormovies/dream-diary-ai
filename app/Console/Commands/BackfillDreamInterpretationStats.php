<?php

namespace App\Console\Commands;

use App\Models\DreamInterpretation;
use App\Models\DreamInterpretationStat;
use Illuminate\Console\Command;

class BackfillDreamInterpretationStats extends Command
{
    protected $signature = 'interpretations:backfill-stats 
                            {--chunk=500 : Размер чанка}
                            {--dry-run : Только показать количество}';
    protected $description = 'Заполнить dream_interpretation_stats из существующих dream_interpretations';

    public function handle(): int
    {
        $total = DreamInterpretation::count();
        $this->info("Всего толкований: {$total}");

        if ($this->option('dry-run')) {
            return 0;
        }

        $chunk = (int) $this->option('chunk');
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        DreamInterpretation::query()
            ->select('id', 'created_at', 'processing_status', 'traditions')
            ->chunk($chunk, function ($interpretations) use ($bar) {
                foreach ($interpretations as $interpretation) {
                    DreamInterpretationStat::syncFromInterpretation($interpretation);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Готово.');

        return 0;
    }
}
