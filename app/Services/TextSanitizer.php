<?php

namespace App\Services;

/**
 * Сервис для санитизации текста описаний снов
 * Удаляет ссылки, домены, email адреса и другие нежелательные элементы
 */
class TextSanitizer
{
    /**
     * Очищает текст от нежелательных элементов
     *
     * @param string|null $text Исходный текст
     * @return string|null Очищенный текст
     */
    public static function clean(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }

        // Удаляем HTML теги (если есть)
        $text = strip_tags($text);

        // Удаляем URL (http://, https://, www.)
        $text = preg_replace('/https?:\/\/[^\s]+/iu', '', $text);
        $text = preg_replace('/www\.[^\s]+/iu', '', $text);

        // Удаляем домены (например: site.com, site.ru, example.org и т.д.)
        // Паттерн: слово + точка + доменная зона (2-6 символов)
        $text = preg_replace('/\b[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?)*(?:\.[a-z]{2,6})\b/iu', '', $text);

        // Удаляем email адреса
        $text = preg_replace('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}\b/iu', '', $text);

        // Удаляем специальные символы для URL (оставляем только те, что нужны для текста)
        // Удаляем скобки вокруг ссылок и других элементов
        $text = preg_replace('/\[([^\]]*)\]/u', '$1', $text); // [текст] -> текст
        $text = preg_replace('/\(([^)]*https?:\/\/[^)]*)\)/iu', '', $text); // (текст с URL) -> удаляем
        $text = preg_replace('/\(([^)]*www\.[^)]*)\)/iu', '', $text); // (текст с www) -> удаляем

        // Удаляем множественные пробелы, переносы строк и табуляции
        $text = preg_replace('/\s+/u', ' ', $text);

        // Обрезаем пробелы в начале и конце
        $text = trim($text);

        return $text === '' ? null : $text;
    }

    /**
     * Очищает несколько текстов
     *
     * @param array $texts Массив текстов
     * @return array Массив очищенных текстов
     */
    public static function cleanMultiple(array $texts): array
    {
        return array_map([self::class, 'clean'], $texts);
    }
}
