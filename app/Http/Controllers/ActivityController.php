<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Comment;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Лента активности
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $filter = $request->get('filter', 'all'); // all, friends

        // Получаем ID друзей (только для авторизованных)
        $friendIds = [];
        if ($user && $filter === 'friends') {
            $friendships = \App\Models\Friendship::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'accepted');
            })->orWhere(function ($query) use ($user) {
                $query->where('friend_id', $user->id)
                    ->where('status', 'accepted');
            })->get();

            $friendIds = $friendships->map(function ($friendship) use ($user) {
                return $friendship->user_id === $user->id ? $friendship->friend_id : $friendship->user_id;
            })->toArray();
        }

        // Получаем последние комментарии
        $commentsQuery = Comment::with(['user', 'report.user'])
            ->where('parent_id', null) // Только корневые комментарии
            ->whereHas('report', function ($q) use ($user, $friendIds, $filter) {
                $q->where('status', 'published')
                  ->where('access_level', '!=', 'none');
                
                // Применяем фильтры по доступу
                if ($user && $filter === 'friends') {
                    // Для авторизованных: только отчеты друзей
                    $q->whereIn('user_id', $friendIds)
                      ->where(function ($query) {
                          $query->where('access_level', 'all')
                                ->orWhere('access_level', 'friends');
                      });
                } else {
                    // Для всех: отчеты с access_level = 'all' (публичные отчеты)
                    // Для авторизованных: еще и отчеты друзей и свои
                    if ($user) {
                        $q->where(function ($query) use ($user, $friendIds) {
                            // Публичные отчеты (видны всем)
                            $query->where('access_level', 'all')
                                  // Отчеты друзей (если доступны друзьям)
                                  ->orWhere(function ($q) use ($friendIds) {
                                      $q->whereIn('user_id', $friendIds)
                                        ->where('access_level', 'friends');
                                  })
                                  // Свои отчеты
                                  ->orWhere('user_id', $user->id);
                        });
                    } else {
                        // Для неавторизованных: только публичные отчеты
                        $q->where('access_level', 'all');
                    }
                }
            })
            ->orderBy('created_at', 'desc')
            ->limit(20);

        $comments = $commentsQuery->get();

        // Получаем последние отчеты (только опубликованные)
        $reportsQuery = Report::with(['user', 'dreams', 'tags'])
            ->where('status', 'published')
            ->where('access_level', '!=', 'none');
        
        // Применяем фильтры по доступу
        if ($user && $filter === 'friends') {
            // Для авторизованных: только отчеты друзей
            $reportsQuery->whereIn('user_id', $friendIds)
                ->where(function ($query) {
                    $query->where('access_level', 'all')
                          ->orWhere('access_level', 'friends');
                });
        } else {
            // Для всех: отчеты с access_level = 'all' (публичные отчеты)
            // Для авторизованных: еще и отчеты друзей и свои
            if ($user) {
                $reportsQuery->where(function ($query) use ($user, $friendIds) {
                    // Публичные отчеты (видны всем)
                    $query->where('access_level', 'all')
                          // Отчеты друзей (если доступны друзьям)
                          ->orWhere(function ($q) use ($friendIds) {
                              $q->whereIn('user_id', $friendIds)
                                ->where('access_level', 'friends');
                          })
                          // Свои отчеты
                          ->orWhere('user_id', $user->id);
                });
            } else {
                // Для неавторизованных: только публичные отчеты
                $reportsQuery->where('access_level', 'all');
            }
        }
        
        $reportsQuery->orderBy('created_at', 'desc')
            ->limit(20);

        $reports = $reportsQuery->get();

        // Объединяем и сортируем по дате
        $activities = collect()
            ->merge($comments->map(function ($comment) {
                return [
                    'type' => 'comment',
                    'item' => $comment,
                    'date' => $comment->created_at,
                ];
            }))
            ->merge($reports->map(function ($report) {
                return [
                    'type' => 'report',
                    'item' => $report,
                    'date' => $report->created_at,
                ];
            }))
            ->sortByDesc('date')
            ->take(30);

        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = \Illuminate\Support\Facades\Cache::remember('global_statistics', 900, function () {
            return [
                'users' => \App\Models\User::count(),
                'reports' => Report::where('status', 'published')->count(),
                'dreams' => \Illuminate\Support\Facades\DB::table('dreams')
                    ->join('reports', 'dreams.report_id', '=', 'reports.id')
                    ->where('reports.status', 'published')
                    ->count(),
                'comments' => Comment::count(),
                'tags' => \App\Models\Tag::count(),
                'avg_dreams_per_report' => Report::where('status', 'published')
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
        $todayReportsCount = 0;
        
        if ($user) {
            // Статистика пользователя
            $userReportsCount = $user->reports()->count();
            $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
            
            // Подсчет друзей
            $allFriendships = \App\Models\Friendship::where(function ($query) use ($user) {
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
            if (!empty($allFriendIds)) {
                $friendsOnline = \App\Models\User::whereIn('id', $allFriendIds)
                    ->whereHas('reports', function ($q) {
                        $q->whereDate('created_at', '>=', now()->subDays(7));
                    })
                    ->with(['reports' => function ($q) {
                        $q->whereDate('created_at', '>=', now()->subDays(7))->latest()->limit(1);
                    }])
                    ->limit(5)
                    ->get();
            }
            
            // Количество отчетов, добавленных сегодня другими пользователями
            $todayReportsCount = Report::where('status', 'published')
                ->where('access_level', 'all')
                ->whereDate('created_at', today())
                ->where('user_id', '!=', $user->id)
                ->count();
            
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
        } else {
            $todayReportsCount = Report::where('status', 'published')
                ->where('access_level', 'all')
                ->whereDate('created_at', today())
                ->count();
        }

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
        $seo = SeoHelper::get('activity');

        return view('activity.index', compact('activities', 'filter', 'stats', 'globalStats', 'userStats', 'friendsOnline', 'todayReportsCount', 'popularTags', 'dreamDictionary', 'seo'));
    }
}
