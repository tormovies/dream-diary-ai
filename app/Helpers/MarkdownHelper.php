<?php

namespace App\Helpers;

class MarkdownHelper
{
    /**
     * Конвертирует Markdown в HTML (без внешних CDN, всё локально).
     * Использует league/commonmark при наличии, иначе встроенный fallback.
     */
    public static function toHtml(string $markdown): string
    {
        if (trim($markdown) === '') {
            return '';
        }

        if (class_exists(\League\CommonMark\CommonMarkConverter::class)) {
            try {
                $converter = new \League\CommonMark\CommonMarkConverter(['html_input' => 'escape', 'allow_unsafe_links' => false]);
                return (string) $converter->convert($markdown);
            } catch (\Throwable $e) {
                return self::basicMarkdownToHtml($markdown);
            }
        }

        return self::basicMarkdownToHtml($markdown);
    }

    /**
     * Минимальная конвертация Markdown → HTML без внешних зависимостей (fallback).
     */
    private static function basicMarkdownToHtml(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
        $text = preg_replace('/^- (.+)$/m', '<li>$1</li>', $text);
        $text = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $text);
        $text = preg_replace('/\n\n+/', '</p><p>', $text);
        $text = '<p>' . trim($text) . '</p>';
        $text = preg_replace('/<p><(h[1-3]|ul|li)/', '<$1', $text);
        $text = preg_replace('/(<\/h[1-3]|<\/ul>)>/', '$1', $text);
        return $text;
    }
}
