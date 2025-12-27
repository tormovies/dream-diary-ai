<?php

namespace App\Services\DreamAnalysisAdapters;

class DreamAnalysisAdapterV1 implements DreamAnalysisAdapterInterface
{
    /**
     * Нормализует данные анализа версии 1.0 в единую структуру
     */
    public function normalize(array $rawAnalysisData): array
    {
        // Определяем тип анализа
        $isSeries = isset($rawAnalysisData['series_analysis']) && isset($rawAnalysisData['dreams']);
        $type = $isSeries ? 'series' : 'single';

        // Базовая структура
        $normalized = [
            'type' => $type,
            'version' => '1.0',
            'traditions' => [],
            'analysis_type' => null,
            'recommendations' => $rawAnalysisData['recommendations'] ?? [],
            'single_analysis' => null,
            'series_analysis' => null,
        ];

        if ($type === 'single') {
            // Нормализуем данные для одиночного сна
            $dreamAnalysis = $rawAnalysisData['dream_analysis'] ?? [];
            
            $normalized['traditions'] = $dreamAnalysis['traditions'] ?? [];
            $normalized['analysis_type'] = $dreamAnalysis['analysis_type'] ?? 'single';
            
            $normalized['single_analysis'] = [
                'dream_title' => $dreamAnalysis['dream_title'] ?? null,
                'dream_detailed' => $dreamAnalysis['dream_detailed'] ?? null,
                'dream_type' => $dreamAnalysis['dream_type'] ?? null,
                'key_symbols' => $dreamAnalysis['key_symbols'] ?? [],
                'unified_locations' => $dreamAnalysis['unified_locations'] ?? [],
                'key_tags' => $dreamAnalysis['key_tags'] ?? [],
                'summary_insight' => $dreamAnalysis['summary_insight'] ?? null,
                'emotional_tone' => $dreamAnalysis['emotional_tone'] ?? null,
            ];
        } else {
            // Нормализуем данные для серии снов
            $seriesAnalysis = $rawAnalysisData['series_analysis'] ?? [];
            $dreams = $rawAnalysisData['dreams'] ?? [];
            
            $normalized['traditions'] = $seriesAnalysis['traditions'] ?? [];
            $normalized['analysis_type'] = $seriesAnalysis['analysis_type'] ?? 'series_integrated';
            
            $normalized['series_analysis'] = [
                'series_title' => $seriesAnalysis['series_title'] ?? null,
                'overall_theme' => $seriesAnalysis['overall_theme'] ?? null,
                'emotional_arc' => $seriesAnalysis['emotional_arc'] ?? null,
                'key_connections' => $seriesAnalysis['key_connections'] ?? [],
                'dreams' => [],
            ];

            // Нормализуем каждый сон в серии
            foreach ($dreams as $index => $dream) {
                $normalized['series_analysis']['dreams'][] = [
                    'dream_number' => $index + 1,
                    'dream_title' => $dream['dream_title'] ?? null,
                    'dream_detailed' => $dream['dream_detailed'] ?? null,
                    'dream_type' => $dream['dream_type'] ?? null,
                    'key_symbols' => $dream['key_symbols'] ?? [],
                    'unified_locations' => $dream['unified_locations'] ?? [],
                    'key_tags' => $dream['key_tags'] ?? [],
                    'summary_insight' => $dream['summary_insight'] ?? null,
                    'emotional_tone' => $dream['emotional_tone'] ?? null,
                ];
            }
        }

        return $normalized;
    }

    /**
     * Возвращает версию формата
     */
    public function getVersion(): string
    {
        return '1.0';
    }
}
























