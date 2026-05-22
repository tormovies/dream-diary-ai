<?php

namespace App\Support;

class DeepSeekJsonParser
{
    /**
     * @return array{data: ?array, error: ?string, error_code: ?int, repaired: bool}
     */
    public static function parseFromAssistantContent(string $content): array
    {
        $original = $content;

        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        }

        $content = trim($content);
        $candidates = self::extractJsonCandidates($content);

        foreach ($candidates as $candidate) {
            $decoded = self::decodeJsonCandidate($candidate['json']);
            if ($decoded !== null) {
                return [
                    'data' => $decoded,
                    'error' => null,
                    'error_code' => null,
                    'repaired' => $candidate['repaired'],
                ];
            }
        }

        return [
            'data' => null,
            'error' => 'Не удалось распарсить JSON из ответа API: '.json_last_error_msg(),
            'error_code' => json_last_error(),
            'repaired' => false,
            'raw_content' => $original,
        ];
    }

    /**
     * @return list<array{json: string, repaired: bool}>
     */
    private static function extractJsonCandidates(string $content): array
    {
        $candidates = [];

        // Закрытый markdown-блок (предпочтительно)
        if (preg_match('/```json\s*\n([\s\S]*?)\n```/i', $content, $matches)) {
            $candidates[] = ['json' => trim($matches[1]), 'repaired' => false];
        }

        // Блок без закрывающих ``` (обрезанный ответ)
        if (preg_match('/```json\s*\n([\s\S]*)$/is', $content, $matches)) {
            $json = trim($matches[1]);
            $json = preg_replace('/\n```\s*$/', '', $json) ?? $json;
            $candidates[] = ['json' => $json, 'repaired' => false];

            $repaired = self::trimToBalancedRootObject($json);
            if ($repaired !== null && $repaired !== $json) {
                $candidates[] = ['json' => $repaired, 'repaired' => true];
            }
        }

        $stripped = trim(preg_replace('/```\s*/', '', preg_replace('/```json\s*/i', '', $content) ?? $content) ?? $content);
        if ($stripped !== '') {
            $candidates[] = ['json' => $stripped, 'repaired' => false];
            $repaired = self::trimToBalancedRootObject($stripped);
            if ($repaired !== null && $repaired !== $stripped) {
                $candidates[] = ['json' => $repaired, 'repaired' => true];
            }
        }

        $jsonStart = strpos($content, '{');
        if ($jsonStart !== false) {
            $slice = substr($content, $jsonStart);
            $candidates[] = ['json' => $slice, 'repaired' => false];
            $repaired = self::trimToBalancedRootObject($slice);
            if ($repaired !== null && $repaired !== $slice) {
                $candidates[] = ['json' => $repaired, 'repaired' => true];
            }
        }

        // Уникальные кандидаты, сохраняя порядок
        $seen = [];
        $unique = [];
        foreach ($candidates as $item) {
            $hash = md5($item['json']);
            if (! isset($seen[$hash])) {
                $seen[$hash] = true;
                $unique[] = $item;
            }
        }

        return $unique;
    }

    private static function decodeJsonCandidate(string $jsonString): ?array
    {
        $variants = [
            self::sanitizeJsonCandidate($jsonString),
            self::sanitizeJsonCandidate(self::fixTrailingCommas($jsonString)),
            self::sanitizeJsonCandidate(self::escapeAsciiQuotesInsideStrings($jsonString)),
        ];

        foreach (array_unique($variants) as $variant) {
            if ($variant === '') {
                continue;
            }

            $decoded = json_decode($variant, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private static function sanitizeJsonCandidate(string $json): string
    {
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $json) ?? $json;

        return trim($json);
    }

    private static function fixTrailingCommas(string $json): string
    {
        return preg_replace('/,\s*([}\]])/', '$1', $json) ?? $json;
    }

    /**
     * Частая ошибка LLM: "сыром" внутри JSON-строки без экранирования.
     */
    private static function escapeAsciiQuotesInsideStrings(string $json): string
    {
        $chars = mb_str_split($json, 1, 'UTF-8');
        $out = '';
        $inString = false;
        $escaped = false;
        $len = count($chars);

        for ($i = 0; $i < $len; $i++) {
            $char = $chars[$i];

            if (! $inString) {
                $out .= $char;
                if ($char === '"') {
                    $inString = true;
                    $escaped = false;
                }
                continue;
            }

            if ($escaped) {
                $out .= $char;
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $out .= $char;
                $escaped = true;
                continue;
            }

            if ($char === '"') {
                if (self::hasWordCharNearbyUtf8($chars, $i - 1, -1) && self::hasWordCharNearbyUtf8($chars, $i + 1, 1)) {
                    $out .= '\\"';
                    continue;
                }

                $out .= $char;
                $inString = false;
                continue;
            }

            $out .= $char;
        }

        return $out;
    }

    private static function trimToBalancedRootObject(string $json): ?string
    {
        $chars = mb_str_split($json, 1, 'UTF-8');
        $openBraces = 0;
        $lastValidIndex = -1;
        $len = count($chars);

        for ($i = 0; $i < $len; $i++) {
            $char = $chars[$i];

            if ($char === '"') {
                $i++;
                while ($i < $len) {
                    if ($chars[$i] === '"' && ($i === 0 || $chars[$i - 1] !== '\\')) {
                        break;
                    }
                    $i++;
                }
                continue;
            }

            if ($char === '{') {
                $openBraces++;
            } elseif ($char === '}') {
                $openBraces--;
                if ($openBraces === 0) {
                    $lastValidIndex = $i;
                    break;
                }
            }
        }

        if ($lastValidIndex > 0) {
            return implode('', array_slice($chars, 0, $lastValidIndex + 1));
        }

        return null;
    }

    /**
     * @param  list<string>  $chars
     */
    private static function hasWordCharNearbyUtf8(array $chars, int $start, int $direction): bool
    {
        $len = count($chars);
        for ($i = $start; $i >= 0 && $i < $len; $i += $direction) {
            $char = $chars[$i];
            if (preg_match('/\s/u', $char)) {
                continue;
            }

            return preg_match('/[\p{L}\p{N}]/u', $char) === 1;
        }

        return false;
    }
}
