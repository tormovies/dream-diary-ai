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
            'banned_at' => 'datetime',
            'is_banned' => 'boolean',
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
        return $this->is_banned === true || $this->is_banned === 1;
    }

    /**
     * Scope для получения незаблокированных пользователей
     */
    public function scopeNotBanned($query)
    {
        return $query->where(function ($q) {
            $q->where('is_banned', false)
              ->orWhereNull('is_banned');
        });
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
