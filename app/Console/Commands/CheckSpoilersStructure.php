<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;

class CheckSpoilersStructure extends Command
{
    protected $signature = 'articles:check-spoilers {slug?}';
    protected $description = 'Проверяет структуру спойлеров в статьях';

    public function handle()
    {
        $slug = $this->argument('slug');
        
        if ($slug) {
            $articles = Article::where('slug', $slug)->where('type', 'guide')->get();
        } else {
            $articles = Article::where('type', 'guide')->get();
        }

        foreach ($articles as $article) {
            $this->info("=== {$article->title} (slug: {$article->slug}) ===");
            
            $content = $article->content;
            
            // Проверяем наличие details
            $hasDetails = strpos($content, '<details') !== false;
            $this->line("Has <details>: " . ($hasDetails ? 'YES' : 'NO'));
            
            // Проверяем наличие класса faq-spoiler
            $hasFaqSpoiler = strpos($content, 'faq-spoiler') !== false;
            $this->line("Has 'faq-spoiler' class: " . ($hasFaqSpoiler ? 'YES' : 'NO'));
            
            // Проверяем наличие summary
            $hasSummary = strpos($content, '<summary') !== false;
            $this->line("Has <summary>: " . ($hasSummary ? 'YES' : 'NO'));
            
            // Проверяем наличие класса faq-spoiler-header
            $hasFaqSpoilerHeader = strpos($content, 'faq-spoiler-header') !== false;
            $this->line("Has 'faq-spoiler-header' class: " . ($hasFaqSpoilerHeader ? 'YES' : 'NO'));
            
            // Показываем первые 500 символов
            $this->line("\nFirst 500 chars:");
            $this->line(substr($content, 0, 500));
            $this->line("\n");
        }
        
        return 0;
    }
}
