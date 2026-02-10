<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\DreamInterpretation;
use App\Models\DreamInterpretationStat;
use App\Models\Report;
use App\Models\Setting;
use App\Models\User;
use App\Rules\NoSpam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        foreach ($user->dreamInterpretations as $interpretation) {
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
