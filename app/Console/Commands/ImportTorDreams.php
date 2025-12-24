<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Report;
use App\Models\Dream;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportTorDreams extends Command
{
    protected $signature = 'import:tor-dreams {--dry-run : Run without saving to database}';
    protected $description = 'Import Tor dreams from JSON file';

    private array $redirects = [];
    private int $importedCount = 0;
    private int $skippedCount = 0;
    private int $errorCount = 0;

    public function handle()
    {
        $this->info('Starting import of Tor dreams...');
        
        // Проверяем файл
        $filePath = storage_path('import/merged_all_tor.json');
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        // Находим пользователя Tor
        $user = User::where('nickname', 'Tor')->first();
        if (!$user) {
            $this->error('User "Tor" not found in database!');
            return 1;
        }

        $this->info("Found user: {$user->nickname} (ID: {$user->id})");

        // Читаем JSON
        $this->info('Reading JSON file...');
        $json = json_decode(file_get_contents($filePath), true);
        
        if (!$json || !isset($json['posts'])) {
            $this->error('Invalid JSON format!');
            return 1;
        }

        $posts = $json['posts'];
        $totalPosts = count($posts);
        $this->info("Found {$totalPosts} posts to import");

        // Подтверждение
        if (!$this->option('dry-run')) {
            if (!$this->confirm('Do you want to continue with the import?', true)) {
                $this->info('Import cancelled.');
                return 0;
            }
        } else {
            $this->warn('DRY RUN MODE - no data will be saved');
        }

        // Импорт
        $progressBar = $this->output->createProgressBar($totalPosts);
        $progressBar->start();

        DB::beginTransaction();
        
        try {
            foreach ($posts as $post) {
                $this->importPost($post, $user);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            if (!$this->option('dry-run')) {
                DB::commit();
                $this->info('Transaction committed successfully!');
            } else {
                DB::rollBack();
                $this->warn('Transaction rolled back (dry-run mode)');
            }

            // Генерируем файл редиректов
            if (!$this->option('dry-run') && count($this->redirects) > 0) {
                $this->generateRedirectsFile();
            }

            // Статистика
            $this->newLine();
            $this->info('=== Import Summary ===');
            $this->info("Imported: {$this->importedCount}");
            $this->info("Skipped: {$this->skippedCount}");
            $this->info("Errors: {$this->errorCount}");
            $this->info("Redirects generated: " . count($this->redirects));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    private function importPost(array $post, User $user): void
    {
        try {
            // Парсим дату
            $dateText = $post['date_text'] ?? null;
            if (!$dateText) {
                $this->skippedCount++;
                return;
            }

            $reportDate = Carbon::parse($dateText);
            $url = $post['url'] ?? '';
            $content = $post['content'] ?? '';

            if (empty($content)) {
                $this->skippedCount++;
                return;
            }

            // Определяем статус и название сна
            $isRemspace = strpos($url, 'remspace.net') !== false;
            $isDiary = strpos($url, 'diary://') === 0;

            if (!$isRemspace && !$isDiary) {
                $this->skippedCount++;
                return;
            }

            // Для remspace.net
            if ($isRemspace) {
                $status = 'draft';
                $dreamTitle = null; // Будет автоматически сгенерировано
                $dreamDescription = $content;
            }
            // Для diary://
            else {
                $status = 'published';
                
                // Извлекаем название из первой строки
                $lines = explode("\n", $content);
                $dreamTitle = trim($lines[0]);
                
                // Остаток - описание
                unset($lines[0]);
                $dreamDescription = trim(implode("\n", $lines));
                
                // Парсим редирект
                if (preg_match('#diary://(\d+)/(.+)#', $url, $matches)) {
                    $num = $matches[1];
                    $slug = $matches[2];
                    $this->redirects[] = [
                        'old_url' => "/diaries/night/{$num}-{$slug}.html",
                        'report_id' => null, // Заполним после создания
                        'post_id' => $post['id'] ?? null,
                    ];
                }
            }

            // Создаем отчет
            if (!$this->option('dry-run')) {
                $report = Report::create([
                    'user_id' => $user->id,
                    'report_date' => $reportDate,
                    'access_level' => 'all',
                    'status' => $status,
                ]);

                // Если название не указано, генерируем автоматически
                if (empty($dreamTitle)) {
                    $dreamTitle = "Отчет от " . $reportDate->format('d.m.Y');
                }

                // Создаем сон
                Dream::create([
                    'report_id' => $report->id,
                    'title' => $dreamTitle,
                    'description' => $dreamDescription,
                    'dream_type' => 'Яркий сон', // По умолчанию для импортируемых снов
                    'order' => 1,
                ]);

                // Сохраняем ID отчета для редиректа
                if ($isDiary && count($this->redirects) > 0) {
                    $lastRedirectIndex = count($this->redirects) - 1;
                    if ($this->redirects[$lastRedirectIndex]['report_id'] === null &&
                        $this->redirects[$lastRedirectIndex]['post_id'] === ($post['id'] ?? null)) {
                        $this->redirects[$lastRedirectIndex]['report_id'] = $report->id;
                    }
                }
            }

            $this->importedCount++;

        } catch (\Exception $e) {
            $this->errorCount++;
            // Логируем ошибку для первых 10 ошибок
            if ($this->errorCount <= 10) {
                $postId = $post['id'] ?? 'unknown';
                \Log::error("Import error for post {$postId}: " . $e->getMessage());
            }
        }
    }

    private function generateRedirectsFile(): void
    {
        $this->info('Generating redirects file...');

        $routesPath = base_path('routes/redirects.php');
        
        $content = "<?php\n\n";
        $content .= "// Auto-generated redirects for Tor's old diary URLs\n";
        $content .= "// Generated at: " . now()->toDateTimeString() . "\n";
        $content .= "// Total redirects: " . count($this->redirects) . "\n\n";
        $content .= "use Illuminate\Support\Facades\Route;\n\n";

        foreach ($this->redirects as $redirect) {
            if ($redirect['report_id']) {
                $oldUrl = $redirect['old_url'];
                $reportId = $redirect['report_id'];
                
                $content .= "Route::get('{$oldUrl}', function() {\n";
                $content .= "    return redirect()->route('reports.show', {$reportId}, 301);\n";
                $content .= "});\n\n";
            }
        }

        file_put_contents($routesPath, $content);
        
        $this->info("Redirects file created: {$routesPath}");
        $this->info("Don't forget to include it in routes/web.php:");
        $this->warn("  require __DIR__.'/redirects.php';");
    }
}
