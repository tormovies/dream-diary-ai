<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DreamInterpretation extends Model
{
    protected $fillable = [
        'hash',
        'user_id',
        'ip_address',
        'dream_description',
        'context',
        'traditions',
        'analysis_type',
        'analysis_data',
        'raw_api_request',
        'raw_api_response',
        'api_error',
    ];

    protected $casts = [
        'traditions' => 'array',
        'analysis_data' => 'array',
    ];

    /**
     * Генерация уникального хеша
     */
    public static function generateHash(): string
    {
        do {
            $hash = Str::random(32);
        } while (self::where('hash', $hash)->exists());

        return $hash;
    }

    /**
     * Пользователь (если зарегистрирован)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить выбранные традиции как строку
     */
    public function getTraditionsStringAttribute(): string
    {
        if (empty($this->traditions)) {
            return 'ECLECTIC';
        }

        $map = [
            'freudian' => 'FREUDIAN',
            'jungian' => 'JUNGIAN',
            'cognitive' => 'COGNITIVE',
            'symbolic' => 'SYMBOLIC',
            'shamanic' => 'SHAMANIC',
            'gestalt' => 'GESTALT',
            'eclectic' => 'ECLECTIC',
        ];

        $traditions = array_map(function ($t) use ($map) {
            return $map[strtolower($t)] ?? strtoupper($t);
        }, $this->traditions);

        return implode('/', $traditions);
    }
}
