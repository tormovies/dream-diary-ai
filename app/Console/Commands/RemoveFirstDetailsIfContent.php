<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class RemoveFirstDetailsIfContent extends Command
{
    protected $signature = 'articles:remove-first-details';
    protected $description = 'Удаляет первый details элемент из всех статей типа guide (если он содержит "Содержание")';

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

            // Проверяем, есть ли details элементы
            if (strpos($content, '<details') === false) {
                $this->info("  Нет details элементов, пропускаем");
                continue;
            }
            
            try {
                $dom = new \DOMDocument();
                libxml_use_internal_errors(true);
                
                $html = '<div id="content-wrapper">' . $content . '</div>';
                $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                libxml_clear_errors();
                
                $wrapper = $dom->getElementById('content-wrapper');
                if (!$wrapper) {
                    $this->error("  Ошибка парсинга HTML");
                    continue;
                }

                $xpath = new \DOMXPath($dom);
                $detailsElements = $xpath->query('.//details[@class="faq-spoiler"]', $wrapper);
                
                if ($detailsElements->length === 0) {
                    $this->info("  Нет details элементов с классом faq-spoiler, пропускаем");
                    continue;
                }
                
                // Проверяем первый details - удаляем его, если содержит "Содержание"
                $firstDetails = $detailsElements->item(0);
                $summary = $xpath->query('.//summary[@class="faq-spoiler-header"]', $firstDetails)->item(0);
                
                $shouldRemove = false;
                
                // Проверяем через HTML напрямую - более надежно
                $firstDetailsHtml = $dom->saveHTML($firstDetails);
                // Нормализуем HTML - убираем лишние пробелы и переносы
                $firstDetailsHtml = preg_replace('/\s+/', ' ', $firstDetailsHtml);
                
                // Проверяем, содержит ли details "содержание" (в любом регистре)
                if (stripos($firstDetailsHtml, 'содержание') !== false) {
                    $shouldRemove = true;
                    $this->info("  Найден details с 'Содержание', удаляем...");
                } else if ($summary) {
                    // Дополнительная проверка через textContent
                    $summaryText = trim($summary->textContent);
                    $summaryText = preg_replace('/\s+/', ' ', $summaryText);
                    if (stripos($summaryText, 'содержание') !== false) {
                        $shouldRemove = true;
                        $this->info("  Найден details с 'Содержание' (через textContent), удаляем...");
                    }
                }
                
                if ($shouldRemove) {
                    if ($firstDetails->parentNode) {
                        $firstDetails->parentNode->removeChild($firstDetails);
                    }
                    
                    $newContent = '';
                    foreach ($wrapper->childNodes as $node) {
                        $newContent .= $dom->saveHTML($node);
                    }
                    
                    $newContent = trim($newContent);
                    
                    DB::table('articles')
                        ->where('id', $article->id)
                        ->update(['content' => $newContent]);
                    
                    $this->info("  ✓ Блок удален");
                } else {
                    $this->info("  Первый details не содержит 'Содержание', пропускаем");
                }
            } catch (\Exception $e) {
                $this->error("  Ошибка: " . $e->getMessage());
            }
        }

        $this->info("\nГотово! Все статьи обработаны.");
        return 0;
    }
}
