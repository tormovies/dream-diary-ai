<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;

class DebugSpoilersStructure extends Command
{
    protected $signature = 'articles:debug-spoilers {slug}';
    protected $description = 'Детальная диагностика структуры спойлеров';

    public function handle()
    {
        $slug = $this->argument('slug');
        $article = Article::where('slug', $slug)->where('type', 'guide')->first();
        
        if (!$article) {
            $this->error("Статья не найдена: {$slug}");
            return 1;
        }
        
        $this->info("=== {$article->title} ===");
        $content = $article->content;
        
        // Находим все details
        preg_match_all('/<details[^>]*class=["\']faq-spoiler["\'][^>]*>(.*?)<\/details>/is', $content, $matches);
        
        $this->info("Найдено details элементов: " . count($matches[0]));
        
        foreach ($matches[0] as $index => $detailsHtml) {
            $this->line("\n--- Details #" . ($index + 1) . " ---");
            
            // Проверяем наличие summary
            if (preg_match('/<summary[^>]*class=["\']faq-spoiler-header["\'][^>]*>(.*?)<\/summary>/is', $detailsHtml, $summaryMatch)) {
                $this->line("Summary найден: " . trim(strip_tags($summaryMatch[1])));
            } else {
                $this->error("Summary НЕ найден!");
            }
            
            // Проверяем, есть ли контент после summary
            $afterSummary = preg_replace('/<summary[^>]*>.*?<\/summary>/is', '', $detailsHtml);
            $afterSummary = trim(strip_tags($afterSummary));
            
            if (empty($afterSummary)) {
                $this->warn("Контент внутри details НЕ найден!");
            } else {
                $this->info("Контент внутри details найден (первые 100 символов): " . substr($afterSummary, 0, 100));
            }
            
            // Показываем полный HTML details
            $this->line("\nПолный HTML (первые 500 символов):");
            $this->line(substr($detailsHtml, 0, 500));
        }
        
        // Проверяем, есть ли контент после details
        $afterDetails = preg_replace('/<details[^>]*>.*?<\/details>/is', '', $content);
        $afterDetails = preg_replace('/<div[^>]*>\s*<\/div>/is', '', $afterDetails);
        $afterDetails = trim(strip_tags($afterDetails));
        
        if (!empty($afterDetails)) {
            $this->warn("\nВНИМАНИЕ: Найден контент ПОСЛЕ details (первые 200 символов):");
            $this->line(substr($afterDetails, 0, 200));
        }
        
        return 0;
    }
}
