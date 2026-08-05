<?php

namespace App\Support;

class AnalysisContextExtractor
{
    /**
     * Достаёт context_for_next_analysis из уже распарсенных данных или из raw API response.
     *
     * @param  array<string, mixed>|null  $analysisData
     * @return array<string, mixed>|null
     */
    public static function extract(?array $analysisData, ?string $rawApiResponse = null): ?array
    {
        if (is_array($analysisData)) {
            $ctx = $analysisData['context_for_next_analysis'] ?? null;
            if (is_array($ctx) && $ctx !== []) {
                return $ctx;
            }

            // Иногда лежит только внутри full_content / raw_content (если parse_error)
            foreach (['full_content', 'raw_content'] as $key) {
                if (! empty($analysisData[$key]) && is_string($analysisData[$key])) {
                    $fromContent = self::fromAssistantContent($analysisData[$key]);
                    if ($fromContent !== null) {
                        return $fromContent;
                    }
                }
            }
        }

        if (is_string($rawApiResponse) && $rawApiResponse !== '') {
            $payload = json_decode($rawApiResponse, true);
            $content = $payload['choices'][0]['message']['content'] ?? null;
            if (is_string($content) && $content !== '') {
                return self::fromAssistantContent($content);
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function fromAssistantContent(string $content): ?array
    {
        $parsed = DeepSeekJsonParser::parseFromAssistantContent($content);
        $ctx = $parsed['data']['context_for_next_analysis'] ?? null;

        return is_array($ctx) && $ctx !== [] ? $ctx : null;
    }
}
