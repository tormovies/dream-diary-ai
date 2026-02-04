<?php

namespace App\Console\Commands;

use App\Models\Redirect;
use Illuminate\Console\Command;

class ImportRedirectsFromFile extends Command
{
    protected $signature = 'redirects:import-from-file 
                            {--skip-existing : Не перезаписывать уже существующие from_path}
                            {--dry-run : Только показать, что будет импортировано}';
    protected $description = 'Импорт редиректов из routes/redirects.php в таблицу redirects';

    public function handle(): int
    {
        $path = base_path('routes/redirects.php');
        if (!is_readable($path)) {
            $this->error("Файл не найден: {$path}");
            return 1;
        }

        $content = file_get_contents($path);
        // Route::get('/path', ... redirect()->route('reports.show', 123, 301)
        $pattern = "/Route::get\('([^']+)',\s*function\s*\(\)\s*\{\s*return\s+redirect\(\)->route\('reports\.show',\s*(\d+)/";
        $matches = [];
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            $this->warn('В файле не найдено подходящих редиректов.');
            return 0;
        }

        $skipExisting = $this->option('skip-existing');
        $dryRun = $this->option('dry-run');
        $created = 0;
        $skipped = 0;

        $this->info('Найдено редиректов: ' . count($matches));

        foreach ($matches as $m) {
            $fromPath = Redirect::normalizePath($m[1]);
            $reportId = (int) $m[2];
            $toUrl = '/reports/' . $reportId;

            if ($skipExisting && Redirect::where('from_path', $fromPath)->exists()) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("  {$fromPath} → {$toUrl}");
                $created++;
                continue;
            }

            Redirect::updateOrCreate(
                ['from_path' => $fromPath],
                [
                    'to_url' => $toUrl,
                    'status_code' => 301,
                    'is_active' => true,
                ]
            );
            $created++;
        }

        if ($dryRun) {
            $this->info("[dry-run] Будет создано/обновлено: {$created}, пропущено: {$skipped}");
            return 0;
        }

        $this->info("Импортировано: {$created}, пропущено (уже есть): {$skipped}");
        return 0;
    }
}
