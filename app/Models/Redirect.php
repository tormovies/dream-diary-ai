<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    /** Кэш карты активных редиректов from_path → [to_url, status_code] (меньше запросов к БД на каждый HTTP-запрос) */
    public const ACTIVE_MAP_CACHE_KEY = 'redirect_active_map_v1';

    protected $fillable = [
        'from_path',
        'to_url',
        'status_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status_code' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::ACTIVE_MAP_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::ACTIVE_MAP_CACHE_KEY));
    }

    /**
     * Активный редирект для уже нормализованного пути (как в middleware).
     *
     * @return array{to_url: string, status_code: int}|null
     */
    public static function findActiveCached(string $normalizedPath): ?array
    {
        $map = Cache::remember(self::ACTIVE_MAP_CACHE_KEY, 3600, function () {
            return self::query()
                ->where('is_active', true)
                ->get(['from_path', 'to_url', 'status_code'])
                ->mapWithKeys(fn ($r) => [
                    $r->from_path => [
                        'to_url' => $r->to_url,
                        'status_code' => (int) $r->status_code,
                    ],
                ])
                ->all();
        });

        return $map[$normalizedPath] ?? null;
    }

    public static function forgetActiveMapCache(): void
    {
        Cache::forget(self::ACTIVE_MAP_CACHE_KEY);
    }

    /**
     * Нормализовать путь для сравнения (без ведущего слэша, единый вид).
     */
    public static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
