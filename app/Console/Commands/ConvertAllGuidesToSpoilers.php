<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class ConvertAllGuidesToSpoilers extends Command
{
    protected $signature = 'articles:convert-all-guides';
    protected $description = 'Преобразует все статьи типа guide в формат с HTML5 details/summary спойлерами, удаляя блок "Содержание"';

    public function handle()
    {
        $articles = Article::where('type', 'guide')->get();

        if ($articles->isEmpty()) {
            $this->info('Нет статей типа guide для обработки.');
            return 0;
        }

        $this->info("Найдено статей типа guide: {$articles->count()}");

        foreach ($articles as $article) {
            $this->info("Обработка: {$article->title} (ID: {$article->id}, slug: {$article->slug})");
            
            $content = $article->content;
            if (empty($content)) {
                $this->warn("  Статья пустая, пропускаем");
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
                $h2Elements = $xpath->query('.//h2', $wrapper);
                
                if ($h2Elements->length === 0) {
                    $this->warn("  Нет h2 элементов, пропускаем");
                    continue;
                }

                $firstH2 = $h2Elements->item(0);
                $isFirstContent = $firstH2 && 
                    stripos(trim($firstH2->textContent), 'содержание') !== false;

                // Удаляем блок "Содержание" (первый h2 и следующий за ним ul)
                if ($isFirstContent) {
                    $this->info("  Удаление блока 'Содержание'...");
                    
                    // Находим следующий ul/ol после h2 "Содержание"
                    $nextSibling = $firstH2->nextSibling;
                    $listToRemove = null;
                    
                    while ($nextSibling) {
                        if ($nextSibling->nodeType === XML_ELEMENT_NODE) {
                            if ($nextSibling->nodeName === 'ul' || $nextSibling->nodeName === 'ol') {
                                $listToRemove = $nextSibling;
                                break;
                            }
                            if ($nextSibling->nodeName === 'h2') {
                                // Встретили следующий h2, останавливаемся
                                break;
                            }
                        }
                        $nextSibling = $nextSibling->nextSibling;
                    }
                    
                    // Удаляем ul/ol (список содержания)
                    if ($listToRemove && $listToRemove->parentNode) {
                        $listToRemove->parentNode->removeChild($listToRemove);
                    }
                    
                    // Удаляем h2 "Содержание"
                    if ($firstH2->parentNode) {
                        $firstH2->parentNode->removeChild($firstH2);
                    }
                    
                    // Обновляем список h2 после удаления
                    $h2Elements = $xpath->query('.//h2', $wrapper);
                }

                if ($h2Elements->length === 0) {
                    $this->warn("  После удаления 'Содержание' не осталось h2, пропускаем");
                    continue;
                }

                $this->info("  Найдено h2 для преобразования: {$h2Elements->length}");

                // Преобразуем все оставшиеся h2 в details/summary
                $questionH2s = [];
                for ($i = 0; $i < $h2Elements->length; $i++) {
                    $questionH2s[] = $h2Elements->item($i);
                }

                foreach ($questionH2s as $h2) {
                    $details = $dom->createElement('details');
                    $details->setAttribute('class', 'faq-spoiler');
                    
                    $summary = $dom->createElement('summary');
                    $summary->setAttribute('class', 'faq-spoiler-header');
                    
                    foreach ($h2->childNodes as $child) {
                        $summary->appendChild($child->cloneNode(true));
                    }
                    
                    $details->appendChild($summary);
                    
                    $contentElements = [];
                    $current = $h2->nextSibling;
                    
                    while ($current) {
                        if ($current->nodeType === XML_ELEMENT_NODE && 
                            $current->nodeName === 'h2') {
                            break;
                        }
                        
                        if ($current->nodeType === XML_ELEMENT_NODE || 
                            ($current->nodeType === XML_TEXT_NODE && trim($current->textContent) !== '')) {
                            $contentElements[] = $current;
                        }
                        
                        $current = $current->nextSibling;
                    }
                    
                    foreach ($contentElements as $element) {
                        $details->appendChild($element->cloneNode(true));
                    }
                    
                    $h2->parentNode->replaceChild($details, $h2);
                    
                    foreach ($contentElements as $element) {
                        if ($element->parentNode) {
                            $element->parentNode->removeChild($element);
                        }
                    }
                }

                $newContent = '';
                foreach ($wrapper->childNodes as $node) {
                    $newContent .= $dom->saveHTML($node);
                }
                
                $newContent = trim($newContent);
                
                DB::table('articles')
                    ->where('id', $article->id)
                    ->update(['content' => $newContent]);
                
                $this->info("  ✓ Статья обновлена");
            } catch (\Exception $e) {
                $this->error("  Ошибка обработки: " . $e->getMessage());
            }
        }

        $this->info("\nГотово! Все статьи типа guide обработаны.");
        return 0;
    }
}
