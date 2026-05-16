<?php

namespace App\Services;

use App\Models\Dream;
use App\Models\DreamInterpretation;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InterpretationDiaryTransferService
{
    private const DREAM_TYPES = [
        'Яркий сон',
        'Бледный сон',
        'Пограничное состояние',
        'Паралич',
        'ВТО',
        'Осознанное сновидение',
        'Глюк',
        'Транс / Гипноз',
    ];

    /**
     * @return list<array{title: ?string, description: string, dream_type: string}>
     */
    public function buildDreamBlocks(DreamInterpretation $interpretation, User $owner, Carbon $reportDate): array
    {
        $interpretation->loadMissing(['result.seriesDreams']);

        $result = $interpretation->result;
        $dreamParts = $this->extractDreamTexts(
            (string) $interpretation->dream_description,
            (string) ($interpretation->analysis_type ?? '')
        );

        if ($dreamParts === []) {
            return [];
        }

        if ($result && $result->type === 'series' && $result->seriesDreams->isNotEmpty()) {
            $seriesDreams = $result->seriesDreams->values();

            if (count($dreamParts) === 1 && $seriesDreams->count() > 1) {
                return $this->ensureTitles([[
                    'title' => $this->sanitizePlainText($result->series_title ?: $seriesDreams->first()->dream_title),
                    'description' => $dreamParts[0],
                    'dream_type' => $this->normalizeDreamType($seriesDreams->first()->dream_type ?? null),
                ]], $owner, $reportDate);
            }

            $blocks = [];
            foreach ($seriesDreams as $idx => $sd) {
                $text = $dreamParts[$idx] ?? null;
                if ($text === null || $text === '') {
                    continue;
                }
                $blocks[] = [
                    'title' => $idx === 0
                        ? $this->sanitizePlainText($sd->dream_title ?: $result->series_title)
                        : $this->sanitizePlainText($sd->dream_title),
                    'description' => $text,
                    'dream_type' => $this->normalizeDreamType($sd->dream_type ?? null),
                ];
            }

            if ($blocks !== []) {
                return $this->ensureTitles($blocks, $owner, $reportDate);
            }
        }

        if ($result && ($result->type ?? '') === 'single') {
            return $this->ensureTitles([[
                'title' => $this->sanitizePlainText($result->dream_title),
                'description' => $dreamParts[0],
                'dream_type' => $this->normalizeDreamType($result->dream_type ?? null),
            ]], $owner, $reportDate);
        }

        if (count($dreamParts) > 1) {
            $blocks = [];
            foreach ($dreamParts as $part) {
                if ($part === '') {
                    continue;
                }
                $blocks[] = [
                    'title' => null,
                    'description' => $part,
                    'dream_type' => 'Яркий сон',
                ];
            }

            if ($blocks !== []) {
                return $this->ensureTitles($blocks, $owner, $reportDate);
            }
        }

        return $this->ensureTitles([[
            'title' => $this->sanitizePlainText($result?->dream_title),
            'description' => $dreamParts[0],
            'dream_type' => $this->normalizeDreamType($result?->dream_type),
        ]], $owner, $reportDate);
    }

    public function transfer(
        DreamInterpretation $interpretation,
        User $user,
        string $reportDate,
        string $accessLevel,
        bool $allowPublicLinking
    ): Report {
        $date = Carbon::parse($reportDate)->startOfDay();
        $blocks = $this->buildDreamBlocks($interpretation, $user, $date);
        if ($blocks === []) {
            throw new \InvalidArgumentException('Не удалось подготовить текст сна для дневника.');
        }

        return DB::transaction(function () use ($interpretation, $user, $accessLevel, $allowPublicLinking, $blocks, $date): Report {
            $report = Report::create([
                'user_id' => $user->id,
                'report_date' => $date->toDateString(),
                'access_level' => $accessLevel,
                'status' => 'published',
                'user_context' => $interpretation->context,
                'analysis_id' => $interpretation->id,
                'analyzed_at' => now(),
            ]);

            foreach ($blocks as $index => $block) {
                Dream::create([
                    'report_id' => $report->id,
                    'title' => $block['title'],
                    'description' => $block['description'],
                    'dream_type' => $block['dream_type'],
                    'order' => $index,
                ]);
            }

            $interpretation->update([
                'report_id' => $report->id,
                'allow_public_linking' => $allowPublicLinking,
            ]);

            return $report->fresh();
        });
    }

    private function normalizeDreamType(?string $fromApi): string
    {
        if ($fromApi === null || $fromApi === '') {
            return 'Яркий сон';
        }
        $t = trim($fromApi);
        foreach (self::DREAM_TYPES as $allowed) {
            if (mb_strtolower($allowed) === mb_strtolower($t)) {
                return $allowed;
            }
        }

        return 'Яркий сон';
    }

    /**
     * @param  list<array{title: ?string, description: string, dream_type: string}>  $blocks
     * @return list<array{title: ?string, description: string, dream_type: string}>
     */
    private function ensureTitles(array $blocks, User $user, Carbon $date): array
    {
        foreach ($blocks as $index => $block) {
            $blocks[$index]['title'] = $this->sanitizePlainText($block['title'] ?? null);
        }

        $hasTitle = false;
        foreach ($blocks as $block) {
            if (($block['title'] ?? null) !== null) {
                $hasTitle = true;
                break;
            }
        }
        if (! $hasTitle && $blocks !== []) {
            $nick = $user->nickname ?? $user->name ?? 'Пользователь';
            $dateStr = $date->format('d.m.Y');
            $blocks[0]['title'] = "Отчёт {$nick} от {$dateStr}";
        }

        return $blocks;
    }

    private function isDreamSeries(string $text): bool
    {
        if (preg_match('/(?:^|\n)\s*---{2,}\s*(?:\n|$)/m', $text)) {
            return true;
        }
        if (preg_match('/[^\n]\s*\n\s*\n\s*[^\n]/', $text)) {
            return true;
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function splitDreams(string $text): array
    {
        $dreams = [];
        if (preg_match('/(?:^|\n)\s*---{2,}\s*(?:\n|$)/m', $text)) {
            $parts = preg_split('/(?:^|\n)\s*---{2,}\s*(?:\n|$)/m', $text);
            foreach ($parts as $part) {
                $part = trim((string) $part);
                if ($part !== '') {
                    $dreams[] = $part;
                }
            }
        } elseif (preg_match('/[^\n]\s*\n\s*\n\s*[^\n]/', $text)) {
            $parts = preg_split('/\n\s*\n+/', $text);
            foreach ($parts as $part) {
                $part = trim((string) $part);
                if ($part !== '') {
                    $dreams[] = $part;
                }
            }
        }

        return $dreams;
    }

    /**
     * Исходные тексты снов пользователя (не толкование из API).
     *
     * @return list<string>
     */
    private function extractDreamTexts(string $rawDreamText, string $analysisType): array
    {
        $rawDreamText = trim($rawDreamText);
        if ($rawDreamText === '') {
            return [];
        }

        if ($analysisType === 'series_integrated' || $this->isDreamSeries($rawDreamText)) {
            $parts = $this->splitDreams($rawDreamText);
            if ($parts !== []) {
                return array_values(array_filter(array_map(
                    fn (string $part) => $this->sanitizeDreamBody($part),
                    $parts
                )));
            }
        }

        $single = $this->sanitizeDreamBody($rawDreamText);

        return $single !== '' ? [$single] : [];
    }

    private function sanitizeDreamBody(string $value): string
    {
        return trim(strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function sanitizePlainText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        if ($text === '' || mb_strtolower($text) === 'null') {
            return null;
        }

        return $text;
    }
}
