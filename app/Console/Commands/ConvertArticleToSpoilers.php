<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertArticleToSpoilers extends Command
{
    protected $signature = 'articles:convert-to-spoilers 
                            {slug : Slug статьи для конвертации}
                            {--dry-run : Показать что будет изменено без сохранения}';

    protected $description = 'Конвертировать статью в формат FAQ со спойлерами (details/summary)';

    public function handle()
    {
        $slug = $this->argument('slug');
        $isDryRun = $this->option('dry-run');

        $article = Article::where('slug', $slug)->first();

        if (!$article) {
            $this->error("Статья с slug '{$slug}' не найдена!");
            return 1;
        }

        $this->info("Конвертация статьи: {$article->title}");
        $this->newLine();

        // Получаем контент напрямую из базы
        $originalContent = DB::table('articles')
            ->where('id', $article->id)
            ->value('content');

        // Парсим контент и конвертируем в формат спойлеров
        $convertedContent = $this->convertToSpoilers($originalContent);

        if ($isDryRun) {
            $this->warn("=== ПРЕДПРОСМОТР ИЗМЕНЕНИЙ ===");
            $this->line("Оригинальная длина: " . strlen($originalContent) . " символов");
            $this->line("Новая длина: " . strlen($convertedContent) . " символов");
            $this->newLine();
            $this->line("=== ПРЕВЬЮ НОВОГО КОНТЕНТА (первые 2000 символов) ===");
            $this->line(substr($convertedContent, 0, 2000) . "...");
            $this->newLine();
            $this->warn('Это был тестовый запуск (--dry-run). Для применения изменений запустите команду без этого флага.');
        } else {
            // Сохраняем напрямую в базу
            DB::table('articles')
                ->where('id', $article->id)
                ->update(['content' => $convertedContent]);
            
            $this->info("✓ Статья успешно конвертирована в формат спойлеров!");
        }

        return 0;
    }

    private function convertToSpoilers(string $content): string
    {
        // Удаляем все ссылки на якоря из содержания (оставляем только текст)
        $content = preg_replace('/<a[^>]*href="#[^"]*"[^>]*>(.*?)<\/a>/i', '$1', $content);
        
        // Находим позиции всех h2
        preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $content, $h2Matches, PREG_OFFSET_CAPTURE);
        
        $questions = [];
        
        for ($i = 0; $i < count($h2Matches[0]); $i++) {
            $h2Full = $h2Matches[0][$i][0];
            $h2Text = strip_tags($h2Matches[0][$i][0]);
            $h2Pos = $h2Matches[0][$i][1];
            
            // Пропускаем "Содержание"
            if (stripos($h2Text, 'Содержание') !== false) {
                continue;
            }
            
            // Определяем конец контента вопроса (начало следующего h2 или конец строки)
            $nextH2Pos = isset($h2Matches[0][$i + 1]) ? $h2Matches[0][$i + 1][1] : strlen($content);
            
            // Извлекаем контент между текущим h2 и следующим
            $questionContent = substr($content, $h2Pos + strlen($h2Full), $nextH2Pos - $h2Pos - strlen($h2Full));
            
            // Очищаем контент
            $questionContent = $this->cleanQuestionContent($questionContent);
            
            if (!empty($h2Text) && !empty($questionContent)) {
                $questions[] = [
                    'title' => trim($h2Text),
                    'content' => $questionContent
                ];
            }
        }
        
        // Создаем новый контент
        $newContent = '<h2>Содержание</h2><ul>';
        foreach ($questions as $q) {
            $newContent .= '<li>' . htmlspecialchars($q['title']) . '</li>';
        }
        $newContent .= '</ul>';
        
        // Добавляем спойлеры
        foreach ($questions as $q) {
            $newContent .= '<details><summary><strong>' . htmlspecialchars($q['title']) . '</strong></summary><div>' . $q['content'] . '</div></details>';
        }
        
        return $newContent;
    }

    private function cleanQuestionContent(string $content): string
    {
        // Удаляем пустые параграфы
        $content = preg_replace('/<p>\s*<\/p>/i', '', $content);
        
        // Удаляем target="_blank" и rel из ссылок
        $content = preg_replace('/\s*target\s*=\s*["\'][^"\']*["\']/i', '', $content);
        $content = preg_replace('/\s*rel\s*=\s*["\'][^"\']*["\']/i', '', $content);
        
        // Удаляем лишние пробелы между тегами
        $content = preg_replace('/>\s+</', '><', $content);
        
        // Удаляем множественные пробелы (но сохраняем один пробел)
        $content = preg_replace('/[ \t]+/', ' ', $content);
        
        $content = trim($content);
        
        return $content;
    }
}
