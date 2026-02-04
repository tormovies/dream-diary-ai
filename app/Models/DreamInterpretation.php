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
        'report_id',
        'ip_address',
        'dream_description',
        'content_hash',
        'context',
        'traditions',
        'analysis_type',
        'processing_status',
        'processing_started_at',
        'analysis_data',
        'raw_api_request',
        'raw_api_response',
        'api_error',
    ];

    protected $casts = [
        'traditions' => 'array',
        'analysis_data' => 'array',
        'processing_started_at' => 'datetime',
    ];

    /** Количество дней, в пределах которых ищем дубликат по content_hash */
    public const DEDUP_DAYS = 30;

    /**
     * Вычисление хеша для дедупликации: нормализованное описание + канонические традиции.
     * Один и тот же текст с теми же традициями даёт один хеш.
     */
    public static function computeContentHash(string $dreamDescription, array $traditions): string
    {
        $normalized = trim(preg_replace('/\s++/u', ' ', $dreamDescription));
        $canonicalTraditions = $traditions;
        sort($canonicalTraditions);
        $traditionsString = implode(',', $canonicalTraditions);
        return hash('sha256', $normalized . "\n" . $traditionsString);
    }

    /**
     * Поиск существующего толкования с тем же content_hash (тот же пользователь или IP) за последние N дней.
     * Если есть завершённое — возвращаем его (любое, например последнее).
     * Если нет завершённого — возвращаем самое первое по созданию (чтобы пользователь ждал именно его).
     */
    public static function findDuplicateForDedup(string $contentHash, ?int $userId, ?string $ipAddress, int $withinDays = self::DEDUP_DAYS): ?self
    {
        $base = static::query()
            ->where('content_hash', $contentHash)
            ->where('created_at', '>=', now()->subDays($withinDays));

        if ($userId !== null) {
            $base->where('user_id', $userId);
        } else {
            $base->whereNull('user_id')->where('ip_address', $ipAddress);
        }

        // Сначала ищем любое завершённое (последнее по дате)
        $completed = (clone $base)->where('processing_status', 'completed')->orderByDesc('created_at')->first();
        if ($completed !== null) {
            return $completed;
        }

        // Нет завершённого — возвращаем самое первое (старое), чтобы ждали именно его
        return $base->orderBy('created_at')->first();
    }

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
     * Нормализованный результат анализа (если есть)
     */
    public function result()
    {
        return $this->hasOne(DreamInterpretationResult::class, 'dream_interpretation_id');
    }

    /**
     * Отчет (если анализ связан с отчетом)
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
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
