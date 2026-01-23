<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Helpers\ArticleContentHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveAllClassesFromArticles extends Command
{
    protected $signature = 'articles:remove-classes 
                            {--dry-run : Показать что будет изменено без сохранения}';

    protected $description = 'Удалить все CSS классы из контента статей, оставив только семантический HTML';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Удаление всех CSS классов из статей...');
        $this->newLine();

        $articles = Article::all();

        if ($articles->isEmpty()) {
            $this->warn('Статьи не найдены.');
            return 0;
        }

        $this->info("Найдено статей: {$articles->count()}");
        $this->newLine();

        $cleaned = 0;
        $unchanged = 0;
        $errors = 0;

        foreach ($articles as $article) {
            try {
                // Получаем исходный контент напрямую из базы, минуя mutator
                $originalContent = DB::table('articles')
                    ->where('id', $article->id)
                    ->value('content');

                // Очищаем через helper
                $cleanedContent = ArticleContentHelper::sanitize($originalContent);

                if ($originalContent === $cleanedContent) {
                    $this->line("  ✓ {$article->title} (ID: {$article->id}) - без изменений");
                    $unchanged++;
                } else {
                    $originalLength = strlen($originalContent);
                    $cleanedLength = strlen($cleanedContent);
                    $diff = $originalLength - $cleanedLength;

                    // Подсчитываем количество классов в оригинале
                    preg_match_all('/class\s*=\s*["\'][^"\']*["\']/i', $originalContent, $matches);
                    $classesCount = count($matches[0]);

                    if ($isDryRun) {
                        $this->warn("  ⚠ {$article->title} (ID: {$article->id}) - будет изменено");
                        $this->line("     Длина: {$originalLength} → {$cleanedLength} (удалено: {$diff} символов)");
                        $this->line("     Найдено классов: {$classesCount}");
                    } else {
                        // Обновляем напрямую в базе, минуя mutator
                        DB::table('articles')
                            ->where('id', $article->id)
                            ->update(['content' => $cleanedContent]);
                        
                        $this->info("  ✓ {$article->title} (ID: {$article->id}) - очищено");
                        $this->line("     Длина: {$originalLength} → {$cleanedLength} (удалено: {$diff} символов)");
                        $this->line("     Удалено классов: {$classesCount}");
                    }
                    $cleaned++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ {$article->title} (ID: {$article->id}) - ошибка: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info('Результаты:');
        $this->line("  Очищено: {$cleaned}");
        $this->line("  Без изменений: {$unchanged}");
        if ($errors > 0) {
            $this->error("  Ошибок: {$errors}");
        }

        if ($isDryRun) {
            $this->newLine();
            $this->warn('Это был тестовый запуск (--dry-run). Для применения изменений запустите команду без этого флага.');
        } else {
            $this->newLine();
            $this->info('Готово! Все классы удалены из статей.');
        }

        return 0;
    }
}
