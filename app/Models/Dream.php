<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dream extends Model
{
    protected $fillable = [
        'report_id',
        'title',
        'description',
        'dream_type',
        'order',
    ];

    /**
     * Отчет, к которому относится сон
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
