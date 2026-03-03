<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\DreamInterpretation;
use App\Models\DreamEntityDaily;
use App\Models\DreamInterpretationEntity;
use App\Models\DreamInterpretationStat;
use App\Models\EntityGroup;
use App\Models\EntityGroupMapping;
use App\Models\Report;
use App\Models\Setting;
use App\Models\User;
use App\Rules\NoSpam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminController extends Controller
{
    // Middleware применяется через маршруты

    /**
     * Дашборд админ-панели
     */
    public function dashboard(): View
    {
        $stats = [
            'users_count' => User::count(),
            'reports_count' => Report::count(),
            'comments_count' => Comment::count(),
            'reports_today' => Report::whereDate('created_at', today())->count(),
        ];

        $recentReports = Report::with(['user', 'dreams'])->latest()->limit(10)->get();
        $recentUsers = User::latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentReports', 'recentUsers'));
    }

    /**
     * Управление пользователями
     */
    public function users(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nickname', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->withCount('reports')->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Редактирование пользователя
     */
    public function editUser(User $user): View
    {
        return view('admin.edit-user', compact('user'));
    }

    /**
     * Обновление пользователя
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', new NoSpam()],
            'nickname' => ['required', 'string', 'max:255', 'unique:users,nickname,' . $user->id, new NoSpam()],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,user'],
            'diary_privacy' => ['required', 'in:public,private,friends'],
            'bio' => ['nullable', 'string', 'max:1000', new NoSpam()],
        ]);

        $user->update($request->only(['name', 'nickname', 'email', 'role', 'diary_privacy', 'bio', 'avatar']));

        return redirect()->route('admin.users')->with('success', 'Пользователь обновлен');
    }

    /**
     * Блокировка пользователя
     */
    public function banUser(Request $request, User $user): RedirectResponse
    {
        // Нельзя заблокировать самого себя
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Вы не можете заблокировать самого себя');
        }

        // Нельзя заблокировать другого администратора
        if ($user->isAdmin()) {
            return back()->with('error', 'Нельзя заблокировать администратора');
        }

        $request->validate([
            'ban_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->ban($request->ban_reason);

        return back()->with('success', "Пользователь {$user->nickname} заблокирован");
    }

    /**
     * Разблокировка пользователя
     */
    public function unbanUser(User $user): RedirectResponse
    {
        $user->unban();

        return back()->with('success', "Пользователь {$user->nickname} разблокирован");
    }

    /**
     * Подтверждение email пользователя администратором (с отправкой уведомления на почту).
     */
    public function verifyUserEmail(User $user): RedirectResponse
    {
        if ($user->hasVerifiedEmail()) {
            return back()->with('info', 'Email пользователя уже подтверждён.');
        }

        $user->markEmailAsVerified();

        $appName = config('app.name');
        $message = "Здравствуйте!\n\nАдминистратор сайта {$appName} подтвердил ваш адрес электронной почты. Теперь вы можете пользоваться всеми возможностями сайта.\n\nС уважением,\n{$appName}";
        $subject = "{$appName} — ваш email подтверждён";

        try {
            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            return back()->with('warning', "Email пользователя подтверждён, но уведомление не удалось отправить: {$e->getMessage()}");
        }

        return back()->with('success', "Email пользователя {$user->nickname} подтверждён. Уведомление отправлено на {$user->email}.");
    }

    /**
     * Удаление пользователя со всем контентом
     */
    public function deleteUser(User $user): RedirectResponse
    {
        // Нельзя удалить самого себя
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Вы не можете удалить самого себя');
        }

        // Нельзя удалить другого администратора
        if ($user->isAdmin()) {
            return back()->with('error', 'Нельзя удалить администратора');
        }

        $nickname = $user->nickname;

        // Удаление всего контента пользователя вручную
        // (на случай если каскадное удаление не настроено в миграциях)
        
        // 1. Удаляем анализы снов и связанные результаты
        foreach ($user->dreamInterpretations()->get() as $interpretation) {
            // Удаляем результат анализа и связанные сны серии
            if ($interpretation->result) {
                $interpretation->result->seriesDreams()->delete();
                $interpretation->result->delete();
            }
            $interpretation->delete();
        }
        
        // 2. Удаляем отчеты и связанные сны
        foreach ($user->reports as $report) {
            $report->dreams()->delete(); // Сны в отчете
            $report->tags()->detach(); // Связи с тегами
            $report->comments()->delete(); // Комментарии к отчету
            $report->delete();
        }
        
        // 3. Удаляем комментарии пользователя
        $user->comments()->delete();
        
        // 4. Удаляем дружеские связи
        $user->friendships()->delete();
        \App\Models\Friendship::where('friend_id', $user->id)->delete();
        
        // 5. Удаляем уведомления
        $user->notifications()->delete();
        
        // 6. Удаляем самого пользователя
        $user->delete();

        return redirect()->route('admin.users')->with('success', "Пользователь {$nickname} и весь его контент удалены");
    }

    /**
     * Управление отчетами
     */
    public function reports(Request $request): View
    {
        $query = Report::with(['user', 'dreams', 'tags']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('dreams', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $reports = $query->latest()->paginate(20);
        $users = User::orderBy('nickname')->get(['id', 'nickname']);

        return view('admin.reports', compact('reports', 'users'));
    }

    /**
     * Управление комментариями
     */
    public function comments(Request $request): View
    {
        $query = Comment::with(['user', 'report.user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('content', 'like', "%{$search}%");
        }

        $comments = $query->latest()->paginate(20);

        return view('admin.comments', compact('comments'));
    }

    /**
     * Удаление комментария (админ)
     */
    public function deleteComment(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Комментарий удален');
    }

    /**
     * Настройки системы
     */
    public function settings(): View
    {
        $settings = [
            'allow_report_deletion' => Setting::getValue('allow_report_deletion', true),
            'edit_dreams_after_days' => Setting::getValue('edit_dreams_after_days', null),
            'diary_spoiler_min_length' => Setting::getValue('diary_spoiler_min_length', 1000),
            'deepseek_api_key' => Setting::getValue('deepseek_api_key', ''),
            'deepseek_http_timeout' => Setting::getValue('deepseek_http_timeout', 600),
            'deepseek_php_execution_timeout' => Setting::getValue('deepseek_php_execution_timeout', 660),
            'timezone' => Setting::getValue('timezone', 'UTC'),
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Сохранение настроек системы
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'allow_report_deletion' => ['nullable', 'boolean'],
            'edit_dreams_after_days' => ['nullable', 'integer', 'min:0'],
            'diary_spoiler_min_length' => ['nullable', 'integer', 'min:0'],
            'deepseek_api_key' => ['nullable', 'string', 'max:255'],
            'deepseek_http_timeout' => ['nullable', 'integer', 'min:60', 'max:1800'],
            'deepseek_php_execution_timeout' => ['nullable', 'integer', 'min:60', 'max:1800'],
            'timezone' => ['nullable', 'string', 'timezone'],
        ]);

        Setting::setValue('allow_report_deletion', $request->boolean('allow_report_deletion', true));
        
        if ($request->filled('edit_dreams_after_days')) {
            Setting::setValue('edit_dreams_after_days', $request->edit_dreams_after_days);
        } else {
            Setting::where('key', 'edit_dreams_after_days')->delete();
        }

        if ($request->filled('diary_spoiler_min_length')) {
            Setting::setValue('diary_spoiler_min_length', $request->diary_spoiler_min_length);
        } else {
            Setting::setValue('diary_spoiler_min_length', 1000); // Значение по умолчанию
        }

        if ($request->filled('deepseek_api_key')) {
            Setting::setValue('deepseek_api_key', $request->deepseek_api_key);
        }

        // Сохранение таймаутов для DeepSeek API
        if ($request->filled('deepseek_http_timeout')) {
            Setting::setValue('deepseek_http_timeout', $request->deepseek_http_timeout);
        } else {
            Setting::setValue('deepseek_http_timeout', 600); // Значение по умолчанию
        }

        if ($request->filled('deepseek_php_execution_timeout')) {
            Setting::setValue('deepseek_php_execution_timeout', $request->deepseek_php_execution_timeout);
        } else {
            Setting::setValue('deepseek_php_execution_timeout', 660); // Значение по умолчанию
        }

        // Сохранение часового пояса
        if ($request->filled('timezone')) {
            Setting::setValue('timezone', $request->timezone);
        } else {
            Setting::setValue('timezone', 'UTC'); // Значение по умолчанию
        }

        return back()->with('success', 'Настройки сохранены');
    }

    /**
     * Статистика по толкованиям снов
     */
    public function interpretations(Request $request): View
    {
        // Получаем часовой пояс из настроек (валидная строка, иначе UTC)
        $timezoneRaw = Setting::getValue('timezone', 'UTC');
        $timezone = is_string($timezoneRaw) && $timezoneRaw !== '' ? $timezoneRaw : 'UTC';
        if (!@timezone_open($timezone)) {
            $timezone = 'UTC';
        }

        // Создаем Carbon экземпляры с учетом часового пояса
        $now = \Carbon\Carbon::now($timezone);
        
        // Период по умолчанию - 30 дней (в локальном времени)
        $startDate = $request->filled('start_date') 
            ? $request->start_date 
            : $now->copy()->subDays(30)->format('Y-m-d');
        $endDate = $request->filled('end_date') 
            ? $request->end_date 
            : $now->format('Y-m-d');
        
        // Конвертируем даты из локального времени в UTC для запросов к БД
        // created_at в БД хранится в UTC, поэтому нужно конвертировать границы периода
        $startDateUtc = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate, $timezone)
            ->startOfDay()
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');
        $endDateUtc = \Carbon\Carbon::createFromFormat('Y-m-d', $endDate, $timezone)
            ->endOfDay()
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');

        // Общая статистика и за период — из лёгкой таблицы dream_interpretation_stats
        $totalCreated = DreamInterpretationStat::count();
        $totalCompleted = DreamInterpretationStat::where('processing_status', 'completed')->count();
        $totalPending = DreamInterpretationStat::where('processing_status', 'pending')->count();
        $totalFailed = DreamInterpretationStat::where('processing_status', 'failed')->count();

        $periodCreated = DreamInterpretationStat::whereBetween('interpretation_created_at', [$startDateUtc, $endDateUtc])->count();
        $periodCompleted = DreamInterpretationStat::whereBetween('interpretation_created_at', [$startDateUtc, $endDateUtc])
            ->where('processing_status', 'completed')->count();
        $periodPending = DreamInterpretationStat::whereBetween('interpretation_created_at', [$startDateUtc, $endDateUtc])
            ->where('processing_status', 'pending')->count();
        $periodFailed = DreamInterpretationStat::whereBetween('interpretation_created_at', [$startDateUtc, $endDateUtc])
            ->where('processing_status', 'failed')->count();

        // Статистика по традициям за период — из stats (лёгкая таблица)
        $traditionsStats = DreamInterpretationStat::whereBetween('interpretation_created_at', [$startDateUtc, $endDateUtc])
            ->whereNotNull('traditions')
            ->get(['traditions'])
            ->flatMap(function ($stat) {
                $traditions = $stat->traditions ?? [];
                if (empty($traditions)) {
                    return [['tradition' => 'eclectic', 'count' => 1]];
                }
                return array_map(fn ($t) => ['tradition' => $t, 'count' => 1], $traditions);
            })
            ->groupBy('tradition')
            ->map(fn ($group) => $group->sum('count'))
            ->sortDesc();

        // Фильтры
        $statusFilter = $request->filled('status') ? $request->status : null;
        $traditionFilter = $request->filled('tradition') ? $request->tradition : null;
        $searchFilter = $request->filled('search') ? $request->search : null;

        // Статистика по дням за период — из stats, группировка в PHP по дате в локальном времени
        $dailyStatsQuery = DreamInterpretationStat::whereBetween('interpretation_created_at', [$startDateUtc, $endDateUtc]);
        if ($statusFilter) {
            $dailyStatsQuery->where('processing_status', $statusFilter);
        }
        if ($traditionFilter) {
            $dailyStatsQuery->whereJsonContains('traditions', $traditionFilter);
        }
        $allStats = $dailyStatsQuery->get(['interpretation_created_at', 'processing_status']);
        $dailyStats = $allStats->groupBy(function ($stat) use ($timezone) {
            return \Carbon\Carbon::parse($stat->interpretation_created_at)->setTimezone($timezone)->format('Y-m-d');
        })->map(function ($group, $date) {
            return (object)[
                'date' => $date,
                'total' => $group->count(),
                'completed' => $group->where('processing_status', 'completed')->count(),
                'pending' => $group->where('processing_status', 'pending')->count(),
                'failed' => $group->where('processing_status', 'failed')->count(),
            ];
        })->sortKeysDesc()->values();

        // Детализация по выбранной дате
        $selectedDate = $request->filled('date') ? $request->date : null;
        $dayInterpretations = null;
        
        if ($selectedDate) {
            // Конвертируем выбранную дату (в локальном времени) в UTC для запроса к БД
            $selectedDateStart = \Carbon\Carbon::createFromFormat('Y-m-d', $selectedDate, $timezone)
                ->startOfDay()
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
            $selectedDateEnd = \Carbon\Carbon::createFromFormat('Y-m-d', $selectedDate, $timezone)
                ->endOfDay()
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
            
            $dayQuery = DreamInterpretation::whereBetween('created_at', [$selectedDateStart, $selectedDateEnd]);

            if ($statusFilter) {
                $dayQuery->where('processing_status', $statusFilter);
            }
            if ($traditionFilter) {
                $dayQuery->whereJsonContains('traditions', $traditionFilter);
            }
            if ($searchFilter) {
                $dayQuery->where('dream_description', 'like', '%' . $searchFilter . '%');
            }

            $dayInterpretations = $dayQuery
                ->select('id', 'hash', 'created_at', 'processing_status', 'traditions', 'report_id', 'ip_address')
                ->with('report:id')
                ->orderBy('created_at', 'desc')
                ->paginate(50)
                ->withQueryString();
        }

        // Список толкований за период (без выбора даты)
        if (!$selectedDate) {
            $query = DreamInterpretation::whereBetween('created_at', [$startDateUtc, $endDateUtc]);

            if ($statusFilter) {
                $query->where('processing_status', $statusFilter);
            }
            if ($traditionFilter) {
                $query->whereJsonContains('traditions', $traditionFilter);
            }
            if ($searchFilter) {
                $query->where('dream_description', 'like', '%' . $searchFilter . '%');
            }

            $interpretations = $query
                ->select('id', 'hash', 'created_at', 'processing_status', 'traditions', 'report_id', 'ip_address')
                ->with('report:id')
                ->orderBy('created_at', 'desc')
                ->paginate(50)
                ->withQueryString();
        } else {
            $interpretations = null;
        }

        // Получаем названия традиций из конфига
        $traditionsConfig = config('traditions', []);

        return view('admin.interpretations', compact(
            'totalCreated',
            'totalCompleted',
            'totalPending',
            'totalFailed',
            'periodCreated',
            'periodCompleted',
            'periodPending',
            'periodFailed',
            'traditionsStats',
            'dailyStats',
            'selectedDate',
            'dayInterpretations',
            'interpretations',
            'startDate',
            'endDate',
            'statusFilter',
            'traditionFilter',
            'traditionsConfig',
            'timezone'
        ));
    }

    /**
     * Сущности толкований (символы, локации, теги). Опционально — топ за выбранную дату.
     */
    public function entities(Request $request): View
    {
        $totalRows = DreamInterpretationEntity::count();
        $countByType = DreamInterpretationEntity::selectRaw('type, count(*) as cnt')
            ->groupBy('type')
            ->pluck('cnt', 'type')
            ->toArray();

        // Уникальные сущности по типу (разные slug) и всего (разные пары type+slug)
        $uniqueByType = [
            'symbol' => (int) DreamInterpretationEntity::where('type', 'symbol')->selectRaw('COUNT(DISTINCT slug) as c')->value('c'),
            'location' => (int) DreamInterpretationEntity::where('type', 'location')->selectRaw('COUNT(DISTINCT slug) as c')->value('c'),
            'tag' => (int) DreamInterpretationEntity::where('type', 'tag')->selectRaw('COUNT(DISTINCT slug) as c')->value('c'),
        ];
        $totalUnique = $uniqueByType['symbol'] + $uniqueByType['location'] + $uniqueByType['tag'];

        $limit = 100;
        $date = $request->filled('date') ? trim((string) $request->date) : null;
        if ($date === '') {
            $date = null;
        }
        // Параметр «q», не «search»: на части хостингов/WAF параметр search отфильтровывается, date при этом доходит
        $search = $request->filled('q') ? trim((string) $request->get('q')) : null;

        if ($search !== null && $search !== '') {
            $symbols = DreamInterpretationEntity::uniqueWithCounts(DreamInterpretationEntity::TYPE_SYMBOL, $limit, $search);
            $locations = DreamInterpretationEntity::uniqueWithCounts(DreamInterpretationEntity::TYPE_LOCATION, $limit, $search);
            $tags = DreamInterpretationEntity::uniqueWithCounts(DreamInterpretationEntity::TYPE_TAG, $limit, $search);
            $branch = 'search';
        } elseif ($date) {
            $symbols = DreamEntityDaily::topForDate(DreamInterpretationEntity::TYPE_SYMBOL, $date, $limit);
            $locations = DreamEntityDaily::topForDate(DreamInterpretationEntity::TYPE_LOCATION, $date, $limit);
            $tags = DreamEntityDaily::topForDate(DreamInterpretationEntity::TYPE_TAG, $date, $limit);
            $branch = 'date';
        } else {
            $symbols = DreamInterpretationEntity::uniqueWithCounts(DreamInterpretationEntity::TYPE_SYMBOL, $limit);
            $locations = DreamInterpretationEntity::uniqueWithCounts(DreamInterpretationEntity::TYPE_LOCATION, $limit);
            $tags = DreamInterpretationEntity::uniqueWithCounts(DreamInterpretationEntity::TYPE_TAG, $limit);
            $branch = 'default';
        }

        // Временная диагностика (убрать после выяснения)
        $requestDebug = [
            'query' => $request->query(),
            'get' => $_GET,
            'query_string' => $_SERVER['QUERY_STRING'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'search_value' => $search,
            'branch' => $branch,
            'counts' => ['symbols' => count($symbols), 'locations' => count($locations), 'tags' => count($tags)],
        ];

        $slugs = collect($symbols)->pluck('slug')->merge(collect($locations)->pluck('slug'))->merge(collect($tags)->pluck('slug'))->unique()->filter()->values()->toArray();
        $slugToGroup = EntityGroupMapping::slugsToGroups($slugs);
        $entityGroups = EntityGroup::orderBy('name')->get(['id', 'name']);

        return view('admin.entities', compact(
            'totalRows',
            'countByType',
            'uniqueByType',
            'totalUnique',
            'symbols',
            'locations',
            'tags',
            'date',
            'search',
            'slugToGroup',
            'entityGroups',
            'requestDebug'
        ));
    }

    /**
     * Скачать список уникальных сущностей выбранного типа (символы, локации, теги) в виде .txt для анализа.
     */
    public function entitiesExport(Request $request): Response
    {
        $type = $request->get('type', '');
        $allowed = [DreamInterpretationEntity::TYPE_SYMBOL, DreamInterpretationEntity::TYPE_LOCATION, DreamInterpretationEntity::TYPE_TAG];
        if (!in_array($type, $allowed, true)) {
            abort(400, 'Недопустимый тип');
        }

        $names = DreamInterpretationEntity::where('type', $type)
            ->selectRaw('MAX(name) as name')
            ->groupBy('slug')
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->values();

        $content = $names->implode("\n");
        $labels = [
            DreamInterpretationEntity::TYPE_SYMBOL => 'symbols',
            DreamInterpretationEntity::TYPE_LOCATION => 'locations',
            DreamInterpretationEntity::TYPE_TAG => 'tags',
        ];
        $filename = 'entities-' . $labels[$type] . '.txt';

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Список групп сущностей и форма добавления (textarea).
     */
    public function entityGroupsIndex(Request $request): View
    {
        $sort = $request->get('sort', 'name');
        $order = strtolower($request->get('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (!in_array($sort, ['name', 'count'], true)) {
            $sort = 'name';
        }

        $query = EntityGroup::withCount('mappings')->with('mappings');
        if ($sort === 'name') {
            $query->orderBy('name', $order);
        } else {
            $query->orderBy('mappings_count', $order);
        }
        $groups = $query->get();

        return view('admin.entity-groups-index', compact('groups', 'sort', 'order'));
    }

    /**
     * Экспорт групп в .txt в формате для импорта (одна строка = одна группа: «Название, сущность1, сущность2»).
     * В экспорт попадают только названия сущностей (не slug).
     */
    public function entityGroupsExport(): Response
    {
        $groups = EntityGroup::with('mappings')->orderBy('name')->get();
        $allSlugs = $groups->pluck('mappings')->flatten(1)->pluck('entity_slug')->unique()->filter()->values()->toArray();
        $slugToName = empty($allSlugs) ? [] : DreamInterpretationEntity::whereIn('slug', $allSlugs)
            ->selectRaw('slug, MAX(name) as name')
            ->groupBy('slug')
            ->pluck('name', 'slug')
            ->toArray();

        $lines = [];
        foreach ($groups as $group) {
            $parts = [$group->name];
            foreach ($group->mappings as $m) {
                $name = $m->entity_name && trim($m->entity_name) !== ''
                    ? trim($m->entity_name)
                    : ($slugToName[$m->entity_slug] ?? $m->entity_slug);
                $parts[] = $name;
            }
            $lines[] = implode(', ', $parts);
        }
        $content = implode("\n", $lines);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="entity-groups-export.txt"',
        ]);
    }

    /**
     * Сохранить группы из textarea: одна строка = одна группа, "Название, сущность2, сущность3".
     */
    public function entityGroupsStore(Request $request): RedirectResponse
    {
        $request->validate(['lines' => 'required|string|max:50000']);
        $lines = array_filter(preg_split('/\r\n|\r|\n/', $request->input('lines')));
        $created = 0;
        foreach ($lines as $line) {
            $parts = array_map(function ($p) {
                return mb_strtolower(trim($p), 'UTF-8');
            }, explode(',', $line));
            $parts = array_unique(array_filter($parts));
            if (count($parts) === 0) {
                continue;
            }
            $groupName = $parts[0];
            $slugToName = [];
            foreach ($parts as $part) {
                $slug = DreamInterpretationEntity::nameToSlug($part);
                if ($slug !== 'n-a') {
                    $slugToName[$slug] = $part;
                }
            }
            if (empty($slugToName)) {
                continue;
            }
            $groupSlug = EntityGroup::nameToSlug($groupName);
            $group = EntityGroup::firstOrCreate(
                ['slug' => $groupSlug],
                ['name' => $groupName]
            );
            if (!$group->wasRecentlyCreated) {
                $group->update(['name' => $groupName]);
            }
            foreach ($slugToName as $slug => $name) {
                EntityGroupMapping::where('entity_slug', $slug)->delete();
                EntityGroupMapping::create([
                    'entity_group_id' => $group->id,
                    'entity_slug' => $slug,
                    'entity_name' => $name,
                ]);
            }
            $created++;
        }
        return redirect()->route('admin.entities.groups.index')->with('success', "Обработано групп: {$created}.");
    }

    /**
     * Редактирование группы: название и список сущностей.
     */
    public function entityGroupEdit(EntityGroup $entity_group): View
    {
        $entity_group->load('mappings');
        $groups = EntityGroup::orderBy('name')->get(['id', 'name']);
        $slugs = $entity_group->mappings->pluck('entity_slug')->unique()->filter()->values()->toArray();
        $slugToName = empty($slugs) ? [] : DreamInterpretationEntity::whereIn('slug', $slugs)
            ->selectRaw('slug, MAX(name) as name')
            ->groupBy('slug')
            ->pluck('name', 'slug')
            ->toArray();
        return view('admin.entity-group-edit', compact('entity_group', 'groups', 'slugToName'));
    }

    /**
     * Обновить название группы.
     */
    public function entityGroupUpdate(Request $request, EntityGroup $entity_group): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:500']);
        $entity_group->update(['name' => $request->input('name')]);
        return redirect()->route('admin.entities.groups.edit', $entity_group)->with('success', 'Название группы обновлено.');
    }

    /**
     * Удалить группу: группа и все привязки удаляются, сущности становятся свободными (без группы).
     */
    public function entityGroupDestroy(EntityGroup $entity_group): RedirectResponse
    {
        $name = $entity_group->name;
        $entity_group->delete();
        return redirect()->route('admin.entities.groups.index', request()->only(['sort', 'order']))->with('success', "Группа «{$name}» удалена. Сущности отвязаны и стали свободными.");
    }

    /**
     * Добавить одну сущность в группу (по slug или названию).
     */
    public function entityGroupAddMapping(Request $request, EntityGroup $entity_group): RedirectResponse
    {
        $request->validate(['entity' => 'required|string|max:500']);
        $input = trim($request->input('entity'));
        $slug = DreamInterpretationEntity::nameToSlug($input);
        if ($slug === 'n-a') {
            return redirect()->back()->with('error', 'Не удалось получить slug из введённого значения.');
        }
        EntityGroupMapping::where('entity_slug', $slug)->delete();
        EntityGroupMapping::create([
            'entity_group_id' => $entity_group->id,
            'entity_slug' => $slug,
            'entity_name' => $input,
        ]);
        return redirect()->back()->with('success', "Сущность «{$input}» добавлена в группу.");
    }

    /**
     * Удалить сущность из группы.
     */
    public function entityGroupRemoveMapping(EntityGroupMapping $mapping): RedirectResponse
    {
        $group = $mapping->group;
        $mapping->delete();
        return redirect()->route('admin.entities.groups.edit', $group)->with('success', 'Сущность удалена из группы.');
    }

    /**
     * Добавить сущность в группу со страницы поиска сущностей (один slug — во всех типах).
     */
    public function entitiesAddToGroup(Request $request): RedirectResponse
    {
        $request->validate([
            'entity_slug' => 'required|string|max:255',
            'entity_group_id' => 'required|exists:entity_groups,id',
            'entity_name' => 'nullable|string|max:500',
        ]);
        $slug = $request->input('entity_slug');
        $groupId = (int) $request->input('entity_group_id');
        $name = trim((string) $request->input('entity_name', ''));
        if ($name === '') {
            $name = DreamInterpretationEntity::where('slug', $slug)->value('name') ?? $slug;
        }
        EntityGroupMapping::where('entity_slug', $slug)->delete();
        EntityGroupMapping::create([
            'entity_group_id' => $groupId,
            'entity_slug' => $slug,
            'entity_name' => $name,
        ]);
        return redirect()->back()->with('success', 'Сущность добавлена в группу.');
    }

    /**
     * Создать новую группу с именем сущности и добавить эту сущность в неё.
     */
    public function entitiesCreateGroupFromEntity(Request $request): RedirectResponse
    {
        $request->validate([
            'entity_slug' => 'required|string|max:255',
            'entity_name' => 'required|string|max:500',
        ]);
        $slug = $request->input('entity_slug');
        $name = trim($request->input('entity_name'));
        if ($name === '') {
            return redirect()->back()->with('error', 'Название сущности пусто.');
        }
        $groupSlug = EntityGroup::nameToSlug($name);
        $group = EntityGroup::firstOrCreate(
            ['slug' => $groupSlug],
            ['name' => $name]
        );
        if (!$group->wasRecentlyCreated) {
            $group->update(['name' => $name]);
        }
        EntityGroupMapping::where('entity_slug', $slug)->delete();
        EntityGroupMapping::create([
            'entity_group_id' => $group->id,
            'entity_slug' => $slug,
            'entity_name' => $name,
        ]);
        return redirect()->back()->with('success', "Создана группа «{$name}» и сущность добавлена в неё.");
    }

    /**
     * Сравнение сущностей за два дня (динамика).
     */
    public function entitiesCompare(Request $request): View
    {
        $date1 = $request->get('date1', now()->subDays(2)->format('Y-m-d'));
        $date2 = $request->get('date2', now()->subDay()->format('Y-m-d'));
        $limit = 80;

        $merge = function (array $day1, array $day2) use ($limit): array {
            $key = fn ($r) => $r['slug'] ?? $r['name'] ?? '';
            $bySlug1 = collect($day1)->keyBy($key);
            $bySlug2 = collect($day2)->keyBy($key);
            $slugs = $bySlug1->keys()->merge($bySlug2->keys())->unique()->filter();
            return $slugs->map(function ($slug) use ($bySlug1, $bySlug2) {
                $r1 = $bySlug1->get($slug);
                $r2 = $bySlug2->get($slug);
                $name = $r1['name'] ?? $r2['name'] ?? $slug;
                $m1 = (int) ($r1['mentions'] ?? 0);
                $m2 = (int) ($r2['mentions'] ?? 0);
                return ['name' => $name, 'mentions1' => $m1, 'mentions2' => $m2, 'diff' => $m2 - $m1];
            })->sortByDesc(fn ($r) => max($r['mentions1'], $r['mentions2']))->values()->take($limit)->toArray();
        };

        $symbolsDay1 = DreamEntityDaily::topForDate(DreamInterpretationEntity::TYPE_SYMBOL, $date1, $limit);
        $symbolsDay2 = DreamEntityDaily::topForDate(DreamInterpretationEntity::TYPE_SYMBOL, $date2, $limit);
        $locationsDay1 = DreamEntityDaily::topForDate(DreamInterpretationEntity::TYPE_LOCATION, $date1, $limit);
        $locationsDay2 = DreamEntityDaily::topForDate(DreamInterpretationEntity::TYPE_LOCATION, $date2, $limit);
        $tagsDay1 = DreamEntityDaily::topForDate(DreamInterpretationEntity::TYPE_TAG, $date1, $limit);
        $tagsDay2 = DreamEntityDaily::topForDate(DreamInterpretationEntity::TYPE_TAG, $date2, $limit);

        $symbols = $merge($symbolsDay1, $symbolsDay2);
        $locations = $merge($locationsDay1, $locationsDay2);
        $tags = $merge($tagsDay1, $tagsDay2);

        return view('admin.entities-compare', compact(
            'date1',
            'date2',
            'symbols',
            'locations',
            'tags'
        ));
    }

    /**
     * Динамика по одной сущности: как менялось число упоминаний по дням за период.
     */
    public function entitiesDynamics(Request $request): View
    {
        $type = $request->get('type', 'symbol');
        $slug = $request->get('slug', '');
        $to = $request->get('to', now()->format('Y-m-d'));
        $from = $request->get('from', now()->subDays(30)->format('Y-m-d'));

        if ($slug === '') {
            return view('admin.entities-dynamics', [
                'type' => $type,
                'slug' => '',
                'entityName' => null,
                'from' => $from,
                'to' => $to,
                'daily' => [],
            ]);
        }

        $daily = DreamEntityDaily::mentionsOverPeriod($type, $slug, $from, $to);

        if (empty($daily)) {
            $start = $from . ' 00:00:00';
            $end = $to . ' 23:59:59';
            $rows = DreamInterpretationEntity::where('type', $type)
                ->where('slug', $slug)
                ->whereBetween('interpretation_created_at', [$start, $end])
                ->selectRaw('DATE(interpretation_created_at) as date, COUNT(*) as mentions')
                ->groupByRaw('DATE(interpretation_created_at)')
                ->orderBy('date')
                ->get();
            $daily = $rows->map(fn ($r) => [
                'date' => \Carbon\Carbon::parse($r->date)->format('Y-m-d'),
                'mentions' => (int) $r->mentions,
            ])->toArray();
        }

        $entityName = DreamEntityDaily::nameFor($type, $slug)
            ?? DreamInterpretationEntity::where('type', $type)->where('slug', $slug)->value('name')
            ?? $slug;

        return view('admin.entities-dynamics', compact(
            'type',
            'slug',
            'entityName',
            'from',
            'to',
            'daily'
        ));
    }

    /**
     * Удаление толкования снов
     */
    public function deleteInterpretation(Request $request, DreamInterpretation $interpretation): RedirectResponse
    {
        // Удаляем связанный результат (если есть) - каскадное удаление должно сработать автоматически,
        // но на всякий случай удаляем вручную для надежности
        if ($interpretation->result) {
            // Удаляем связанные сны серии (если есть)
            $interpretation->result->seriesDreams()->delete();
            $interpretation->result->delete();
        }
        
        // Удаляем связанные SEO записи (если есть)
        // Для толкований снов
        \App\Models\SeoMeta::where('page_type', 'dream-analyzer-result')
            ->where('page_id', $interpretation->id)
            ->delete();
        
        // Для анализов отчетов
        \App\Models\SeoMeta::where('page_type', 'report-analysis')
            ->where('page_id', $interpretation->id)
            ->delete();

        // Удаляем само толкование
        $interpretation->delete();

        // Возвращаемся на ту же страницу с теми же параметрами
        $queryParams = $request->only(['start_date', 'end_date', 'date', 'status', 'tradition', 'page']);

        return redirect()->route('admin.interpretations', $queryParams)
            ->with('success', 'Толкование успешно удалено');
    }
}
