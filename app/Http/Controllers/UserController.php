<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Публичный профиль пользователя
     */
    public function profile(User $user): View
    {
        // Если пользователь заблокирован, показываем 404 (кроме администраторов)
        if ($user->isBanned() && (!auth()->check() || !auth()->user()->isAdmin())) {
            abort(404);
        }

        $reportsCount = $user->reports()->count();
        $lastReport = $user->reports()->orderBy('report_date', 'desc')->first();
        
        // Проверяем, можем ли видеть дневник
        $canViewDiary = false;
        if (auth()->check()) {
            $currentUser = auth()->user();
            if ($currentUser->id === $user->id || $currentUser->isAdmin()) {
                $canViewDiary = true;
            } elseif ($user->diary_privacy === 'public') {
                $canViewDiary = true;
            } elseif ($user->diary_privacy === 'friends') {
                $canViewDiary = \App\Models\Friendship::where(function ($query) use ($currentUser, $user) {
                    $query->where('user_id', $currentUser->id)
                        ->where('friend_id', $user->id)
                        ->where('status', 'accepted');
                })->orWhere(function ($query) use ($currentUser, $user) {
                    $query->where('user_id', $user->id)
                        ->where('friend_id', $currentUser->id)
                        ->where('status', 'accepted');
                })->exists();
            }
        } else {
            $canViewDiary = $user->diary_privacy === 'public';
        }

        return view('users.profile', compact('user', 'reportsCount', 'lastReport', 'canViewDiary'));
    }

    /**
     * Поиск пользователей
     */
    public function search(Request $request): View
    {
        $user = auth()->user();
        
        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = \Illuminate\Support\Facades\Cache::remember('global_statistics', 900, function () {
            return [
                'users' => User::notBanned()->count(), // Только незаблокированные
                'reports' => \App\Models\Report::where('status', 'published')->count(),
                'dreams' => \Illuminate\Support\Facades\DB::table('dreams')
                    ->join('reports', 'dreams.report_id', '=', 'reports.id')
                    ->where('reports.status', 'published')
                    ->count(),
                'comments' => \App\Models\Comment::count(),
                'tags' => \App\Models\Tag::count(),
                'avg_dreams_per_report' => \App\Models\Report::where('status', 'published')
                    ->withCount('dreams')
                    ->get()
                    ->avg('dreams_count') ?: 0,
            ];
        });
        
        // Статистика проекта (для неавторизованных)
        $stats = [
            'users' => $globalStats['users'],
            'reports' => $globalStats['reports'],
            'dreams' => $globalStats['dreams'],
            'comments' => $globalStats['comments'],
            'tags' => $globalStats['tags'],
        ];
        
        // Статистика пользователя (если авторизован)
        $userStats = null;
        $friendsOnline = collect();
        $friends = collect();
        $incomingRequests = collect();
        
        if ($user) {
            // Статистика пользователя
            $userReportsCount = $user->reports()->count();
            $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
            
            // Получаем всех друзей (принятые запросы)
            $friendships = \App\Models\Friendship::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'accepted');
            })->orWhere(function ($query) use ($user) {
                $query->where('friend_id', $user->id)
                      ->where('status', 'accepted');
            })->with(['user', 'friend'])->get();

            $friends = $friendships->map(function ($friendship) use ($user) {
                return $friendship->user_id === $user->id ? $friendship->friend : $friendship->user;
            });

            // Входящие запросы
            $incomingRequests = \App\Models\Friendship::where('friend_id', $user->id)
                ->where('status', 'pending')
                ->with('user')
                ->get();
            
            // Подсчет друзей для статистики
            $friendIds = $friends->pluck('id')->toArray();
            $friendsCount = count($friendIds);
            
            // Друзья онлайн
            if (!empty($friendIds)) {
                $friendsOnline = User::whereIn('id', $friendIds)
                    ->notBanned() // Исключаем заблокированных
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
        }

        // Поиск пользователей
        $query = User::query();
        
        // Админы видят всех, обычные пользователи - только незаблокированных
        if (!$user || !$user->isAdmin()) {
            $query->notBanned();
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nickname', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Исключаем текущего пользователя
        if ($user) {
            $query->where('id', '!=', $user->id);
        }

        $users = $query->paginate(20);

        // Популярные теги (топ 6)
        $popularTags = \App\Models\Tag::withCount('reports')
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
        $seo = SeoHelper::get('users');

        return view('users.search', compact('users', 'stats', 'globalStats', 'userStats', 'friendsOnline', 'popularTags', 'dreamDictionary', 'friends', 'incomingRequests', 'seo'));
    }
}
