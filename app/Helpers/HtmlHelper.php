<?php

namespace App\Helpers;

class HtmlHelper
{
    /**
     * Санитизация HTML - разрешает только безопасные теги: h2, h3, p, ul, li, strong, em
     * 
     * @param string|null $html HTML строка для санитизации
     * @return string Очищенный HTML
     */
    public static function sanitize($html): string
    {
        if (empty($html)) {
            return '';
        }

        // Разрешенные теги (добавлены h4, h5, h6, ol, b, i, br для совместимости)
        $allowedTags = '<h2><h3><h4><h5><h6><p><ul><ol><li><strong><em><b><i><br>';
        
        // Санитизируем HTML, оставляя только разрешенные теги
        $sanitized = strip_tags($html, $allowedTags);
        
        // Оборачиваем <li> элементы, которые не находятся внутри <ul> или <ol>, в <ul>
        // Сначала сохраняем все существующие списки
        $lists = [];
        $sanitized = preg_replace_callback(
            '/(<ul[^>]*>.*?<\/ul>|<ol[^>]*>.*?<\/ol>)/is',
            function($matches) use (&$lists) {
                $key = '___LIST_' . count($lists) . '___';
                $lists[$key] = $matches[0];
                return $key;
            },
            $sanitized
        );
        
        // Теперь находим последовательности <li>...</li> и оборачиваем их в <ul>
        // Заменяем последовательные <li> элементы на <ul><li>...</li></ul>
        $sanitized = preg_replace(
            '/((?:<li[^>]*>.*?<\/li>\s*)+)/is',
            '<ul>$1</ul>',
            $sanitized
        );
        
        // Восстанавливаем сохраненные списки
        foreach ($lists as $key => $list) {
            $sanitized = str_replace($key, $list, $sanitized);
        }
        
        return $sanitized;
    }

    /**
     * Санитизация заголовка - убирает HTML теги h2/h3 из начала, возвращает только текст
     * Используется когда заголовок должен быть обернут в свой тег
     * 
     * @param string|null $html HTML строка с заголовком
     * @return string Текст без HTML тегов
     */
    public static function sanitizeTitle($html): string
    {
        if (empty($html)) {
            return '';
        }

        // Убираем все HTML теги
        $text = strip_tags($html);
        
        return trim($text);
    }
}

