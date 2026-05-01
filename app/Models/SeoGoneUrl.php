<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SeoGoneUrl extends Model
{
    /** Кэш множества путей (ключ путь → true) для проверки без запроса на каждый HTTP-запрос */
    public const PATH_SET_CACHE_KEY = 'seo_gone_url_paths_v1';

    protected $table = 'seo_gone_urls';

    protected $fillable = [
        'path',
        'source',
        'note',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::PATH_SET_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::PATH_SET_CACHE_KEY));
    }

    /**
     * Проверка наличия пути среди 410 Gone (кэшируется; сброс при изменении таблицы).
     *
     * @param  string  $normalizedPath  уже нормализованный путь (как в middleware)
     */
    public static function pathExistsCached(string $normalizedPath): bool
    {
        if ($normalizedPath === '/' || $normalizedPath === '') {
            return false;
        }

        $set = Cache::remember(self::PATH_SET_CACHE_KEY, 3600, function () {
            return self::query()
                ->pluck('path')
                ->flip()
                ->all();
        });

        return isset($set[$normalizedPath]);
    }

    public static function forgetPathCache(): void
    {
        Cache::forget(self::PATH_SET_CACHE_KEY);
    }

    /** Как у {@see Redirect::normalizePath()} */
    public static function normalizePath(string $path): string
    {
        return Redirect::normalizePath($path);
    }
}
