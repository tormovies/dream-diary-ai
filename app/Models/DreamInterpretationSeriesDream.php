<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DreamInterpretationSeriesDream extends Model
{
    protected $fillable = [
        'dream_interpretation_result_id',
        'dream_number',
        'dream_title',
        'dream_detailed',
        'dream_type',
        'key_symbols',
        'unified_locations',
        'key_tags',
        'summary_insight',
        'emotional_tone',
        'order',
    ];

    protected $casts = [
        'key_symbols' => 'array',
        'unified_locations' => 'array',
        'key_tags' => 'array',
    ];

    /**
     * Связь с результатом анализа серии
     */
    public function result(): BelongsTo
    {
        return $this->belongsTo(DreamInterpretationResult::class, 'dream_interpretation_result_id');
    }
}
