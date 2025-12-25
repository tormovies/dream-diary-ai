<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Report;
use App\Models\Friendship;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StatisticsController extends Controller
{
    /**
     * Статистика пользователя
     */
    public function index(): View
    {
        $user = auth()->user();

        // Общее количество отчетов
        $totalReports = $user->reports()->count();

        // Общее количество снов
        $totalDreams = $user->reports()->withCount('dreams')->get()->sum('dreams_count');

        // Среднее количество снов на отчет
        $avgDreamsPerReport = $totalReports > 0 ? round($totalDreams / $totalReports, 2) : 0;

        // Сны по дням (последние 30 дней) - считаем количество снов, а не отчетов
        $reportsByDay = DB::table('reports')
            ->join('dreams', 'reports.id', '=', 'dreams.report_id')
            ->where('reports.user_id', $user->id)
            ->where('reports.report_date', '>=', now()->subDays(30))
            ->selectRaw('DATE(reports.report_date) as date, COUNT(dreams.id) as count')
            ->groupBy('date')
            ->orderByDesc('date')
            ->get()
            ->pluck('count', 'date');

        // Статистика по типам снов
        $dreamsByType = DB::table('dreams')
            ->join('reports', 'dreams.report_id', '=', 'reports.id')
            ->where('reports.user_id', $user->id)
            ->select('dreams.dream_type', DB::raw('COUNT(*) as count'))
            ->groupBy('dreams.dream_type')
            ->orderByDesc('count')
            ->get();

        // Статистика по месяцам
        $reportsByMonth = Report::where('user_id', $user->id)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');

        // Самые используемые теги
        $topTags = DB::table('report_tag')
            ->join('reports', 'report_tag.report_id', '=', 'reports.id')
            ->join('tags', 'report_tag.tag_id', '=', 'tags.id')
            ->where('reports.user_id', $user->id)
            ->select('tags.name', DB::raw('COUNT(*) as count'))
            ->groupBy('tags.id', 'tags.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Самый активный день недели
        $reportsByWeekday = Report::where('user_id', $user->id)
            ->selectRaw("CASE DAYOFWEEK(report_date)
                WHEN 1 THEN 'Воскресенье'
                WHEN 2 THEN 'Понедельник'
                WHEN 3 THEN 'Вторник'
                WHEN 4 THEN 'Среда'
                WHEN 5 THEN 'Четверг'
                WHEN 6 THEN 'Пятница'
                WHEN 7 THEN 'Суббота'
            END as weekday, COUNT(*) as count")
            ->groupBy('weekday')
            ->orderByDesc('count')
            ->get();

        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = Cache::remember('global_statistics', 900, function () {
            return [
                'users' => User::count(),
                'reports' => Report::where('status', 'published')->count(),
                'dreams' => DB::table('dreams')
                    ->join('reports', 'dreams.report_id', '=', 'reports.id')
                    ->where('reports.status', 'published')
                    ->count(),
                'comments' => \App\Models\Comment::count(),
                'tags' => Tag::count(),
                'avg_dreams_per_report' => Report::where('status', 'published')
                    ->withCount('dreams')
                    ->get()
                    ->avg('dreams_count') ?: 0,
            ];
        });

        // Статистика пользователя
        $userReportsCount = $user->reports()->count();
        $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
        
        // Подсчет друзей
        $allFriendships = Friendship::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('status', 'accepted');
        })->orWhere(function ($query) use ($user) {
            $query->where('friend_id', $user->id)
                ->where('status', 'accepted');
        })->get();

        $allFriendIds = $allFriendships->map(function ($friendship) use ($user) {
            return $friendship->user_id === $user->id ? $friendship->friend_id : $friendship->user_id;
        })->toArray();
        
        $friendsCount = count($allFriendIds);
        
        // Друзья онлайн
        $friendsOnline = collect();
        if (!empty($allFriendIds)) {
            $friendsOnline = User::whereIn('id', $allFriendIds)
                ->whereHas('reports', function ($q) {
                    $q->whereDate('created_at', '>=', now()->subDays(7));
                })
                ->with(['reports' => function ($q) {
                    $q->whereDate('created_at', '>=', now()->subDays(7))->latest()->limit(1);
                }])
                ->limit(5)
                ->get();
        }
        
        // Среднее количество снов в месяц
        $firstReport = $user->reports()->orderBy('report_date')->first();
        if ($firstReport) {
            $monthsDiff = $firstReport->report_date->diffInMonths(now());
            $avgDreamsPerMonth = $monthsDiff > 0 ? round($userDreamsCount / max($monthsDiff, 1), 1) : $userDreamsCount;
        } else {
            $avgDreamsPerMonth = 0;
        }
        
        $userStats = [
            'reports' => $userReportsCount,
            'dreams' => $userDreamsCount,
            'friends' => $friendsCount,
            'avg_per_month' => $avgDreamsPerMonth,
        ];

        // Популярные теги (топ 6)
        $popularTags = Tag::withCount('reports')
            ->orderByDesc('reports_count')
            ->limit(6)
            ->get();

        // Сонник (статичные данные)
        $dreamDictionary = [
            ['symbol' => 'Летать', 'meaning' => 'Символизирует свободу, стремление к независимости, преодоление препятствий. Часто снится в периоды важных жизненных изменений.'],
            ['symbol' => 'Вода', 'meaning' => 'Олицетворяет эмоции, подсознание, очищение и перерождение. Чистая вода — к душевному покою, мутная — к внутренним конфликтам.'],
            ['symbol' => 'Дом', 'meaning' => 'Отражение вашего внутреннего мира. Исследование дома во сне означает самопознание и внутренний рост.'],
            ['symbol' => 'Потеряться', 'meaning' => 'Указывает на чувство растерянности в реальной жизни, поиск своего пути или необходимость принятия важного решения.'],
        ];

        // SEO данные
        $seo = SeoHelper::get('statistics');

        return view('statistics.index', compact(
            'totalReports',
            'totalDreams',
            'avgDreamsPerReport',
            'reportsByDay',
            'dreamsByType',
            'reportsByMonth',
            'topTags',
            'reportsByWeekday',
            'globalStats',
            'userStats',
            'friendsOnline',
            'popularTags',
            'dreamDictionary',
            'seo'
        ));
    }
}
