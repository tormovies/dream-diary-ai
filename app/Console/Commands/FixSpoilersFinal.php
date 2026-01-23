<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class FixSpoilersFinal extends Command
{
    protected $signature = 'articles:fix-spoilers-final';
    protected $description = 'Исправляет структуру спойлеров используя DOMDocument';

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

            // Используем простую замену: находим </details> и следующий <div> с контентом
            $originalContent = $content;
            $fixed = false;
            
            // Находим все позиции </details>
            $offset = 0;
            $replacements = [];
            
            while (($pos = strpos($content, '</details>', $offset)) !== false) {
                $detailsEnd = $pos + 10;
                
                // Проверяем, что это details с классом faq-spoiler
                $before = substr($content, max(0, $pos - 200), 200);
                if (strpos($before, 'class="faq-spoiler"') === false && 
                    strpos($before, "class='faq-spoiler'") === false) {
                    $offset = $detailsEnd;
                    continue;
                }
                
                // Проверяем, есть ли контент внутри details
                $detailsStart = strrpos($before, '<details');
                if ($detailsStart !== false) {
                    $detailsStart = $pos - 200 + $detailsStart;
                    $detailsHtml = substr($content, $detailsStart, $detailsEnd - $detailsStart);
                    $inner = preg_replace('/<summary[^>]*>.*?<\/summary>/is', '', $detailsHtml);
                    $inner = trim(strip_tags($inner));
                    
                    if (empty($inner)) {
                        // Контента нет, ищем следующий div
                        $after = substr($content, $detailsEnd, 5000);
                        
                        // Ищем первый <div> после details (может быть с пробелами и вложенными div)
                        // Используем более гибкий паттерн, который учитывает вложенность
                        if (preg_match('/^\s*(<div[^>]*>)/is', $after, $divStartMatch, PREG_OFFSET_CAPTURE)) {
                            $divStartPos = $divStartMatch[0][1];
                            $divStartTag = $divStartMatch[1][0];
                            
                            // Находим закрывающий тег этого div (учитываем вложенность)
                            $divContent = substr($after, $divStartPos);
                            $depth = 1;
                            $pos = strlen($divStartMatch[0][0]);
                            
                            while ($depth > 0 && $pos < strlen($divContent) && $pos < 10000) {
                                if (preg_match('/<\/div>/', $divContent, $closeMatch, PREG_OFFSET_CAPTURE, $pos)) {
                                    $depth--;
                                    $pos = $closeMatch[0][1] + 7;
                                } elseif (preg_match('/<div[^>]*>/', $divContent, $openMatch, PREG_OFFSET_CAPTURE, $pos)) {
                                    $depth++;
                                    $pos = $openMatch[0][1] + strlen($openMatch[0][0]);
                                } else {
                                    break;
                                }
                            }
                            
                            if ($depth === 0) {
                                $divHtml = substr($divContent, 0, $pos);
                                
                                // Проверяем, что после этого div нет другого details
                                $afterDiv = substr($after, $divStartPos + strlen($divHtml), 200);
                                if (!preg_match('/^\s*<details[^>]*class=["\']faq-spoiler["\']/is', $afterDiv)) {
                                    // Сохраняем замену
                                    $replacements[] = [
                                        'start' => $detailsStart,
                                        'end' => $detailsEnd + $divStartPos + strlen($divHtml),
                                        'new' => substr($content, $detailsStart, $detailsEnd - $detailsStart) . "\n" . $divHtml . "\n</details>"
                                    ];
                                    $fixed = true;
                                }
                            }
                        }
                    }
                }
                
                $offset = $detailsEnd;
            }
            
            // Применяем замены в обратном порядке
            if ($fixed && !empty($replacements)) {
                foreach (array_reverse($replacements) as $repl) {
                    $content = substr_replace($content, $repl['new'], $repl['start'], $repl['end'] - $repl['start']);
                }
                $this->info("  Исправлено details элементов: " . count($replacements));
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
