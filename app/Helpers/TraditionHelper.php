<?php

namespace App\Helpers;

class TraditionHelper
{
    /**
     * Получить все традиции
     */
    public static function all(): array
    {
        return config('traditions', []);
    }

    /**
     * Получить только активные традиции
     */
    public static function enabled(): array
    {
        return array_filter(config('traditions', []), fn($tradition) => $tradition['enabled'] ?? false);
    }

    /**
     * Получить ключи активных традиций для валидации (строка через запятую)
     */
    public static function validationKeys(): string
    {
        $keys = array_keys(self::enabled());
        return implode(',', $keys);
    }

    /**
     * Получить традицию по ключу
     */
    public static function get(string $key): ?array
    {
        return config("traditions.{$key}");
    }

    /**
     * Получить короткое название традиции
     */
    public static function shortName(string $key): string
    {
        return config("traditions.{$key}.name_short", $key);
    }

    /**
     * Получить полное название традиции
     */
    public static function fullName(string $key): string
    {
        return config("traditions.{$key}.name_full", $key);
    }

    /**
     * Получить описание для DeepSeek
     */
    public static function deepSeekDescription(string $key): string
    {
        return config("traditions.{$key}.deepseek_description", $key);
    }

    /**
     * Получить иконку традиции
     */
    public static function icon(string $key): ?string
    {
        return config("traditions.{$key}.icon");
    }

    /**
     * Проверить, активна ли традиция
     */
    public static function isEnabled(string $key): bool
    {
        return config("traditions.{$key}.enabled", false);
    }

    /**
     * Получить массив описаний для DeepSeek (для промпта)
     */
    public static function deepSeekDescriptions(): array
    {
        $descriptions = [];
        foreach (self::enabled() as $key => $tradition) {
            $descriptions[$key] = $tradition['deepseek_description'];
        }
        return $descriptions;
    }

    /**
     * Получить специфический промпт для single анализа традиции
     * 
     * @param string $key Ключ традиции
     * @return string|null Текст промпта или null, если не задан
     */
    public static function singleTraditionPrompt(string $key): ?string
    {
        $prompt = config("traditions.{$key}.single_tradition_prompt");
        return $prompt ?: null;
    }
}













