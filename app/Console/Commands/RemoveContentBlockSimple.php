<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class RemoveContentBlockSimple extends Command
{
    protected $signature = 'articles:remove-content-simple';
    protected $description = 'Удаляет блок "Содержание" из всех статей типа guide через регулярные выражения';

    public function handle()
    {
        $articles = Article::where('type', 'guide')->get();

        if ($articles->isEmpty()) {
            $this->info('Нет статей типа guide для обработки.');
            return 0;
        }

        $this->info("Найдено статей типа guide: {$articles->count()}");

        foreach ($articles as $article) {
            $this->info("Обработка: {$article->title} (ID: {$article->id})");
            
            $content = $article->content;
            if (empty($content)) {
                $this->warn("  Статья пустая, пропускаем");
                continue;
            }

            // Проверяем наличие "Содержание" (в любом регистре) - используем mb_stripos для UTF-8
            if (mb_stripos($content, 'содержание', 0, 'UTF-8') === false) {
                $this->info("  Блок 'Содержание' не найден, пропускаем");
                continue;
            }

            $this->info("  Найден текст 'Содержание', удаляем блок...");
            
            $originalLength = strlen($content);
            
            // Удаляем details с summary "Содержание" через регулярное выражение
            // Паттерн: <details...>...<summary...>...Содержание...</summary>...</details>
            // Используем более гибкий паттерн с учетом многострочности и вложенных тегов
            $content = preg_replace(
                '/<details[^>]*class\s*=\s*["\']faq-spoiler["\'][^>]*>[\s\S]*?<summary[^>]*class\s*=\s*["\']faq-spoiler-header["\'][^>]*>[\s\S]*?содержание[\s\S]*?<\/summary>[\s\S]*?<\/details>/iu',
                '',
                $content,
                1 // Только первое вхождение
            );
            
            // Также удаляем h2 "Содержание" и следующий ul/ol
            $content = preg_replace(
                '/<h2[^>]*>.*?содержание.*?<\/h2>\s*<ul[^>]*>.*?<\/ul>/is',
                '',
                $content,
                1
            );
            
            $content = preg_replace(
                '/<h2[^>]*>.*?содержание.*?<\/h2>\s*<ol[^>]*>.*?<\/ol>/is',
                '',
                $content,
                1
            );
            
            // Очищаем от лишних пробелов и переносов строк
            $content = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $content);
            $content = trim($content);
            
            $newLength = strlen($content);
            
            if ($newLength < $originalLength) {
                DB::table('articles')
                    ->where('id', $article->id)
                    ->update(['content' => $content]);
                
                $this->info("  ✓ Блок удален (было: {$originalLength}, стало: {$newLength})");
            } else {
                $this->warn("  Размер не изменился, возможно блок уже удален");
            }
        }

        $this->info("\nГотово! Все статьи обработаны.");
        return 0;
    }
}
