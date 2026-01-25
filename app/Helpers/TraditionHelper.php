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

    /**
     * Нормализует название традиции (русское или английское) в ключ конфига
     * 
     * @param string $tradition Название традиции (может быть русским или ключом)
     * @return string Ключ традиции из конфига
     */
    public static function normalizeKey(string $tradition): string
    {
        $traditionLower = mb_strtolower(trim($tradition));
        
        // Маппинг русских названий и альтернативных ключей на ключи конфига
        $russianToKeyMap = [
            // Эклектическая/комплексная традиция (разные варианты написания)
            'эклектический' => 'eclectic',
            'эклектическая' => 'eclectic',
            'эклектичный' => 'eclectic',
            'эклектичная' => 'eclectic',
            'комплексная' => 'eclectic',
            'комплексный' => 'eclectic',
            'complex_analysis' => 'eclectic',
            // Фрейдистская
            'фрейдистская' => 'freudian',
            'фрейдистский' => 'freudian',
            // Юнгианская
            'юнгианская' => 'jungian',
            'юнгианский' => 'jungian',
            // Когнитивная
            'когнитивная' => 'cognitive',
            'когнитивный' => 'cognitive',
            // Символическая
            'символическая' => 'symbolic',
            'символический' => 'symbolic',
            // Шаманская
            'шаманская' => 'shamanic',
            'шаманский' => 'shamanic',
            // Гештальт
            'гештальт' => 'gestalt',
            // Практика ОС
            'практика ос' => 'lucid_centered',
            'lucid_centered' => 'lucid_centered',
            // Хакеры снов
            'хакеры снов' => 'dream_hackers',
            'dream_hackers' => 'dream_hackers',
            // Толтекская
            'толтекская' => 'castaneda_toltec',
            'толтекский' => 'castaneda_toltec',
            'castaneda_toltec' => 'castaneda_toltec',
        ];
        
        // Если это уже ключ из конфига - возвращаем как есть
        if (isset(config('traditions')[$traditionLower])) {
            return $traditionLower;
        }
        
        // Если это русское название - преобразуем в ключ
        if (isset($russianToKeyMap[$traditionLower])) {
            return $russianToKeyMap[$traditionLower];
        }
        
        // Если не найдено - возвращаем оригинал (на случай новых традиций)
        return $traditionLower;
    }

    /**
     * Получить отображаемое название традиции (с нормализацией)
     * 
     * @param string $tradition Название традиции (может быть русским или ключом)
     * @return string Отображаемое название
     */
    public static function getDisplayName(string $tradition): string
    {
        $key = self::normalizeKey($tradition);
        return self::shortName($key);
    }
}













