<?php

namespace App\Support;

class InterpretationQualityAnalyzer
{
    public const ISSUE_PARSE_FAILED = 'parse_failed';

    public const ISSUE_TRUNCATED_TOKENS = 'truncated_tokens';

    public const ISSUE_TRUNCATED_JSON = 'truncated_json';

    public const ISSUE_JSON_SYNTAX = 'json_syntax';

    /** Ответ распарсился, но нет dream_detailed / series (чужой schema или пустой result). */
    public const ISSUE_EMPTY_CONTENT = 'empty_content';

    /**
     * @param  array<string, mixed>|null  $analysisData
     */
    public static function detect(?array $analysisData, ?string $rawApiResponse, bool $jsonWasRepaired = false): ?string
    {
        if (self::responseWasTruncatedByTokenLimit($analysisData, $rawApiResponse)) {
            return self::ISSUE_TRUNCATED_TOKENS;
        }

        if (is_array($analysisData) && isset($analysisData['parse_error'])) {
            return self::responseWasTruncatedByTokenLimit($analysisData, $rawApiResponse)
                ? self::ISSUE_PARSE_FAILED
                : self::ISSUE_JSON_SYNTAX;
        }

        if ($jsonWasRepaired) {
            return self::ISSUE_TRUNCATED_JSON;
        }

        if (is_array($analysisData) && ! self::hasUsableAnalysisContent($analysisData)) {
            // Пустой/чужой schema при наличии ответа API — нужно переделать анализ
            if (self::hasApiPayload($analysisData, $rawApiResponse)) {
                return self::ISSUE_EMPTY_CONTENT;
            }
        }

        return null;
    }

    /**
     * Есть ли текст анализа в ожидаемом (или совместимом) формате.
     *
     * @param  array<string, mixed>  $analysisData
     */
    public static function hasUsableAnalysisContent(array $analysisData): bool
    {
        if (! empty($analysisData['dream_analysis']['dream_detailed'])) {
            return true;
        }

        if (! empty($analysisData['series_analysis']['overall_theme'])) {
            return true;
        }

        if (! empty($analysisData['dreams']) && is_array($analysisData['dreams'])) {
            foreach ($analysisData['dreams'] as $dream) {
                if (is_array($dream) && ! empty($dream['dream_detailed'])) {
                    return true;
                }
            }
        }

        // Legacy schema (unified_schema / dream_metadata)
        if (! empty($analysisData['dream_metadata']['dream_detailed'])) {
            return true;
        }

        if (! empty($analysisData['dream_analysis']['dream_metadata']['dream_detailed'])) {
            return true;
        }

        if (! empty($analysisData['dream_analysis']['core_theme'])
            || ! empty($analysisData['dream_analysis']['central_message'])) {
            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>|null  $analysisData
     */
    private static function hasApiPayload(?array $analysisData, ?string $rawApiResponse): bool
    {
        if (is_string($rawApiResponse) && $rawApiResponse !== '') {
            return true;
        }

        if (! is_array($analysisData)) {
            return false;
        }

        return ! empty($analysisData['full_content']) || ! empty($analysisData['raw_content']);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public static function detectFromDeepSeekResult(array $result): ?string
    {
        $analysisData = $result['analysis_data'] ?? null;
        if (! is_array($analysisData)) {
            $analysisData = null;
        }

        return self::detect(
            $analysisData,
            $result['raw_response'] ?? null,
            (bool) ($result['json_was_repaired'] ?? false)
        );
    }

    public static function label(?string $issue): ?string
    {
        return match ($issue) {
            self::ISSUE_PARSE_FAILED => 'JSON не распарсился (обрезка)',
            self::ISSUE_JSON_SYNTAX => 'Синтаксис JSON в ответе',
            self::ISSUE_TRUNCATED_TOKENS => 'Обрезка по лимиту токенов',
            self::ISSUE_TRUNCATED_JSON => 'Обрезанный JSON (восстановлен)',
            self::ISSUE_EMPTY_CONTENT => 'Пустой анализ (нужен повтор)',
            default => null,
        };
    }

    public static function badgeClass(?string $issue): string
    {
        return match ($issue) {
            self::ISSUE_PARSE_FAILED => 'bg-red-100 text-red-800',
            self::ISSUE_JSON_SYNTAX => 'bg-rose-100 text-rose-800',
            self::ISSUE_TRUNCATED_TOKENS => 'bg-orange-100 text-orange-800',
            self::ISSUE_TRUNCATED_JSON => 'bg-amber-100 text-amber-800',
            self::ISSUE_EMPTY_CONTENT => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function filterOptions(): array
    {
        return [
            '' => 'Все',
            'any' => 'Любая проблема',
            self::ISSUE_EMPTY_CONTENT => 'Пустой анализ',
            self::ISSUE_JSON_SYNTAX => 'Синтаксис JSON',
            self::ISSUE_PARSE_FAILED => 'JSON не распарсился (обрезка)',
            self::ISSUE_TRUNCATED_TOKENS => 'Обрезка по токенам',
            self::ISSUE_TRUNCATED_JSON => 'Обрезанный JSON',
        ];
    }

    /**
     * Пустой нормализованный result при наличии ответа — тоже помечаем к переделке.
     *
     * @param  \App\Models\DreamInterpretationResult|null  $result
     */
    public static function detectEmptyNormalizedResult(?object $result, ?array $analysisData, ?string $rawApiResponse): ?string
    {
        if ($result === null) {
            if (self::hasApiPayload($analysisData, $rawApiResponse) && ! self::hasUsableAnalysisContent($analysisData ?? [])) {
                return self::ISSUE_EMPTY_CONTENT;
            }

            return null;
        }

        $type = $result->type ?? null;
        if ($type === 'single' && empty($result->dream_detailed)) {
            return self::ISSUE_EMPTY_CONTENT;
        }

        if ($type === 'series' && empty($result->overall_theme)) {
            $hasSeriesDreams = method_exists($result, 'relationLoaded')
                && $result->relationLoaded('seriesDreams')
                && $result->seriesDreams->isNotEmpty();
            if (! $hasSeriesDreams) {
                return self::ISSUE_EMPTY_CONTENT;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $analysisData
     */
    private static function responseWasTruncatedByTokenLimit(?array $analysisData, ?string $rawApiResponse): bool
    {
        if (is_array($analysisData) && ($analysisData['api_finish_reason'] ?? null) === 'length') {
            return true;
        }

        return self::finishReasonFromRawResponse($rawApiResponse) === 'length';
    }

    private static function finishReasonFromRawResponse(?string $rawApiResponse): ?string
    {
        if ($rawApiResponse === null || $rawApiResponse === '') {
            return null;
        }

        $decoded = json_decode($rawApiResponse, true);
        if (! is_array($decoded)) {
            return null;
        }

        $reason = $decoded['choices'][0]['finish_reason'] ?? null;

        return is_string($reason) ? $reason : null;
    }
}
