<?php

/**
 * Скрипт для импорта статей-инструкций на продакшн
 * Использование: php import_articles.php articles_export.json
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Article;
use App\Models\SeoMeta;
use App\Models\User;

if ($argc < 2) {
    echo "Использование: php import_articles.php articles_export.json\n";
    exit(1);
}

$jsonFile = $argv[1];

if (!file_exists($jsonFile)) {
    echo "Файл не найден: $jsonFile\n";
    exit(1);
}

$json = file_get_contents($jsonFile);
$articles = json_decode($json, true);

if (!$articles) {
    echo "Ошибка: не удалось распарсить JSON\n";
    exit(1);
}

// Находим первого админа
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "Ошибка: администратор не найден!\n";
    exit(1);
}

echo "Используется администратор: {$admin->nickname} (ID: {$admin->id})\n";
echo "Импорт " . count($articles) . " статей...\n\n";

$created = 0;
$updated = 0;
$skipped = 0;

foreach ($articles as $articleData) {
    // Ищем существующую статью по slug
    $article = Article::where('slug', $articleData['slug'])->first();

    if ($article) {
        // Обновляем существующую статью
        $article->update([
            'title' => $articleData['title'],
            'content' => $articleData['content'],
            'questions_preview' => $articleData['questions_preview'] ?? null,
            'status' => $articleData['status'] ?? 'draft',
            'order' => $articleData['order'] ?? 0,
            'image' => $articleData['image'] ?? null,
            'published_at' => $articleData['published_at'] ? \Carbon\Carbon::parse($articleData['published_at']) : null,
        ]);

        echo "✓ Обновлено: {$articleData['title']} (ID: {$article->id})\n";
        $updated++;
    } else {
        // Создаем новую статью
        $article = Article::create([
            'title' => $articleData['title'],
            'slug' => $articleData['slug'],
            'content' => $articleData['content'],
            'questions_preview' => $articleData['questions_preview'] ?? null,
            'type' => $articleData['type'] ?? 'guide',
            'status' => $articleData['status'] ?? 'draft',
            'order' => $articleData['order'] ?? 0,
            'author_id' => $admin->id,
            'image' => $articleData['image'] ?? null,
            'published_at' => $articleData['published_at'] ? \Carbon\Carbon::parse($articleData['published_at']) : null,
        ]);

        echo "✓ Создано: {$articleData['title']} (ID: {$article->id})\n";
        $created++;
    }

    // Обновляем/создаем SEO метаданные
    if (!empty($articleData['seo'])) {
        $seoMeta = SeoMeta::where('page_type', 'guide')
            ->where('page_id', $article->id)
            ->first();

        $seoData = [
            'page_type' => 'guide',
            'page_id' => $article->id,
            'title' => $articleData['seo']['title'] ?? null,
            'description' => $articleData['seo']['description'] ?? null,
            'h1' => $articleData['seo']['h1'] ?? null,
            'h1_description' => $articleData['seo']['h1_description'] ?? null,
            'og_title' => $articleData['seo']['og_title'] ?? null,
            'og_description' => $articleData['seo']['og_description'] ?? null,
            'og_image' => $articleData['seo']['og_image'] ?? null,
            'is_active' => true,
            'priority' => 0,
        ];

        // Удаляем пустые значения
        $seoData = array_filter($seoData, function ($value) {
            return $value !== null && $value !== '';
        });

        if (!empty($seoData)) {
            if ($seoMeta) {
                $seoMeta->update($seoData);
            } else {
                SeoMeta::create($seoData);
            }
        }
    }
}

echo "\nГотово!\n";
echo "Создано: $created\n";
echo "Обновлено: $updated\n";
echo "Всего обработано: " . ($created + $updated) . "\n";
