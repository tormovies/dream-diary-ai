<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class NormalizeGuidesContent extends Command
{
    protected $signature = 'articles:normalize-guides';
    protected $description = 'Очищает статьи типа guide от лишних тегов и приводит к единому формату';

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

            try {
                $dom = new \DOMDocument();
                libxml_use_internal_errors(true);
                
                // Обертываем контент для правильного парсинга
                $html = '<div id="content-wrapper">' . $content . '</div>';
                $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                libxml_clear_errors();
                
                $wrapper = $dom->getElementById('content-wrapper');
                if (!$wrapper) {
                    $this->error("  Ошибка парсинга HTML");
                    continue;
                }

                $xpath = new \DOMXPath($dom);
                
                // Удаляем пустые div обертки
                $emptyDivs = $xpath->query('.//div[not(node()) or (count(node())=1 and node()[1][self::text() and normalize-space(.)=""])]', $wrapper);
                foreach ($emptyDivs as $emptyDiv) {
                    if ($emptyDiv->parentNode && $emptyDiv !== $wrapper) {
                        $emptyDiv->parentNode->removeChild($emptyDiv);
                    }
                }
                
                // Находим все details элементы
                $detailsElements = $xpath->query('.//details[@class="faq-spoiler"]', $wrapper);
                
                // Если есть details, проверяем их структуру и исправляем
                if ($detailsElements->length > 0) {
                    // Обрабатываем в обратном порядке
                    for ($i = $detailsElements->length - 1; $i >= 0; $i--) {
                        $details = $detailsElements->item($i);
                        
                        // Проверяем, есть ли контент внутри details (кроме summary)
                        $hasContent = false;
                        foreach ($details->childNodes as $child) {
                            if ($child->nodeType === XML_ELEMENT_NODE && 
                                $child->nodeName !== 'summary' &&
                                !in_array(strtolower($child->nodeName), ['i', 'br'])) {
                                $hasContent = true;
                                break;
                            }
                        }
                        
                        // Если контента нет, ищем его после details
                        if (!$hasContent) {
                            // Ищем в родительском элементе и его родителях
                            $currentParent = $details->parentNode;
                            
                            while ($currentParent && $currentParent !== $wrapper) {
                                $foundDetails = false;
                                $elementsToMove = [];
                                
                                // Собираем все siblings после details
                                foreach ($currentParent->childNodes as $sibling) {
                                    if ($sibling === $details) {
                                        $foundDetails = true;
                                        continue;
                                    }
                                    
                                    if ($foundDetails) {
                                        // Если встретили следующий details, останавливаемся
                                        if ($sibling->nodeType === XML_ELEMENT_NODE && $sibling->nodeName === 'details') {
                                            break;
                                        }
                                        
                                        // Собираем элементы с контентом
                                        if ($sibling->nodeType === XML_ELEMENT_NODE) {
                                            $siblingText = trim($sibling->textContent);
                                            if (!empty($siblingText)) {
                                                $elementsToMove[] = $sibling;
                                            }
                                        }
                                    }
                                }
                                
                                // Перемещаем элементы внутрь details
                                if (!empty($elementsToMove)) {
                                    foreach ($elementsToMove as $element) {
                                        // Если это div, перемещаем его содержимое
                                        if ($element->nodeName === 'div') {
                                            $divChildren = [];
                                            foreach ($element->childNodes as $divChild) {
                                                $divChildren[] = $divChild;
                                            }
                                            foreach ($divChildren as $divChild) {
                                                $details->appendChild($divChild->cloneNode(true));
                                            }
                                        } else {
                                            // Иначе перемещаем сам элемент
                                            $details->appendChild($element->cloneNode(true));
                                        }
                                        if ($element->parentNode) {
                                            $element->parentNode->removeChild($element);
                                        }
                                    }
                                    break; // Нашли и переместили контент
                                }
                                
                                // Если не нашли в текущем родителе, проверяем родителя родителя
                                $currentParent = $currentParent->parentNode;
                            }
                        }
                    }
                } else {
                    // Если нет details, ищем h2 и преобразуем их
                    $h2Elements = $xpath->query('.//h2', $wrapper);
                    
                    if ($h2Elements->length > 0) {
                        $this->info("  Найдено h2 элементов: {$h2Elements->length}, преобразуем в details");
                        
                        // Пропускаем первый h2 если это "Содержание"
                        $questionH2s = [];
                        for ($i = 0; $i < $h2Elements->length; $i++) {
                            $h2 = $h2Elements->item($i);
                            $h2Text = trim($h2->textContent);
                            if (stripos($h2Text, 'содержание') === false) {
                                $questionH2s[] = $h2;
                            } else {
                                // Удаляем h2 "Содержание" и следующий ul
                                $nextSibling = $h2->nextSibling;
                                while ($nextSibling) {
                                    if ($nextSibling->nodeType === XML_ELEMENT_NODE) {
                                        if ($nextSibling->nodeName === 'ul' || $nextSibling->nodeName === 'ol') {
                                            if ($nextSibling->parentNode) {
                                                $nextSibling->parentNode->removeChild($nextSibling);
                                            }
                                            break;
                                        }
                                        if ($nextSibling->nodeName === 'h2') {
                                            break;
                                        }
                                    }
                                    $nextSibling = $nextSibling->nextSibling;
                                }
                                if ($h2->parentNode) {
                                    $h2->parentNode->removeChild($h2);
                                }
                            }
                        }
                        
                        // Преобразуем h2 в details/summary
                        foreach ($questionH2s as $h2) {
                            $details = $dom->createElement('details');
                            $details->setAttribute('class', 'faq-spoiler');
                            
                            $summary = $dom->createElement('summary');
                            $summary->setAttribute('class', 'faq-spoiler-header');
                            
                            foreach ($h2->childNodes as $child) {
                                $summary->appendChild($child->cloneNode(true));
                            }
                            
                            $details->appendChild($summary);
                            
                            // Собираем контент после h2
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
                            
                            // Добавляем контент в details
                            foreach ($contentElements as $element) {
                                // Если это div, добавляем его содержимое, а не сам div
                                if ($element->nodeType === XML_ELEMENT_NODE && $element->nodeName === 'div') {
                                    foreach ($element->childNodes as $divChild) {
                                        $details->appendChild($divChild->cloneNode(true));
                                    }
                                } else {
                                    $details->appendChild($element->cloneNode(true));
                                }
                            }
                            
                            // Заменяем h2 на details
                            $h2->parentNode->replaceChild($details, $h2);
                            
                            // Удаляем оригинальные элементы
                            foreach ($contentElements as $element) {
                                if ($element->parentNode) {
                                    $element->parentNode->removeChild($element);
                                }
                            }
                        }
                    }
                }
                
                // Агрессивно удаляем все div обертки внутри details
                // Оставляем только прямые элементы: p, ul, ol, li, h3, h4 и т.д.
                $detailsElements = $xpath->query('.//details[@class="faq-spoiler"]', $wrapper);
                
                foreach ($detailsElements as $details) {
                    // Находим все div внутри details (но не внутри summary)
                    $divsInside = $xpath->query('.//div', $details);
                    $divsToProcess = [];
                    
                    foreach ($divsInside as $div) {
                        // Пропускаем div внутри summary
                        $parent = $div->parentNode;
                        $isInSummary = false;
                        while ($parent) {
                            if ($parent->nodeName === 'summary') {
                                $isInSummary = true;
                                break;
                            }
                            if ($parent === $details) {
                                break;
                            }
                            $parent = $parent->parentNode;
                        }
                        
                        if (!$isInSummary) {
                            $divsToProcess[] = $div;
                        }
                    }
                    
                    // Удаляем div и разворачиваем их содержимое (обрабатываем в обратном порядке)
                    // Сортируем по глубине вложенности (сначала самые глубокие)
                    usort($divsToProcess, function($a, $b) {
                        $depthA = 0;
                        $depthB = 0;
                        $parentA = $a->parentNode;
                        $parentB = $b->parentNode;
                        
                        while ($parentA && $parentA->nodeName !== 'details') {
                            $depthA++;
                            $parentA = $parentA->parentNode;
                        }
                        
                        while ($parentB && $parentB->nodeName !== 'details') {
                            $depthB++;
                            $parentB = $parentB->parentNode;
                        }
                        
                        return $depthB - $depthA; // Обрабатываем сначала самые глубокие
                    });
                    
                    foreach ($divsToProcess as $div) {
                        $parent = $div->parentNode;
                        if ($parent) {
                            // Перемещаем все дочерние элементы div на уровень выше
                            $children = [];
                            foreach ($div->childNodes as $child) {
                                $children[] = $child;
                            }
                            
                            foreach ($children as $child) {
                                $parent->insertBefore($child->cloneNode(true), $div);
                            }
                            
                            $parent->removeChild($div);
                        }
                    }
                }
                
                // Удаляем все остальные лишние div обертки
                $iterations = 0;
                while ($iterations < 10) {
                    $iterations++;
                    $changed = false;
                    
                    $allDivs = $xpath->query('.//div', $wrapper);
                    $divsToProcess = [];
                    foreach ($allDivs as $div) {
                        if ($div !== $wrapper) {
                            $divsToProcess[] = $div;
                        }
                    }
                    
                    foreach ($divsToProcess as $div) {
                        $divText = trim($div->textContent);
                        
                        // Удаляем пустые div
                        if (empty($divText)) {
                            if ($div->parentNode) {
                                $div->parentNode->removeChild($div);
                                $changed = true;
                                continue;
                            }
                        }
                        
                        // Если div содержит только один элемент, разворачиваем его
                        if ($div->childNodes->length === 1) {
                            $singleChild = $div->childNodes->item(0);
                            if ($singleChild->nodeType === XML_ELEMENT_NODE && 
                                $singleChild->nodeName !== 'details') {
                                if ($div->parentNode) {
                                    $div->parentNode->replaceChild($singleChild->cloneNode(true), $div);
                                    $changed = true;
                                    continue;
                                }
                            }
                        }
                        
                        // Если div содержит только details, разворачиваем его
                        $detailsCount = $xpath->query('.//details', $div)->length;
                        $childCount = $div->childNodes->length;
                        if ($detailsCount === $childCount && $detailsCount > 0) {
                            if ($div->parentNode) {
                                foreach ($div->childNodes as $child) {
                                    $div->parentNode->insertBefore($child->cloneNode(true), $div);
                                }
                                $div->parentNode->removeChild($div);
                                $changed = true;
                                continue;
                            }
                        }
                    }
                    
                    if (!$changed) {
                        break;
                    }
                }
                
                // Получаем очищенный HTML
                $newContent = '';
                foreach ($wrapper->childNodes as $node) {
                    $newContent .= $dom->saveHTML($node);
                }
                
                $newContent = trim($newContent);
                
                // Используем регулярные выражения для финальной очистки
                // Перемещаем контент после </details> внутрь details
                $iterations = 0;
                while ($iterations < 20) {
                    $iterations++;
                    $changed = false;
                    
                    // Ищем все details без контента
                    preg_match_all('/<details[^>]*class=["\']faq-spoiler["\'][^>]*>(.*?)<\/details>/is', $newContent, $allDetails, PREG_OFFSET_CAPTURE);
                    
                    // Обрабатываем в обратном порядке
                    for ($i = count($allDetails[0]) - 1; $i >= 0; $i--) {
                        $fullMatch = $allDetails[0][$i][0];
                        $fullPos = $allDetails[0][$i][1];
                        $inner = $allDetails[1][$i][0];
                        
                        // Проверяем, есть ли контент кроме summary
                        $withoutSummary = preg_replace('/<summary[^>]*>.*?<\/summary>/is', '', $inner);
                        $withoutSummary = trim(strip_tags($withoutSummary));
                        
                        if (empty($withoutSummary)) {
                            // Контента нет, ищем следующий div после этого details
                            $afterPos = $fullPos + strlen($fullMatch);
                            $after = substr($newContent, $afterPos, 2000);
                            
                            // Ищем первый <div> после details (может быть с пробелами и вложенными div)
                            if (preg_match('/^\s*(<div[^>]*>)/is', $after, $divStart, PREG_OFFSET_CAPTURE)) {
                                $divStartPos = $divStart[0][1];
                                $divContent = substr($after, $divStartPos);
                                
                                // Находим закрывающий тег div с учетом вложенности
                                $depth = 1;
                                $pos = strlen($divStart[0][0]);
                                $maxPos = min(strlen($divContent), 5000);
                                
                                while ($depth > 0 && $pos < $maxPos) {
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
                                    $afterDiv = substr($after, $divStartPos + strlen($divHtml), 200);
                                    if (!preg_match('/^\s*<details[^>]*class=["\']faq-spoiler["\']/is', $afterDiv)) {
                                        // Перемещаем div внутрь details
                                        $newDetails = str_replace('</details>', "\n" . $divHtml . "\n</details>", $fullMatch);
                                        $toReplace = $fullMatch . substr($after, 0, $divStartPos + strlen($divHtml));
                                        
                                        $newContent = substr_replace($newContent, $newDetails, $fullPos, strlen($toReplace));
                                        $changed = true;
                                        break; // Начинаем заново после изменения
                                    }
                                }
                            }
                        }
                    }
                    
                    if (!$changed) {
                        break;
                    }
                }
                
                // Финальная очистка: удаляем все div обертки через DOM
                $dom2 = new \DOMDocument();
                libxml_use_internal_errors(true);
                $html2 = '<div id="final-wrapper">' . $newContent . '</div>';
                $dom2->loadHTML('<?xml encoding="UTF-8">' . $html2, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                libxml_clear_errors();
                
                $finalWrapper = $dom2->getElementById('final-wrapper');
                if ($finalWrapper) {
                    $xpath2 = new \DOMXPath($dom2);
                    
                    // Удаляем все div, которые содержат только details
                    $allDivs = $xpath2->query('.//div', $finalWrapper);
                    $divsToRemove = [];
                    
                    foreach ($allDivs as $div) {
                        if ($div !== $finalWrapper) {
                            $hasOnlyDetails = true;
                            $detailsCount = 0;
                            
                            foreach ($div->childNodes as $child) {
                                if ($child->nodeType === XML_ELEMENT_NODE) {
                                    if ($child->nodeName === 'details') {
                                        $detailsCount++;
                                    } else {
                                        $hasOnlyDetails = false;
                                        break;
                                    }
                                } elseif ($child->nodeType === XML_TEXT_NODE && trim($child->textContent) !== '') {
                                    $hasOnlyDetails = false;
                                    break;
                                }
                            }
                            
                            if ($hasOnlyDetails && $detailsCount > 0) {
                                $divsToRemove[] = $div;
                            }
                        }
                    }
                    
                    // Удаляем div и разворачиваем их содержимое
                    foreach ($divsToRemove as $div) {
                        $parent = $div->parentNode;
                        if ($parent) {
                            $children = [];
                            foreach ($div->childNodes as $child) {
                                $children[] = $child;
                            }
                            
                            foreach ($children as $child) {
                                $parent->insertBefore($child->cloneNode(true), $div);
                            }
                            
                            $parent->removeChild($div);
                        }
                    }
                    
                    // Получаем финальный HTML
                    $newContent = '';
                    foreach ($finalWrapper->childNodes as $node) {
                        // Если это div, который содержит только details, разворачиваем его
                        if ($node->nodeType === XML_ELEMENT_NODE && $node->nodeName === 'div') {
                            $hasOnlyDetails = true;
                            foreach ($node->childNodes as $child) {
                                if ($child->nodeType === XML_ELEMENT_NODE && $child->nodeName !== 'details') {
                                    $hasOnlyDetails = false;
                                    break;
                                } elseif ($child->nodeType === XML_TEXT_NODE && trim($child->textContent) !== '') {
                                    $hasOnlyDetails = false;
                                    break;
                                }
                            }
                            
                            if ($hasOnlyDetails) {
                                // Разворачиваем содержимое div
                                foreach ($node->childNodes as $child) {
                                    $newContent .= $dom2->saveHTML($child);
                                }
                            } else {
                                $newContent .= $dom2->saveHTML($node);
                            }
                        } else {
                            $newContent .= $dom2->saveHTML($node);
                        }
                    }
                    $newContent = trim($newContent);
                }
                
                // Финальная очистка: удаляем все div обертки через регулярные выражения
                // Удаляем <div>...</div> которые содержат только details
                $iterations = 0;
                while ($iterations < 10) {
                    $iterations++;
                    $changed = false;
                    
                    // Ищем div, который содержит только details (может быть с пробелами)
                    // Обрабатываем в обратном порядке через preg_replace_callback
                    $newContent = preg_replace_callback(
                        '/<div[^>]*>\s*(<details[^>]*class=["\']faq-spoiler["\'][^>]*>.*?<\/details>)\s*<\/div>/is',
                        function($matches) use (&$changed) {
                            $changed = true;
                            return $matches[1];
                        },
                        $newContent
                    );
                    
                    if (!$changed) {
                        break;
                    }
                }
                
                // Удаляем div в начале контента, если он начинается с <div><details>
                if (preg_match('/^<div[^>]*>\s*<details/is', $newContent)) {
                    // Находим закрывающий тег этого div
                    $depth = 1;
                    $pos = strpos($newContent, '<div');
                    if ($pos !== false) {
                        $pos = strpos($newContent, '>', $pos) + 1;
                        $divStart = substr($newContent, 0, $pos);
                        $afterDiv = substr($newContent, $pos);
                        
                        // Ищем закрывающий </div> для первого div
                        $closePos = 0;
                        while ($depth > 0 && $closePos < strlen($afterDiv)) {
                            $nextOpen = strpos($afterDiv, '<div', $closePos);
                            $nextClose = strpos($afterDiv, '</div>', $closePos);
                            
                            if ($nextClose === false) {
                                break;
                            }
                            
                            if ($nextOpen !== false && $nextOpen < $nextClose) {
                                $depth++;
                                $closePos = $nextOpen + 4;
                            } else {
                                $depth--;
                                if ($depth === 0) {
                                    // Нашли закрывающий тег первого div
                                    $divEnd = $nextClose + 6;
                                    $divContent = substr($afterDiv, 0, $divEnd);
                                    
                                    // Проверяем, что внутри только details
                                    if (preg_match('/^<details/is', trim($divContent))) {
                                        // Удаляем div обертку
                                        $newContent = $divContent . substr($afterDiv, $divEnd);
                                    }
                                    break;
                                }
                                $closePos = $nextClose + 6;
                            }
                        }
                    }
                }
                
                // Также простая замена для случаев <div><details>...</details></div>
                $newContent = preg_replace('/^<div[^>]*>\s*(<details[^>]*class=["\']faq-spoiler["\'][^>]*>.*?<\/details>)\s*<\/div>/is', '$1', $newContent);
                
                // Очищаем от лишних пробелов и переносов
                $newContent = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $newContent);
                $newContent = trim($newContent);
                
                DB::table('articles')
                    ->where('id', $article->id)
                    ->update(['content' => $newContent]);
                
                $this->info("  ✓ Статья нормализована");
            } catch (\Exception $e) {
                $this->error("  Ошибка обработки: " . $e->getMessage());
            }
        }

        $this->info("\nГотово! Все статьи типа guide нормализованы.");
        return 0;
    }
}
