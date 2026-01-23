<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class FixSpoilersContent extends Command
{
    protected $signature = 'articles:fix-spoilers-content';
    protected $description = 'Исправляет структуру спойлеров - перемещает контент внутрь details';

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

            // Используем простой подход: находим </details> и следующий <div> с контентом
            $originalContent = $content;
            $fixed = false;
            $iterations = 0;
            
            // Ищем все details без контента и перемещаем следующий div внутрь
            while ($iterations < 50) {
                $iterations++;
                $changed = false;
                
                // Находим все details элементы
                preg_match_all('/<details[^>]*class=["\']faq-spoiler["\'][^>]*>(.*?)<\/details>/is', $content, $matches, PREG_OFFSET_CAPTURE);
                
                // Обрабатываем в обратном порядке
                for ($i = count($matches[0]) - 1; $i >= 0; $i--) {
                    $fullMatch = $matches[0][$i][0];
                    $fullPos = $matches[0][$i][1];
                    $inner = $matches[1][$i][0];
                    
                    // Проверяем, есть ли контент кроме summary
                    $withoutSummary = preg_replace('/<summary[^>]*>.*?<\/summary>/is', '', $inner);
                    $withoutSummary = preg_replace('/<i[^>]*>.*?<\/i>/is', '', $withoutSummary);
                    $withoutSummary = trim(strip_tags($withoutSummary));
                    
                    if (empty($withoutSummary)) {
                        // Контента нет, ищем следующий div
                        $afterPos = $fullPos + strlen($fullMatch);
                        $after = substr($content, $afterPos, 2000); // Смотрим следующие 2000 символов
                        
                        // Ищем первый <div> после details (может быть с пробелами)
                        if (preg_match('/^\s*(<div[^>]*>)/is', $after, $divStart, PREG_OFFSET_CAPTURE)) {
                            $divStartPos = $divStart[0][1];
                            $divStartTag = $divStart[1][0];
                            
                            // Находим закрывающий тег этого div (учитываем вложенность)
                            $divContent = substr($after, $divStartPos);
                            $depth = 1;
                            $pos = strlen($divStart[0][0]);
                            
                            while ($depth > 0 && $pos < strlen($divContent)) {
                                if (preg_match('/<\/div>/', $divContent, $close, PREG_OFFSET_CAPTURE, $pos)) {
                                    $depth--;
                                    $pos = $close[0][1] + 7;
                                } elseif (preg_match('/<div[^>]*>/', $divContent, $open, PREG_OFFSET_CAPTURE, $pos)) {
                                    $depth++;
                                    $pos = $open[0][1] + strlen($open[0][0]);
                                } else {
                                    break;
                                }
                            }
                            
                            if ($depth === 0) {
                                $divHtml = substr($divContent, 0, $pos);
                                
                                // Проверяем, что после этого div нет другого details
                                $afterDiv = substr($after, $divStartPos + strlen($divHtml));
                                if (!preg_match('/^\s*<details[^>]*class=["\']faq-spoiler["\']/is', $afterDiv)) {
                                    // Перемещаем div внутрь details
                                    $newDetails = str_replace('</details>', "\n" . $divHtml . "\n</details>", $fullMatch);
                                    $toReplace = $fullMatch . substr($after, 0, $divStartPos + strlen($divHtml));
                                    
                                    $content = substr_replace($content, $newDetails, $fullPos, strlen($toReplace));
                                    $changed = true;
                                    $fixed = true;
                                    $this->info("  Исправлен details элемент #" . ($i + 1));
                                    break;
                                }
                            }
                        }
                    }
                }
                
                if (!$changed) {
                    break;
                }
            }
            
            if ($fixed) {
                DB::table('articles')
                    ->where('id', $article->id)
                    ->update(['content' => $content]);
                
                $this->info("  ✓ Статья исправлена");
            } else {
                $this->info("  Структура уже правильная, пропускаем");
            }
        }

        $this->info("\nГотово! Все статьи обработаны.");
        return 0;
    }
}
