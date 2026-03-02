<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EntityGroup extends Model
{
    protected $table = 'entity_groups';

    protected $fillable = ['slug', 'name'];

    public function mappings(): HasMany
    {
        return $this->hasMany(EntityGroupMapping::class, 'entity_group_id');
    }

    /**
     * Slug группы из названия (для URL).
     */
    public static function nameToSlug(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return 'group-' . substr(uniqid(), -6);
        }
        return Str::slug(mb_strtolower($name, 'UTF-8'), '-', 'ru') ?: 'group-' . substr(md5($name), 0, 8);
    }
}
