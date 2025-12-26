<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'report_date',
        'access_level',
        'status',
        'analysis_id',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'analyzed_at' => 'datetime',
        ];
    }

    /**
     * Пользователь-владелец отчета
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Сны в отчете
     */
    public function dreams(): HasMany
    {
        return $this->hasMany(Dream::class)->orderBy('order');
    }

    /**
     * Теги отчета
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'report_tag');
    }

    /**
     * Комментарии к отчету
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Анализ отчета
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(DreamInterpretation::class, 'analysis_id');
    }

    /**
     * Проверка наличия анализа
     */
    public function hasAnalysis(): bool
    {
        try {
            return !is_null($this->analysis_id);
        } catch (\Exception $e) {
            // Если поле analysis_id не существует в БД, возвращаем false
            \Log::warning('hasAnalysis() error - possibly missing migration', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
