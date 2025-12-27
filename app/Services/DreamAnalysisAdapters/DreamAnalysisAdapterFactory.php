<?php

namespace App\Services\DreamAnalysisAdapters;

use InvalidArgumentException;

class DreamAnalysisAdapterFactory
{
    /**
     * Получить адаптер для указанной версии
     * 
     * @param string $version Версия формата (например, '1.0')
     * @return DreamAnalysisAdapterInterface
     * @throws InvalidArgumentException Если версия не поддерживается
     */
    public static function getAdapter(string $version = '1.0'): DreamAnalysisAdapterInterface
    {
        return match ($version) {
            '1.0' => new DreamAnalysisAdapterV1(),
            default => throw new InvalidArgumentException("Версия формата '{$version}' не поддерживается"),
        };
    }

    /**
     * Определить версию формата из сырых данных
     * 
     * @param array $rawAnalysisData Оригинальные данные из API
     * @return string Версия формата
     */
    public static function detectVersion(array $rawAnalysisData): string
    {
        // Если есть поле version, используем его
        if (isset($rawAnalysisData['version'])) {
            return (string) $rawAnalysisData['version'];
        }

        // По умолчанию версия 1.0 (текущий формат)
        return '1.0';
    }
}
























