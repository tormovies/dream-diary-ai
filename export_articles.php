<?php

/**
 * Скрипт для экспорта статей-инструкций из локальной БД
 * Использование: php export_articles.php > articles_export.json
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Article;
use App\Models\SeoMeta;

// Получаем все статьи-инструкции
$articles = Article::where('type', 'guide')
    ->with('author')
    ->orderBy('order')
    ->get();

$export = [];

foreach ($articles as $article) {
    // Получаем SEO метаданные
    $seoMeta = SeoMeta::where('page_type', 'guide')
        ->where('page_id', $article->id)
        ->first();

    $export[] = [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => $article->content,
        'questions_preview' => $article->questions_preview,
        'type' => $article->type,
        'status' => $article->status,
        'order' => $article->order,
        'image' => $article->image,
        'published_at' => $article->published_at ? $article->published_at->toDateTimeString() : null,
        'author_email' => $article->author->email ?? null,
        'seo' => $seoMeta ? [
            'title' => $seoMeta->title,
            'description' => $seoMeta->description,
            'h1' => $seoMeta->h1,
            'h1_description' => $seoMeta->h1_description,
            'og_title' => $seoMeta->og_title,
            'og_description' => $seoMeta->og_description,
            'og_image' => $seoMeta->og_image,
        ] : null,
    ];
}

echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
