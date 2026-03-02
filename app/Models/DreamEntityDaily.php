<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DreamEntityDaily extends Model
{
    protected $table = 'dream_entity_daily';

    protected $fillable = ['date', 'type', 'slug', 'name', 'mentions'];

    /**
     * Топ сущностей за дату из дневной агрегации (если есть), иначе из основной таблицы.
     */
    public static function topForDate(string $type, string $date, int $limit = 100): array
    {
        $rows = static::where('type', $type)
            ->where('date', $date)
            ->orderByDesc('mentions')
            ->limit($limit)
            ->get(['slug', 'name', 'mentions'])
            ->toArray();

        if (!empty($rows)) {
            return $rows;
        }

        return DreamInterpretationEntity::topForDate($type, $date, $limit);
    }

    /**
     * Упоминания одной сущности по дням за период (для графика динамики).
     *
     * @return array<int, array{date: string, mentions: int}> отсортировано по date
     */
    public static function mentionsOverPeriod(string $type, string $slug, string $from, string $to): array
    {
        $rows = static::where('type', $type)
            ->where('slug', $slug)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get(['date', 'name', 'mentions'])
            ->toArray();

        return array_map(fn ($r) => [
            'date' => is_string($r['date']) ? $r['date'] : (\Carbon\Carbon::parse($r['date'])->format('Y-m-d')),
            'mentions' => (int) ($r['mentions'] ?? 0),
        ], $rows);
    }

    /**
     * Получить имя сущности по type и slug (из любой записи).
     */
    public static function nameFor(string $type, string $slug): ?string
    {
        $row = static::where('type', $type)->where('slug', $slug)->first(['name']);
        return $row?->name;
    }
}
