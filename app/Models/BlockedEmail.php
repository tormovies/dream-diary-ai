<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedEmail extends Model
{
    protected $table = 'blocked_emails';

    protected $fillable = [
        'email',
        'is_permanent',
    ];

    protected function casts(): array
    {
        return [
            'is_permanent' => 'boolean',
        ];
    }

    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public static function isBlocked(string $email): bool
    {
        $norm = self::normalizeEmail($email);

        return $norm !== '' && self::query()->where('email', $norm)->exists();
    }

    /** Запись при блокировке (снимается при разблокировке). */
    public static function addForBan(string $email): void
    {
        $norm = self::normalizeEmail($email);
        if ($norm === '') {
            return;
        }

        $existing = self::query()->where('email', $norm)->first();
        if ($existing?->is_permanent) {
            return;
        }

        self::query()->updateOrCreate(
            ['email' => $norm],
            ['is_permanent' => false]
        );
    }

    /** Снятие записи при разблокировке (постоянные записи не трогаем). */
    public static function removeIfTemporary(string $email): void
    {
        $norm = self::normalizeEmail($email);
        self::query()->where('email', $norm)->where('is_permanent', false)->delete();
    }

    /** После полного удаления аккаунта — email остаётся в списке без возможности регистрации. */
    public static function markPermanent(string $email): void
    {
        $norm = self::normalizeEmail($email);
        if ($norm === '') {
            return;
        }

        self::query()->updateOrCreate(
            ['email' => $norm],
            ['is_permanent' => true]
        );
    }
}
