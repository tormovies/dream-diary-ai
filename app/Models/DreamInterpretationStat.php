<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DreamInterpretationStat extends Model
{
    protected $table = 'dream_interpretation_stats';

    protected $fillable = [
        'dream_interpretation_id',
        'interpretation_created_at',
        'processing_status',
        'traditions',
    ];

    protected $casts = [
        'traditions' => 'array',
        'interpretation_created_at' => 'datetime',
    ];

    public function interpretation(): BelongsTo
    {
        return $this->belongsTo(DreamInterpretation::class, 'dream_interpretation_id');
    }

    /**
     * Синхронизировать одну запись из DreamInterpretation в stats.
     */
    public static function syncFromInterpretation(DreamInterpretation $interpretation): void
    {
        $status = $interpretation->processing_status ?? 'pending';
        $traditions = $interpretation->traditions ?? null;

        static::updateOrCreate(
            ['dream_interpretation_id' => $interpretation->id],
            [
                'interpretation_created_at' => $interpretation->created_at,
                'processing_status' => $status,
                'traditions' => $traditions,
            ]
        );
    }
}
