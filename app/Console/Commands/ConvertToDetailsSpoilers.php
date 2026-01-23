<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class ConvertToDetailsSpoilers extends Command
{
    protected $signature = 'articles:convert-to-details {slug?}';
    protected $description = 'Преобразует статью в формат с HTML5 details/summary спойлерами';

    public function handle()
    {
        $slug = $this->argument('slug');
        
        if ($slug) {
            $article = Article::where('slug', $slug)->first();
            if (!$article) {
                $this->error("Статья с slug '{$slug}' не найдена!");
                return 1;
            }
            $articles = collect([$article]);
        } else {
            $articles = Article::all();
        }

        foreach ($articles as $article) {
            $this->info("Обработка статьи: {$article->title} (ID: {$article->id})");
            
            $content = $article->content;
            if (empty($content)) {
                $this->warn("  Статья пустая, пропускаем");
                continue;
            }

            // Используем DOMDocument для парсинга
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            
            // Обертываем контент в контейнер для правильного парсинга
            $html = '<div id="content-wrapper">' . $content . '</div>';
            $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            
            // Получаем контейнер
            $wrapper = $dom->getElementById('content-wrapper');
            if (!$wrapper) {
                $this->error("  Ошибка парсинга HTML");
                continue;
            }

            $xpath = new \DOMXPath($dom);
            
            // Находим все h2 внутри контейнера
            $h2Elements = $xpath->query('.//h2', $wrapper);
            
            if ($h2Elements->length === 0) {
                $this->warn("  Нет h2 элементов, пропускаем");
                continue;
            }

            $firstH2 = $h2Elements->item(0);
            $isFirstContent = $firstH2 && 
                stripos(trim($firstH2->textContent), 'содержание') !== false;

            // Пропускаем первый h2 если это "Содержание"
            $questionH2s = [];
            for ($i = $isFirstContent ? 1 : 0; $i < $h2Elements->length; $i++) {
                $questionH2s[] = $h2Elements->item($i);
            }

            if (empty($questionH2s)) {
                $this->warn("  Нет вопросов для преобразования, пропускаем");
                continue;
            }

            $this->info("  Найдено " . count($questionH2s) . " вопросов для преобразования");

            // Обрабатываем каждый h2
            foreach ($questionH2s as $h2) {
                // Создаем details элемент
                $details = $dom->createElement('details');
                $details->setAttribute('class', 'faq-spoiler');
                
                // Создаем summary с содержимым h2
                $summary = $dom->createElement('summary');
                $summary->setAttribute('class', 'faq-spoiler-header');
                
                // Копируем содержимое h2 в summary
                foreach ($h2->childNodes as $child) {
                    $summary->appendChild($child->cloneNode(true));
                }
                
                $details->appendChild($summary);
                
                // Собираем все элементы после h2 до следующего h2
                $contentElements = [];
                $current = $h2->nextSibling;
                
                while ($current) {
                    if ($current->nodeType === XML_ELEMENT_NODE && 
                        $current->nodeName === 'h2') {
                        break;
                    }
                    
                    if ($current->nodeType === XML_ELEMENT_NODE || 
                        $current->nodeType === XML_TEXT_NODE) {
                        $contentElements[] = $current;
                    }
                    
                    $current = $current->nextSibling;
                }
                
                // Перемещаем элементы в details
                foreach ($contentElements as $element) {
                    $details->appendChild($element->cloneNode(true));
                }
                
                // Заменяем h2 на details
                $h2->parentNode->replaceChild($details, $h2);
                
                // Удаляем оригинальные элементы контента
                foreach ($contentElements as $element) {
                    if ($element->parentNode) {
                        $element->parentNode->removeChild($element);
                    }
                }
            }

            // Получаем обновленный HTML из контейнера
            $newContent = '';
            foreach ($wrapper->childNodes as $node) {
                $newContent .= $dom->saveHTML($node);
            }
            
            // Очищаем от лишних пробелов
            $newContent = trim($newContent);
            
            // Обновляем статью напрямую в БД, чтобы обойти мутатор
            DB::table('articles')
                ->where('id', $article->id)
                ->update(['content' => $newContent]);
            
            $this->info("  ✓ Статья обновлена");
        }

        $this->info("\nГотово! Все статьи обработаны.");
        return 0;
    }
}
