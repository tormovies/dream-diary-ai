<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'report_id',
        'parent_id',
        'content',
    ];

    /**
     * Пользователь, оставивший комментарий
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Отчет, к которому относится комментарий
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Родительский комментарий (для вложенных)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Дочерние комментарии (ответы)
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Проверка, является ли комментарий корневым (первого уровня)
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }
}
