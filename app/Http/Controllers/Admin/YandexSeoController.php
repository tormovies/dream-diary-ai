<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\YandexSeoImportService;
use App\Services\YandexWebmasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class YandexSeoController extends Controller
{
    public function index(Request $request): View
    {
        $tab = in_array($request->input('tab'), ['pages', 'queries', 'overview', 'loading'], true) ? $request->input('tab') : 'pages';
        $host = (string) Setting::getValue('yandex_seo_host_id', '');
        $from = now('Europe/Moscow')->subDays(30)->toDateString();
        $to = now('Europe/Moscow')->subDay()->toDateString();
        $metrics = DB::table('yandex_seo_metrics')->whereBetween('date', [$from, $to]);
        $totals = (clone $metrics)->join('yandex_seo_pages', 'yandex_seo_pages.id', '=', 'yandex_seo_metrics.page_id')
            ->where('host_id', $host)->selectRaw('COALESCE(SUM(clicks), 0) AS clicks, COALESCE(SUM(impressions), 0) AS impressions')->first();
        $aggregates = (clone $metrics)->selectRaw('page_id, SUM(clicks) AS clicks, SUM(impressions) AS impressions')->groupBy('page_id');
        $query = DB::table('yandex_seo_pages AS p')->where('p.host_id', $host)
            ->leftJoinSub($aggregates, 'm', 'm.page_id', '=', 'p.id')
            ->selectRaw('p.*, COALESCE(m.clicks, 0) AS clicks, COALESCE(m.impressions, 0) AS impressions, CASE WHEN m.impressions > 0 THEN 100.0 * m.clicks / m.impressions ELSE 0 END AS ctr');
        if ($tab === 'queries') {
            $query = (clone $metrics)->join('yandex_seo_pages AS p', 'p.id', '=', 'yandex_seo_metrics.page_id')->where('p.host_id', $host)
                ->selectRaw('query, query_hash, SUM(clicks) AS clicks, SUM(impressions) AS impressions, CASE WHEN SUM(impressions) > 0 THEN 100.0 * SUM(clicks) / SUM(impressions) ELSE 0 END AS ctr')
                ->groupBy('query_hash', 'query');
        }
        $search = mb_substr((string) $request->input('search', ''), 0, 200);
        if ($search !== '') {
            $query->where($tab === 'queries' ? 'query' : 'p.path', 'like', '%'.$search.'%');
        }
        $sort = in_array($request->input('sort'), ['clicks', 'impressions', 'ctr'], true) ? $request->input('sort') : 'impressions';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $rows = $query->orderBy($sort, $direction)->paginate(25)->withQueryString();
        $tasks = DB::table('yandex_seo_tasks')->where('host_id', $host)->latest('id')->paginate(25, ['*'], 'tasks_page');
        $coverage = DB::table('yandex_seo_coverage')->join('yandex_seo_pages AS p', 'p.id', '=', 'page_id')
            ->where('p.host_id', $host)->whereBetween('date', [$from, $to])->where('status', 'loaded')->count();
        $settings = [];
        foreach (['enabled', 'host_id', 'host_url', 'user_id', 'hosts', 'last_error', 'last_tick', 'free_remaining', 'limits', 'index_state'] as $key) {
            $settings[$key] = Setting::getValue('yandex_seo_'.$key);
        }
        $settings['token_set'] = (bool) Setting::getValue('yandex_seo_token');

        return view('admin.seo.yandex', compact('tab', 'rows', 'tasks', 'totals', 'coverage', 'settings', 'from', 'to'));
    }

    private function page(int $page): object
    {
        $record = DB::table('yandex_seo_pages')->where('id', $page)
            ->where('host_id', (string) Setting::getValue('yandex_seo_host_id', ''))->first();
        abort_unless($record, 404);
        $host = parse_url((string) Setting::getValue('yandex_seo_host_url', ''), PHP_URL_HOST);
        $localHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        abort_unless($host && strtolower($host) === strtolower((string) $localHost), 422, 'Редактировать SEO можно только для текущего сайта.');

        return $record;
    }

    private function currentSeo(string $path): ?array
    {
        $static = [
            '/' => 'home', '/search' => 'search', '/activity' => 'activity', '/users' => 'users',
            '/tolkovanie-snov' => 'dream-analyzer', '/guide' => 'guide-index', '/articles' => 'articles-index',
            '/symbol' => 'symbol-index', '/obratnaya-svyaz' => 'feedback', '/register' => 'register', '/login' => 'login',
        ];
        if (isset($static[$path])) {
            return \App\Helpers\SeoHelper::get($static[$path]);
        }
        if (preg_match('~^/(guide|articles|symbol)/([^/?]+)$~', $path, $matches)) {
            $type = ['guide' => 'guide', 'articles' => 'article', 'symbol' => 'entity_group'][$matches[1]];
            $article = \App\Models\Article::published()->where('type', $type)->where('slug', $matches[2])->first();
            if ($article) {
                return \App\Helpers\SeoHelper::get($type, $article->id, ['title' => $article->title, 'slug' => $article->slug, 'article' => $article]);
            }
        }
        if (preg_match('~^/tolkovanie-snov/([A-Za-z0-9]+)$~', $path, $matches)) {
            $interpretation = \App\Models\DreamInterpretation::where('hash', $matches[1])->first();
            if ($interpretation) {
                return \App\Helpers\SeoHelper::forDreamAnalyzerResult($interpretation);
            }
        }
        if (preg_match('~^/users/(\d+)$~', $path, $matches)) {
            $user = \App\Models\User::find($matches[1]);
            if ($user) {
                return \App\Helpers\SeoHelper::forProfile($user);
            }
        }
        if (preg_match('~^/diary/([^/?]+)$~', $path, $matches)) {
            $user = \App\Models\User::where('public_link', $matches[1])->first();
            if ($user) {
                return \App\Helpers\SeoHelper::forDiary($user);
            }
        }
        if (preg_match('~^/reports/(\d+)(/analysis)?$~', $path, $matches)) {
            $report = \App\Models\Report::where('status', 'published')->find($matches[1]);
            if ($report) {
                if (! empty($matches[2])) {
                    return $report->analysis ? \App\Helpers\SeoHelper::forReportAnalysis($report, $report->analysis) : null;
                }

                return \App\Helpers\SeoHelper::forReport($report);
            }
        }

        return null;
    }

    public function detail(int $page): \Illuminate\Http\JsonResponse
    {
        $record = $this->page($page);
        $hash = hash('sha256', $record->path);
        $override = DB::table('seo_url_overrides')->where('path_hash', $hash)->first();
        $queries = DB::table('yandex_seo_metrics')->where('page_id', $page)
            ->whereBetween('date', [now('Europe/Moscow')->subDays(30)->toDateString(), now('Europe/Moscow')->subDay()->toDateString()])
            ->selectRaw('query, SUM(clicks) AS clicks, SUM(impressions) AS impressions')->groupBy('query')->orderByDesc('impressions')->limit(50)->get();
        $history = DB::table('seo_url_changes')->where('path_hash', $hash)->latest('id')->limit(30)->get()->map(function ($change) {
            $change->before = json_decode($change->before, true);
            $change->after = json_decode($change->after, true);

            return $change;
        });

        return response()->json(['page' => $record, 'fields' => [
            'title' => $override->title ?? '', 'description' => $override->description ?? '', 'h1' => $override->h1 ?? '',
        ], 'current' => $this->currentSeo($record->path), 'queries' => $queries, 'history' => $history]);
    }

    public function suggestions(int $page, \App\Services\DeepSeekService $ai): \Illuminate\Http\JsonResponse
    {
        $details = $this->detail($page)->getData(true);
        try {
            $result = $ai->suggestSeo([
                'path' => $details['page']['path'],
                'search_title' => $details['page']['search_title'],
                'manual_fields' => $details['fields'],
                'current' => $details['current'] ? array_intersect_key($details['current'], array_flip(['title', 'description', 'h1'])) : null,
                'queries' => $details['queries'],
                'period_days' => 30,
                'statistics_are_partial' => true,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Не удалось получить рекомендации ИИ. Проверьте настройки и повторите запрос.'], 502);
        }

        return response()->json($result);
    }

    public function save(Request $request, int $page): \Illuminate\Http\JsonResponse
    {
        $record = $this->page($page);
        $data = $request->validate([
            'title' => ['present', 'nullable', 'string', 'max:255'],
            'description' => ['present', 'nullable', 'string', 'max:500'],
            'h1' => ['present', 'nullable', 'string', 'max:255'],
            'confirmed' => ['accepted'],
        ]);
        $after = [];
        foreach (['title', 'description', 'h1'] as $field) {
            $after[$field] = isset($data[$field]) && trim($data[$field]) !== '' ? trim($data[$field]) : null;
        }
        DB::transaction(function () use ($record, $after, $request) {
            $hash = hash('sha256', $record->path);
            DB::table('yandex_seo_pages')->where('id', $record->id)->lockForUpdate()->first();
            $existing = DB::table('seo_url_overrides')->where('path_hash', $hash)->first();
            $before = [];
            foreach (['title', 'description', 'h1'] as $field) {
                $before[$field] = $existing->{$field} ?? null;
            }
            if ($before === $after) {
                return;
            }
            DB::table('seo_url_overrides')->upsert([
                array_merge($after, ['path_hash' => $hash, 'path' => $record->path, 'created_at' => now(), 'updated_at' => now()]),
            ], ['path_hash'], ['title', 'description', 'h1', 'updated_at']);
            DB::table('seo_url_changes')->insert([
                'path_hash' => $hash, 'path' => $record->path, 'user_id' => $request->user()->id,
                'before' => json_encode($before, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'after' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'created_at' => now(),
            ]);
        });

        return $this->detail($page);
    }

    public function connect(Request $request, YandexWebmasterService $api): RedirectResponse
    {
        $data = $request->validate(['token' => ['nullable', 'string', 'max:4096']]);
        if (! empty($data['token'])) {
            Setting::setValue('yandex_seo_token', Crypt::encryptString($data['token']));
            Setting::setValue('yandex_seo_enabled', false);
        }
        try {
            $user = $api->request('GET', 'user');
            if (empty($user['user_id'])) {
                throw new \RuntimeException('Яндекс не вернул ID пользователя.');
            }
            $hosts = $api->request('GET', 'user/'.rawurlencode((string) $user['user_id']).'/hosts');
            Setting::setValue('yandex_seo_user_id', (string) $user['user_id']);
            Setting::setValue('yandex_seo_hosts', $hosts['hosts'] ?? []);
        } catch (\Throwable $e) {
            return back()->withErrors(['connection' => $e->getMessage()]);
        }

        return back()->with('success', 'Подключение проверено. Выберите подтверждённый сайт.');
    }

    public function settings(Request $request): RedirectResponse
    {
        $data = $request->validate(['host_id' => ['required', 'string', 'max:255'], 'enabled' => ['nullable', 'boolean']]);
        $host = collect(Setting::getValue('yandex_seo_hosts', []))->first(fn ($host) => ($host['host_id'] ?? '') === $data['host_id'] && ($host['verified'] ?? false));
        if (! $host) {
            return back()->withErrors(['host_id' => 'Выберите подтверждённый сайт из списка Яндекса.']);
        }
        $url = $host['ascii_host_url'] ?? '';
        if (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return back()->withErrors(['host_id' => 'Яндекс вернул некорректный адрес сайта.']);
        }
        Setting::setValue('yandex_seo_host_id', $data['host_id']);
        Setting::setValue('yandex_seo_host_url', $url);
        Setting::setValue('yandex_seo_enabled', $request->boolean('enabled'));

        return back()->with('success', 'Настройки загрузки сохранены. Платная квота не используется.');
    }

    public function recover(Request $request, int $task, YandexSeoImportService $import): RedirectResponse
    {
        $request->validate(['new_export' => ['nullable', 'boolean']]);
        try {
            $import->recoverTask($task, $request->boolean('new_export'));
        } catch (\Throwable $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back()->with('success', 'Задача восстановлена для следующего цикла загрузки.');
    }
}
