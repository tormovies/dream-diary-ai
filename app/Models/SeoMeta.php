<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $fillable = [
        'page_type',
        'page_id',
        'route_name',
        'title',
        'description',
        'h1',
        'h1_description',
        'keywords',
        'og_title',
        'og_description',
        'og_image',
        'is_active',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    /**
     * Получить SEO-запись для конкретной страницы
     */
    public static function getForPage(string $pageType, $pageId = null, ?string $routeName = null)
    {
        $query = static::where('page_type', $pageType)
            ->where('is_active', true);

        // Сначала ищем для конкретной страницы
        if ($pageId !== null) {
            $specific = $query->where('page_id', $pageId)->orderBy('priority', 'desc')->first();
            if ($specific) {
                return $specific;
            }
        }

        // Затем по route_name
        if ($routeName !== null) {
            $byRoute = static::where('page_type', $pageType)
                ->where('route_name', $routeName)
                ->where('is_active', true)
                ->whereNull('page_id')
                ->orderBy('priority', 'desc')
                ->first();
            if ($byRoute) {
                return $byRoute;
            }
        }

        // Если не найдено - ищем общий шаблон для типа страницы
        return static::where('page_type', $pageType)
            ->whereNull('page_id')
            ->whereNull('route_name')
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->first();
    }
}
