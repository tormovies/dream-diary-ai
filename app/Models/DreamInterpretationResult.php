<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DreamInterpretationResult extends Model
{
    protected $fillable = [
        'dream_interpretation_id',
        'type',
        'format_version',
        'traditions',
        'analysis_type',
        'recommendations',
        // Поля для одиночного сна
        'dream_title',
        'dream_detailed',
        'dream_type',
        'key_symbols',
        'unified_locations',
        'key_tags',
        'summary_insight',
        'emotional_tone',
        // Поля для серии снов
        'series_title',
        'overall_theme',
        'emotional_arc',
        'key_connections',
    ];

    protected $casts = [
        'traditions' => 'array',
        'recommendations' => 'array',
        'key_symbols' => 'array',
        'unified_locations' => 'array',
        'key_tags' => 'array',
        'key_connections' => 'array',
    ];

    /**
     * Связь с основной таблицей интерпретаций
     */
    public function interpretation(): BelongsTo
    {
        return $this->belongsTo(DreamInterpretation::class, 'dream_interpretation_id');
    }

    /**
     * Сны в серии (только для type='series')
     */
    public function seriesDreams(): HasMany
    {
        return $this->hasMany(DreamInterpretationSeriesDream::class, 'dream_interpretation_result_id')
                    ->orderBy('order')
                    ->orderBy('dream_number');
    }
}
