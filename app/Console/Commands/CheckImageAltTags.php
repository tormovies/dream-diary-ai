<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use DOMDocument;
use DOMXPath;

class CheckImageAltTags extends Command
{
    protected $signature = 'seo:check-alt-tags 
                            {--output= : Output file path (default: alt_tags_check.txt)}';
    
    protected $description = 'Проверяет наличие alt тегов у всех изображений';

    private $issues = [];
    private $totalImages = 0;
    private $imagesWithAlt = 0;
    private $imagesWithoutAlt = 0;

    public function handle()
    {
        $this->info('🔍 Проверка alt тегов для изображений...');
        $this->newLine();

        // 1. Проверка Blade шаблонов
        $this->info('1️⃣ Проверка Blade шаблонов...');
        $this->checkBladeTemplates();

        // 2. Проверка контента статей в БД
        $this->info('2️⃣ Проверка контента статей в БД...');
        $this->checkArticlesContent();

        // 3. Вывод результатов
        $this->displayResults();

        // 4. Сохранение отчета
        $outputFile = $this->option('output') ?: 'alt_tags_check.txt';
        $this->saveReport($outputFile);

        return 0;
    }

    private function checkBladeTemplates()
    {
        $viewsPath = resource_path('views');
        $files = $this->getBladeFiles($viewsPath);
        
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);

            // Проверка прямых <img> тегов
            if (preg_match_all('/<img\s+[^>]*>/i', $content, $matches)) {
                foreach ($matches[0] as $imgTag) {
                    $this->totalImages++;
                    
                    // Проверка наличия alt
                    if (preg_match('/alt\s*=\s*["\']([^"\']*)["\']/i', $imgTag, $altMatch)) {
                        $altValue = trim($altMatch[1]);
                        if ($altValue === '' || $altValue === null) {
                            $this->imagesWithoutAlt++;
                            $this->issues[] = [
                                'type' => 'blade',
                                'file' => $relativePath,
                                'issue' => 'Пустой alt тег: ' . substr($imgTag, 0, 100),
                            ];
                        } else {
                            $this->imagesWithAlt++;
                        }
                    } else {
                        $this->imagesWithoutAlt++;
                        $this->issues[] = [
                            'type' => 'blade',
                            'file' => $relativePath,
                            'issue' => 'Отсутствует alt тег: ' . substr($imgTag, 0, 100),
                        ];
                    }
                }
            }

            // Проверка использования lazy-image без alt
            if (preg_match_all('/<x-lazy-image\s+[^>]*>/i', $content, $matches)) {
                foreach ($matches[0] as $componentTag) {
                    $this->totalImages++;
                    
                    // Проверка наличия alt
                    if (preg_match('/:alt\s*=\s*["\']([^"\']*)["\']|alt\s*=\s*["\']([^"\']*)["\']/i', $componentTag, $altMatch)) {
                        $altValue = trim($altMatch[1] ?? $altMatch[2] ?? '');
                        if ($altValue === '' || $altValue === null) {
                            $this->imagesWithoutAlt++;
                            $this->issues[] = [
                                'type' => 'blade',
                                'file' => $relativePath,
                                'issue' => 'lazy-image без alt: ' . substr($componentTag, 0, 100),
                            ];
                        } else {
                            $this->imagesWithAlt++;
                        }
                    } else {
                        $this->imagesWithoutAlt++;
                        $this->issues[] = [
                            'type' => 'blade',
                            'file' => $relativePath,
                            'issue' => 'lazy-image без alt параметра: ' . substr($componentTag, 0, 100),
                        ];
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function checkArticlesContent()
    {
        $articles = Article::whereNotNull('content')->get();
        
        $bar = $this->output->createProgressBar($articles->count());
        $bar->start();

        foreach ($articles as $article) {
            $content = $article->content;
            
            if (empty($content)) {
                $bar->advance();
                continue;
            }

            // Парсим HTML контент
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $content);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            $images = $xpath->query('//img');

            foreach ($images as $img) {
                $this->totalImages++;
                $alt = $img->getAttribute('alt');
                
                if ($alt === '' || $alt === null) {
                    $this->imagesWithoutAlt++;
                    $this->issues[] = [
                        'type' => 'article',
                        'file' => "Article ID: {$article->id} ({$article->title})",
                        'issue' => 'Отсутствует alt тег в контенте статьи',
                    ];
                } else {
                    $this->imagesWithAlt++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function displayResults()
    {
        $this->newLine();
        $this->info('📊 Результаты проверки:');
        $this->newLine();
        
        $this->line("Всего изображений: {$this->totalImages}");
        $this->line("С alt тегами: {$this->imagesWithAlt} ✅");
        $this->line("Без alt тегов: {$this->imagesWithoutAlt} " . ($this->imagesWithoutAlt > 0 ? '❌' : '✅'));
        
        if ($this->totalImages > 0) {
            $percentage = round(($this->imagesWithAlt / $this->totalImages) * 100, 1);
            $this->line("Процент с alt: {$percentage}%");
        }

        if (count($this->issues) > 0) {
            $this->newLine();
            $this->warn('⚠️ Найдено проблем: ' . count($this->issues));
            $this->newLine();
            
            $this->table(
                ['Тип', 'Файл/Статья', 'Проблема'],
                array_map(function($issue) {
                    return [
                        $issue['type'],
                        substr($issue['file'], 0, 60),
                        substr($issue['issue'], 0, 80),
                    ];
                }, $this->issues)
            );
        } else {
            $this->newLine();
            $this->info('✅ Все изображения имеют alt теги!');
        }
    }

    private function saveReport($outputFile)
    {
        $report = "Проверка alt тегов для изображений\n";
        $report .= "Дата: " . date('Y-m-d H:i:s') . "\n";
        $report .= str_repeat('=', 80) . "\n\n";
        
        $report .= "Всего изображений: {$this->totalImages}\n";
        $report .= "С alt тегами: {$this->imagesWithAlt}\n";
        $report .= "Без alt тегов: {$this->imagesWithoutAlt}\n";
        
        if ($this->totalImages > 0) {
            $percentage = round(($this->imagesWithAlt / $this->totalImages) * 100, 1);
            $report .= "Процент с alt: {$percentage}%\n";
        }
        
        $report .= "\n" . str_repeat('=', 80) . "\n\n";
        
        if (count($this->issues) > 0) {
            $report .= "Найденные проблемы:\n\n";
            foreach ($this->issues as $issue) {
                $report .= "Тип: {$issue['type']}\n";
                $report .= "Файл/Статья: {$issue['file']}\n";
                $report .= "Проблема: {$issue['issue']}\n";
                $report .= str_repeat('-', 80) . "\n";
            }
        } else {
            $report .= "✅ Все изображения имеют alt теги!\n";
        }

        file_put_contents($outputFile, $report);
        $this->info("📄 Отчет сохранен: {$outputFile}");
    }

    private function getBladeFiles($directory)
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php' && 
                strpos($file->getFilename(), '.blade.php') !== false) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
