<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddAnchorsToArticles extends Command
{
    protected $signature = 'articles:add-anchors 
                            {--dry-run : Показать что будет изменено без сохранения}';

    protected $description = 'Добавить id="question-X" к заголовкам h2 в статьях';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Добавление якорей к заголовкам в статьях...');
        $this->newLine();

        $articles = Article::orderBy('id')->get();

        $this->info("Найдено статей: {$articles->count()}");
        $this->newLine();

        $updated = 0;
        $unchanged = 0;

        foreach ($articles as $article) {
            try {
                // Получаем контент напрямую из базы
                $originalContent = DB::table('articles')
                    ->where('id', $article->id)
                    ->value('content');

                // Счетчик для заголовков
                $questionNumber = 0;
                
                // Добавляем id к заголовкам h2 (кроме первого "Содержание")
                $updatedContent = preg_replace_callback(
                    '/<h2([^>]*)>(.*?)<\/h2>/is',
                    function($matches) use (&$questionNumber) {
                        $attrs = $matches[1];
                        $content = $matches[2];
                        
                        // Первый h2 - это "Содержание", пропускаем
                        if ($questionNumber == 0) {
                            $questionNumber++;
                            return $matches[0]; // Возвращаем без изменений
                        }
                        
                        // Проверяем, есть ли уже id
                        if (preg_match('/\bid\s*=/', $attrs)) {
                            return $matches[0]; // Уже есть id
                        }
                        
                        // Добавляем id="question-X"
                        $id = 'id="question-' . $questionNumber . '"';
                        $questionNumber++;
                        
                        return '<h2 ' . trim($attrs . ' ' . $id) . '>' . $content . '</h2>';
                    },
                    $originalContent
                );

                if ($originalContent === $updatedContent) {
                    $this->line("  ✓ {$article->title} (ID: {$article->id}) - без изменений");
                    $unchanged++;
                } else {
                    if ($isDryRun) {
                        $this->warn("  ⚠ {$article->title} (ID: {$article->id}) - будет изменено");
                        // Подсчитываем сколько якорей будет добавлено
                        preg_match_all('/<h2 id="question-\d+"/', $updatedContent, $matches);
                        $this->line("     Будет добавлено якорей: " . count($matches[0]));
                    } else {
                        // Сохраняем напрямую в базу
                        DB::table('articles')
                            ->where('id', $article->id)
                            ->update(['content' => $updatedContent]);
                        
                        $this->info("  ✓ {$article->title} (ID: {$article->id}) - обновлено");
                        // Подсчитываем сколько якорей добавлено
                        preg_match_all('/<h2 id="question-\d+"/', $updatedContent, $matches);
                        $this->line("     Добавлено якорей: " . count($matches[0]));
                    }
                    $updated++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ {$article->title} (ID: {$article->id}) - ошибка: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('Результаты:');
        $this->line("  Обновлено: {$updated}");
        $this->line("  Без изменений: {$unchanged}");

        if ($isDryRun) {
            $this->newLine();
            $this->warn('Это был тестовый запуск (--dry-run). Для применения изменений запустите команду без этого флага.');
        } else {
            $this->newLine();
            $this->info('Готово! Якоря добавлены к заголовкам.');
        }

        return 0;
    }
}
