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
     * Учитывает «обрезанные» выборки (например в retry только id, hash, user_id, processing_status):
     * interpretation_created_at и traditions при необходимости подгружаются из БД.
     */
    public static function syncFromInterpretation(DreamInterpretation $interpretation): void
    {
        $status = $interpretation->processing_status ?? 'pending';

        $createdAt = $interpretation->created_at;
        if ($createdAt === null) {
            $createdAt = DreamInterpretation::where('id', $interpretation->id)->value('created_at') ?? now();
        }

        $traditions = $interpretation->traditions;
        if ($traditions === null) {
            $traditions = DreamInterpretation::where('id', $interpretation->id)->value('traditions');
        }

        static::updateOrCreate(
            ['dream_interpretation_id' => $interpretation->id],
            [
                'interpretation_created_at' => $createdAt,
                'processing_status' => $status,
                'traditions' => $traditions,
            ]
        );
    }
}
