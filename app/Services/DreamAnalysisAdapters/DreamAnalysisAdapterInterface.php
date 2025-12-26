<?php

namespace App\Services\DreamAnalysisAdapters;

interface DreamAnalysisAdapterInterface
{
    /**
     * Нормализует данные анализа в единую структуру
     * 
     * @param array $rawAnalysisData Оригинальные данные из API
     * @return array Нормализованная структура
     */
    public function normalize(array $rawAnalysisData): array;

    /**
     * Возвращает версию формата, которую поддерживает адаптер
     * 
     * @return string Версия формата (например, '1.0')
     */
    public function getVersion(): string;
}







