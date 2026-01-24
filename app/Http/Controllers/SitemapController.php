<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Article;
use App\Models\DreamInterpretation;
use App\Models\Report;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    private const MAX_URLS_PER_SITEMAP = 50000; // Максимум URL в одном sitemap (рекомендация Google)
    public const PAGINATION_LIMIT = 1000; // Практический лимит для производительности (значение по умолчанию)
    
    /**
     * Получить лимит URL на одной странице sitemap из настроек
     */
    private function getPaginationLimit(): int
    {
        $limit = \App\Models\Setting::getValue('sitemap.urls_per_page', self::PAGINATION_LIMIT);
        // Ограничиваем значение: минимум 100, максимум MAX_URLS_PER_SITEMAP
        return max(100, min((int)$limit, self::MAX_URLS_PER_SITEMAP));
    }
    
    /**
     * Главный sitemap index
     */
    public function index(): Response
    {
        $baseUrl = rtrim(config('seo.base_url', config('app.url')), '/');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Статические страницы
        if (\App\Models\Setting::getValue('sitemap.static.enabled', true)) {
            $xml .= $this->sitemap($baseUrl . '/sitemap-static.xml', now());
        }
        
        // Инструкции (включаем в index, даже если контента нет)
        if (\App\Models\Setting::getValue('sitemap.guides.enabled', true)) {
            // Всегда добавляем в index, даже если контента нет (sitemap файл будет пустым)
            $xml .= $this->sitemap($baseUrl . '/sitemap-guides.xml', now());
        }
        
        // Статьи (включаем в index, даже если контента нет)
        if (\App\Models\Setting::getValue('sitemap.articles.enabled', true)) {
            // Всегда добавляем в index, даже если контента нет (sitemap файл будет пустым)
            $xml .= $this->sitemap($baseUrl . '/sitemap-articles.xml', now());
        }
        
        // Толкования сновидений (включаем в index, даже если контента нет)
        if (\App\Models\Setting::getValue('sitemap.interpretations.enabled', true)) {
            $minDate = \Carbon\Carbon::create(2026, 1, 16, 0, 0, 0);
            $interpretationsCount = DreamInterpretation::where('processing_status', 'completed')
                ->whereNull('api_error')
                ->whereHas('result')
                ->where('created_at', '>=', $minDate)
                ->count();
            
            // Всегда добавляем в index, даже если контента нет
            $limit = $this->getPaginationLimit();
            $pages = $interpretationsCount > 0 ? ceil($interpretationsCount / $limit) : 1;
            for ($page = 1; $page <= $pages; $page++) {
                $xml .= $this->sitemap($baseUrl . "/sitemap-interpretations-{$page}.xml", now());
            }
        }
        
        // Публичные отчеты (включаем в index, даже если контента нет)
        if (\App\Models\Setting::getValue('sitemap.reports.enabled', true)) {
            $publicReportsCount = Report::where('status', 'published')
                ->where('access_level', 'all')
                ->with('user')
                ->get()
                ->filter(function($r) {
                    return $r->user && $r->user->diary_privacy === 'public';
                })
                ->count();
            
            // Всегда добавляем в index, даже если контента нет
            $limit = $this->getPaginationLimit();
            $pages = $publicReportsCount > 0 ? ceil($publicReportsCount / $limit) : 1;
            for ($page = 1; $page <= $pages; $page++) {
                $xml .= $this->sitemap($baseUrl . "/sitemap-reports-{$page}.xml", now());
            }
        }
        
        // Анализы отчетов (включаем в index, даже если контента нет)
        if (\App\Models\Setting::getValue('sitemap.report_analyses.enabled', true)) {
            $reportsWithAnalysis = Report::where('status', 'published')
                ->where('access_level', 'all')
                ->whereNotNull('analysis_id')
                ->with('user')
                ->get()
                ->filter(function($r) {
                    return $r->user && $r->user->diary_privacy === 'public' && $r->hasAnalysis();
                })
                ->count();
            
            // Всегда добавляем в index, даже если контента нет
            $limit = $this->getPaginationLimit();
            $pages = $reportsWithAnalysis > 0 ? ceil($reportsWithAnalysis / $limit) : 1;
            for ($page = 1; $page <= $pages; $page++) {
                $xml .= $this->sitemap($baseUrl . "/sitemap-report-analyses-{$page}.xml", now());
            }
        }
        
        $xml .= '</sitemapindex>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
    
    /**
     * Статические страницы
     */
    public function static(): Response
    {
        if (!\App\Models\Setting::getValue('sitemap.static.enabled', true)) {
            return $this->emptySitemap();
        }
        
        $baseUrl = rtrim(config('seo.base_url', config('app.url')), '/');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $xml .= $this->url($baseUrl . '/', now(), 'daily', 1.0);
        $xml .= $this->url($baseUrl . '/tolkovanie-snov', now(), 'weekly', 0.9);
        $xml .= $this->url($baseUrl . '/guide', now(), 'weekly', 0.8);
        $xml .= $this->url($baseUrl . '/articles', now(), 'weekly', 0.8);
        $xml .= $this->url($baseUrl . '/activity', now(), 'daily', 0.7);
        
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
    
    /**
     * Инструкции
     */
    public function guides(): Response
    {
        if (!\App\Models\Setting::getValue('sitemap.guides.enabled', true)) {
            return $this->emptySitemap();
        }
        
        $baseUrl = rtrim(config('seo.base_url', config('app.url')), '/');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $guides = Article::where('type', 'guide')
            ->where('status', 'published')
            ->orderBy('order')
            ->get();
        
        foreach ($guides as $guide) {
            $xml .= $this->url(
                $baseUrl . '/guide/' . $guide->slug,
                $guide->updated_at ?? $guide->created_at,
                'monthly',
                0.8
            );
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
    
    /**
     * Статьи
     */
    public function articles(): Response
    {
        if (!\App\Models\Setting::getValue('sitemap.articles.enabled', true)) {
            return $this->emptySitemap();
        }
        
        $baseUrl = rtrim(config('seo.base_url', config('app.url')), '/');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $articles = Article::where('type', 'article')
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();
        
        foreach ($articles as $article) {
            $xml .= $this->url(
                $baseUrl . '/articles/' . $article->slug,
                $article->updated_at ?? $article->created_at,
                'monthly',
                0.7
            );
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
    
    /**
     * Толкования сновидений (с пагинацией)
     */
    public function interpretations(int $page = 1): Response
    {
        if (!\App\Models\Setting::getValue('sitemap.interpretations.enabled', true)) {
            return $this->emptySitemap();
        }
        
        $baseUrl = rtrim(config('seo.base_url', config('app.url')), '/');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $minDate = \Carbon\Carbon::create(2026, 1, 16, 0, 0, 0);
        $limit = $this->getPaginationLimit();
        $offset = ($page - 1) * $limit;
        
        $interpretations = DreamInterpretation::where('processing_status', 'completed')
            ->whereNull('api_error')
            ->whereHas('result')
            ->where('created_at', '>=', $minDate)
            ->with('result')
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        
        foreach ($interpretations as $interpretation) {
            try {
                $seo = SeoHelper::forDreamAnalyzerResult($interpretation);
                
                $defaultTitlePattern = 'Толкование сна - Анализ сна';
                $hasValidTitle = !empty($seo['title']) && 
                    !empty($seo['description']) && 
                    !str_contains($seo['title'], $defaultTitlePattern) &&
                    !str_contains($seo['title'], 'Анализ серии снов') &&
                    mb_strlen($seo['title']) > 30;
                
                if ($hasValidTitle) {
                    $url = !empty($seo['canonical']) 
                        ? $seo['canonical'] 
                        : route('dream-analyzer.show', ['hash' => $interpretation->hash]);
                    
                    $lastmod = $interpretation->updated_at ?? $interpretation->created_at;
                    $xml .= $this->url($url, $lastmod, 'monthly', 0.6);
                }
            } catch (\Exception $e) {
                \Log::warning('Sitemap: Error generating SEO for interpretation ' . $interpretation->id, [
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
    
    /**
     * Публичные отчеты (с пагинацией)
     */
    public function reports(int $page = 1): Response
    {
        if (!\App\Models\Setting::getValue('sitemap.reports.enabled', true)) {
            return $this->emptySitemap();
        }
        
        $baseUrl = rtrim(config('seo.base_url', config('app.url')), '/');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $limit = $this->getPaginationLimit();
        $offset = ($page - 1) * $limit;
        
        $publicReports = Report::where('status', 'published')
            ->where('access_level', 'all')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        
        foreach ($publicReports as $report) {
            if ($report->user && $report->user->diary_privacy === 'public') {
                $xml .= $this->url(
                    route('reports.show', ['report' => $report->id]),
                    $report->updated_at ?? $report->created_at,
                    'weekly',
                    0.5
                );
            }
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
    
    /**
     * Анализы публичных отчетов (с пагинацией)
     */
    public function reportAnalyses(int $page = 1): Response
    {
        if (!\App\Models\Setting::getValue('sitemap.report_analyses.enabled', true)) {
            return $this->emptySitemap();
        }
        
        $baseUrl = rtrim(config('seo.base_url', config('app.url')), '/');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $limit = $this->getPaginationLimit();
        $offset = ($page - 1) * $limit;
        
        $publicReports = Report::where('status', 'published')
            ->where('access_level', 'all')
            ->whereNotNull('analysis_id')
            ->with(['user', 'analysis'])
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        
        foreach ($publicReports as $report) {
            if ($report->user && $report->user->diary_privacy === 'public' && $report->hasAnalysis() && $report->analysis) {
                try {
                    $analysisSeo = SeoHelper::forReportAnalysis($report, $report->analysis);
                    
                    if (!empty($analysisSeo['title']) && 
                        !empty($analysisSeo['description']) &&
                        mb_strlen($analysisSeo['title']) > 30) {
                        
                        $analysisUrl = !empty($analysisSeo['canonical']) 
                            ? $analysisSeo['canonical'] 
                            : route('reports.analysis', ['report' => $report->id]);
                        
                        $analysisLastmod = $report->analysis->updated_at 
                            ?? $report->analysis->created_at 
                            ?? $report->updated_at 
                            ?? $report->created_at;
                        
                        $xml .= $this->url($analysisUrl, $analysisLastmod, 'monthly', 0.6);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Sitemap: Error generating SEO for report analysis ' . $report->id, [
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
    
    /**
     * Формирование элемента sitemap в index
     */
    private function sitemap(string $loc, $lastmod): string
    {
        $lastmodFormatted = $lastmod instanceof \DateTimeInterface 
            ? $lastmod->format('Y-m-d') 
            : now()->format('Y-m-d');
        
        return "  <sitemap>\n" .
               "    <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n" .
               "    <lastmod>{$lastmodFormatted}</lastmod>\n" .
               "  </sitemap>\n";
    }
    
    /**
     * Формирование URL элемента для sitemap
     */
    private function url(string $loc, $lastmod, string $changefreq, float $priority): string
    {
        $lastmodFormatted = $lastmod instanceof \DateTimeInterface 
            ? $lastmod->format('Y-m-d') 
            : now()->format('Y-m-d');
        
        return "  <url>\n" .
               "    <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n" .
               "    <lastmod>{$lastmodFormatted}</lastmod>\n" .
               "    <changefreq>{$changefreq}</changefreq>\n" .
               "    <priority>{$priority}</priority>\n" .
               "  </url>\n";
    }
    
    /**
     * Пустой sitemap (для отключенных типов)
     */
    private function emptySitemap(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
