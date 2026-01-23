<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class FixSpoilersStructure extends Command
{
    protected $signature = 'articles:fix-spoilers-structure';
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

            // Используем регулярные выражения для более надежной обработки
            $originalContent = $content;
            $fixed = false;
            
            // Находим все details без контента и следующий за ними контент
            $pattern = '/(<details[^>]*class=["\']faq-spoiler["\'][^>]*>.*?<\/summary>)\s*<\/details>\s*(<div[^>]*>.*?<\/div>|<p[^>]*>.*?<\/p>|<ul[^>]*>.*?<\/ul>|<ol[^>]*>.*?<\/ol>)/is';
            
            while (preg_match($pattern, $content, $matches)) {
                $detailsStart = $matches[1];
                $contentAfter = $matches[2];
                
                // Проверяем, что после этого контента нет другого details
                $afterMatch = substr($content, strpos($content, $matches[0]) + strlen($matches[0]));
                if (preg_match('/^\s*<details[^>]*class=["\']faq-spoiler["\']/is', $afterMatch)) {
                    // Следующий details, останавливаемся
                    break;
                }
                
                // Перемещаем контент внутрь details
                $newDetails = $detailsStart . "\n" . $contentAfter . "\n</details>";
                $content = str_replace($matches[0], $newDetails, $content);
                $fixed = true;
                $this->info("  Исправлен details элемент");
            }
            
            // Если регулярные выражения не помогли, используем DOM
            if (!$fixed) {
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
                        $this->info("  Нет details элементов, пропускаем");
                        continue;
                    }

                    foreach ($detailsElements as $details) {
                        // Проверяем, есть ли контент внутри details (кроме summary)
                        $hasRealContent = false;
                        foreach ($details->childNodes as $child) {
                            if ($child->nodeType === XML_ELEMENT_NODE && 
                                $child->nodeName !== 'summary' &&
                                !in_array(strtolower($child->nodeName), ['i', 'br'])) {
                                $hasRealContent = true;
                                break;
                            }
                        }
                        
                        // Если внутри details нет контента, ищем его после details
                        if (!$hasRealContent) {
                            $parent = $details->parentNode;
                            if ($parent) {
                                $foundDetails = false;
                                $contentToMove = [];
                                
                                foreach ($parent->childNodes as $sibling) {
                                    if ($sibling === $details) {
                                        $foundDetails = true;
                                        continue;
                                    }
                                    
                                    if ($foundDetails) {
                                        // Если встретили следующий details, останавливаемся
                                        if ($sibling->nodeType === XML_ELEMENT_NODE) {
                                            if ($sibling->nodeName === 'details') {
                                                break;
                                            }
                                            // Перемещаем div, p, ul, ol и другие элементы
                                            if (in_array($sibling->nodeName, ['div', 'p', 'ul', 'ol', 'li', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code'])) {
                                                $contentToMove[] = $sibling;
                                            }
                                        }
                                    }
                                }
                                
                                // Перемещаем контент внутрь details
                                if (!empty($contentToMove)) {
                                    foreach ($contentToMove as $element) {
                                        $details->appendChild($element->cloneNode(true));
                                    }
                                    
                                    // Удаляем оригинальные элементы
                                    foreach ($contentToMove as $element) {
                                        if ($element->parentNode) {
                                            $element->parentNode->removeChild($element);
                                        }
                                    }
                                    
                                    $fixed = true;
                                    $this->info("  Исправлен details элемент (DOM)");
                                }
                            }
                        }
                    }
                    
                    if ($fixed) {
                        $newContent = '';
                        foreach ($wrapper->childNodes as $node) {
                            $newContent .= $dom->saveHTML($node);
                        }
                        $content = trim($newContent);
                    }
                } catch (\Exception $e) {
                    $this->error("  Ошибка DOM обработки: " . $e->getMessage());
                }
            }
            
            if ($fixed) {

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
