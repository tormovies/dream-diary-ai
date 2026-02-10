<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use App\Models\Report;
use App\Models\User;
use App\Models\DreamInterpretation;
use App\Models\Article;
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

        // Сортировка по ID
        $sortBy = $request->get('sort_by');
        $sortOrder = $request->get('sort_order');
        
        if ($sortBy === 'id') {
            // Если явно указана сортировка по ID, используем её
            $query->orderBy('id', $sortOrder ?? 'desc');
        } else {
            // Сортировка по умолчанию - по ID по убыванию (последние записи первыми)
            $query->orderBy('id', 'desc');
        }

        $seoMetas = $query->paginate(20)->appends($request->query());
        
        // Загружаем данные для формирования ссылок одним запросом
        $interpretationIds = [];
        $reportIds = [];
        $userIds = [];
        $articleIds = [];
        
        foreach ($seoMetas as $seo) {
            if ($seo->page_id) {
                switch ($seo->page_type) {
                    case 'dream-analyzer-result':
                        $interpretationIds[] = $seo->page_id;
                        break;
                    case 'report-analysis':
                        // Для анализов отчетов нужно загрузить interpretation, чтобы получить report_id
                        $interpretationIds[] = $seo->page_id;
                        break;
                    case 'report':
                        $reportIds[] = $seo->page_id;
                        break;
                    case 'profile':
                    case 'diary':
                        $userIds[] = $seo->page_id;
                        break;
                    case 'guide':
                    case 'article':
                        $articleIds[] = $seo->page_id;
                        break;
                }
            }
        }
        
        // Загружаем все данные одним запросом
        $interpretations = [];
        $reportAnalysisReports = []; // Отчеты для анализов отчетов
        if (!empty($interpretationIds)) {
            $interpretations = DreamInterpretation::whereIn('id', array_unique($interpretationIds))
                ->with('report:id') // Загружаем связанный отчет для анализов
                ->get(['id', 'hash', 'report_id'])
                ->keyBy('id');
            
            // Собираем report_id для анализов отчетов
            foreach ($interpretations as $interpretation) {
                if ($interpretation->report_id) {
                    $reportAnalysisReports[$interpretation->id] = $interpretation->report_id;
                }
            }
        }
        
        // Инициализируем как коллекцию, чтобы можно было использовать merge()
        $reports = collect();
        if (!empty($reportIds)) {
            $reports = Report::whereIn('id', array_unique($reportIds))
                ->get(['id'])
                ->keyBy('id');
        }
        
        // Добавляем отчеты из анализов в общий массив
        if (!empty($reportAnalysisReports)) {
            $reportIdsFromAnalysis = array_unique(array_values($reportAnalysisReports));
            $reportsFromAnalysis = Report::whereIn('id', $reportIdsFromAnalysis)
                ->get(['id'])
                ->keyBy('id');
            // Объединяем коллекции
            $reports = $reports->merge($reportsFromAnalysis);
        }
        
        $users = [];
        if (!empty($userIds)) {
            $users = User::whereIn('id', array_unique($userIds))
                ->get(['id', 'public_link'])
                ->keyBy('id');
        }
        
        $articles = [];
        if (!empty($articleIds)) {
            $articles = Article::whereIn('id', array_unique($articleIds))
                ->get(['id', 'slug', 'type'])
                ->keyBy('id');
        }

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
            'report-analysis' => 'Анализ отчета (результат)',
            'guide-index' => 'Инструкции (заглавная)',
            'articles-index' => 'Статьи (заглавная)',
            'guide' => 'Инструкция',
            'article' => 'Статья',
        ];

        return view('admin.seo.index', compact('seoMetas', 'pageTypes', 'interpretations', 'reports', 'users', 'articles', 'reportAnalysisReports'));
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
            'report-analysis' => 'Анализ отчета (результат)',
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
            ->select('id', 'hash', 'created_at')
            ->with('result:id,dream_interpretation_id,dream_title,series_title')
            ->get();

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
            'report-analysis' => 'Анализ отчета (результат)',
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
            ->select('id', 'hash', 'created_at')
            ->with('result:id,dream_interpretation_id,dream_title,series_title')
            ->get();

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
        
        // Количество ссылок при перелинковке (из настроек или значение по умолчанию)
        $linkingLinksCount = \App\Models\Setting::getValue('sitemap.linking_links_count', 5);
        
        // Дата последнего обновления кеша (конвертируем из UTC в часовой пояс приложения)
        // Это может быть либо дата генерации кеша, либо дата очистки кеша
        $lastCacheUpdate = \App\Models\Setting::getValue('sitemap.last_cache_update', null);
        
        // Получаем часовой пояс из настроек базы данных (как в AdminController)
        $timezone = \App\Models\Setting::getValue('timezone', config('app.timezone', 'UTC'));
        
        // Проверяем, что значение не пустое и не false
        if ($lastCacheUpdate && $lastCacheUpdate !== '' && $lastCacheUpdate !== '0') {
            try {
                // Парсим как UTC и конвертируем в часовой пояс из настроек
                $lastCacheUpdate = \Carbon\Carbon::parse($lastCacheUpdate, 'UTC')
                    ->setTimezone($timezone);
            } catch (\Exception $e) {
                $lastCacheUpdate = null;
            }
        } else {
            $lastCacheUpdate = null;
        }
        
        // Проверяем, существует ли кеш (если кеш существует, значит он актуален)
        // Если кеш не существует, но есть дата - значит кеш был очищен и ждет пересоздания
        $cacheExists = \Illuminate\Support\Facades\Cache::has('sitemap:index:page:1');

        return view('admin.seo.sitemap', compact('stats', 'urlsPerPage', 'linkingLinksCount', 'lastCacheUpdate', 'cacheExists', 'timezone'));
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

        // Сохранение количества ссылок при перелинковке
        $linkingLinksCount = $request->input('linking_links_count');
        if ($linkingLinksCount !== null) {
            // Валидация: минимум 1, максимум 20
            $linkingLinksCount = max(1, min((int)$linkingLinksCount, 20));
            \App\Models\Setting::setValue('sitemap.linking_links_count', $linkingLinksCount);
        }

        return back()->with('success', 'Настройки sitemap сохранены');
    }
    
    /**
     * Очистка кеша sitemap
     */
    public function clearSitemapCache(): RedirectResponse
    {
        // Очищаем кеш для всех типов sitemap
        // Для database драйвера нужно очищать по ключам
        $types = ['index', 'static', 'guides', 'articles', 'interpretations', 'reports', 'report_analyses'];
        
        foreach ($types as $type) {
            // Очищаем первую страницу (основная)
            \Illuminate\Support\Facades\Cache::forget("sitemap:{$type}:page:1");
            
            // Очищаем дополнительные страницы (до 10 страниц на всякий случай)
            for ($page = 2; $page <= 10; $page++) {
                \Illuminate\Support\Facades\Cache::forget("sitemap:{$type}:page:{$page}");
            }
        }
        
        // Очищаем кеш для всех типов sitemap
        // После очистки кеш будет пересоздан при следующем запросе sitemap
        
        // Устанавливаем дату очистки кеша (это дата, когда кеш был очищен)
        // Когда кеш пересоздастся при следующем запросе, дата обновится автоматически
        \App\Models\Setting::setValue('sitemap.last_cache_update', now('UTC')->toDateTimeString());
        
        // Очищаем кеш настроек, если он используется
        \Illuminate\Support\Facades\Cache::forget('settings');
        
        // Используем явный редирект вместо back() для гарантированного обновления страницы
        return redirect()->route('admin.seo.sitemap')->with('success', 'Кеш sitemap очищен. Он будет автоматически перегенерирован при следующем запросе sitemap.');
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
