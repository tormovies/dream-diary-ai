<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DreamInterpretationEntity extends Model
{
    public const TYPE_SYMBOL = 'symbol';
    public const TYPE_LOCATION = 'location';
    public const TYPE_TAG = 'tag';

    public const SOURCE_RESULT = 'result';
    public const SOURCE_SERIES_DREAM = 'series_dream';

    protected $table = 'dream_interpretation_entities';

    protected $fillable = [
        'dream_interpretation_id',
        'type',
        'slug',
        'name',
        'meaning',
        'source',
        'source_id',
        'interpretation_created_at',
    ];

    protected $casts = [
        'interpretation_created_at' => 'datetime',
    ];

    public function interpretation(): BelongsTo
    {
        return $this->belongsTo(DreamInterpretation::class, 'dream_interpretation_id');
    }

    /**
     * Нормализовать название в slug. Регистронезависимо: «Дом» и «дом» дают один slug.
     */
    public static function nameToSlug(string $name): string
    {
        $name = trim(strip_tags($name));
        $name = preg_replace('/\s+/u', ' ', $name);
        if ($name === '') {
            return 'n-a';
        }
        $name = mb_strtolower($name, 'UTF-8');
        $slug = Str::slug($name, '-', 'ru');
        return $slug !== '' ? $slug : 'e-' . substr(md5($name), 0, 8);
    }

    /**
     * Топ сущностей за указанную дату (или за вчера).
     */
    public static function topForDate(string $type, ?string $date = null, int $limit = 50): array
    {
        $date = $date ?? now()->subDay()->format('Y-m-d');
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';

        return static::query()
            ->where('type', $type)
            ->whereBetween('interpretation_created_at', [$start, $end])
            ->selectRaw('slug, MAX(name) as name, count(*) as mentions')
            ->groupBy('slug')
            ->orderByDesc('mentions')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Уникальные сущности с общим числом упоминаний (для списка страниц символов/локаций/тегов).
     */
    public static function uniqueWithCounts(string $type, int $limit = 500): array
    {
        return static::query()
            ->where('type', $type)
            ->selectRaw('slug, MAX(name) as name, count(*) as mentions')
            ->groupBy('slug')
            ->orderByDesc('mentions')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
