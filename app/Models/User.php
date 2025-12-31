<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nickname',
        'avatar',
        'bio',
        'diary_privacy',
        'comment_privacy',
        'public_link',
        'diary_name',
        'theme',
        'is_banned',
        'banned_at',
        'ban_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
        ];
    }

    /**
     * Проверка, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Проверка, заблокирован ли пользователь
     */
    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    /**
     * Заблокировать пользователя
     * Автоматически делает дневник приватным (скрывает весь контент)
     */
    public function ban(?string $reason = null): void
    {
        // Сохраняем текущую приватность для возможности восстановления
        $oldPrivacy = $this->diary_privacy;
        
        $this->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => $reason,
            'diary_privacy' => 'private', // Скрываем весь контент
        ]);
        
        // Сохраняем старое значение в ban_reason если оно было 'public' или 'friends'
        // Формат: "причина|старая_приватность"
        if (in_array($oldPrivacy, ['public', 'friends'])) {
            $reasonWithPrivacy = $reason ? "{$reason}|{$oldPrivacy}" : "|{$oldPrivacy}";
            $this->update(['ban_reason' => $reasonWithPrivacy]);
        }
    }

    /**
     * Разблокировать пользователя
     * Восстанавливает предыдущую настройку приватности дневника
     */
    public function unban(): void
    {
        // Пытаемся восстановить старую приватность из ban_reason
        $oldPrivacy = 'public'; // По умолчанию
        
        if ($this->ban_reason && str_contains($this->ban_reason, '|')) {
            $parts = explode('|', $this->ban_reason);
            $extractedPrivacy = end($parts);
            if (in_array($extractedPrivacy, ['public', 'friends', 'private'])) {
                $oldPrivacy = $extractedPrivacy;
            }
        }
        
        $this->update([
            'is_banned' => false,
            'banned_at' => null,
            'ban_reason' => null,
            'diary_privacy' => $oldPrivacy,
        ]);
    }

    /**
     * Отчеты пользователя (дневник)
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Друзья пользователя
     */
    public function friendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    /**
     * Комментарии пользователя
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Уведомления пользователя
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Анализы снов пользователя
     */
    public function dreamInterpretations(): HasMany
    {
        return $this->hasMany(DreamInterpretation::class);
    }

    /**
     * Получить название дневника пользователя
     * Если не указано, возвращает дефолтное название
     */
    public function getDiaryName(): string
    {
        return $this->diary_name ?? "Дневник пользователя {$this->nickname}";
    }

    /**
     * Нормализация nickname для использования в URL
     * Транслитерация русских букв в латиницу, затем оставляем только нижний регистр английских букв и цифры
     */
    public static function normalizeNickname(string $nickname): string
    {
        // Переводим в нижний регистр
        $normalized = mb_strtolower($nickname, 'UTF-8');
        
        // Транслитерация русских букв в латиницу
        $translitMap = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ];
        
        // Заменяем русские буквы на латиницу (по символу для корректной работы с UTF-8)
        $result = '';
        $length = mb_strlen($normalized, 'UTF-8');
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($normalized, $i, 1, 'UTF-8');
            $result .= $translitMap[$char] ?? $char;
        }
        $normalized = $result;
        
        // Оставляем только английские буквы и цифры
        $normalized = preg_replace('/[^a-z0-9]/u', '', $normalized);
        
        return $normalized;
    }

    /**
     * Boot метод для автоматического обновления public_link
     */
    protected static function boot(): void
    {
        parent::boot();

        // При создании или обновлении nickname автоматически обновляем public_link
        static::saving(function ($user) {
            if ($user->isDirty('nickname') || empty($user->public_link)) {
                $user->public_link = static::normalizeNickname($user->nickname);
            }
        });
    }
}
