<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'questions_preview',
        'type',
        'status',
        'order',
        'author_id',
        'image',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Автор статьи
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * SEO метаданные
     */
    public function seoMeta()
    {
        $pageType = $this->type === 'guide' ? 'guide' : 'article';
        return \App\Models\SeoMeta::where('page_type', $pageType)
            ->where('page_id', $this->id)
            ->first();
    }

    /**
     * Scope: только опубликованные
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: только черновики
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: инструкции (guide)
     */
    public function scopeGuide($query)
    {
        return $query->where('type', 'guide');
    }

    /**
     * Scope: статьи (article)
     */
    public function scopeArticle($query)
    {
        return $query->where('type', 'article');
    }

    /**
     * Автогенерация slug из title
     */
    public function setSlugAttribute($value)
    {
        if (empty($value) && !empty($this->attributes['title'])) {
            $this->attributes['slug'] = $this->generateUniqueSlug($this->attributes['title']);
        } else {
            $this->attributes['slug'] = $value;
        }
    }

    /**
     * Генерация уникального slug с транслитерацией
     */
    protected function generateUniqueSlug(string $title): string
    {
        $slug = $this->transliterate($title);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Транслитерация русского текста в латиницу для slug
     */
    protected function transliterate(string $text): string
    {
        $translitMap = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo',
            'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
            'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
            'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        ];

        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, $translitMap);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');

        return $text;
    }

    /**
     * Проверка, опубликована ли статья
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Получить URL статьи
     */
    public function getUrlAttribute(): string
    {
        $prefix = $this->type === 'guide' ? 'guide' : 'articles';
        return route("{$prefix}.show", $this->slug);
    }
}
