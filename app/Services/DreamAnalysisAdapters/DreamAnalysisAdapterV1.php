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
            // Нормализуем данные для одиночного сна (текущий + legacy schema)
            $dreamAnalysis = $rawAnalysisData['dream_analysis'] ?? [];
            $legacyMeta = $rawAnalysisData['dream_metadata']
                ?? ($dreamAnalysis['dream_metadata'] ?? []);

            $dreamTitle = $dreamAnalysis['dream_title']
                ?? ($legacyMeta['dream_title'] ?? null);
            $dreamDetailed = $dreamAnalysis['dream_detailed']
                ?? ($legacyMeta['dream_detailed'] ?? null);
            // Старые ответы: core_theme / central_message вместо dream_detailed
            if ($dreamDetailed === null || $dreamDetailed === '') {
                $dreamDetailed = $dreamAnalysis['central_message']
                    ?? ($dreamAnalysis['core_theme'] ?? null);
            }
            if ($dreamTitle === null || $dreamTitle === '') {
                $dreamTitle = $dreamAnalysis['core_theme'] ?? null;
            }

            $traditions = $dreamAnalysis['traditions'] ?? null;
            if ($traditions === null && isset($rawAnalysisData['response_metadata']['tradition_used'])) {
                $traditions = $rawAnalysisData['response_metadata']['tradition_used'];
            }
            if (is_string($traditions)) {
                $traditions = [$traditions];
            }
            if (! is_array($traditions)) {
                $traditions = [];
            }
            $normalized['traditions'] = $traditions;

            $normalized['analysis_type'] = $dreamAnalysis['analysis_type'] ?? 'single';

            $keySymbols = $dreamAnalysis['key_symbols'] ?? [];
            if ($keySymbols === [] && isset($rawAnalysisData['symbolic_elements']['objects']) && is_array($rawAnalysisData['symbolic_elements']['objects'])) {
                $keySymbols = self::mapLegacySymbolicObjects($rawAnalysisData['symbolic_elements']['objects']);
            }

            $normalized['single_analysis'] = [
                'dream_title' => $dreamTitle,
                'dream_detailed' => $dreamDetailed,
                'dream_type' => $dreamAnalysis['dream_type'] ?? ($legacyMeta['dream_type'] ?? null),
                'key_symbols' => $keySymbols,
                'unified_locations' => $dreamAnalysis['unified_locations']
                    ?? self::mapLegacyLocations($rawAnalysisData['symbolic_elements']['locations'] ?? []),
                'key_tags' => $dreamAnalysis['key_tags']
                    ?? self::mapLegacyTags($rawAnalysisData['tags_and_categories'] ?? []),
                'summary_insight' => $dreamAnalysis['summary_insight'] ?? ($legacyMeta['summary_insight'] ?? null),
                'emotional_tone' => $dreamAnalysis['emotional_tone'] ?? ($legacyMeta['emotional_tone'] ?? null),
            ];

            $normalized['recommendations'] = self::normalizeRecommendations(
                $rawAnalysisData['recommendations'] ?? ($normalized['recommendations'] ?? [])
            );
        } else {
            // Нормализуем данные для серии снов
            $seriesAnalysis = $rawAnalysisData['series_analysis'] ?? [];
            $dreams = $rawAnalysisData['dreams'] ?? [];

            $normalized['traditions'] = $seriesAnalysis['traditions'] ?? [];
            $normalized['analysis_type'] = $seriesAnalysis['analysis_type'] ?? 'series_integrated';
            $normalized['recommendations'] = self::normalizeRecommendations(
                $rawAnalysisData['recommendations'] ?? []
            );

            $normalized['series_analysis'] = [
                'series_title' => $seriesAnalysis['series_title'] ?? null,
                'overall_theme' => $seriesAnalysis['overall_theme'] ?? null,
                'emotional_arc' => $seriesAnalysis['emotional_arc'] ?? null,
                'key_connections' => $seriesAnalysis['key_connections'] ?? [],
                'dreams' => [],
            ];

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

    /**
     * @param  mixed  $recommendations
     * @return list<string>
     */
    private static function normalizeRecommendations(mixed $recommendations): array
    {
        if (! is_array($recommendations)) {
            return [];
        }

        // Уже плоский список строк
        if (array_is_list($recommendations)) {
            return array_values(array_filter(
                array_map(fn ($item) => is_string($item) ? $item : null, $recommendations)
            ));
        }

        // Legacy: { for_practice_development: [...], for_integration: [...], ... }
        $flat = [];
        foreach ($recommendations as $value) {
            if (is_string($value)) {
                $flat[] = $value;
            } elseif (is_array($value)) {
                foreach ($value as $item) {
                    if (is_string($item)) {
                        $flat[] = $item;
                    } elseif (is_array($item) && isset($item['text']) && is_string($item['text'])) {
                        $flat[] = $item['text'];
                    } elseif (is_array($item) && isset($item['recommendation']) && is_string($item['recommendation'])) {
                        $flat[] = $item['recommendation'];
                    }
                }
            }
        }

        return $flat;
    }

    /**
     * @param  list<mixed>  $objects
     * @return list<array{symbol: string, meaning: string}>
     */
    private static function mapLegacySymbolicObjects(array $objects): array
    {
        $out = [];
        foreach ($objects as $obj) {
            if (! is_array($obj)) {
                continue;
            }
            $symbol = $obj['name'] ?? $obj['symbol'] ?? $obj['object'] ?? null;
            $meaning = $obj['meaning'] ?? $obj['interpretation'] ?? $obj['description'] ?? '';
            if (is_string($symbol) && $symbol !== '') {
                $out[] = [
                    'symbol' => $symbol,
                    'meaning' => is_string($meaning) ? $meaning : '',
                ];
            }
        }

        return $out;
    }

    /**
     * @param  list<mixed>  $locations
     * @return list<string>
     */
    private static function mapLegacyLocations(array $locations): array
    {
        $out = [];
        foreach ($locations as $loc) {
            if (is_string($loc) && $loc !== '') {
                $out[] = $loc;
            } elseif (is_array($loc)) {
                $name = $loc['name'] ?? $loc['location'] ?? null;
                if (is_string($name) && $name !== '') {
                    $out[] = $name;
                }
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $tags
     * @return list<string>
     */
    private static function mapLegacyTags(array $tags): array
    {
        $out = [];
        foreach (['primary_tags', 'emotional_tags', 'theme_tags', 'skill_tags'] as $key) {
            if (! isset($tags[$key]) || ! is_array($tags[$key])) {
                continue;
            }
            foreach ($tags[$key] as $tag) {
                if (is_string($tag) && $tag !== '') {
                    $out[] = $tag;
                }
            }
        }

        return array_values(array_unique($out));
    }
}
























