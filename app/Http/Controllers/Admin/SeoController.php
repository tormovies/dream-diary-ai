<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use App\Models\Report;
use App\Models\User;
use App\Models\DreamInterpretation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SeoController extends Controller
{
    /**
     * Список всех SEO-записей
     */
    public function index(Request $request): View
    {
        $query = SeoMeta::query();

        if ($request->filled('page_type')) {
            $query->where('page_type', $request->page_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('h1', 'like', "%{$search}%");
            });
        }

        $seoMetas = $query->orderBy('page_type')->orderBy('priority', 'desc')->paginate(20);

        $pageTypes = [
            'home' => 'Главная',
            'report' => 'Отчет',
            'profile' => 'Профиль',
            'diary' => 'Дневник',
            'search' => 'Поиск',
            'activity' => 'Лента активности',
            'users' => 'Сообщество',
            'dashboard' => 'Мои отчёты',
            'statistics' => 'Статистика',
            'notifications' => 'Уведомления',
            'dream-analyzer' => 'Толкование снов (форма)',
            'dream-analyzer-result' => 'Толкование сна (результат)',
            'guide-index' => 'Инструкции (заглавная)',
            'articles-index' => 'Статьи (заглавная)',
            'guide' => 'Инструкция',
            'article' => 'Статья',
        ];

        return view('admin.seo.index', compact('seoMetas', 'pageTypes'));
    }

    /**
     * Форма создания SEO-записи
     */
    public function create(): View
    {
        $pageTypes = [
            'home' => 'Главная',
            'report' => 'Отчет',
            'profile' => 'Профиль',
            'diary' => 'Дневник',
            'search' => 'Поиск',
            'activity' => 'Лента активности',
            'users' => 'Сообщество',
            'dashboard' => 'Мои отчёты',
            'statistics' => 'Статистика',
            'notifications' => 'Уведомления',
            'dream-analyzer' => 'Толкование снов (форма)',
            'dream-analyzer-result' => 'Толкование сна (результат)',
            'guide-index' => 'Инструкции (заглавная)',
            'articles-index' => 'Статьи (заглавная)',
            'guide' => 'Инструкция',
            'article' => 'Статья',
        ];

        // Для выбора конкретных страниц
        $reports = Report::latest()->limit(50)->get(['id', 'report_date']);
        $users = User::latest()->limit(50)->get(['id', 'nickname', 'name']);
        $interpretations = DreamInterpretation::latest()
            ->limit(50)
            ->get(['id', 'hash', 'dream_description', 'created_at']);

        return view('admin.seo.create', compact('pageTypes', 'reports', 'users', 'interpretations'));
    }

    /**
     * Сохранение новой SEO-записи
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'page_type' => ['required', 'string', 'max:255'],
            'page_id' => ['nullable', 'integer'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'h1' => ['nullable', 'string'],
            'h1_description' => ['nullable', 'string'],
            'keywords' => ['nullable', 'string'],
            'og_title' => ['nullable', 'string'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string'],
            'og_image_file' => ['nullable', 'image', 'max:2048'], // 2MB max
            'is_active' => ['boolean'],
            'priority' => ['integer', 'min:0', 'max:100'],
        ]);

        // Если загружено изображение
        if ($request->hasFile('og_image_file')) {
            $path = $request->file('og_image_file')->store('seo/og-images', 'public');
            $validated['og_image'] = 'storage/' . $path;
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['priority'] = $validated['priority'] ?? 0;

        // Очищаем page_id и route_name, если они пустые
        if (empty($validated['page_id'])) {
            $validated['page_id'] = null;
        }
        if (empty($validated['route_name'])) {
            $validated['route_name'] = null;
        }

        SeoMeta::create($validated);

        return redirect()->route('admin.seo.index')->with('success', 'SEO-запись создана успешно');
    }

    /**
     * Форма редактирования SEO-записи
     */
    public function edit(SeoMeta $seo): View
    {
        $pageTypes = [
            'home' => 'Главная',
            'report' => 'Отчет',
            'profile' => 'Профиль',
            'diary' => 'Дневник',
            'search' => 'Поиск',
            'activity' => 'Лента активности',
            'users' => 'Сообщество',
            'dashboard' => 'Мои отчёты',
            'statistics' => 'Статистика',
            'notifications' => 'Уведомления',
            'dream-analyzer' => 'Толкование снов (форма)',
            'dream-analyzer-result' => 'Толкование сна (результат)',
            'guide-index' => 'Инструкции (заглавная)',
            'articles-index' => 'Статьи (заглавная)',
            'guide' => 'Инструкция',
            'article' => 'Статья',
        ];

        // Для выбора конкретных страниц
        $reports = Report::latest()->limit(50)->get(['id', 'report_date']);
        $users = User::latest()->limit(50)->get(['id', 'nickname', 'name']);
        $interpretations = DreamInterpretation::latest()
            ->limit(50)
            ->get(['id', 'hash', 'dream_description', 'created_at']);

        return view('admin.seo.edit', compact('seo', 'pageTypes', 'reports', 'users', 'interpretations'));
    }

    /**
     * Обновление SEO-записи
     */
    public function update(Request $request, SeoMeta $seo): RedirectResponse
    {
        $validated = $request->validate([
            'page_type' => ['required', 'string', 'max:255'],
            'page_id' => ['nullable', 'integer'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'h1' => ['nullable', 'string'],
            'h1_description' => ['nullable', 'string'],
            'keywords' => ['nullable', 'string'],
            'og_title' => ['nullable', 'string'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string'],
            'og_image_file' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'priority' => ['integer', 'min:0', 'max:100'],
        ]);

        // Если загружено новое изображение
        if ($request->hasFile('og_image_file')) {
            // Удаляем старое, если оно было загружено на сервер
            if ($seo->og_image && strpos($seo->og_image, 'storage/') === 0) {
                $oldPath = str_replace('storage/', '', $seo->og_image);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('og_image_file')->store('seo/og-images', 'public');
            $validated['og_image'] = 'storage/' . $path;
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['priority'] = $validated['priority'] ?? 0;

        // Очищаем page_id и route_name, если они пустые
        if (empty($validated['page_id'])) {
            $validated['page_id'] = null;
        }
        if (empty($validated['route_name'])) {
            $validated['route_name'] = null;
        }

        $seo->update($validated);

        return redirect()->route('admin.seo.index')->with('success', 'SEO-запись обновлена успешно');
    }

    /**
     * Удаление SEO-записи
     */
    public function destroy(SeoMeta $seo): RedirectResponse
    {
        // Удаляем изображение, если оно было загружено на сервер
        if ($seo->og_image && strpos($seo->og_image, 'storage/') === 0) {
            $path = str_replace('storage/', '', $seo->og_image);
            Storage::disk('public')->delete($path);
        }

        $seo->delete();

        return redirect()->route('admin.seo.index')->with('success', 'SEO-запись удалена успешно');
    }

    /**
     * Управление sitemap
     */
    public function sitemap(): View
    {
        $today = now()->startOfDay();
        
        // Статистика по типам контента
        $stats = [
            'static' => [
                'enabled' => \App\Models\Setting::getValue('sitemap.static.enabled', true),
                'total' => 5, // Статические страницы всегда 5
                'today' => 0, // Статические страницы не добавляются ежедневно
            ],
            'guides' => [
                'enabled' => \App\Models\Setting::getValue('sitemap.guides.enabled', true),
                'total' => \App\Models\Article::where('type', 'guide')->where('status', 'published')->count(),
                'today' => \App\Models\Article::where('type', 'guide')
                    ->where('status', 'published')
                    ->whereDate('created_at', $today)
                    ->count(),
            ],
            'articles' => [
                'enabled' => \App\Models\Setting::getValue('sitemap.articles.enabled', true),
                'total' => \App\Models\Article::where('type', 'article')->where('status', 'published')->count(),
                'today' => \App\Models\Article::where('type', 'article')
                    ->where('status', 'published')
                    ->whereDate('created_at', $today)
                    ->count(),
            ],
            'interpretations' => [
                'enabled' => \App\Models\Setting::getValue('sitemap.interpretations.enabled', true),
                'total' => $this->getValidInterpretationsCount(),
                'today' => $this->getValidInterpretationsCount($today),
            ],
            'reports' => [
                'enabled' => \App\Models\Setting::getValue('sitemap.reports.enabled', true),
                'total' => $this->getPublicReportsCount(),
                'today' => $this->getPublicReportsCount($today),
            ],
            'report_analyses' => [
                'enabled' => \App\Models\Setting::getValue('sitemap.report_analyses.enabled', true),
                'total' => $this->getPublicReportAnalysesCount(),
                'today' => $this->getPublicReportAnalysesCount($today),
            ],
        ];

        // Количество URL на одной странице sitemap (из настроек или значение по умолчанию)
        $urlsPerPage = \App\Models\Setting::getValue('sitemap.urls_per_page', \App\Http\Controllers\SitemapController::PAGINATION_LIMIT);

        return view('admin.seo.sitemap', compact('stats', 'urlsPerPage'));
    }

    /**
     * Сохранение настроек sitemap
     */
    public function updateSitemapSettings(Request $request): RedirectResponse
    {
        // Checkbox не отправляется, если не отмечен, поэтому проверяем наличие поля
        // Если поле есть в запросе - true, если нет - false
        
        \App\Models\Setting::setValue('sitemap.static.enabled', $request->has('static_enabled'));
        \App\Models\Setting::setValue('sitemap.guides.enabled', $request->has('guides_enabled'));
        \App\Models\Setting::setValue('sitemap.articles.enabled', $request->has('articles_enabled'));
        \App\Models\Setting::setValue('sitemap.interpretations.enabled', $request->has('interpretations_enabled'));
        \App\Models\Setting::setValue('sitemap.reports.enabled', $request->has('reports_enabled'));
        \App\Models\Setting::setValue('sitemap.report_analyses.enabled', $request->has('report_analyses_enabled'));

        // Сохранение количества URL на странице
        $urlsPerPage = $request->input('urls_per_page');
        if ($urlsPerPage !== null) {
            // Валидация: минимум 100, максимум 50000
            $urlsPerPage = max(100, min((int)$urlsPerPage, 50000));
            \App\Models\Setting::setValue('sitemap.urls_per_page', $urlsPerPage);
        }

        return back()->with('success', 'Настройки sitemap сохранены');
    }

    /**
     * Получить количество валидных толкований
     * Примечание: это приблизительное количество (готовые толкования),
     * реальное количество в sitemap может быть меньше из-за проверки SEO заголовков
     */
    private function getValidInterpretationsCount($dateFrom = null): int
    {
        $minDate = \Carbon\Carbon::create(2026, 1, 16, 0, 0, 0);
        $query = \App\Models\DreamInterpretation::where('processing_status', 'completed')
            ->whereNull('api_error')
            ->whereHas('result')
            ->where('created_at', '>=', $minDate);

        if ($dateFrom) {
            $query->whereDate('created_at', $dateFrom);
        }

        return $query->count();
    }

    /**
     * Получить количество публичных отчетов
     */
    private function getPublicReportsCount($dateFrom = null): int
    {
        $query = \App\Models\Report::where('status', 'published')
            ->where('access_level', 'all')
            ->with('user');

        if ($dateFrom) {
            $query->whereDate('created_at', $dateFrom);
        }

        return $query->get()->filter(function($r) {
            return $r->user && $r->user->diary_privacy === 'public';
        })->count();
    }

    /**
     * Получить количество публичных анализов отчетов
     */
    private function getPublicReportAnalysesCount($dateFrom = null): int
    {
        $query = \App\Models\Report::where('status', 'published')
            ->where('access_level', 'all')
            ->whereNotNull('analysis_id')
            ->with(['user', 'analysis']);

        if ($dateFrom) {
            $query->whereDate('created_at', $dateFrom);
        }

        return $query->get()->filter(function($r) {
            return $r->user && 
                   $r->user->diary_privacy === 'public' && 
                   $r->hasAnalysis() && 
                   $r->analysis;
        })->count();
    }
}
