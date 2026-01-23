<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class RemoveContentBlockFromGuides extends Command
{
    protected $signature = 'articles:remove-content-block';
    protected $description = 'Удаляет блок "Содержание" из всех статей типа guide';

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
                // Сначала проверяем через регулярные выражения - быстрее и надежнее
                $hasContentBlock = false;
                
                // Проверяем наличие "Содержание" в контенте
                if (stripos($content, 'содержание') === false) {
                    $this->info("  Блок 'Содержание' не найден в тексте, пропускаем");
                    continue;
                }
                
                $this->info("  Найден текст 'Содержание', обрабатываем...");
                
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
                $removed = false;
                
                // Ищем details с summary "Содержание" - проверяем все details
                $detailsElements = $xpath->query('.//details[@class="faq-spoiler"]', $wrapper);
                
                foreach ($detailsElements as $details) {
                    $summary = $xpath->query('.//summary[@class="faq-spoiler-header"]', $details)->item(0);
                    if ($summary) {
                        $summaryText = trim($summary->textContent);
                        // Нормализуем текст - убираем лишние пробелы
                        $summaryText = preg_replace('/\s+/', ' ', $summaryText);
                        if (stripos($summaryText, 'содержание') !== false) {
                            $this->info("  Найден блок 'Содержание' (details), удаляем...");
                            if ($details->parentNode) {
                                $details->parentNode->removeChild($details);
                                $removed = true;
                                break;
                            }
                        }
                    }
                }
                
                // Если не нашли details, ищем h2 "Содержание"
                if (!$removed) {
                    $h2Elements = $xpath->query('.//h2', $wrapper);
                    foreach ($h2Elements as $h2) {
                        $h2Text = trim($h2->textContent);
                        $h2Text = preg_replace('/\s+/', ' ', $h2Text);
                        if (stripos($h2Text, 'содержание') !== false) {
                            $this->info("  Найден блок 'Содержание' (h2), удаляем...");
                            
                            // Удаляем h2
                            if ($h2->parentNode) {
                                $h2->parentNode->removeChild($h2);
                            }
                            
                            // Удаляем следующий ul/ol
                            $nextSibling = $h2->nextSibling;
                            while ($nextSibling) {
                                if ($nextSibling->nodeType === XML_ELEMENT_NODE) {
                                    if ($nextSibling->nodeName === 'ul' || $nextSibling->nodeName === 'ol') {
                                        if ($nextSibling->parentNode) {
                                            $nextSibling->parentNode->removeChild($nextSibling);
                                        }
                                        break;
                                    }
                                    if ($nextSibling->nodeName === 'h2' || $nextSibling->nodeName === 'details') {
                                        break;
                                    }
                                }
                                $nextSibling = $nextSibling->nextSibling;
                            }
                            
                            $removed = true;
                            break;
                        }
                    }
                }

                if (!$removed) {
                    $this->warn("  Не удалось найти и удалить блок 'Содержание'");
                    continue;
                }

                $newContent = '';
                foreach ($wrapper->childNodes as $node) {
                    $newContent .= $dom->saveHTML($node);
                }
                
                $newContent = trim($newContent);
                
                DB::table('articles')
                    ->where('id', $article->id)
                    ->update(['content' => $newContent]);
                
                $this->info("  ✓ Блок 'Содержание' удален");
            } catch (\Exception $e) {
                $this->error("  Ошибка обработки: " . $e->getMessage());
            }
        }

        $this->info("\nГотово! Все статьи типа guide обработаны.");
        return 0;
    }
}
