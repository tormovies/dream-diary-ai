<?php

namespace App\Support;

class InterpretationQualityAnalyzer
{
    public const ISSUE_PARSE_FAILED = 'parse_failed';

    public const ISSUE_TRUNCATED_TOKENS = 'truncated_tokens';

    public const ISSUE_TRUNCATED_JSON = 'truncated_json';

    /**
     * @param  array<string, mixed>|null  $analysisData
     */
    public static function detect(?array $analysisData, ?string $rawApiResponse, bool $jsonWasRepaired = false): ?string
    {
        if (is_array($analysisData) && isset($analysisData['parse_error'])) {
            return self::ISSUE_PARSE_FAILED;
        }

        if (self::finishReasonIsLength($rawApiResponse)) {
            return self::ISSUE_TRUNCATED_TOKENS;
        }

        if ($jsonWasRepaired) {
            return self::ISSUE_TRUNCATED_JSON;
        }

        return null;
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
            self::ISSUE_PARSE_FAILED => 'JSON не распарсился',
            self::ISSUE_TRUNCATED_TOKENS => 'Обрезка по лимиту токенов',
            self::ISSUE_TRUNCATED_JSON => 'Обрезанный JSON (восстановлен)',
            default => null,
        };
    }

    public static function badgeClass(?string $issue): string
    {
        return match ($issue) {
            self::ISSUE_PARSE_FAILED => 'bg-red-100 text-red-800',
            self::ISSUE_TRUNCATED_TOKENS => 'bg-orange-100 text-orange-800',
            self::ISSUE_TRUNCATED_JSON => 'bg-amber-100 text-amber-800',
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
            self::ISSUE_PARSE_FAILED => 'JSON не распарсился',
            self::ISSUE_TRUNCATED_TOKENS => 'Обрезка по токенам',
            self::ISSUE_TRUNCATED_JSON => 'Обрезанный JSON',
        ];
    }

    private static function finishReasonIsLength(?string $rawApiResponse): bool
    {
        if ($rawApiResponse === null || $rawApiResponse === '') {
            return false;
        }

        $decoded = json_decode($rawApiResponse, true);
        if (! is_array($decoded)) {
            return false;
        }

        $finishReason = $decoded['choices'][0]['finish_reason'] ?? null;

        return $finishReason === 'length';
    }
}
