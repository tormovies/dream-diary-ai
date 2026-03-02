<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityGroupMapping extends Model
{
    protected $table = 'entity_group_mappings';

    protected $fillable = ['entity_group_id', 'entity_slug', 'entity_name'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(EntityGroup::class, 'entity_group_id');
    }

    /**
     * Для списка slug вернуть массив [ slug => ['id' => group_id, 'name' => group_name], ... ].
     */
    public static function slugsToGroups(array $slugs): array
    {
        if (empty($slugs)) {
            return [];
        }
        $slugs = array_unique(array_filter($slugs));
        return static::query()
            ->whereIn('entity_slug', $slugs)
            ->with('group:id,name,slug')
            ->get()
            ->mapWithKeys(function ($m) {
                return [$m->entity_slug => ['id' => $m->entity_group_id, 'name' => $m->group->name ?? '—']];
            })
            ->toArray();
    }
}
