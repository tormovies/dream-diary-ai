<?php

namespace App\Helpers;

use App\Models\Article;
use App\Models\EntityGroup;
use Illuminate\Support\Facades\Cache;

/**
 * Карта entity_slug → URL страницы символа (для ссылок из толкований).
 * Меняется только при удалении страницы символа или при изменении состава сущностей в группе.
 */
class SymbolPageLinkHelper
{
    private const CACHE_KEY = 'symbol_page_url_by_entity_slug';
    private const CACHE_TTL_SECONDS = 600; // 10 минут

    /**
     * Возвращает массив [ entity_slug => url ] для всех сущностей,
     * входящих в группы с опубликованной страницей символа.
     */
    public static function getSymbolPageUrlByEntitySlug(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            $groups = EntityGroup::with('mappings')
                ->whereHas('symbolPage', fn ($q) => $q->where('status', 'published'))
                ->get();

            $map = [];
            foreach ($groups as $group) {
                $url = route('symbol.show', $group->slug);
                foreach ($group->mappings as $mapping) {
                    $slug = trim((string) $mapping->entity_slug);
                    if ($slug !== '') {
                        $map[$slug] = $url;
                    }
                }
            }
            return $map;
        });
    }

    /**
     * Сбросить кеш (вызывать при удалении страницы символа или изменении маппингов группы).
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
