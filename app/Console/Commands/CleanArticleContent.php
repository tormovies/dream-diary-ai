<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Helpers\ArticleContentHelper;
use Illuminate\Console\Command;

class CleanArticleContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:clean-content 
                            {--dry-run : Показать что будет изменено без сохранения}
                            {--id= : Очистить только конкретную статью по ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистить контент всех статей от инлайн стилей и неподдерживаемых тегов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $articleId = $this->option('id');

        $this->info('Очистка контента статей...');
        $this->newLine();

        // Получаем статьи для обработки
        $query = Article::query();
        if ($articleId) {
            $query->where('id', $articleId);
        }
        $articles = $query->get();

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
                $originalContent = $article->content;
                $cleanedContent = ArticleContentHelper::sanitize($originalContent);

                if ($originalContent === $cleanedContent) {
                    $this->line("  ✓ {$article->title} (ID: {$article->id}) - без изменений");
                    $unchanged++;
                } else {
                    $originalLength = strlen($originalContent);
                    $cleanedLength = strlen($cleanedContent);
                    $diff = $originalLength - $cleanedLength;

                    if ($isDryRun) {
                        $this->warn("  ⚠ {$article->title} (ID: {$article->id}) - будет изменено");
                        $this->line("     Длина: {$originalLength} → {$cleanedLength} (удалено: {$diff} символов)");
                    } else {
                        $article->content = $cleanedContent;
                        $article->save();
                        $this->info("  ✓ {$article->title} (ID: {$article->id}) - очищено");
                        $this->line("     Длина: {$originalLength} → {$cleanedLength} (удалено: {$diff} символов)");
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
            $this->info('Готово! Все статьи очищены.');
        }

        return 0;
    }
}
