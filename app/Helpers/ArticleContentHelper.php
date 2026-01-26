<?php

namespace App\Helpers;

use DOMDocument;
use DOMXPath;

class ArticleContentHelper
{
    /**
     * Теги, поддерживаемые Quill редактором
     */
    private const ALLOWED_TAGS = [
        'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'em', 'u', 's', 'b', 'i',
        'ul', 'ol', 'li',
        'blockquote',
        'code', 'pre',
        'a', 'img', 'video',
        'div', 'span', 'br', 'hr',
        'details', 'summary', // Для спойлеров FAQ
    ];

    /**
     * Атрибуты, которые разрешены для определенных тегов
     * Удаляем все классы - стили будут применяться через CSS
     */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'target', 'title', 'id'], // Убрали 'class'
        'img' => ['src', 'alt', 'title', 'id', 'width', 'height'], // Убрали 'class'
        'video' => ['src', 'controls', 'id', 'width', 'height'], // Убрали 'class'
        'h1' => ['id'], // Убрали 'class'
        'h2' => ['id'], // Убрали 'class' - id нужен для якорей
        'h3' => ['id'], // Убрали 'class'
        'h4' => ['id'], // Убрали 'class'
        'h5' => ['id'], // Убрали 'class'
        'h6' => ['id'], // Убрали 'class'
        'p' => ['id'], // Убрали 'class'
        'div' => ['id', 'data-spoiler', 'data-spoiler-header', 'data-spoiler-content', 'data-spoiler-open'], // Поддержка спойлеров через data-атрибуты
        'span' => ['id'], // Убрали 'class'
        'ul' => ['id'], // Убрали 'class'
        'ol' => ['id'], // Убрали 'class'
        'li' => ['id'], // Убрали 'class'
        'blockquote' => ['id'], // Убрали 'class'
        'code' => ['id'], // Убрали 'class'
        'pre' => ['id'], // Убрали 'class'
        'details' => ['open', 'class'], // Для спойлеров - атрибут open для открытого состояния и class для стилей
        'summary' => ['class'], // Заголовок спойлера - class для стилей
    ];

    /**
     * Очистка контента статьи от инлайн стилей и неподдерживаемых тегов
     *
     * @param string $content
     * @return string
     */
    public static function sanitize(string $content): string
    {
        if (empty(trim($content))) {
            return $content;
        }

        try {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            
            // Обертываем контент для правильного парсинга
            $html = '<div id="content-wrapper">' . $content . '</div>';
            $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            $wrapper = $xpath->query('//div[@id="content-wrapper"]')->item(0);
            
            if (!$wrapper) {
                return self::fallbackSanitize($content);
            }

            self::processNode($wrapper, $dom);

            // Получаем очищенный HTML
            $cleanedContent = '';
            foreach ($wrapper->childNodes as $node) {
                $cleanedContent .= $dom->saveHTML($node);
            }

            return trim($cleanedContent);
        } catch (\Exception $e) {
            return self::fallbackSanitize($content);
        }
    }

    /**
     * Преобразует h2 в details/summary для FAQ формата
     * Вызывается перед сохранением, если TinyMCE удалил details/summary
     * Работает только для статей типа 'guide'
     *
     * @param string $content
     * @param string|null $articleType Тип статьи ('guide' или 'article')
     * @return string
     */
    public static function convertH2ToDetails(string $content, ?string $articleType = null): string
    {
        // Преобразуем только для статей типа 'guide'
        if ($articleType !== 'guide') {
            return $content;
        }

        if (empty(trim($content)) || strpos($content, '<details') !== false) {
            // Если уже есть details или контент пустой, возвращаем как есть
            return $content;
        }

        try {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            
            $html = '<div id="content-wrapper">' . $content . '</div>';
            $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            $wrapper = $xpath->query('//div[@id="content-wrapper"]')->item(0);
            
            if (!$wrapper) {
                return $content;
            }

            // Находим все h2
            $h2Elements = $xpath->query('.//h2', $wrapper);
            
            if ($h2Elements->length === 0) {
                return $content;
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
                return $content;
            }

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
                        ($current->nodeType === XML_TEXT_NODE && trim($current->textContent) !== '')) {
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

            // Получаем обновленный HTML
            $newContent = '';
            foreach ($wrapper->childNodes as $node) {
                $newContent .= $dom->saveHTML($node);
            }
            
            return trim($newContent);
        } catch (\Exception $e) {
            return $content;
        }
    }

    /**
     * Рекурсивная обработка узлов DOM
     */
    private static function processNode($node, $dom)
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        $tagName = strtolower($node->nodeName);
        $allowedAttrs = self::ALLOWED_ATTRIBUTES[$tagName] ?? [];

        // Удаляем тег, если он не разрешен
        if (!in_array($tagName, self::ALLOWED_TAGS)) {
            // Перемещаем содержимое в родительский элемент
            $parent = $node->parentNode;
            if ($parent) {
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);
            }
            return;
        }

        // Удаляем инлайн стили
        if ($node->hasAttribute('style')) {
            $node->removeAttribute('style');
        }

        // Специальная обработка для ссылок на якоря
        if ($tagName === 'a' && $node->hasAttribute('href')) {
            $href = $node->getAttribute('href');
            // Если ссылка на якорь (начинается с #), удаляем target и rel
            if (strpos($href, '#') === 0) {
                if ($node->hasAttribute('target')) {
                    $node->removeAttribute('target');
                }
                if ($node->hasAttribute('rel')) {
                    $node->removeAttribute('rel');
                }
            }
        }

        // Специальная обработка для изображений - добавляем loading="lazy" и decoding="async" для оптимизации
        if ($tagName === 'img') {
            // Добавляем loading="lazy" если его нет
            if (!$node->hasAttribute('loading')) {
                $node->setAttribute('loading', 'lazy');
            }
            // Добавляем decoding="async" если его нет
            if (!$node->hasAttribute('decoding')) {
                $node->setAttribute('decoding', 'async');
            }
            // Если нет alt, добавляем пустой alt (для декоративных изображений)
            if (!$node->hasAttribute('alt')) {
                $node->setAttribute('alt', '');
            }
        }

        // Удаляем неразрешенные атрибуты
        $attributesToRemove = [];
        foreach ($node->attributes as $attr) {
            $attrName = strtolower($attr->name);
            // Пропускаем data-атрибуты (они могут использоваться для идентификации блоков и спойлеров)
            if (strpos($attrName, 'data-') === 0) {
                continue;
            }
            // Пропускаем style (уже обработан выше)
            if ($attrName === 'style') {
                continue;
            }
            if (!in_array($attrName, $allowedAttrs)) {
                $attributesToRemove[] = $attr->name;
            }
        }

        foreach ($attributesToRemove as $attrName) {
            $node->removeAttribute($attrName);
        }

        // Рекурсивно обрабатываем дочерние элементы
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            self::processNode($child, $dom);
        }
    }

    /**
     * Fallback метод очистки через регулярные выражения
     */
    private static function fallbackSanitize(string $content): string
    {
        // Удаляем инлайн стили
        $content = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $content);

        // Удаляем target и rel из якорных ссылок
        $content = preg_replace_callback(
            '/<a\s+([^>]*href\s*=\s*["\']#([^"\']*)["\'][^>]*)>/i',
            function($matches) {
                $attrs = preg_replace('/\s*(target|rel)\s*=\s*["\'][^"\']*["\']/i', '', $matches[1]);
                return '<a ' . $attrs . '>';
            },
            $content
        );

        return $content;
    }
}
