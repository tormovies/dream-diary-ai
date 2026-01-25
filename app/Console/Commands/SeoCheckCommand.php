<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Article;
use App\Models\Report;
use App\Models\User;
use App\Models\DreamInterpretation;
use DOMDocument;
use DOMXPath;

class SeoCheckCommand extends Command
{
    protected $signature = 'seo:check 
                            {--url= : Base URL (default: http://127.0.0.1:3000)}
                            {--output= : Output CSV file path (default: seo_check.csv)}';
    
    protected $description = 'Проверяет SEO и разметку на статичных страницах сайта';

    private $baseUrl;
    private $results = [];

    public function handle()
    {
        $this->baseUrl = $this->option('url') ?: 'http://127.0.0.1:3000';
        $outputFile = $this->option('output') ?: 'seo_check.csv';

        $this->info("Начинаю проверку SEO на {$this->baseUrl}...");
        $this->newLine();

        // Определяем типы страниц и получаем примеры URL
        $pageTypes = $this->getPageTypes();

        $bar = $this->output->createProgressBar(count($pageTypes));
        $bar->start();

        foreach ($pageTypes as $type => $url) {
            if ($url) {
                $this->checkPage($type, $url);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Сохраняем результаты в CSV
        $this->saveToCsv($outputFile);
        
        $this->info("Проверка завершена! Результаты сохранены в: {$outputFile}");
        $this->info("Проверено страниц: " . count($this->results));
    }

    private function getPageTypes(): array
    {
        $types = [];

        // Главная страница
        $types['Главная'] = '/';

        // Статьи
        $article = Article::where('type', 'article')
            ->published()
            ->first();
        if ($article) {
            $types['Статья'] = $this->normalizeUrl(route('articles.show', $article->slug));
            $types['Список статей'] = $this->normalizeUrl(route('articles.index'));
        }

        // Инструкции
        $guide = Article::where('type', 'guide')
            ->published()
            ->first();
        if ($guide) {
            $types['Инструкция'] = $this->normalizeUrl(route('guide.show', $guide->slug));
            $types['Список инструкций'] = $this->normalizeUrl(route('guide.index'));
        }

        // Профиль пользователя
        $user = User::where(function($query) {
            $query->whereNotNull('public_link')
                  ->orWhereNotNull('nickname');
        })->first();
        if ($user) {
            $types['Профиль пользователя'] = $this->normalizeUrl(route('users.profile', $user));
        }

        // Публичный дневник
        $diaryUser = User::whereNotNull('public_link')->first();
        if ($diaryUser) {
            $types['Публичный дневник'] = $this->normalizeUrl(route('diary.public', $diaryUser->public_link));
        }

        // Отчет
        $report = Report::where('status', 'published')->first();
        if ($report) {
            $types['Отчет'] = $this->normalizeUrl(route('reports.show', $report));
            
            // Анализ отчета
            $interpretation = DreamInterpretation::where('report_id', $report->id)->first();
            if ($interpretation) {
                $types['Анализ отчета'] = $this->normalizeUrl(route('reports.analysis', $report));
            }
        }

        // Толкование снов
        $interpretation = DreamInterpretation::whereNotNull('hash')->first();
        if ($interpretation) {
            $types['Толкование сна'] = $this->normalizeUrl(route('dream-analyzer.show', $interpretation->hash));
        }
        $types['Создание толкования'] = $this->normalizeUrl(route('dream-analyzer.create'));

        // Лента активности
        $types['Лента активности'] = $this->normalizeUrl(route('activity.index'));

        // Поиск
        $types['Поиск'] = $this->normalizeUrl(route('reports.search'));

        return $types;
    }

    private function checkPage(string $type, string $url): void
    {
        try {
            // Если URL уже полный, используем его, иначе добавляем baseUrl
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                $fullUrl = $url;
            } else {
                $fullUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
            }
            
            // Делаем HTTP запрос
            $response = Http::timeout(10)->get($fullUrl);
            
            if (!$response->successful()) {
                $this->results[] = [
                    'type' => $type,
                    'url' => $url,
                    'status' => $response->status(),
                    'title' => 'ERROR',
                    'description' => 'HTTP Error',
                    'h1' => '',
                    'canonical' => '',
                    'og_title' => '',
                    'og_description' => '',
                    'json_ld_types' => '',
                    'json_ld_count' => 0,
                    'errors' => 'HTTP ' . $response->status()
                ];
                return;
            }

            $html = $response->body();
            
            // Парсим HTML
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new DOMXPath($dom);

            // Извлекаем SEO данные
            $title = $this->extractTitle($xpath);
            $description = $this->extractMeta($xpath, 'description');
            $h1 = $this->extractH1($xpath);
            $canonical = $this->extractCanonical($xpath);
            $ogTitle = $this->extractMeta($xpath, 'og:title');
            $ogDescription = $this->extractMeta($xpath, 'og:description');
            
            // Извлекаем JSON-LD
            $jsonLdData = $this->extractJsonLd($xpath);
            $jsonLdTypes = implode(', ', array_keys($jsonLdData));
            $jsonLdCount = array_sum(array_map('count', $jsonLdData));

            // Нормализуем URL для сохранения (только путь)
            $normalizedUrl = parse_url($fullUrl, PHP_URL_PATH) ?: $url;
            
            $this->results[] = [
                'type' => $type,
                'url' => $normalizedUrl,
                'status' => $response->status(),
                'title' => $this->cleanText($title),
                'description' => $this->cleanText($description),
                'h1' => $this->cleanText($h1),
                'canonical' => $canonical,
                'og_title' => $this->cleanText($ogTitle),
                'og_description' => $this->cleanText($ogDescription),
                'json_ld_types' => $jsonLdTypes,
                'json_ld_count' => $jsonLdCount,
                'errors' => ''
            ];

        } catch (\Exception $e) {
            $this->results[] = [
                'type' => $type,
                'url' => $url,
                'status' => 'ERROR',
                'title' => 'ERROR',
                'description' => 'Exception',
                'h1' => '',
                'canonical' => '',
                'og_title' => '',
                'og_description' => '',
                'json_ld_types' => '',
                'json_ld_count' => 0,
                'errors' => $e->getMessage()
            ];
        }
    }

    private function extractTitle(DOMXPath $xpath): string
    {
        $nodes = $xpath->query('//title');
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }

    private function extractMeta(DOMXPath $xpath, string $name): string
    {
        $query = "//meta[@name='{$name}' or @property='{$name}']/@content";
        $nodes = $xpath->query($query);
        return $nodes->length > 0 ? trim($nodes->item(0)->value) : '';
    }

    private function extractH1(DOMXPath $xpath): string
    {
        $nodes = $xpath->query('//h1');
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }

    private function extractCanonical(DOMXPath $xpath): string
    {
        $nodes = $xpath->query("//link[@rel='canonical']/@href");
        return $nodes->length > 0 ? trim($nodes->item(0)->value) : '';
    }

    private function extractJsonLd(DOMXPath $xpath): array
    {
        $result = [];
        $scripts = $xpath->query("//script[@type='application/ld+json']");
        
        foreach ($scripts as $script) {
            $json = trim($script->textContent);
            if (empty($json)) {
                continue;
            }
            
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }
            
            // Обрабатываем массив или объект
            if (isset($data['@type'])) {
                $type = $data['@type'];
                if (!isset($result[$type])) {
                    $result[$type] = [];
                }
                $result[$type][] = $data;
            } elseif (is_array($data)) {
                foreach ($data as $item) {
                    if (isset($item['@type'])) {
                        $type = $item['@type'];
                        if (!isset($result[$type])) {
                            $result[$type] = [];
                        }
                        $result[$type][] = $item;
                    }
                }
            }
        }
        
        return $result;
    }

    private function normalizeUrl(string $url): string
    {
        // Если URL полный, извлекаем только путь
        $parsed = parse_url($url);
        if (isset($parsed['path'])) {
            $path = $parsed['path'];
            if (isset($parsed['query'])) {
                $path .= '?' . $parsed['query'];
            }
            return $path;
        }
        return $url;
    }

    private function cleanText(string $text): string
    {
        // Удаляем лишние пробелы и переносы строк
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function saveToCsv(string $filename): void
    {
        $file = fopen($filename, 'w');
        
        // Записываем BOM для корректного отображения кириллицы в Excel
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Заголовки
        $headers = [
            'Тип страницы',
            'URL',
            'HTTP статус',
            'Title',
            'Description',
            'H1',
            'Canonical',
            'OG Title',
            'OG Description',
            'JSON-LD типы',
            'JSON-LD количество',
            'Ошибки'
        ];
        
        fputcsv($file, $headers, ';');
        
        // Данные
        foreach ($this->results as $row) {
            fputcsv($file, [
                $row['type'],
                $row['url'],
                $row['status'],
                $row['title'],
                $row['description'],
                $row['h1'],
                $row['canonical'],
                $row['og_title'],
                $row['og_description'],
                $row['json_ld_types'],
                $row['json_ld_count'],
                $row['errors']
            ], ';');
        }
        
        fclose($file);
    }
}
